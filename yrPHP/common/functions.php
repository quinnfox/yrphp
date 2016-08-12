<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 *
 * 系统函数库
 */

/*
/设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
$include_path=get_include_path();                         //原基目录
$include_path.=PATH_SEPARATOR.ROOT_PATH;       //框架中基类所在的目录
//设置include包含文件所在的所有目录
set_include_path($include_path);
*/
//spl_autoload_register('autoLoadClass');//注册自动加载函数
spl_autoload_register(function ($className) {
    core\Debug::start();
    $classList = array();
    if (isset($classList[$className])) {
        $classPath = $classList[$className];
    } else {
        $classPath = "";
        $path = explode('\\', $className);

        if (false !== strpos($className, '\\')) {
            $className = array_pop($path);
            $pathCount = count($path) - 1;
            $classPath = implode('/', $path) . '/' . $className . '.class.php';

            if (file_exists(APP_PATH . $classPath)) {
                $classPath = APP_PATH . $classPath;
            } else if (file_exists(BASE_PATH . $classPath)) {
                $classPath = BASE_PATH . $classPath;
            } else if (strtolower($path[0]) == 'model') {
                $classPath = APP_PATH . 'models' . '/' . $className . '.class.php';
            } else {
                return false;
            }
        }
    }

    if (file_exists($classPath)) requireCache($classPath);

    core\Debug::stop();
    core\Debug::addMsg(array('path' => $classPath, 'time' => core\Debug::spent()), 1);
}
);


/**
 * 访问控制器的原始资源
 * 返回当前实例控制器对象
 *
 * @return Controller 资源
 */
function &get_instance()
{
    return core\Controller::get_instance();
}


/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name = null, $value = null, $default = null)
{
    static $config = array();
    // 批量设置
    if (is_array($name)) {
        $config = array_merge($config, $name);
        return $config;
    }
    if (!empty($name) && !empty($value)) $config[$name] = $value;
    if (empty($name)) return $config;
    if (isset($config[$name])) return $config[$name];
    return $default;
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

    if (C('urlType') != 2) {

        if ($indexPage) {
            $base_url .= 'index.php' . '/';
        }


    }
    if (!empty($url)) {
        $base_url .= ltrim($url, '/');
    }
    return $base_url;
}


/**
 *  获取语言 支持批量定义
 * @param null $key 语言关键词
 * @param null $value 配置值
 * @return array|null
 */
function getLang($key = null, $value = null)
{
    static $lang = array();
    // 批量设置
    if (is_array($key)) {
        $lang = array_merge($lang, $key);
        return $lang;
    }
    if (!empty($key) && !empty($value)) $lang[$key] = $value;
    if (empty($key)) return '';
    //if (empty($key)) return $lang;

    if (isset($lang[$key])) return $lang[$key];
    return $key;

}


/**
 * loadClass($className [, mixed $parameter [, mixed $... ]])
 * @param $className 需要得到单例对象的类名
 * @param $parameter $args 0个或者更多的参数，做为类实例化的参数。
 * @return  object
 */
function loadClass()
{
    static $instanceList = array();
    //取得所有参数
    $arguments = func_get_args();
    //弹出第一个参数，这是类名，剩下的都是要传给实例化类的构造函数的参数了
    $className = array_shift($arguments);
    $key = trim($className, '\\');

    if (!isset($instanceList[$key])) {
        $class = new ReflectionClass($className);
        $instanceList[$key] = $class->newInstanceArgs($arguments);
    }
    return $instanceList[$key];
}


/**
 * 如果存在自定义的模型类，则实例化自定义模型类，如果不存在，则会实例化Model基类,同时对于已实例化过的模型，不会重复去实例化。
 * @param string $modelName 模型类名
 * @return object
 */
function Model($modelName = "")
{
    static $modelInstance = array();
    if (isset($modelInstance[$modelName])) {
        return $modelInstance[$modelName];
    }
    core\Debug::start();
    $modelPath = C('modelDir') . $modelName . '.class.php';
    if (empty($modelName)) {
        return loadClass('core\Model');
    }

    if (!file_exists($modelPath)) {
        $modelInstance[$modelName] = loadClass('core\Model');
    } else {
        $modelInstance[$modelName] = loadClass('Model\\' . $modelName);
    }
    return $modelInstance[$modelName];
}


/**
 * 调试时代码高亮显示
 * @param $str
 * @return mixed
 */
