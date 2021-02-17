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


    public function __construct(string $name = null, string $content = null, bool $encrypted = false)
    {
        $this->name = $name;
        $this->content = $content;
        $this->encrypted = $encrypted;
    }
}