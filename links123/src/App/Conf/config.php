<?php
return array(
    'URL_MODEL'                 =>  2,										// 如果你的环境不支持PATHINFO 请设置为3
    
    'DB_TYPE'                   =>  'mysqli',
	'DB_HOST'                   =>  'localhost',//'112.124.15.96',
	'DB_NAME'                   =>  'links123_public',
	'DB_USER'                   =>  'root',//'links123_public',
	'DB_PWD'                    =>  '',//'links1230820',	
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'lnk_',

	'MEMBER_AUTH_KEY'			=> 'lnkMemberId',
    'SESSION_AUTO_START'        =>	false,
		
	'APP_GROUP_LIST' 			=>	'Home,Admin,English,Homepage,Members',					//项目分组设定
	'DEFAULT_GROUP'  			=>	'Home',									//默认分组
	
	'VAR_PAGE'					=>	'p',
	
	'URL_ROOT'					=>	'localhost/links123',
	'URL_HOMEPAGE'				=>	'localhost/links123/homepage',

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
	'TMPL_PARSE_STRING'         =>array(
		'__PUBLIC__'						=> 'http://static.links123.cn/Public',
		'__STATIC__'            => 'http://static.links123.cn',//'http://a.links123.net', // 更改默认的/Public 替换规则
	),
    "VIDEO_UPLOAD_PATH" =>  "http://121.199.26.124/" //英语角视频存储路径
    ,"COOKIE_DOMAIN"            =>  ".links123.net"
);
