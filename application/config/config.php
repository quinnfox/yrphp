<?php
return array(
	'url_model' => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
	// 0 (普通模式); 1 (PATHINFO 模式); 2(REWRITE重写模式) 默认为PATHINFO 模式

	'contentType'            => 'application/json', //指定客户端能够接收的内容类型
	'charset'            => 'UTF-8', //采用编码格式

	/*--------------------以下是数据库配置---------------------------------------*/
	'defaultFilter'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数过滤 多个用|分割stripslashes|htmlspecialchars
	'openCache'        =>  false, //是否开启数据库缓存
	'dbCacheTime'        =>  0, //数据缓存时间0表示永久
	'dbCacheType'        =>  'file', //数据缓存类型 file|memcache|memcached|redis
	'dbCachePath'        =>  APP_PATH.'runtime/data/',//数据缓存文件地址(仅对file有效)


);


