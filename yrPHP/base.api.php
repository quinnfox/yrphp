<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:nathankwin@163.com
 */

define('STARTTIME', microtime(true));
ini_set('memory_limit', -1);
//set_time_limit(18000) ;
//PHP程序所有需要的路径，都使用相对路径
define("BASE_PATH", str_replace("\\", "/", dirname(__FILE__)) . '/'); //框架的路径
define("ROOT_PATH", dirname(BASE_PATH) . '/'); //项目的根路径，也就是框架所在的目录
define("APP_PATH", ROOT_PATH . rtrim(APP, '/') . '/'); //用户项目的应用绝对路径
define("CORE_PATH", BASE_PATH . 'core/'); //框架核心类库
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

if (isset($_GET['country'])) {
    $_SESSION['country'] = strtoupper($_GET['country']);
} else {
    if (!isset($_SESSION['country'])) {
        $_SESSION['country'] = 'CN';
    }
}

$langPath = APP_PATH . 'lang/lang_' . $_SESSION['lang'] . '.php';
if (file_exists($langPath)) getLang(require $langPath);

$url = loadClass('core\Uri')->rsegment();

$ctrBasePath = APP_PATH . 'controls/';


if (C('urlType') == 0) { //普通模式 GET

    if (empty($_GET[C('ctlTrigger')])) {
        $className = C('defaultCtl');
    } else {
        $url = ltrim('/', strtolower($_GET[C('ctlTrigger')]));

        foreach ($url as $k => $v) {

            if (is_dir($ctrBasePath . $v)) {
                $ctrBasePath = $ctrBasePath . $v . '/';
                $className = empty($url[$k + 1]) ? C('defaultCtl') : strtolower($url[$k + 1]);
            } else {
                break;
            }
        }

    }

    $action = empty($_GET[C('actTrigger')]) ? C('defaultAct') : strtolower($_GET[C('actTrigger')]);

} else { //(PATHINFO 模式)

    $className = C('defaultCtl');
    $action = C('defaultCtl');

    foreach ($url as $k => $v) {
        $v = strtolower($v);

        if (is_dir($ctrBasePath . $v)) {
            $ctrBasePath = $ctrBasePath . $v . '/';
            $className = empty($url[$k + 1]) ? C('defaultCtl') : strtolower($url[$k + 1]);
            $action = empty($url[$k + 2]) ? C('defaultCtl') : strtolower($url[$k + 2]);
        } else {
            break;
        }
    }

}


$nowAction = $className . '/' . $action;

$classPath = $ctrBasePath . ucfirst($className) . '.class.php';

C(array('classPath' => $classPath, 'ctlName' => $className, 'actName' => $action, 'lang' => $_SESSION['lang']));

if (file_exists($classPath)) {
    require $classPath;

    if (class_exists($className)) {
        $class = loadClass($className);
        /**************配合API调试*************************/
        $free = array('financial/alipayverify', 'web/privacy');

        if (in_array($className, array('test')) || $className == 'web') {
            $class->$action();
        } else if (DEBUG) {
            echo $class->$action();
        } else {
            $des = loadClass('libs\DES3');
            //  echo '{"data":"' . $des->encrypt($class->$action()) . '"}';     //接口专用
            echo '{"data":"' . $class->$action() . '"}';     //接口专用
        }
        /***************************************************/


    }

} else {

    error404();
}


