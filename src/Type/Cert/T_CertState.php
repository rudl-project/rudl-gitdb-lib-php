<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_CertState
{

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }
    
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $last_issued_date;
    /**
     * @var string
     */
    public $valid_to_date;

    /**
     * @var int
     */
    public $last_error_ts;

    /**
     * @var string
     */
    public $last_error_msg;

    /**
     * @var int
     */
    public $cert_validTo;

    /**
     * @var int
     */
    public $cert_validFrom;
    /**
     * @var string
     */
    public $cert_serial;


    /**
     * @var string[]
     */
    public $common_names = [];

}