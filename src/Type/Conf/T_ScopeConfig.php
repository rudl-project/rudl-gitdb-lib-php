<?php


namespace Rudl\LibGitDb\Type\Conf;


class T_ScopeConfig
{

    /**
     * @var string
     */
    public $scope;

    /**
     * @var string[]
     */
    public $allowRead = [];

    /**
     * @var string[]
     */
    public $allowWrite = [];

}