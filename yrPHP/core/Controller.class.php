<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:nathankwin@163.com
 */
namespace core;
requireCache('MyTpl.class.php');

abstract class Controller extends MyTpl
{
    function __construct()
    {
        /*******************构造方法，用于初使化模版(Smarty)对象中的成员属性********************/
        parent::__construct();         //调用父类被覆盖的构造方法
        $this->templateDir = C('setTemplateDir');       //定义模板文件存放的目录
        $this->compileDir = C('setCompileDir');      //定义通过模板引擎组合后文件存放目录
        $this->caching = C('caching');     //缓存开关 1开启，0为关闭
        $this->cacheLifeTime = C('cache_lifetime');  //设置缓存的时间 0代表永久缓存
        $this->cacheDir = C('setCacheDir');      //设置缓存的目录
        $this->leftDelimiter = C('left_delimiter');          //在模板中嵌入动态数据变量的左定界符号
        $this->rightDelimiter = C('right_delimiter'); //模板文件中使用的“右”分隔符号

        $this->uri = loadClass('core\Uri');


    }


    /**
     * 重写这个方法 在构造函数中调用
     * 缓存初始化 判断缓存ID是否合理 避免生成无用静态文件
     */
    private function checkCacheId(){
        $act = C('actName');
        switch ($act){
            case "index":
                $param =$_GET['id'];
                if($param=='') error404('参数错误');

                break;
            default:
                $param = '';
                break;
        }
        $this->init($param);
    }

    public function __call($method, $args)
    {
        error404();
    }
}