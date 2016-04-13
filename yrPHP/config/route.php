<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:nathankwin@163.com
 */
/**
 * 路由规则 就是正则匹配
 */
$route['abc/test1/(.*)'] = 'users/test/:1';//访问abc/test1/123 则实际访问的是users/test/123
