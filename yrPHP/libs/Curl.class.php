<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ:284843370
 * Email:quinnH@163.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
 */
namespace libs;
class Curl
{
    protected $options = array();
    protected $ch;

    function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * [setUrl description]
     * @param string $url [description]
     */
    function setUrl($url = '')
    {
        $this->ch = curl_init();
        $this->options = array();
        $this->options[CURLOPT_URL] = $url;
        return $this;
    }

    /**
     * @param string $option
     * @param string $value
     * @return $this
     */
    function setOptions($option = '', $value = '')
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * 是否验证证书
     * @param  boolean $verify 是否验证证书
     * @param  string $path 验证证书时，证书路径
     * @return [type]          [description]
     */
    function sslVerify($verify = false, $path = '')
    {
        if ($verify) {
            $this->options[CURLOPT_SSL_VERIFYPEER] = true;//开启后cURL将从服务端进行验证。
            $this->options[CURLOPT_CAINFO] = $path; //一个保存着1个或多个用来让服务端验证的证书的文件名
        } else {
            $this->options[CURLOPT_SSL_VERIFYPEER] = false; //禁用后cURL将终止从服务端进行验证。
            $this->options[CURLOPT_SSL_VERIFYHOST] = 0;//不检查证书
            //设为0表示不检查证书 设为1表示检查证书中是否有CN(common name)字段  设为2表示在1的基础上校验当前的域名是否与CN匹配 默认2
        }
        return $this;
    }

    /**
     * @param string $userPassword 格式为："[username]:[password]"
     */
    function setUserPassword($userPassword = '')
    {
        $this->options[CURLOPT_USERPWD] = $userPassword; //传递一个连接中需要的用户名和密码
        return $this;
    }

    /**
     * @param array $header //请求头
     */
    function setHeader($header = array())
    {
        $this->options[CURLOPT_HTTPHEADER] = $header; //一个用来设置HTTP头字段的数组
        return $this;
    }


    /**
     * @param array|string $data
     * @param string $enctype application|multipart
     */
    function post($data = array(), $enctype = 'application')
    {
        if ($enctype == 'application' && is_array($data)) {
            $data = http_build_query($data);
        } elseif ($enctype == 'multipart' && is_string($data)) {
            $data = parse_str($data, $data);
        }
        $this->options[CURLOPT_POST] = true;//启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded
        $this->options[CURLOPT_POSTFIELDS] = $data;//如果$data是一个数组，Content-Type头将会被设置成multipart/form-data
        return $this;
    }


    /**
     * @param array|string $data array('user'=>'admin','pass'=>'admin') | admin&admin
     * @return $this
     */
    function get($data = array())
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }

        if (strstr($this->options[CURLOPT_URL], '?') !== false) {
            $this->options[CURLOPT_URL] = $this->options[CURLOPT_URL] . '&' . $data;
        } else {
            $this->options[CURLOPT_URL] = $this->options[CURLOPT_URL] . '?' . $data;
        }

        return $this;
    }

    /**
     * 获得cookies
     * @param string $path 定义Cookie存储路径 必须使用绝对路径
     */
    function getCookie($path = '')
    {
        $this->options[CURLOPT_COOKIEJAR] = $path;//连接结束后保存cookie信息的文件
        return $this;
    }

    /**
     * 设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
     * @param string|array $cookies 定义Cookie的值
     */
    function setCookie($cookies = array())
    {
        $cookieString = '';
        if (is_array($cookies)) {
            foreach ($cookies as $name => $value) {
                $cookieString .= $name . '=' . $value . ';';
            }
        } else {
            $cookieString = $cookies;
        }
        $this->options[CURLOPT_COOKIE] = $cookieString;//cookie字符串
        return $this;
    }

    /**
     * 取出cookie，一起提交给服务器
     * @param string $path 定义Cookie存储路径 必须使用绝对路径
     */
    function setCookieFile($path = '')
    {
        $this->options[CURLOPT_COOKIEFILE] = $path;//包含cookie数据的文件名
        return $this;
    }


    function exec($debug = false)
    {
        $this->options[CURLOPT_RETURNTRANSFER] = true; //将 curl_exec() 获取的信息以文件流的形式返回，而不是直接输出。
        if ($debug) {
            $this->options[CURLOPT_VERBOSE] = true;//启用时会汇报所有的信息，存放在STDERR或指定的CURLOPT_STDERR中。
            $this->options[CURLINFO_HEADER_OUT] = true;//启用时追踪句柄的请求字符串。
            $this->options[CURLOPT_HEADER] = true;//启用时会将头文件的信息作为数据流输出。
        }

        curl_setopt_array($this->ch, $this->options);
        $re = curl_exec($this->ch);
        if ($debug) {
            $httpCode = curl_getinfo($this->ch);
            var_export($httpCode);
        }
        return $re;
    }

    function getInfo()
    {
        return curl_getinfo($this->ch);
    }

    function __destruct()
    {
        curl_close($this->ch);

    }
}