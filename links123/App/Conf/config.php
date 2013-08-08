<?php
return array(
    'URL_MODEL'                 =>  2,										// 如果你的环境不支持PATHINFO 请设置为3
    
    'DB_TYPE'                   =>  'mysqli',
	'DB_HOST'                   =>  '127.0.0.1',
	'DB_NAME'                   =>  'linkstest',
	'DB_USER'                   =>  'linkstest',
	'DB_PWD'                    =>  'linkstest7066',	
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'lnk_',

	'MEMBER_AUTH_KEY'			=> 'lnkMemberId',
    'SESSION_AUTO_START'        =>	false,
		
	'APP_GROUP_LIST' 			=>	'Home,Admin,English,Homepage',					//项目分组设定
	'DEFAULT_GROUP'  			=>	'Home',									//默认分组
	
	'VAR_PAGE'					=>	'p',
	
	'URL_ROOT'					=>	'/links123',
	'URL_HOMEPAGE'				=>	'/links123/homepage',

	/**
	 * 路由相关
	 */
	'URL_ROUTER_ON'				=>	true,									//开启路由
	'URL_CASE_INSENSITIVE'		=>	false,									//URL大小写
	'URL_HTML_SUFFIX'			=>	'html|htm|shtml',						//网页后缀名
	'URL_ROUTE_RULES'			=>	array(
		'homepage/search/top/:search_type/:keyword'		=>	'Homepage/Search/top',
		'homepage/search/top/:search_type'				=>	'Homepage/Search/top',
		'/^homepage\/search\/(.*)\/(.*)/' 				=>	'Homepage/Search/index?search_type=:1&keyword=:2',
		'/^homepage\/search\/(.*)$/' 					=>	'Homepage/Search/index?search_type=:1',
	),
);
