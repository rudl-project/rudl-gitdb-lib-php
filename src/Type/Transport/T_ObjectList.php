<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_ObjectList
{

    /**
     * @var T_File[]
     */
    public $files = [];

    public function __construct(array $files = [])
    {
        // Requird to separate constructor from Property definition for hydrator
        $this->files = $files;
    }



}