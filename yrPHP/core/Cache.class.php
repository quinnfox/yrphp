<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 * 缓存处理工厂
 */
namespace core;


class Cache
{
    static $_instance = null;

    private function __construct()
    {

    }

    static function getInstance($dbCacheType = null)
    {
        if (!(self::$_instance instanceof self)) {
            $dbCacheType = is_null($dbCacheType) ? C('dbCacheType') : $dbCacheType;
            $dbCacheType = strtolower($dbCacheType);

            switch ($dbCacheType) {
                case "file":
                    // $class = loadClass('core\cache\File');
                    self::$_instance = new cache\File;
                    break;
                case "memcache":
                    self::$_instance = new cache\Memcache;
                    break;
                case "memcached":
                    self::$_instance = new cache\Memcached;

                    break;
                case "redis":
                    self::$_instance = new cache\Redis;

                    break;
                default:
                    die('请选择正确的缓存方式');
                    break;
            }

            if (!(self::$_instance instanceof cache\ICache)) {
                die('错误：必须实现db\Driver接口');
            }
        }

        return self::$_instance;
    }

    private function __clone()
    {

    }
}