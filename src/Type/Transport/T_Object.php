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


    public function dehydrate($content) : self
    {
        $extension = pathinfo($this->name, PATHINFO_EXTENSION);
        try {
            if (in_array($extension, ["yml", "yaml"])) {
                $this->content = phore_yaml_encode($content);
            } else if ($extension === "json") {
                $this->content = phore_json_encode($content);
            } else {
                throw new \InvalidArgumentException("Unrecoginized extension '$extension' for dehydration");
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("dehydrate error in object '$this->name': " . $e->getMessage(), 10, $e);
        }
        return $this;
    }

    public function hydrate(string $class) : mixed
    {
        $extension = pathinfo($this->name, PATHINFO_EXTENSION);
        try {
            if (in_array($extension, ["yml", "yaml"])) {
                $data = phore_yaml_decode($this->content);
            } else if ($extension === "json") {
                $data = phore_json_decode($this->content);
            } else {
                throw new \InvalidArgumentException("Unrecoginized extension '$extension' for deserialization");
            }
            return phore_hydrate($data, $class);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("hydrate error in object '$this->name': " . $e->getMessage(), 11, $e);
        }
    }

}