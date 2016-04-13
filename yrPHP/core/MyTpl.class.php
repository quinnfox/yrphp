<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:quinnh@163.com
 */
namespace core;
class MyTpl
{
    protected static $callNumber = 0;       //防止重复调用
    public $cacheIdRule = array();      //定义通过模板引擎组合后文件存放目录
    protected $templateDir; //编译好的模版文件名
    protected $comFileName;   //定义编译文件存放目录
    protected $compileDir; //bool 设置缓存是否开启
    protected $caching = true;    //定义缓存时间
    protected $cacheLifeTime = 3600; //定义生成的缓存文件地址
    protected $cacheDir;          //定义生成的缓存文件名
    protected $cacheFile;       //在模板中嵌入动态数据变量的左定界符号
    protected $leftDelimiter = '{';    //在模板中嵌入动态数据变量的右定界符号
    protected $rightDelimiter = '}'; //内部使用的临时变量
    protected $preg_arr = array();//替换搜索的模式的数组
    protected $replace_arr = array();//用于替换的字符串数组


    public function __construct()
    {
        ob_start();
    }

    /**
     * 将PHP中分配的值会保存到成员属性$tplVars中，用于将板中对应的变量进行替换
     * @param    string $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    mixed $value 需要一个标量类型的值，用来分配给模板中变量的值
     */
    function assign($tplVar, $value = null)
    {
        if ($tplVar != '')
            $this->tplVars[$tplVar] = $value;
    }

    /**
     * 加载指定目录下的模板文件，并将替换后的内容生成组合文件存放到另一个指定目录下
     * @param    string $fileName 提供模板文件的文件名
     * @param    array $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    function display($fileName, $tplVars = '', $cacheId = '')
    {
        //缓存静态文件
        $this->init($cacheId);

        if (!empty($tplVars)) $this->tplVars = array_merge($this->tplVars, $tplVars);

        extract($this->tplVars);

        /* 到指定的目录中寻找模板文件 */
        $tplFile = $this->templateDir . $fileName;

        /* 如果需要处理的模板文件不存在,则退出并报告错误 */
        if (!file_exists($tplFile)) die("模板文件{$tplFile}不存在！");

        /* 获取组合的模板文件，该文件中的内容都是被替换过的 */
        $comFileDir = $this->compileDir . C('ctlName');

        if (!file_exists($comFileDir)) mkdir($comFileDir, 0755);

        $this->comFileName = $comFileDir . '/' . $fileName . '.php';

        $ctlFile = C('classPath');//控制器文件

        if (!file_exists($this->comFileName) || filemtime($this->comFileName) < filemtime($tplFile) || filemtime($this->comFileName) < filemtime($ctlFile)) {
            $repContent = $this->tpl_replace(file_get_contents($tplFile));
            /* 保存由系统组合后的脚本文件 */
            file_put_contents($this->comFileName, $repContent);
        }
        /* 包含处理后的模板文件输出给客户端 */

