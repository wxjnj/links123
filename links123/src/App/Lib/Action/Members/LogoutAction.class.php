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
        cookie('lnkarealist', null);
        cookie('lnkmyarea_sort', null);
        cookie(md5(C('MEMBER_AUTH_KEY')), null);//设置cookie记录用户登录信息，提供给英语角同步登录 Adam 2013.09.27 @todo 安全性，下一步进行单点登录优化 
		cookie("USER_ID", null);//退出清除下次自动登录
		header("Location: " . $_SERVER["HTTP_REFERER"]); //退出后刷新页面
	}
}