function highlightCode($str)
{
    /* The highlight string function encodes and highlights
     * brackets so we need them to start raw.
     *
     * Also replace any existing PHP tags to temporary markers
     * so they don't accidentally break the string out of PHP,
     * and thus, thwart the highlighting.
     */
    $str = str_replace(
        array('&lt;', '&gt;', '<?', '?>', '<%', '%>', '\\', '</script>'),
        array('<', '>', 'phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'),
        $str
    );

    // The highlight_string function requires that the text be surrounded
    // by PHP tags, which we will remove later
    $str = highlight_string('<?php ' . $str . ' ?>', TRUE);

    // Remove our artificially added PHP, and the syntax highlighting that came with it
    $str = preg_replace(
        array(
            '/<span style="color: #([A-Z0-9]+)">&lt;\?php(&nbsp;| )/i',
            '/(<span style="color: #[A-Z0-9]+">.*?)\?&gt;<\/span>\n<\/span>\n<\/code>/is',
            '/<span style="color: #[A-Z0-9]+"\><\/span>/i'
        ),
        array(
            '<span style="color: #$1">',
            "$1</span>\n</span>\n</code>",
            ''
        ),
        $str
    );

    // Replace our markers back to PHP tags.
    return str_replace(
        array('phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'),
        array('&lt;?', '?&gt;', '&lt;%', '%&gt;', '\\', '&lt;/script&gt;'),
        $str
    );
}

//过滤数据
function filter(& $data = null)
{
    $filters = C('defaultFilter');
    if (is_string($filters)) {
        $filters = explode('|', $filters);
    }
    foreach ($filters as $filter) {
        if (function_exists($filter)) {
            $data = $filter($data);
        }
    }
}

/**
 *  * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param bool|false $default 默认值
 * @param null $filter 参数过滤方法 默认调用配置里的defaultFilter array或string 用|分割
 * @return array
 */
function I($name = '', $default = null, $filter = null)
{
    if (strpos($name, '.')) { // 指定修饰符
        list($method, $name) = explode('.', $name, 2);
    } else { // 默认为自动判断
        $method = 'param';
    }
    switch (strtolower($method)) {
        case 'get'     :
            $input =& $_GET;
            break;
        case 'post'    :
            $input =& $_POST;
            break;
        case 'param'   :
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                default:
                    $input = $_GET;
            }
            break;

        case 'request' :
            $input =& $_REQUEST;
            break;
        case 'session' :
            $input =& $_SESSION;
            break;
        case 'cookie'  :
            $input =& $_COOKIE;
            break;
        case 'server'  :
            $input =& $_SERVER;
            break;
        case 'globals' :
            $input =& $GLOBALS;
            break;

        default:
            return null;
    }

    if (empty($name)) {
        $data = &$input;
    } else {
        if (isset($input[$name])) {
            $data[$name] = $input[$name];
        } else {
            return $default;
        }
    }

    if (!is_null($filter)) C('defaultFilter', $filter);

    array_walk($data, 'filter');//回调过滤数据


    return empty($name) ? $data : $data[$name];

}


/**
 * 管理session
 * @param string $key
 * @param string $val
 * @return bool
 */
function session($key = '', $val = '')
{

    if (!session_id()) session_start();
    $session_prefix = C('session_prefix');
    if (is_null($key)) {
        session_unset();//释放当前注册的所有会话变量
        session_destroy();
        return true;
    }

    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $_SESSION[$session_prefix . $k] = $v;
        }
        return true;
    }

    if (is_null($val)) {
        unset($_SESSION[$session_prefix . $key]);
        return true;
    }

    if (!empty($val)) {

        $_SESSION[$session_prefix . $key] = $val;
        return true;
    }

    if (!empty($key)) {

        return $_SESSION[$session_prefix . $key];
    }

    return $_SESSION;
}

/**
 * 管理cookie
 * @param string $key
 * @param string $val
 * @return bool
 */
function cookie($key = '', $val = '')
{
    $cookie_prefix = C('cookie_prefix');
    /**
     * setcookie(name, value, expire, path, domain);
     *  $_COOKIE["user"];
     * setcookie("user", "", time()-3600);
     */
    if (is_null($key)) {
        
        foreach ($_COOKIE as $key => $value) {
        	 setcookie($cookie_prefix . $key, "", time() - 3600);
        }
        
        return true;
    }

    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $_SESSION[$cookie_prefix . $k] = $v;
        }
        return true;
    }

    if (is_null($val)) {

        setcookie($cookie_prefix . $key, "", time() - 3600);
        return true;
    }

    if (!empty($val)) {

        setcookie($cookie_prefix . $key, $val, time() + C('cookie_expire'), C('cookie_path'), C('cookie_domain'));
        return true;
    }

    if (!empty($key)) {

        return $_COOKIE[$cookie_prefix . $key];
    }

    return $_COOKIE;
}

