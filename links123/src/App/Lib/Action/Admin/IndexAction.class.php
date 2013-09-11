<?php

/**
 * @name IndexAction.class.php
 * @package Admin
 * @desc 框架默认首页
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class IndexAction extends CommonAction {

	/**
	 * @desc 框架首页
	 * @see IndexAction::index()
	 */
    public function index() {
    	if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
    		redirect(PHP_FILE .C('USER_AUTH_GATEWAY'));
    		exit(0);
    	}
        C('SHOW_RUN_TIME',false);
        C('SHOW_PAGE_TRACE',false);
        $this->display();
        return;
    }
}