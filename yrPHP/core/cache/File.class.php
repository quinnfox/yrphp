<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace core\cache;


class File implements Cache
{

    private $dbCacheTime;
    private $dbCachePath;
    private $dbCacheExt;

    public function __construct()
    {
        $this->dbCacheTime = C('dbCacheTime');
        $this->dbCachePath = C('dbCachePath');
        $this->dbCacheExt = C('dbCacheExt');
    }

    /**
     * 如果不存在或则已过期则返回true
     * @param $key
     * @return bool
     */
    public function isExpired($key)
    {
        $file = $this->dbCachePath . $key . '.' . $this->dbCacheExt;
        if (file_exists($file)) {
            if ($this->dbCacheTime === 0) return false;
            if (filectime($file) + $this->dbCacheTime < time()) {
                return false;
            }
        }

        return true;
    }

    public function set($key, $val)
    {
        return file_put_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt, mySerialize($val));
    }

    public function get($key)
    {
        return myUnSerialize(file_get_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt));
    }

    public function clear()
    {
        \libs\file::rm();
    }

    /**
     *
     * @param string $key
     */
    public function del($key = '')
    {
        $file = \libs\file::search($this->dbCachePath, $key);
        foreach ($file as $k => $v) {
            \libs\file::rm($v);
        }
    }


}