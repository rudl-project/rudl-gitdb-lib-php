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
    ){}


    protected function getRequestUri(array $path) : string
    {
        $path = array_filter($path, fn($in) => urlencode($in));
        if ( ! str_ends_with($this->endpointUrl, "/"))
            $this->endpointUrl .= "/";

        $this->endpointUrl .= "api/";

        $url = phore_url($this->endpointUrl . implode("/", $path));

        if ($this->systemId !== null && $this->accessKey !== null) {
            $url = $url->withUser($this->systemId)->withPass( $this->accessKey);
        }
        return $url;
    }

    public function listObjects(string $scope) : T_ObjectList
    {
        return phore_hydrate(
            phore_http_request($this->getRequestUri(["o", $scope]))->send()->getBodyJson(),
            T_FileList::class
        );
    }

    public function writeObjects(string $scope, T_ObjectList $objectList, string $commitMessage = "")
    {
        
        $result = phore_http_request($this->getRequestUri(["o", $scope]))
            ->withJsonBody((array)$objectList)
            ->send()->getBodyJson();
        print_r ($result);
    }

}