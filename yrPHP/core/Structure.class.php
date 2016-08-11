<?php
/**
 * 自动生成目录结构
 * Created by yrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 */
namespace core;


class Structure
{

    static function run()
    {

        $fun = <<<st
<?php
/**
 * 自定义函数库
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

st;

        $controls = <<<st
<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
use core\Controller;

class index extends Controller
{
    function __construct()
    {
        parent::__construct();

    }


    function  index()
    {
    echo "<h1>欢迎使用yrPHP 有什么建议或则问题 请随时联系我<br/>QQ：284843370<br/>email:kwinwong@hotmail.com</h1>";
    }
    }
st;


        $html = <<<st
<!DOCTYPE html>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>

<p>Directory access is forbidden.</p>

</body>
</html>
st;


        $phpReturn = <<<st
<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

return array(

            );
st;






        $path = array(
            APP_PATH . 'common/index.html'              => $html,
            APP_PATH . 'common/functions.php'           => $fun,
            APP_PATH . 'config/index.html'              => $html,
            APP_PATH . 'controls/index.html'            => $html,
            APP_PATH . 'controls/Index.class.php'       => $controls,
            APP_PATH . 'core/index.html'                => $html,
            APP_PATH . 'lang/index.html'                => $html,
            APP_PATH . 'lang/lang_cn.php'               => $phpReturn,
            APP_PATH . 'libs/index.html'                => $html,
            APP_PATH . 'models/index.html'              => $html,
            APP_PATH . 'runtime/index.html'             => $html,
            APP_PATH . 'runtime/cache/index.html'       => $html,
            APP_PATH . 'runtime/session/index.html'       => $html,
            APP_PATH . 'runtime/compile_tpl/index.html' => $html,
            APP_PATH . 'runtime/data/index.html'        => $html,
            APP_PATH . 'runtime/Logs/index.html'        => $html,
            APP_PATH . 'views/index.html'               => $html,
        );


        foreach ($path as $k => $v) {
            \libs\File::vi($k, $v);
        }
        \libs\File::cp(BASE_PATH.'config',APP_PATH.'config');
        \libs\File::mkDir(ROOT_PATH.'public');
        header("Location: " . getUrl());

    }
}