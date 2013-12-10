<?php
/**
 * @name RegisterAction
 * @desc 用户注册
 * @package Members
 * @version 1.0
 * @author frank qian 2013-08-12
 */

import("@.Common.CommonAction");
class RegisterAction extends CommonAction
{
	/**
	 * @desc 用户注册显示页
	 * @author frank UPDATE 2013-08-12
	 */
	public function index()
	{
		if ($this->userService->isLogin()) {
			header("Location: " . __APP__ . "/");
			exit(0);
		}
		$this->assign('banner', $this->getAdvs(5, "banner"));

		$this->getHeaderInfo(array('title' => '注册'));
		
		$this->display();
	}

	/**
	 * @desc 用户注册提交
	 * @name saveReg
	 * @param string $nickname 用户昵称
	 * @param string $password 密码
	 * @param int $verify 验证码
	 * @return string
	 * @author lee UPDATE 2013-08-15
	 */
	public function saveReg() 
	{
        $nickname = trim($this->_param('nickname'));
        $password = trim($this->_param('password'));
        $verify = trim($this->_param('verify'));
        $email = trim($this->_param('email'));

		if (!checkName($nickname)) {
			echo '用户名只能包含字符、数字、下划线和汉字';
			return false;
		}
		if (!checkStr($password)) {
			echo '密码应为6到20位数字或字母';
			return false;
		}

		if (!checkEmail($email)) {
			echo '请填写正确格式的Email';
			return false;
		}
		if ($_SESSION['verify'] != md5(strtoupper($verify))) {
			echo "验证码错误";
			return false;
		}

		$status = $this->userService->regist($nickname,$email,$password);
		switch($status){
			case 207:
				echo '昵称只能包含2-20个字符、数字、下划线和汉字';
				return false;
			case 208:
				echo '密码应为6到20位数字或字母';
				return false;
			case 209:
				echo '请填写正确格式的Email';
				return false;
			case 210:
				echo '该昵称已注册过';
				return false;
			case 213:
				echo '该邮箱已注册过';
				return false;
			case 211:
				echo "会员注册失败！";
				return false;
			case 200:
				echo "regOK";
				return true;
		}
	}
}