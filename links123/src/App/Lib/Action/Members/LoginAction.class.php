<?php 
/**
 * @name LoginAction.class.php
 * @package Members
 * @desc 用户登录
 * @author frank 2013-08-12
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
	
	/**
	 * @desc 用户登录
	 * @author frank UPDATE 2013-08-15
	 * @param string $username 用户昵称或Email
	 * @param string $password 密码
	 * @return string
	 */
	public function checkLogin() 
	{
		extract($_POST);
		if (checkEmail($username)) {
			$param = 'email';
		} else if (checkName($username)) {
			$param = 'nickname';
		}else {
			echo "用户名有不法字符";
			return false;
		}
		
		$member = M("Member");
		$mbrNow = $member->where(" $param = '".$username."'")->find();
		
		if (empty($mbrNow)) {
			echo "用户不存在";
			return false;
		} else if ($mbrNow['status'] == -1) {
			echo "已禁用！";
			return false;
		}
		
		$password = md5(md5($password).$mbrNow['salt']);
		if ($password != $mbrNow['password']) {
			echo "密码错误！";
			return false;
		}
		
		$_SESSION[C('MEMBER_AUTH_KEY')] = $mbrNow['id'];
		$_SESSION['nickname'] = $mbrNow['nickname'];
		$_SESSION['face'] = empty($mbrNow['face'])? 'face.jpg' :$mbrNow['face'];
		
		//使用cookie过期时间来控制前台登陆的过期时间
		cookie(md5('home_session_expire') , time() ,intval(D("Variable")->getVariable("home_session_expire")));
		
		//如果选中下次自动登录，记录用户信息
		if (intval($auto_login) == 1) {
			$str = $mbrNow['id'] . "|" . md5($mbrNow['password'] . $mbrNow['nickname']);
			$auto_login_time = intval(D("Variable")->getVariable("auto_login_time"));
			cookie("USER_ID", $str, $auto_login_time ? : 60 * 60 * 24 * 7);
		}
		
		echo "loginOK";
	}
	
}