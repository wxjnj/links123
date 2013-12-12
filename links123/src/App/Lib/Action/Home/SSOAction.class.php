<?php

/**
 * 单点登录接口action
 *
 * @author adam
 */
class SSOAction extends Action {

    public function index() {
		D('User','Service')->onsynlogin();
        exit;
    }
    public function synlogin(){
		echo D('User','Service')->synlogin();
    }

}

?>
