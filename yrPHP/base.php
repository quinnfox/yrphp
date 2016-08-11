<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
 */

define('STARTTIME', microtime(true));
ini_set('memory_limit', -1);
//set_time_limit(18000) ;
//PHP程序所有需要的路径，都使用绝对路径
define("BASE_PATH", str_replace("\\", "/", dirname(__FILE__)) . '/'); //框架的路径
define("ROOT_PATH", dirname(BASE_PATH) . '/'); //项目的根路径，也就是框架所在的目录
define("APP_PATH", ROOT_PATH . rtrim(APP, '/') . '/'); //用户项目的应用绝对路径
define("CORE_PATH", BASE_PATH . 'core/'); //框架核心类库
require CORE_PATH . "Debug.class.php";
//包含系统公共函数
require BASE_PATH . "common/functions.php";

//包含自定义公共函数
$commonPath = \libs\File::search(APP_PATH . 'common', '.php');
foreach ($commonPath as $v) {
    require $v;
}

//包含系统配置文件
C(require BASE_PATH . "config/config.php");
//包含自定义配置文件
$configPath = APP_PATH . "config/config.php";
if (defined('APP_MODE')) {
    $configPath = APP_PATH . "config/config_" . APP_MODE . ".php";
}

if (file_exists($configPath)) {
    C(require $configPath);
}

header("Content-Type:" . C('contentType') . ";charset=" . C('charset')); //设置系统的输出字符为utf-8
date_default_timezone_set(C('timezone')); //设置时区（默认中国）

if ($sessionName = C('session_name')) {
    session_name($sessionName);
}

if ($sessionPath = C('session_save_path')) {
    session_save_path($sessionPath);
}

if ($session_expire = C('session_expire')) {
    ini_set('session.gc_maxlifetime', $session_expire);
    ini_set('session.cookie_lifetime', $session_expire);
}
ini_set('session.cookie_domain', C('session_domain'));

error_reporting(-1); //报告所有PHP错误
if (C('logRecord')) {
    ini_set('log_errors', 1); //设置是否将脚本运行的错误信息记录到服务器错误日志或者error_log之中
    ini_set('error_log', C('logFile')); //将错误信息写进日志 APP.'runtime/Logs'.date('Y-m-d').'.txt'
}

if (!defined('DEBUG')) {
    define('DEBUG', false);
}

//错误信息是否显示
if (DEBUG) {
    ini_set("display_errors", 1); //显示错误到屏幕
} else {
    ini_set("display_errors", 0); //隐藏而不显示
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = strtolower($_GET['lang']);
} else {
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'en';
    }
}

if (isset($_GET['country'])) {
    $_SESSION['country'] = strtoupper($_GET['country']);
} else {
    if (!isset($_SESSION['country'])) {
        $_SESSION['country'] = 'CN';
    }
}

$langPath = APP_PATH . 'lang/lang_' . $_SESSION['lang'] . '.php';
if (file_exists($langPath)) {
    getLang(require $langPath);
}

$url = loadClass('core\Uri')->rsegment();

$ctrBasePath = C('ctrBasePath');

//默认控制器文件
$defaultCtl = C('defaultCtl');

//默认方法
$defaultAct = C('defaultAct');

if (C('urlType') == 0) {
    //普通模式 GET

    if (empty($_GET[C('ctlTrigger')])) {
        $className = $defaultCtl;
    } else {
        $url = ltrim('/', strtolower($_GET[C('ctlTrigger')]));

        foreach ($url as $k => $v) {

            if (is_dir($ctrBasePath . $v)) {
                $ctrBasePath = $ctrBasePath . $v . '/';
                $className = empty($url[$k + 1]) ? $defaultCtl : strtolower($url[$k + 1]);
            } else {
                break;
            }
        }

    }

    $action = empty($_GET[C('actTrigger')]) ? $defaultAct : strtolower($_GET[C('actTrigger')]);

} else {
    //(PATHINFO 模式)


    foreach ($url as $k => $v) {

        $v = strtolower($v);
        if (is_dir($ctrBasePath . $v)) {
            $ctrBasePath .= empty($v) ? '' : $v . '/';
            $className = empty($url[$k + 1]) ? $defaultCtl : strtolower($url[$k + 1]);
            $action = empty($url[$k + 2]) ? $defaultAct : strtolower($url[$k + 2]);
        } else {
            $className = empty($url[$k]) ? $defaultCtl : strtolower($url[$k]);
            $action = empty($url[$k + 1]) ? $defaultAct : strtolower($url[$k + 1]);
            break;
        }
    }

}

$className = ucfirst($className);

$nowAction = $className . '/' . $action;

$classPath = $ctrBasePath . $className . '.class.php';

C(array('classPath' => $classPath, 'ctlName' => $className, 'actName' => $action, 'lang' => $_SESSION['lang']));

if (file_exists($classPath)) {
    require $classPath;

    if (class_exists($className)) {

        $class = loadClass($className);

        $class->$action();

    }

} else {

    error404();
}
