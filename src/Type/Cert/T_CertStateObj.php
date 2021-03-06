<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_CertStateObj
{

    /**
     * @var T_CertState[]
     */
    public $state = [];
    
    
    public function getStateByName(string $name) : ?T_CertState
    {
        foreach ($this->state as $cur) {
            if ($cur->name === $name)
                return $cur;
        }
        return nulL;
    }

}