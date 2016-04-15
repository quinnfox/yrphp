<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace core\cache;


interface   Cache
{
    /**
     * 如果不存在或则已过期则返回true
     */
    public function isExpired($key);

    /**
     * 设置缓存
     * @param $key
     * @param $val
     * @return mixed
     */
    public function set($key, $val);

    /**
     * 获得缓存
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * 清空缓存
     * @return mixed
     */
    public function clear();

    /**
     *根据key值删除缓存
     * @param string $key
     */
    public function del($key = '');


}