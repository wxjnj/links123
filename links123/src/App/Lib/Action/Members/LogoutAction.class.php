<?php 
/**
 * @name LogoutAction
 * @package Member
 * @desc 用户退出
 * @version 1.0
 * @author frank qian 2013-08-13
 */

import("@.Common.CommonAction");
class LogoutAction extends CommonAction
{
	/**
	 * @desc 用户退出
	 * @author frank qian 2013-08-18
	 * @see LogoutAction::index()
	 */
	public function index()
	{
		$this->userService->logout();
		header("Location: " . $_SERVER["HTTP_REFERER"]); //退出后刷新页面
	}
}