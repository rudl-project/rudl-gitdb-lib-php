<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_File
{

    /**
     * @var string
     */
    public $filename;

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