        require($this->comFileName);

    }

    /**
     * 静态化
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    public function init($cacheId = '')
    {
        if (self::$callNumber) return;

        if ($this->caching) {
            //self::$cacheId[] = $cacheId;
            $cacheDir = rtrim($this->cacheDir, '/') . '/' . C('ctlName');

            if (!file_exists($cacheDir)) mkdir($cacheDir, 0755);

            $this->cacheFile = $cacheDir . '/' . C('actName');
            $this->cacheFile .= empty($cacheId) ? '' : '_' . $cacheId;
            $this->cacheFile .= '.html';


            if (file_exists($this->cacheFile)) {
                requireCache($this->cacheFile);
                exit;
            }
            self::$callNumber++;

        }
    }

    private function tpl_replace($content)
    {
        $left = preg_quote($this->leftDelimiter, '/');
        $right = preg_quote($this->rightDelimiter, '/');
        $this->preg_arr[] = '/' . $left . '\s*\$(.*)\s*' . $right . '/isU';//输出变量
        $this->preg_arr[] = '/' . $left . '\s*(\s*.*\(.*\)\s*)\s*' . $right . '/isU';//输出函数
        $this->preg_arr[] = '/' . $left . '\s*(foreach|loop)\s*(.*)\s*' . $right . '/isU'; //foreach
        $this->preg_arr[] = '/' . $left . '\s*while\s*\((.*)\)\s*' . $right . '/isU'; //while
        $this->preg_arr[] = '/' . $left . '\s*for\s*\((.*)\)\s*' . $right . '/isU'; //for
        $this->preg_arr[] = '/' . $left . '\s*if\s+(.*)\s*' . $right . '/isU';//判断
        $this->preg_arr[] = '/' . $left . '\s*else\s*' . $right . '/';//判断
        $this->preg_arr[] = '/' . $left . '\s*(\/foreach|\/for|\/while|\/if|})\s*' . $right . '/isU'; //end foreach end for end if }
        $this->preg_arr[] = '/' . $left . '\s*(include|require)\s+(.*)\s*' . $right . '/isU';//包含标签
        //$this->preg_arr[] = '/'.$left.'\s*assign\s+var=(.*)\s+value=(.*)\/'.$right.'/isU';//;//assign输出
        $this->preg_arr[] = '/' . $left . '\s*assign\s+(.*)\s*=\s*(.*)' . $right . '/isU'; //分配变量
        $this->preg_arr[] = '/' . $left . '\s*(break|continue)\s*' . $right . '/isU';

        $this->replace_arr[] = "<?php echo $\\1;?>";
        $this->replace_arr[] = "<?php echo \\1;?>";
        $this->replace_arr[] = "<?php foreach(\\2){?>";
        $this->replace_arr[] = "<?php while(\\1){?>";
        $this->replace_arr[] = "<?php for(\\1){ ?>";
        $this->replace_arr[] = "<?php if(\\1){?>\n";
        $this->replace_arr[] = "<?php }else{?>";
        $this->replace_arr[] = "<?php } ?>";
        $this->replace_arr[] = "<?php \$this->display('\\2');?>";
        $this->replace_arr[] = "<?php \\1 = \\2;?>";
        $this->replace_arr[] = "<?php \\1;?>";

        $content = preg_replace($this->preg_arr, $this->replace_arr, $content);

        //变量替换
        foreach ($this->tplVars as $key => $value) {
            $content = preg_replace('/\$(' . $key . ')/', '$\\1', $content);
        }
        return $content;

    }

    /**
     * 析构函数 最后生成静态文件
     */
    function __destruct()
    {
        $content = ob_get_contents();


        if(file_exists($this->comFileName) && $this->caching){
            if(!file_exists($this->cacheFile)){
                file_put_contents($this->cacheFile, $content);
            }elseif($this->cacheLifeTime != 0 && filemtime($this->cacheFile) + $this->cacheLifeTime < time()){
                file_put_contents($this->cacheFile, $content);
            }
        }

        ob_end_flush();
        if (DEBUG) {
            ini_set('display_errors', 1);
            echo Debug::message();
        } else {
            ini_set('display_errors', 0);
        }
    }

    /**
     * 清空缓存 默认清空所以缓存
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     * @param    string $cacheId 清除指定模版ID
     */
    protected function clearCache($template = '', $cacheId = '')
    {
        if (empty($cacheId)) {
            return $this->delDir($this->cacheDir, $template);
        } else {
            return unlink($this->cacheDir . $template . '_' . $cacheId . '.html');
        }
    }

    /**
     * 清空文件夹 默认清空所有文件
     * @param    string $file 目录或则目录地址 当是目录时 清空目录内所有文件
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     */
    protected function delDir($file, $template = '')
    {
        if (is_dir($file)) {
            //如果不存在rmdir()函数会出错
            if ($dir_handle = @opendir($file)) {            //打开目录并判断是否成功
                while ($filename = readdir($dir_handle)) {        //循环遍历目录
                    if ($filename != "." && $filename != "..") {    //一定要排除两个特殊的目录
                        $subFile = $file . "/" . $filename;    //将目录下的文件和当前目录相连
                        if (is_dir($subFile))                    //如果是目录条件则成立
                            $this->delDir($subFile);                //递归调用自己删除子目录
                        if (is_file($subFile)) {                //如果是文件条件则成立
                            if (empty($template)) {
                                unlink($subFile);                    //直接删除这个文件
                            } elseif (strpos($filename, $template) !== false) {
                                unlink($subFile);
                            }
                        }
                    }
                }
                closedir($dir_handle);                        //关闭目录资源
                return true;
                //rmdir($file);                     			//删除空目录

            }
        } elseif (is_file($file)) {
            unlink($file);
        }
    }
}

