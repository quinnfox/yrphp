<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ:284843370
 * Email:quinnH@163.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
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

    /**
     * 解析URL
     * @return array|string
     */
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

    /**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }
}