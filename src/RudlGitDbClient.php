<?php


namespace Rudl\LibGitDb;


use Laminas\Diactoros\Uri;
use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Rudl\LibGitDb\Ex\AccessDeniedException;
use Rudl\LibGitDb\Ex\GeneralAccessException;
use Rudl\LibGitDb\Type\Transport\T_FileList;
use Rudl\LibGitDb\Type\Transport\T_ObjectList;

class RudlGitDbClient
{

    public function __construct(
        private string $endpointUrl,
        private ?string $systemId = null,
        private ?string $accessKey = null
    ){
        if ( ! str_ends_with($this->endpointUrl, "/"))
            $this->endpointUrl .= "/";

        $this->endpointUrl .= "api/";
    }

    public function loadSystemConfigFromEnv()
    {
        $endpointUrl = getenv("RUDL_GITDB_URL");
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
        $this->systemId = getenv("RUDL_GITDB_CLIENT_ID");
        if ($this->systemId === false || $this->systemId === "")
            throw new \InvalidArgumentException("Required ENV 'RUDL_GITDB_CLIENT_ID' undefined.");

        $secret = getenv("RUDL_GITDB_CLIENT_SECRET");
        if ($secret === false || $secret === "")
            throw new \InvalidArgumentException("Required ENV 'RUDL_GITDB_CLIENT_SECRET' undefined.");
        if (preg_match("|^file:(.*)$|", $secret, $matches)) {
            $loadFile = $matches[1];
            if ( ! is_file($loadFile) || ! is_readable($loadFile))
                throw new \InvalidArgumentException("Secret file specified in RUDL_GITDB_CLIENT_SECRET is not readable: '$loadFile'");
            $secret = file_get_contents($loadFile);
        }
        if (strlen($secret) < 8) {
            throw new \InvalidArgumentException("Secret defined in 'RUDL_GITDB_CLIENT_SECRET' length is " . strlen($secret). ". Minimul length is 8 bytes.");
        }
        $this->accessKey = $secret;

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
                ->withBasicAuth($this->systemId, $this->accessKey)
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
                    ->withBasicAuth($this->systemId, $this->accessKey)
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
     * @param string $scope
     * @param string $path
     * @throws \Phore\FileSystem\Exception\FilesystemException
     */
    public function syncObjects(string $scope, string $targetPath)
    {
        $target = phore_dir($targetPath);

        try {
            foreach ($this->listObjects($scope)->objects as $object) {
                $target->withFileName($object->name)->set_contents($object->content);
            }
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    public function logOk($message)
    {

    }

    public function logWarning($message)
    {

    }

    public function logError($message)
    {

    }


    public function writeObjects(string $scope, T_ObjectList $objectList, string $commitMessage = "", bool $simulate = false)
    {
        $url = $this->getRequestUri(["o", $scope]);
        if ($simulate === true)
            $url .= "?simulate";
        
        try {
            $result = phore_http_request($url)
                ->withBasicAuth($this->systemId, $this->accessKey)
                ->withJsonBody((array)$objectList)
                ->send()->getBodyJson();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

}