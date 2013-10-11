<?php
/**
 * @name NavAction
 * @desc 导航
 * @package Home
 * @version 1.0
 * @author kevin chen  date: 2013-10-11
 */
import("@.Common.CommonAction");
class NavAction extends CommonAction {

	/**
	 * @desc 导航首页
	 * @author kevin chen
	 */
	public function index() {
		$this->getHeaderInfo();
		$this->display('index');
	}
}