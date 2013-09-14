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
		unset($_SESSION[C('MEMBER_AUTH_KEY')]);
		unset($_SESSION['nickname']);
		unset($_SESSION['face']);
		session_destroy();
		cookie("USER_ID", null);//退出清除下次自动登录
		header("Location: " . $_SERVER["HTTP_REFERER"]); //退出后刷新页面
	}
}