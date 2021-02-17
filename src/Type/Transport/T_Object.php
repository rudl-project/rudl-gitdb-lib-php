<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_Object
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $encrypted;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $lastChangeTs = null;
}