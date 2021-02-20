<?php


namespace Rudl\LibGitDb\Type\Conf;


class T_GitDbConfig
{

    /**
     * @var int
     */
    public $version;

    /**
     * @var T_ClientConfig[]
     */
    public $clients = [];

    /**
     * @var T_ScopeConfig[]
     */
    public $scopes = [];

}