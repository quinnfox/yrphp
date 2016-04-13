<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:quinnh@163.com
 */

//数据库配置例子 请将该文件复制到你的项目下的conf文件夹下 不允许直接在该文件下配置
return array(
    //主服务器
    'masterServer' => array(
        'dsn'         => '',
        'dbDriver'    => 'pdo', // 数据库类型
        'dbType'      => 'mysql', // 数据库类型
        'dbHost'      => '', // 服务器地址
        'dbName'      => '', // 数据库名
        'dbUser'      => '', // 用户名
        'dbPwd'       => '', // 密码
        'dbPort'      => '3306', // 端口
        'tablePrefix' => '', // 数据库表前缀
        'charset'     => 'utf8',
    ),
    //从服务器可以配置多个,也可以不配置，不做读写分离
    'slaveServer'  => array(
        array(
            'dsn'         => '',
            'dbDriver'    => 'pdo', // 数据库类型
            'dbType'      => 'mysql', // 数据库类型
            'dbHost'      => '', // 服务器地址
            'dbName'      => '', // 数据库名
            'dbUser'      => '', // 用户名
            'dbPwd'       => '', // 密码
            'dbPort'      => '3306', // 端口
            'tablePrefix' => '', // 数据库表前缀
            'charset'     => 'utf8',
        ),
        array(
            'dsn'         => '',
            'dbDriver'    => 'pdo', // 数据库类型
            'dbType'      => 'mysql', // 数据库类型
            'dbHost'      => '', // 服务器地址
            'dbName'      => '', // 数据库名
            'dbUser'      => '', // 用户名
            'dbPwd'       => '', // 密码
            'dbPort'      => '3306', // 端口
            'tablePrefix' => '', // 数据库表前缀
            'charset'     => 'utf8',
        ),
    ),
);


