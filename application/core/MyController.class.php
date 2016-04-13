<?php
/**
 * Created by young.
 * User: Nathan
 * QQ:284843370
 * Email:nathankvin@163.com
 */
namespace core;

class MyController extends Controller
{
    public $data;
    public $lang;
    public $userInfo;
    public function __construct()
    {
        parent::__construct();
        $this->des = loadClass('libs\DES3');
        $this->db = Model('userModel');
        $post = isset($_POST['data']) ? json_decode($this->des->decrypt($_POST['data']), TRUE) : array();
        $get = isset($_GET['data']) ? json_decode($this->des->decrypt(str_replace(" ", "+", $_GET['data'])), TRUE) : array();
        $this->data = array_merge($post, $get);
        //$this->userInfo = $this->check();
        $this->lang = I('get.lang');
        $this->easemob =  loadClass('libs\Easemob');

    }


    public function check()
    {
        $action = C('ctlName').'/'.C('actName');
        $free = array('users/index', 'users/login', 'users/userregister','users/getcountry','financial/alipayverify','users/getqinniutoken','users/forgetpassword');
        if( C('ctlName')=='web' && $action != 'web/bankRemit') return;

        if (!in_array($action, $free)) {
            $str = '{"data":"'.$this->des->encrypt('{"status":"110","message":"'.getLang('tokenError').'","result":""}').'"}';
            if (!isset($this->data['token'])) {
              die($str);
            } else {
                $userInfo = $this->db->getOne('users', array('login_token' => $this->data['token']));
                if (empty($userInfo)) {
                 die($str);
                } else {
                    return $userInfo;
                }
            }
            die($str);
        }

    }


}
