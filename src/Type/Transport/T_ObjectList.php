<?php


namespace Rudl\LibGitDb\Type\Transport;


class T_ObjectList
{

    /**
     * @var T_Object[]
     */
    public $objects = [];

    /**
     * T_ObjectList constructor.
     * @param T_Object[] $objects
     */
    public function __construct(array $objects = [])
    {
        $this->objects = $objects;
    }



}