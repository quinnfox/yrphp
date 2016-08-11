<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
use core\Controller;

class index extends Controller
{
    function __construct()
    {
        parent::__construct();

    }


    function  index()
    {
    echo "<h1>欢迎使用yrPHP 有什么建议或则问题 请随时联系我<br/>QQ：284843370<br/>email:kwinwong@hotmail.com</h1>";
    }


    function img(){



        $config = array('width'=>100,'height'=>40);
      $thumb = loadClass('\libs\VerifyCode',$config);

        $thumb->show();
    }

        }