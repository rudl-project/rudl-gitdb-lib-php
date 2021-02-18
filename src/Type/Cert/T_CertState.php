<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_CertState
{

    public $lastIssuedDate;

    public $lastIssuedTs;

    /**
     * @var string[]
     */
    public $hosts = [];

}