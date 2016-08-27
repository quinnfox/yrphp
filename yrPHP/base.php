<?php

/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
 */
class Entry
{
    static function init()
    {
        define('STARTTIME', microtime(true));
        ini_set('memory_limit', -1);
        set_time_limit(0) ;
        //PHP程序所有需要的路径，都使用绝对路径
        define("BASE_PATH", str_replace("\\", "/", dirname(__FILE__)) . '/'); //框架的路径
        define("ROOT_PATH", dirname(BASE_PATH) . '/'); //项目的根路径，也就是框架所在的目录
        define("APP_PATH", ROOT_PATH . rtrim(APP, '/') . '/'); //用户项目的应用绝对路径
        define("LIB_PATH", APP_PATH . 'libs' . '/'); //用户项目的应用绝对路径
        define("CORE_PATH", BASE_PATH . 'core/'); //框架核心类库

        /**
         * 注册自动加载函数
         * 也可以使用一个匿名函数
         * spl_autoload_register(function(){});
         */
        spl_autoload_register('self::autoLoadClass');


        require CORE_PATH . "Debug.class.php";
        //包含系统公共函数
        require BASE_PATH . "common/functions.php";

        //包含自定义公共函数
        $commonPath = \libs\File::search(APP_PATH . 'common', '.php');
        foreach ($commonPath as $v) {
            require $v;
        }

    }


    static function loadConf()
    {
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

        session_start();

        error_reporting(-1); //报告所有PHP错误
        if (C('logRecord')) {
            ini_set('log_errors', 1); //设置是否将脚本运行的错误信息记录到服务器错误日志或者error_log之中
            $logFile = rtrim(C('logDir'), '/') . '/sys_log_' . date("Y-m-d") . '.log';//定义日志文件名;
            ini_set('error_log', $logFile); //将错误信息写进日志 APP.'runtime/logs'/sys_log_' . date("Y-m-d") . '.log'
            //开启自定义错误日志
            set_error_handler(array('Entry', "yrError"));
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


    }


    /*
    /设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
    $include_path=get_include_path();                         //原基目录
    $include_path.=PATH_SEPARATOR.ROOT_PATH;       //框架中基类所在的目录
    //设置include包含文件所在的所有目录
    set_include_path($include_path);
    */
    static function autoLoadClass($className)
    {
        core\Debug::start();
        $classList = array();
        if (isset($classList[$className])) {
            $classPath = $classList[$className];
        } else {
            $classPath = "";

            $classMap = require APP_PATH . "config/classMap.php";

            if (isset($classMap[$className])) {
                $classPath = $classMap[$className];

            } else if (false !== strpos($className, '\\')) {

                $path = explode('\\', $className);
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


    /**
     * 错误处理函数
     * @param $errNo
     * @param $errStr
     * @param $errFile
     * @param $errLine
     * @return bool
     */
    static function yrError($errNo, $errStr, $errFile, $errLine)
    {

        $log_file =  '%s_log_' . date("Y-m-d") . '.log';//定义日志文件名;
        $template = '';

        switch ($errNo) {
            case E_USER_ERROR:
                $template .= "用户ERROR级错误，必须修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'error');

                break;
            case E_WARNING://运行时警告（非致命的错误）2 
            case E_USER_WARNING:
                $template .= "用户WARNING级错误，建议修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'warning');
                break;

            case E_NOTICE://运行时注意消息（可能是或者可能不是一个问题） 8
            case E_USER_NOTICE:
                $template .= "用户NOTICE级错误，不影响系统，可不修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'notice');
                break;

            default:
                $template .= "未知错误类型: 错误编号[$errNo] $errStr  ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'unknown');
                break;
        }

        \core\Debug::log($log_file,$template);

        return true;
    }

    static function run()
    {

        self::init();
        self::loadConf();

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

        C(array(
            'classPath' => $classPath,
            'ctlName' => $className,
            'actName' => $action,
            'nowAction' => $nowAction,
            'lang' => $_SESSION['lang']
        ));

        if (file_exists($classPath)) {
            require $classPath;

            if (class_exists($className)) {

                $class = loadClass($className);

                $class->$action();

            }

        } else {

            error404();
        }
    }


}


Entry::run();
