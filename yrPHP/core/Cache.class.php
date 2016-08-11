<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace core;


class Cache
{
    static function getInstance($dbCacheType = null)
    {
        $dbCacheType = is_null($dbCacheType) ? C('dbCacheType') : $dbCacheType;

        switch ($dbCacheType) {
            case "file":
                $class = loadClass('core\cache\File');
                //$class = new cache\File();
                break;
            case "memcache":
                $class = loadClass('core\cache\Memcache');
                break;
            case "memcached":
                $class = loadClass('core\cache\Memcached');
                break;
            case "redis":
                $class = loadClass('core\cache\Redis');
                break;
            default:
            die('请选择正确的缓存方式');
                break;
        }
        return $class;
    }

}