<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:quinnh@163.com
 */

define('STARTTIME', microtime(true));
ini_set('memory_limit', -1);
//set_time_limit(18000) ;
//PHP程序所有需要的路径，都使用相对路径
define("BASE_PATH", str_replace("\\", "/", dirname(__FILE__)) . '/'); //框架的路径
define("ROOT_PATH", dirname(BASE_PATH) . '/'); //项目的根路径，也就是框架所在的目录
define("APP_PATH", ROOT_PATH . rtrim(APP, '/') . '/'); //用户项目的应用绝对路径
define("CORE_PATH", BASE_PATH . 'core/'); //框架核心类库
define("LIBS_PATH", BASE_PATH . 'libs/'); //框架集成常用类库
require CORE_PATH . "Debug.class.php";
//包含系统公共函数
require BASE_PATH . "common/functions.php";
//包含自定义公共函数
if (file_exists(APP_PATH . "common/functions.php")) {
    require APP_PATH . "common/functions.php";
}


//包含系统配置文件
C(require BASE_PATH . "config/config.php");
//包含自定义配置文件
$configPath = APP_PATH . "config/config.php";
if (defined('APP_MODE'))
    $configPath = APP_PATH . "config/config_" . APP_MODE . ".php";

if (file_exists($configPath)) C(require $configPath);


header("Content-Type:" . C('contentType') . ";charset=" . C('charset')); //设置系统的输出字符为utf-8
date_default_timezone_set(C('timezone')); //设置时区（默认中国）

if ($sessionName = C('session_name')) session_name($sessionName);
if ($sessionPath = C('session_save_path')) session_save_path($sessionPath);
if ($session_expire = C('session_expire')) {
    ini_set('session.gc_maxlifetime', $session_expire);
    ini_set('session.cookie_lifetime', $session_expire);
}
ini_set('session.cookie_domain', C('session_domain'));

error_reporting(-1);//报告所有PHP错误
if (C('logRecord')) {
    ini_set('log_errors', 1);//设置是否将脚本运行的错误信息记录到服务器错误日志或者error_log之中
    ini_set('error_log', C('logFile'));//将错误信息写进日志 APP.'runtime/Logs'.date('Y-m-d').'.txt'
}


if (!defined('DEBUG')) define('DEBUG', false);

//错误信息是否显示
if (DEBUG) {
    ini_set("display_errors", 1);//显示错误到屏幕
} else {
    ini_set("display_errors", 0);//隐藏而不显示
}


if (isset($_GET['lang'])) {
    $_SESSION['lang'] = strtolower($_GET['lang']);
} else {
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'en';
    }
}


$langPath = APP_PATH . 'lang/lang_' . $_SESSION['lang'] . '.php';
if (file_exists($langPath)) getLang(require $langPath);

$url = loadClass('core\Uri')->rsegment();

if (C('url_model') == 0) { //普通模式 GET
    $className = empty($_GET[C('controller_trigger')]) ? C('default_controller') : strtolower($_GET[C('controller_trigger')]);

    $action = empty($_GET[C('function_trigger')]) ? C('default_action') : strtolower($_GET[C('function_trigger')]);
} else { //(PATHINFO 模式)
    $className = empty($url[1]) ? C('default_controller') : strtolower($url[1]);

    $action = empty($url[2]) ? C('default_action') : strtolower($url[2]);

}

$nowAction = $className . '/' . $action;

$classPath = APP_PATH . 'controls/' . ucfirst($className) . '.class.php';

C(array('classPath' => $classPath, 'ctlName' => $className, 'actName' => $action, 'lang' => $_SESSION['lang']));

if (file_exists($classPath)) {

    require $classPath;

    $class->$action();

} else {

    error404();
}


