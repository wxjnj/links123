<?php
class IndexAction extends CommonAction {
    // 框架首页
    public function index() {
    	if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
    		redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
    	}
        C ( 'SHOW_RUN_TIME', false ); // 运行时间显示
        C ( 'SHOW_PAGE_TRACE', false );
        $this->display();
        return;
    }
}