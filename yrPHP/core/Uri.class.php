<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ:284843370
 * Email:quinnH@163.com
 */

namespace core;


class Uri
{
    public $url;
    public $path;
    public $query;
    protected $routes = array();

    public function __construct()
    {

        if (file_exists(APP_PATH . 'config/route.php')) {
            require_once APP_PATH . 'config/route.php';
        } else {
            require_once BASE_PATH . 'config/route.php';
        }
        if (isset($route)) {
            $this->routes = $route;
        }

        $this->parseUrl();
    }

    public function parseUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        $uri = parse_url($_SERVER['REQUEST_URI']);

        $this->query = isset($uri['query']) ? $uri['query'] : '';
        $path = isset($uri['path']) ? $uri['path'] : '';
        $this->path = str_replace(C('urlSuffix'), '', $path);
        if (strpos($path, $_SERVER['SCRIPT_NAME']) === 0) {
            $this->path = (string)substr($this->path, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($path, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $this->path = (string)substr($this->path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        return array('path' => $this->path, 'query' => $this->query);

    }


    /**
     * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param bool|true $indexPage 如果是REWRITE重写模式 可以不必理会 否则默认显示index.php
     * @return string
     */
    function getUrl($url = '', $indexPage = true)
    {
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $base_url = (isHttps() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
                . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        } else {
            $base_url = 'http://localhost/';
        }

        if (C('url_model') != 2) {

            if ($indexPage) {
                $base_url .= 'index.php' . '/';
            }


        }
        if ($url === ''){
            $base_url .= $this->path;
        }else{
            $base_url .= ltrim($url, '/');
        }
        return $base_url;
    }

    /**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    public function rsegment($n = null, $no_result = null)
    {
        $uri = $this->parseRoutes();
        $uri = explode('/', $uri);
        unset($uri[0]);
        if (is_int($n)) return isset($uri[$n]) ? $uri[$n] : $no_result;

        return $uri;
    }

    /**
     * 路由验证
     * @return mixed|string
     */
    public function parseRoutes()
    {
        $uri = $this->path;

        foreach ($this->routes as $k => $v) {
            $k = str_replace('/', '\/', $k);
            if (preg_match("/$k/", $uri)) {
                $v = str_replace(':', "\\", $v);
                $uri = preg_replace("/$k/", $v, $uri);
            }
        }

        return $uri;
    }

    /**返回没有经过路由替换过的uri数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    public function segment($n = null, $no_result = null)
    {
        $uri = explode('/', $this->path);
        unset($uri[0]);
        if (is_int($n)) return isset($uri[$n]) ? $uri[$n] : $no_result;

        return $uri;
    }

    /**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
    public function getRPath()
    {
        return $this->parseRoutes();
    }

    public function getQuery()
    {
        return $this->query;
    }
}