<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_Log
{

    /**
     *
     * Values: success, warning, error
     * @var string
     */
    public $type = "success";

    /**
     * @var string
     */
    public $msg;
    
    public function __construct(string $type, string $msg)
    {
        $this->type = $type;
        $this->msg = $msg;
    }

}