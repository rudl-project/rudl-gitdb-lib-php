<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_CertReqDef
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $hosts = [];

    /**
     * @var bool
     */
    public $autoissue = false;

}