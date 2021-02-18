<?php


namespace Rudl\LibGitDb\Type\Cert;


class T_CertState
{

    public $last_issued_date;
    public $valid_to_date;

    public $last_error_ts;
    public $last_error_msg;

    public $cert_validTo;
    public $cert_validFrom;
    public $cert_serial;


    /**
     * @var string[]
     */
    public $common_names = [];

}