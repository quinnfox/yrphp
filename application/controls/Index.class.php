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
        $db = Model();
/*        $re = $db->query("update ipr_users set fullname=? where id=?",array(array('1659','1659'),array('1660','1660')));
        //echo $db->count();
        var_export($re->rowCount());*/

        //var_dump($re);


        $data = I('get.id','111');


        echo($data);
    }

    function  index1()
    {
        $db = Model();
        /*        $re = $db->query("update ipr_users set fullname=? where id=?",array(array('1659','1659'),array('1660','1660')));
                //echo $db->count();
                var_export($re->rowCount());*/
        $re = $db->get('users')->row();


        echo "Hello World";
    }
}