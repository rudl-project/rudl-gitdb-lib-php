<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_Cert
{

    /**
     * @var string
     */
    public $name;

    /**
     * Common Names (CN) Array
     *
     * @var string[]
     */
    public $common_names = [];

    /**
     * @var bool
     */
    public $autoissue = false;

}