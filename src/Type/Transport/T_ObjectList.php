<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_ObjectList
{

    /**
     * @var T_Object[]
     */
    public $objects = [];

    /**
     * The current Revision
     * 
     * @var string 
     */
    public $rev = "";
    
    /**
     * T_ObjectList constructor.
     * @param T_Object[] $objects
     */
    public function __construct(array $objects = [])
    {
        $this->objects = $objects;
    }



}