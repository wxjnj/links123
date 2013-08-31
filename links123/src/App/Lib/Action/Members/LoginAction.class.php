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
	/**
	 * @desc 用户登陆显示页
	 * @author lee UPDATE 2013-08-16
	 */
	public function index()
	{
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if ($mid) {
			header("Location: " . __APP__ . "/");
			exit(0);
		}
		$this->assign('banner', $this->getAdvs(4, "banner"));
		$this->assign('title', '另客岛民请登录，享受您另客岛民专有的服务');
		$this->assign('Description', '另客会员专区有众多只有会员才能享有的资源和服务');
                
		$this->display();
	}
	
	/**
	 * @desc 用户登录验证
	 * @author frank UPDATE 2013-08-16
	 * @param string $username 用户昵称或Email
	 * @param string $password 密码
     * @param int $auto_login 自动登录
	 * @return string
	 */
	public function checkLogin() 
	{
        $username = trim($this->_param('username'));
        $password = $this->_param('password');
        $auto_login = $this->_param('auto_login');

		if (checkEmail($username)) {
			$param = 'email';
		} else if (checkName($username)) {
			$param = 'nickname';
		} else {
			echo "用户名有不法字符";
			return false;
		}
		
		$member = M("Member");
		$mbrNow = $member->where("$param = '%s'", $username)->find();
		
		if (empty($mbrNow)) {
			echo "用户不存在";
			return false;
		}
        if ($mbrNow['status'] == -1) {
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
		$_SESSION['face'] = empty($mbrNow['face']) ? 'face.jpg' : $mbrNow['face'];
		$_SESSION['skinId'] = $mbrNow['skin'];
		
		//使用cookie过期时间来控制前台登陆的过期时间
		cookie(md5('home_session_expire'), time(), intval(D("Variable")->getVariable("home_session_expire")));
		
		//如果选中下次自动登录，记录用户信息
		if (intval($auto_login) == 1) {
			$str = $mbrNow['id'] . "|" . md5($mbrNow['password'] . $mbrNow['nickname']);
			$auto_login_time = intval(D("Variable")->getVariable("auto_login_time"));
			cookie("USER_ID", $str, $auto_login_time ? : 60*60*24*7);
		}
		
		echo "loginOK";
	}
	
	/**
	 * @name missPwd
	 * @desc 忘记密码
	 * @param string email
	 * @return boolean
	 */
	public function missPwd() {
		$email = $this->_param('email');
		if (empty($email)) {
			echo "邮箱丢失！";
			return false;
		}
		$mbr = M("Member");
		$mbrNow = $mbr->getByEmail($email);
		
		if ($mbrNow) {
			import("@.ORG.String");
			$password = String::randString();
			if (false !== $mbr->where("id = '%d'", $mbrNow['id'])->setField('password', md5(md5($password) . $mbrNow['salt']))) {
				$mail = array();
				$mail['mailto'] = $email;
				$mail['title'] = "[另客网]忘记密码";
				$mail['content'] = "您好，您的新密码是：" . $password . "<br /><br />为了您的账户安全，请登录后尽快修改您的密码，谢谢！<br /><br />--------------------<br /><br />（这是一封自动发送的邮件，请不要直接回复）";
				if (sendMail($mail)) {
					$mailserver = 'mail.' . substr($email, strpos($email, '@') + 1);
					$mailserver = strtolower($mailserver);
					$mailserver = str_replace('gmail', 'google', $mailserver);
					$mailserver = str_replace('mail.hotmail.com', 'www.hotmail.com', $mailserver);
					echo "sendOK|" . $mailserver;
				} else {
					echo "发送新密码失败！";
				}
			}
		} else {
			echo "未发现您输入的邮箱！";
		}
	}
	
}