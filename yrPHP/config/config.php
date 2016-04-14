<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:nathankwin@163.com
 */
return array(
    'url_model'          => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2(REWRITE重写模式) 默认为PATHINFO 模式
    'controller_trigger' => 'c',
    'function_trigger'   => 'm',
    'default_controller' => 'Index', // 默认控制器名称
    'default_action'     => 'index', // 默认操作名称
    'contentType'        => 'text/html', //指定客户端能够接收的内容类型
    'charset'            => 'UTF-8', //采用编码格式
    'timezone'           => 'PRC', //时区
    'urlSuffix'          => '.html',     // 默认URL文件后缀
    /* -----------错误处理----------------------------------*/
    'logRecord'          => true,   // 默认错误记录日志
    'logFile'            => APP_PATH . 'runtime/Logs/' . date('Y-m-d') . '.txt', // 日志记录类型 默认为文件方式

    'modelDir'           => APP_PATH . "models/", //设置模型目录位置
    /*--------------------以下是模版配置---------------------------------------*/
    'setTemplateDir'     => APP_PATH . "views/", //设置模板目录位置
    'setCompileDir'      => APP_PATH . "runtime/compile_tpl/", //设置模板被编译成PHP文件后的文件位置
    'auto_literal'       => false, //忽略限定符周边的空白
    'caching'            => 1, //缓存开关 1开启，0为关闭
    'setCacheDir'        => (APP_PATH . "runtime/cache/"), //设置缓存的目录
    'cache_lifetime'     => 60 * 60 * 24 * 7, //设置缓存的时间
    'left_delimiter'     => "{", //模板文件中使用的“左”分隔符号
    'right_delimiter'    => "}", //模板文件中使用的“右”分隔符号
//    'setPluginsDir'      => '', // 设置插件目录。即自定义的一系列函数所在位置。


    /*--------------------以下是数据库配置---------------------------------------*/
    'openCache'          => true, //是否开启缓存
    'defaultFilter'      => 'htmlspecialchars', // 默认参数过滤方法 用于I函数过滤 多个用|分割stripslashes|htmlspecialchars
    'dbCacheTime'        => 0, //数据缓存时间0表示永久
    'dbCacheType'        => 'file', //数据缓存类型 file|memcache|memcached|redis
    //单个item大于1M的数据存memcache和读取速度比file
    'dbCachePath'        => APP_PATH . 'runtime/data/',//数据缓存文件地址(仅对file有效)
    'dbCacheExt'         => 'php',//生成的缓存文件后缀(仅对file有效)

    'memcache'           => '127.0.0.1:11211',//string|array多个用数组传递 array('127.0.0.1:11211','127.0.0.1:1121')
    'redis'              =>'127.0.0.1:6379',//string|array多个用数组传递 array('127.0.0.1:6379','127.0.0.1:6378')


    /*--------------------以下是session配置---------------------------------------*/
    'session_prefix'     => 'yrPHP_',
    'session_expire'     => 7200,//有效期时长
    'session_save_path'  => APP_PATH . "runtime/session/",
    'session_name'       => 'yrPHP',
    'session_domain'     => '',//设置域，默认为当前域名

    /*--------------------以下是cookie配置---------------------------------------*/
    'cookie_prefix'      => 'yrPHP_',
    'cookie_expire'      => 7200,//有效期时长
    'cookie_path'        => "/",
    'cookie_domain'      => '',//设置域，默认为当前域名

);


