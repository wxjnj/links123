<?php
return array(
    'TMPL_ACTION_ERROR'         =>	'Public:error',							// 默认错误跳转对应的模板文件
    
    'SHOW_PAGE_TRACE'           =>	false,									//显示调试信息
    
	'MEMBER_AUTH_KEY'			=>	'lnkMemberId',
		
	'COOKIE_EXPIRE'				=>	86400,
	'COOKIE_PREFIX'				=>	'lnk',
	

	'404_PAGE'             		=>	'Public:404' ,    						//网站404页面模板文件
	'UPDATE_PAGE'             	=>	'Public:updating' ,						//网站升级维护页面模板文件
);
