<?php

/*
 * 英语角基础控制类
 * @author adam 2013.5.28
 */

class EnglishAction extends BaseAction {

    //重写父类的初始化方法
    public function _initialize() {
    	
    	// 重定向到新的英语角en @author slate
    	$redirectUrl = C('ENGLISH_REDIRECT_URL');
    	if ($redirectUrl) {
    		header('HTTP/1.1 301 Moved Permanently');  
			header('Location: ' . $redirectUrl); 
    	} 
        parent::_initialize(); //调用父类初始化方法
    }

}

?>
