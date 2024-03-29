<?php


namespace Rudl\LibGitDb;


use Laminas\Diactoros\Uri;
use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Rudl\LibGitDb\Ex\AccessDeniedException;
use Rudl\LibGitDb\Ex\GeneralAccessException;
use Rudl\LibGitDb\Type\Transport\T_FileList;
use Rudl\LibGitDb\Type\Transport\T_Log;
use Rudl\LibGitDb\Type\Transport\T_ObjectList;

class RudlGitDbClient
{

    private string $endpointUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $endpointUrl = null)
    {

    }

    /**
     * Use for development only. Use loadClientConfigFromEnv for production
     *
     * @param $endpointUrl
     */
    public function setEndpointDev($endpointUrl)
    {
        $this->endpointUrl = parse_url($endpointUrl, PHP_URL_SCHEME) . "://" . parse_url($endpointUrl, PHP_URL_HOST);
        if ( ! str_ends_with($this->endpointUrl, "/"))
            $this->endpointUrl .= "/";
        $this->endpointUrl .= "api/";

        $this->clientId = parse_url($endpointUrl, PHP_URL_USER);
        $this->clientSecret = parse_url($endpointUrl, PHP_URL_PASS);
    }

    public function loadClientConfigFromEnv()
    {
        if (defined("RUDL_GITDB_URL")) {
            $endpointUrl = constant("RUDL_GITDB_URL");
        } else {
            $endpointUrl = getenv("RUDL_GITDB_URL");
        }
        if ($endpointUrl === false || $endpointUrl === "")
            throw new \InvalidArgumentException("Required ENV 'RUDL_GITDB_URL' undefined.");
        $host = parse_url($endpointUrl, PHP_URL_HOST);
        $scheme = parse_url($endpointUrl, PHP_URL_SCHEME);
        if ($host === "" || $scheme === "")
            throw new \InvalidArgumentException("Invalid url in ENV: RUDL_GIT_URL should be valid url. '$endpointUrl' defined");
        if (str_contains($host, ".")) {
            if ($scheme !== "https")
                throw new \InvalidArgumentException("Secure (SSL) connection is required for non local hosts (RUDL_GIT_URL): '$endpointUrl'");
        }
        if ( ! str_ends_with($endpointUrl, "/"))
            $endpointUrl .= "/";
        $endpointUrl .= "api/";
        $this->endpointUrl = $endpointUrl;

        
        if (defined("RUDL_GITDB_CLIENT_ID")) {
            $this->clientId = constant("RUDL_GITDB_CLIENT_ID");
        } else {
            $this->clientId = getenv("RUDL_GITDB_CLIENT_ID");
        }
        if ($this->clientId === false || $this->clientId === "")
            throw new \InvalidArgumentException("Required ENV 'RUDL_GITDB_CLIENT_ID' undefined.");

        if (defined("RUDL_GITDB_CLIENT_SECRET")) {
            $secret = constant("RUDL_GITDB_CLIENT_SECRET");
        } else {
            $secret = getenv("RUDL_GITDB_CLIENT_SECRET");
        }
        if ($secret === false || $secret === "")
            throw new \InvalidArgumentException("Required ENV 'RUDL_GITDB_CLIENT_SECRET' undefined.");
        if (preg_match("|^file:(.*)$|", $secret, $matches)) {
            $loadFile = $matches[1];
            if ( ! is_file($loadFile) || ! is_readable($loadFile))
                throw new \InvalidArgumentException("Secret file specified in RUDL_GITDB_CLIENT_SECRET is not readable: '$loadFile'");
            $secret = trim (file_get_contents($loadFile));
        }
        if (strlen($secret) < 8) {
            throw new \InvalidArgumentException("Secret defined in 'RUDL_GITDB_CLIENT_SECRET' length is " . strlen($secret). ". Minimum length is 8 bytes.");
        }
        $this->clientSecret = $secret;

    }


    protected function getRequestUri(array $path) : string
    {
        $path = array_filter($path, fn($in) => urlencode($in));
        $url = phore_url($this->endpointUrl . implode("/", $path));
        return $url;
    }

    private function handleError(\Exception $e)
    {
        if ($e instanceof PhoreHttpRequestException) {
            if ( ! $e->hasResponse()) {
                throw new GeneralAccessException("Cant connect: " . $e->getMessage());
            }
            if ($e->getCode() === 403) {
                throw new AccessDeniedException("Access denied: " . $e->getMessage());
            }
        }
        throw $e;
    }

    public function getRevision() : string
    {
        try {
            return phore_http_request($this->getRequestUri(["revision"]))
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->send()->getBody();
        } catch (\Exception $e) {
            $this->handleError($e);
        }

    }

    public function listObjects(string $scope) : T_ObjectList
    {
        try {
            return phore_hydrate(
                phore_http_request($this->getRequestUri(["o", $scope]))
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->send()->getBodyJson(),
                T_ObjectList::class
            );
        } catch (\Exception $e) {
            $this->handleError($e);
        }

    }

    /**
     * Create object files in targetPath
     *
     * Returns true if files changed
     * 
     * @param string $scope
     * @param string $path
     * @throws \Phore\FileSystem\Exception\FilesystemException
     * @return bool
     */

    public function syncObjects(string $scope, string $targetPath, array &$changedObjects=[]) : T_ObjectList
    {
        $target = phore_dir($targetPath)->mkdir(0755);

        $changedObjects = [];

        try {

            $objectList = $this->listObjects($scope);
            foreach ($objectList->objects as $object) {
                $curFile = $target->withFileName($object->name);
                if ( ! $curFile->exists() || $curFile->get_contents() !== $object->content) {
                    if ( ! $curFile->getDirname()->exists())
                        $curFile->getDirname()->assertDirectory(true);
                    $curFile->set_contents($object->content);
                    $changedObjects[] = $object;
                }
            }
            return $objectList;
        } catch (\Exception $e) {
            $this->handleError($e);
        }

    }

    public function log(T_Log $log)
    {
        $url = $this->getRequestUri(["log"]);
        try {
            phore_http_request($url)
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->withJsonBody((array)$log)
                ->send()->getBodyJson();
        } catch (\Exception $e) {
            echo "Unable to send log message: " . $e->getMessage();
        }
    }

    public function logOk($message)
    {
        $this->log(new T_Log("success", $message));
    }

    public function logWarning($message)
    {
        $this->log(new T_Log("warning", $message));
    }

    public function logError($message)
    {
        $this->log(new T_Log("error", $message));
    }


    public function writeObjects(string $scope, T_ObjectList $objectList, string $commitMessage = "", bool $simulate = false)
    {
        $url = $this->getRequestUri(["o", $scope]);
        if ($simulate === true)
            $url .= "?simulate";
        
        try {
            $result = phore_http_request($url)
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->withJsonBody((array)$objectList)
                ->send()->getBodyJson();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

}