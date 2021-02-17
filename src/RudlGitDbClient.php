<?php


namespace Rudl\LibGitDb;


use Laminas\Diactoros\Uri;
use Rudl\LibGitDb\Type\Transport\T_FileList;

class RudlGitDbClient
{

    public function __construct(
        private string $endpointUrl,
        private string $systemId,
        private string $accessKey
    ){}


    protected function getRequestUri(string $scope)
    {
        $uri = new Uri($this->endpointUrl);
        $uri = $uri->withUserInfo($this->systemId, $this->accessKey);
        $uri = $uri->withPath($scope);
        return $uri;
    }

    public function listObjects(string $scope) : T_FileList
    {
        return phore_hydrate(
            phore_http_request($this->getRequestUri($scope))->send()->getBodyJson(),
            T_FileList::class
        );
    }

    public function writeObjects(string $scope, T_FileList $fileList, string $commitMessage = "")
    {
        phore_http_request($this->getRequestUri($scope))->withJsonBody($fileList)->send()->getBodyJson();
    }

}