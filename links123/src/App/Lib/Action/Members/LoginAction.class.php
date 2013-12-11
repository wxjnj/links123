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
		if ($this->userService->isLogin()) {
			header("Location: " . __APP__ . "/");
			exit(0);
		}
		$this->assign('banner', $this->getAdvs(4, "banner"));
		
		$this->getHeaderInfo(array('title' => '登录'));
		
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
		$autologin = $this->_param('auto_login');
        $verify = trim($this->_param('verify'));

		// 尝试登录超过5次，用户需要输入验证码
		if (isset($_SESSION['userLoginCounterPaswd']) && $_SESSION['userLoginCounterPaswd'] > 5) {
			if (empty($verify)) {
				echo json_encode(array("code"=>505, "content" => "请输入验证码"));
				return false;
			}else {
				if ($_SESSION['verify'] != md5(strtoupper($verify))) {
					echo json_encode(array("code"=>506, "content" => "验证码错误"));
					return false;
				}
			}
		}

        $status = $this->userService->login($username,$password,$autologin);
		switch($status){
			case 202:
				echo json_encode(array("code"=>501, "content" => "用户名有不法字符"));
				return false;
			case 203:
				echo json_encode(array("code"=>502, "content" => "用户名不存在"));
				return false;
			case 204:
				echo json_encode(array("code"=>403, "content" => "已禁用！"));
				return false;
			case 205:
				isset($_SESSION['userLoginCounterPaswd']) ? $_SESSION['userLoginCounterPaswd']++ : $_SESSION['userLoginCounterPaswd'] = 1;

				if ($_SESSION['userLoginCounterPaswd'] > 2){    // 输入错误密码次数超过2次给用户不同的提示信息
					echo json_encode(array("code"=>504, "content" => "建议检查用户名是否正确"));
				}
				else {
					echo json_encode(array("code"=>503, "content" => "密码与用户名不符"));
				}
				return false;
			case 205:
				echo json_encode(array("code"=>502, "content" => "登录失败！"));
				return false;
			case 200:
				echo json_encode(array("code"=>200, "content" => "loginOK"));
				return true;
		}
		return false;

		
		//同步游客数据到用户
// 		$member_guest_id = $this->get_member_guest();
// 		if ($member_guest_id < 0) {
// 			$this->synchronize_schedule($mbrNow['id'], $member_guest_id);
// 		}
		

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
		
		$verify = trim($this->_param('verify'));
		if ($_SESSION['verify'] != md5(strtoupper($verify))) {
			echo "验证码错误";
			return false;
		}
		$status = $this->userService->resetPassword($email);
		switch($status){
			case 203:
				echo "未发现您输入的邮箱！";
				return false;
			case 204:
				echo "已禁用！";
				return false;
			case 209:
				echo "邮箱格式错误";
				return false;
			case 219:
				echo "发送新密码失败！";
				return false;
			case 200:
				echo "sendOK";
				return true;
		}
		return false;
	}
	
}