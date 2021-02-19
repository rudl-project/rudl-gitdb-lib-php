<?php


namespace Rudl\LibGitDb\Type\Ingress;


class T_IngressConfig_VHost
{

    /**
     * @var string[]
     */
    public $server_names;

    /**
     * @var string
     */
    public $ssl_cert = null;

    /**
     * @var bool
     */
    public $enforce_ssl = false;

    /**
     * @var T_IngressConfig_Location[]
     */
    public $locations;
}