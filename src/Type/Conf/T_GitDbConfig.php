<?php


namespace Rudl\LibGitDb\Type\Conf;


class T_GitDbConfig
{

    /**
     * @var int
     */
    public $version;

    /**
     * @var T_SystemConfig[]
     */
    public $systems = [];

    /**
     * @var T_ScopeConfig[]
     */
    public $scopes = [];

}