<?php
/**
 * @name DemoAction
 * 
 * @desc Demo: 新首页
 * 
 * @package Home
 * 
 * @version 3.0
 * 
 * @author slate date:2013-09-06
 */

import("@.Common.CommonAction");

class DemoAction extends CommonAction {
	
	/**
	 * @desc 新首页
	 * 
	 * @author slate date:2013-09-06
	 */
	public function index() {
		
		$this->getHeaderInfo();
		$this->display();
	}
}