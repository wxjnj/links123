<?php 
/**
 * @name LoginAction.class.php
 * @package Members
 * @desc 用户登录
 * @author frank qian 2013-08-12
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class LoginAction extends CommonAction
{
	public function index()
	{
		$this->assign('banner', $this->getAdvs(4, "banner"));
		$this->assign('title', '另客岛民请登录，享受您另客岛民专有的服务');
		$this->assign('Description', '另客会员专区有众多只有会员才能享有的资源和服务');
		$this->display();
	}
	
	public function checkLogin() {
		
		$member = M("Member");
		$mbrNow = $member->where('nickname = \'' . $_POST['username'] . '\' or email = \'' . $_POST['username'] . '\'')->find();
		if (!$mbrNow) {
			echo "无此用户！";
			return false;
		} else {
			if ($mbrNow['status'] == -1) {
				echo "已禁用！";
				return false;
			}
		}
		//
		$password = md5(md5($_POST['password']) . $mbrNow['salt']);
		if ($password != $mbrNow['password']) {
			echo "密码错误！";
			return false;
		}
		//
		$_SESSION[C('MEMBER_AUTH_KEY')] = $mbrNow['id'];
		$_SESSION['nickname'] = $mbrNow['nickname'];
		$_SESSION['face'] = $mbrNow['face'];
		if (empty($_SESSION['face'])) {
			$_SESSION['face'] = "face.jpg";
		}
		//使用cookie过期时间来控制前台登陆的过期时间
		$home_session_expire = D("Variable")->getVariable("home_session_expire");
		cookie(md5("home_session_expire") , time() , $home_session_expire);
		//如果选中下次自动登录，记录用户信息
		if (intval($_POST['auto_login']) == 1) {
			$str = $mbrNow['id'] . "|" . md5($mbrNow['password'] . $mbrNow['nickname']);
			$auto_login_time = D("Variable")->getVariable("auto_login_time");
			$auto_login_time = intval($auto_login_time) > 0 ? $auto_login_time : 60 * 60 * 24 * 7;
			cookie("USER_ID", $str, $auto_login_time);
		}
	
		//
		echo "loginOK";
	}
	
}