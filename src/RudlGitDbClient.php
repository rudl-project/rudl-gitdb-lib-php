<?php


namespace Rudl\LibGitDb;


use Laminas\Diactoros\Uri;
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


    protected function getRequestUri(array $path) : string
    {
        $path = array_filter($path, fn($in) => urlencode($in));
        $url = phore_url($this->endpointUrl . implode("/", $path));

        if ($this->systemId !== null && $this->accessKey !== null) {
            $url = $url->withUser($this->systemId)->withPass( $this->accessKey);
        }
        return $url;
    }

    public function getRevision() : string
    {
        return phore_http_request($this->getRequestUri(["revision"]))->send()->getBody();
    }

    public function listObjects(string $scope) : T_ObjectList
    {
        return phore_hydrate(
            phore_http_request($this->getRequestUri(["o", $scope]))->send()->getBodyJson(),
            T_ObjectList::class
        );
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

        foreach ($this->listObjects($scope)->objects as $object) {
            $target->withFileName($object->name)->set_contents($object->content);
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
        
        $result = phore_http_request($url)
            ->withJsonBody((array)$objectList)
            ->send()->getBodyJson();
    }

}