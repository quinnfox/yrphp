<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
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
        if (!file_exists($file)) return false;

        $contents = myUnSerialize(file_get_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt));

        if ($contents['ttl'] != 0 && $contents['ttl'] > $contents['time']) {
            \libs\file::rm($file);
            return false;
        }

        return true;
    }

    public function set($key, $val, $timeout = null)
    {

        $contents = array(
            'time' => time(),
            'ttl' => is_null($timeout) ? $this->dbCacheTime : $timeout,
            'data' => $val
        );


        return file_put_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt, mySerialize($contents));
    }

    public function get($key = null)
    {
        if (is_null($key)) return false;

        $file = $this->dbCachePath . $key . '.' . $this->dbCacheExt;
        if (!file_exists($file)) return false;

        $contents = myUnSerialize(file_get_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt));

        if ($contents['ttl'] != 0 && $contents['ttl'] > $contents['time']) {
            \libs\file::rm($file);
            return false;
        }

        return $contents['data'];
    }

    public function clear()
    {
        \libs\file::rm();
    }

    /**
     *
     * @param string $key
     */
    public function del($key = null)
    {
        if(is_null($key)) return false;

        $file = \libs\file::search($this->dbCachePath, $key);
        foreach ($file as $k => $v) {
            \libs\file::rm($v);
        }
    }


}