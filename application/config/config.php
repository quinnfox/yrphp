<?php
return array(
	'url_model' => 2, // URL����ģʽ,��ѡ����0��1��2��3,������������ģʽ��
	// 0 (��ͨģʽ); 1 (PATHINFO ģʽ); 2(REWRITE��дģʽ) Ĭ��ΪPATHINFO ģʽ

	'contentType'            => 'application/json', //ָ���ͻ����ܹ����յ���������
	'charset'            => 'UTF-8', //���ñ����ʽ

	/*--------------------���������ݿ�����---------------------------------------*/
	'defaultFilter'        =>  'htmlspecialchars', // Ĭ�ϲ������˷��� ����I�������� �����|�ָ�stripslashes|htmlspecialchars
	'openCache'        =>  false, //�Ƿ������ݿ⻺��
	'dbCacheTime'        =>  0, //���ݻ���ʱ��0��ʾ����
	'dbCacheType'        =>  'file', //���ݻ������� file|memcache|memcached|redis
	'dbCachePath'        =>  APP_PATH.'runtime/data/',//���ݻ����ļ���ַ(����file��Ч)


);


