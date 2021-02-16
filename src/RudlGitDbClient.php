<?php


namespace Rudl\LibGitDb;


use Rudl\LibGitDb\Type\Transport\T_FileList;

class RudlGitDbClient
{

    public function __construct(
        private string $endpointUrl,
        private string $scope,
        private string $systemId,
        private string $accessKey
    ){}

    public function listObjects() : T_FileList
    {

    }

    public function writeObjects(T_FileList $fileList, string $commitMessage = "")
    {

    }

}