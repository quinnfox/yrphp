<?php
/**
 * Created by young.
 * User: Nathan
 * QQ:284843370
 * Email:nathankvin@163.com
 */
namespace core;

class MyModel extends Model
{
    protected $langKey;

    public function __construct()
    {
        parent::__construct();
        $lang = $_SESSION['lang'];
        switch ($lang) {
            case "cn":
                $this->langKey = 'ZH_CN';
                break;
            default:
                $this->langKey = 'EN_US';
                break;
        }
    }

    /**
     * 获得表行数
     * @param null $table 表名
     * @param array $where 条件
     * @return bool
     */
    public function exist($table = null, $where = array(), $cache = false)
    {
        if (!$table) {
            return false;
        }

        return intval($this->select("count(*) as count")->where($where)->get($table)->row(false,$cache)->count);

    }

    public function  getOne($table = null, $where = array())
    {
        if (!$table) {
            return false;
        }
        return $this->where($where)->limit(1)->get($table)->row(false);

    }


    public function getList($table = null, $where = array(), $lastId = 0)
    {
        if (!$table) {
            return false;
        }

        $limit = 10;
        if ($lastId > 0) {
            $where['id'] = array($lastId, '<');
            $limit = 5;
        }

        $re = $this->where($where)->order('id desc')->limit($limit)->get($table)->result();
        return $re;


    }


    /**
     * 业务种类
     * @param null $businessCode
     * @return Object
     */
    public function getCaseType($businessCode = null)
    {
        if (empty($businessCode)) {
            return null;
        } else {
            $w['businessCode'] = $businessCode;
        }
        $w['langKey'] = $this->langKey;
        $re = $this->where($w)->limit(1)->get('config_business_type', false)->row();
        return empty($re) ? $businessCode : $re->businessName;
    }

}
