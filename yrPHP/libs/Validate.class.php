<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace libs;

class Validate
{


    /**
     * ������ֵ���ʱ return true
     * @param string $data
     * @param string $val
     * @return bool
     */
    static function equal($data = null, $val = null)
    {
        if(is_null($data)) return false;

        if ($data == $val) return true;

        return false;
    }

    /**
     * ��������ֵ���ʱ return true
     * @param string $data
     * @param string $val
     * @return bool
     */
    static function notEqual($data = null, $val = null)
    {
        if(is_null($data)) return false;

        if ($data != $val) return true;

        return false;
    }

    /**
     * ������ָ����Χʱreturn true
     * @param string $data
     * @param array|string $range
     * @return bool
     */
    static function in($data = '', $range = '')
    {
        if(is_string($range)){
            $range = explode(',', $range);
        }elseif(!is_array($range)){
         return false;
        }

        if (in_array($range,$data)) return true;

        return false;
    }


    /**
     * ��������ָ����Χʱreturn true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function notIn($data = '', $range = '')
    {
        if(is_string($range)){
            $range = explode(',', $range);
        }elseif(!is_array($range)){
            return false;
        }

        if (in_array($range,$data)) return false;

        return true;
    }


    /**
     * ������ָ����Χʱreturn true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function between($data = '', $range = '')
    {
        if(is_string($range)){
            $range = explode(',', $range);
        }elseif(!is_array($range)){
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($data >= $min && $data <= $max) {
        return true;
        }

        return false;
    }


    /**
     * ��������ָ����Χʱreturn true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function notBetween($data = '', $range = '')
    {
        if(is_string($range)){
            $range = explode(',', $range);
        }elseif(!is_array($range)){
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($data >= $min && $data <= $max) {
            return false;
        }

        return true;
    }

    /**
     * �����ݿ���ֵ����ʱ return false
     * @param $tableName
     * @param $field
     * @param $val
     * @return bool
     */
    static function  unique($tableName,$field,$val){
        $db = Model();
        $count = $db->query("select {$field} from {$tableName} where {$field}={$val}")->rowCount();
        if ($count) return false;

        return true;
    }
    /**
     * ���ַ����ȴ���ָ����Χʱreturn true
     * @param null $data
     * @param array|string $range
     * @return bool
     * length('abc',$rage = 3); strlen('abc') ==3
     * length('abc',$rage = array(5,3))==length('abc',$rage = array(3,5)) => strlen('abc') >=3 && strlen('abc') <=5
     */
    static function length($data = '', $range = '')
    {
        if(is_string($range)){
            $range = explode(',', $range);
        }elseif(!is_array($range)){
            return false;
        }

        $max = max($range);
        $min = min($range);
        $strLen = strlen($data);
        if($max == $min){
        if($strLen == $max){
            return true;
        }
        }elseif ($strLen >= $min && $strLen <= $max) {
            return true;
        }

        return false;
    }

    /**
     * Email��ʽ��֤
     * @param	string	$value	��Ҫ��֤��ֵ
     */
    static function email($value) {
        $rules= "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";

        if(!preg_match($rules, $value))  return false;

        return true;
    }
    /**
     * URL��ʽ��֤
     * @param	string	$value	��Ҫ��֤��ֵ
     */
    static function url($value) {

        $rules='/^http\:\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?$/';
        if(!preg_match($rules, $value)) return false;

        return true;
    }
    /**
     * ���ָ�ʽ��֤
     * @param	string	$value	��Ҫ��֤��ֵ
     */
    static function number($value) {

        $rules='/^\d+$/';
        if(!preg_match($rules, $value))  return false;

        return true;

    }
    /**
     * ���Ҹ�ʽ��֤
     * @param	string	$value	��Ҫ��֤��ֵ
     */
    static function currency($value) {

        $rules='/^\d+(\.\d+)?$/';
        if(!preg_match($rules, $value)) return false;

        return true;

    }

    /**
     * ʹ���Զ����������ʽ������֤
     * @param	string	$value	��Ҫ��֤��ֵ
     * @param	string	$rules	������ʽ
     */
    static function regex($value,$rules) {
        if(!preg_match($rules, $value)) return false;

        return true;

    }
}