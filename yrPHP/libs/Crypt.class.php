<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace libs;


class Crypt
{
    function __construct()
    {
        $mode = C('crypt_mode');
        switch ($mode){
            case "des3":
            $this->class = loadClass('libs\crypt\DES3');
                break;
            default:
            die('´íÎó¼ÓÃÜ·½Ê½');
                break;
        }


    }
    function encrypt($input){
    return $this->class->encrypt($input);
    }
    function decrypt($encrypted){
     return   $this->class->decrypt($encrypted);
    }
}