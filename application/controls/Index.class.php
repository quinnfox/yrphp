<?php
/**
 * Created by young.
 * User: Nathan
 * QQ:284843370
 * Email:nathankvin@163.com
 */
use core\Controller;

class index extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->caching = 0;
    }


    function  index()
    {
        echo "Hello World";
    }


}