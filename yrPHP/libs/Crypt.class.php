<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ: 284843370
 * Email: nathankwin@163.com
 */
namespace libs;


class Crypt
{
    function __construct()
    {
/*        require 'crypt/DES3.class.php';
        $this->class = new crypt\DES3();*/
        $this->class = loadClass('libs\crypt\DES3');

    }
    function encrypt($input){
    return $this->class->encrypt($input);
    }
    function decrypt($encrypted){
     return   $this->class->decrypt($encrypted);
    }
}