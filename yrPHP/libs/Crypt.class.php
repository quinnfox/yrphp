<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
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
            die('类型错误');
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