/**
 * 判断是不是 AJAX 请求
 * 测试请求是否包含HTTP_X_REQUESTED_WITH请求头。
 * @return    bool
 */
function isAjaxRequest()
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}


/**
 * 判断是不是 POST 请求
 *
 * @return    bool
 */
function isPost()
{
    return ($_SERVER['REQUEST_METHOD'] == 'POST');
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function isHttps()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return TRUE;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return TRUE;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return TRUE;
    }

    return FALSE;
}


/**
 * 定义一个用来序列化对象的函数
 * 当数组值包含如双引号、单引号或冒号等字符时，
 * 它们被反序列化后，可能会出现问题。为了克服这个问题，
 * 一个巧妙的技巧是使用base64_encode和base64_decode。
 * 但是base64编码将增加字符串的长度。为了克服这个问题，
 * 可以和gzcompress一起使用。
 * @param $obj
 * @return string
 */
function mySerialize($obj = '')
{
    if (empty($obj)) return false;
    return base64_encode(gzcompress(serialize($obj), 6));
}

/**
 * 反序列化
 * @param $txt
 * @return mixed
 */
function myUnSerialize($txt = '')
{
    if (empty($txt)) return false;
    return unserialize(gzuncompress(base64_decode($txt)));
}


/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function requireCache($filename)
{
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists($filename)) {
            $_importFiles[$filename] = require $filename;;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

/**
 * 404跳转
 * @param string $msg 提示字符串
 * @param string $url 跳转URL
 * @param int $time 指定时间跳转
 */
function error404($msg = '', $url = '', $time = 3)
{

    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");

    $msg = empty($msg) ? '你访问的页面不存在或被删除！' : $msg;

    $url = empty($url) ? getUrl() : $url;

    require BASE_PATH . 'resource/tpl/404.php';
    die;
}

/**
 * 例  clientDown('http://img.bizhi.sogou.com/images/2012/02/13/66899.jpg');
 * @param $url 一个远程文件
 * @return bool
 */
function clientDown($url)
{

    if (empty($url)) return false;

    $fileName = basename($url);
    ob_start();
    ob_clean();

    if (function_exists('curl_init')) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        $content = file_get_contents($url);
    }

    echo $content;

    //file_put_contents($fileName, $content);//保存到服务器
    header('Content-Description: File Download');
    header('Content-type: application.octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . ob_get_length());
    header('Content-Disposition: attachment; filename=' . $fileName);

}

/**
 * 获取某个月第一天与最后一天的时间戳
 * @param  [type] $month [description]
 * @param  string $year [description]
 * @return [type]        [description]
 */
function getMonthTime($month, $year = '')
{
    if (empty($year)) $year = date('Y');

    $date['firstDay'] = strtotime($year . '-' . $month . '-1');
    $date['firstDayFormat'] = date('Y-m-d', $date['firstDay']);
    $date['lastDay'] = strtotime($date['firstDayFormat'] . '+1 month') - 1;
    $date['lastDayFormat'] = date('Y-m-d', $date['lastDay']);
    return $date;
}


/**
 * //新浪根据IP获得地址
 * @param string $ip
 * @return mixed|string
 * array ( 'ret' => 1, 'start' => -1, 'end' => -1, 'country' => '中国', 'province' => '浙江', 'city' => '杭州', 'district' => '', 'isp' => '', 'type' => '', 'desc' => '', )
 */
function Ip2Area($ip = '')
{
    $ch = curl_init();
    $options[CURLOPT_URL] = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
    $options[CURLOPT_RETURNTRANSFER] = true;
    curl_setopt_array($ch, $options);
    $re = curl_exec($ch);
    $area = json_decode($re, true);
    if (!is_array($area) || $area['ret'] == -1) return '未知地区';
    return $area;
    return $area['country'] . '  ' . $area['province'] . '  ' . $area['city'];

}


/**
 * 生成随机字符
 * @param  string $type w：英文字符 d：数字 wd: dw:数字加英文字符
 * @param  integer $len [description]
 * @return [type]        [description]
 */
function randStr($type = 'w', $len = 8)
{
    $type = strtolower($type);

    switch ($type) {
        case 'w':
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'dw':
        case 'wd':
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'd':
            $pool = '0123456789';
            break;
        default:
            $pool = mt_rand();
            break;
    }

    return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);

}


/**
 * 页面跳转
 * @param null $url
 */
function gotoUrl($url = null)
{
    if (is_null($url)) $url = getUrl();
    $location = "parent.window.location.href=\"$url\";";
    echo "<script type='text/javascript'>$location</script>";
    die;
}
