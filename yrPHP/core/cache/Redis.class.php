<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace core\cache;


class Redis implements Cache
{
    private static $object;

    static function getInstance()
    {
        if (!extension_loaded('redis')) {
            die('û�а�װredis��չ');
        }
        if (is_object(self::$object)) {
            return self::$object;
        } else {
            self::$object = new \Redis;
            $config = C('redis');
            if (is_string($config)) $config = array($config);

            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    $conf = explode(':', $v);
                    self::$object->connect($conf[0], $conf[1]);
                }
            } else {
                die('��������');
            }
            self::$object->select(0);
            return self::$object;
        }

    }

    /**
     * ��������ڻ����ѹ����򷵻�true
     */
    public function isExpired($key)
    {
        return !self::getInstance()->exists($key);
    }


    /**
     * ���û���
     * @param $key
     * @param $val
     * @return mixed
     */
    public function set($key = '', $val = '', $timeout = null)
    {
        $timeout = is_null($timeout) ? C('dbCacheTime') : $timeout;
        return self::getInstance()->set($key, mySerialize($val), $timeout);
    }

    /**
     * ��û���
     * @param $key
     * @return mixed
     */
    public function get($key = '')
    {
        return myUnSerialize(self::getInstance()->get($key));
    }

    /**
     * ��ջ���
     * @return mixed
     */
    public function clear()
    {
        return self::getInstance()->Flushdb();
    }

    /**
     *����keyֵɾ������
     * @param string $key
     */
    public function del($key = '')
    {
        $keys = self::getInstance()->keys("*$key*");
        return self::getInstance()->delete($keys);
    }

}