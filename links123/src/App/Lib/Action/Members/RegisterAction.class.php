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
                
		$member = M("Member");

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

        if ($member->where("nickname = '%s'", $nickname)->select()) {
			echo '该昵称已注册过';
			return false;
		}
		
		if ($member->where("email = '%s'", $email)->select()) {
			echo '该邮箱已注册过';
			return false;
		}
		
		import("@.ORG.String");
		$data['nickname'] = $nickname;
		$data['email'] = $email;
		$data['salt'] = String::randString();
		$data['password'] = md5(md5($password) . $data['salt']);
		$data['status'] = 1;
		$data['create_time'] = time();
		
		if (false !== $member->add($data)) {
			$_SESSION[C('MEMBER_AUTH_KEY')] = $member->getLastInsID();
			$_SESSION['nickname'] = $nickname;
			$_SESSION['face'] = 'face.jpg';
			//给新增用户添加默认自留地
			$myareaModel = D("Myarea");
			$default_myarea = $myareaModel->field("web_name, url, sort")->where("mid = 0")->Group("url")->order("sort ASC")->limit(30)->select();

			foreach ($default_myarea as $value) {
				$value['create_time'] = &$data['create_time'];
				$value['mid'] = &$_SESSION[C('MEMBER_AUTH_KEY')];
				$myareaModel->add($value);
			}
			
			$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
			cookie(md5("home_session_expire"), time(), $home_session_expire);
            
            $str = $_SESSION[C('MEMBER_AUTH_KEY')] . "|" . md5($data['password'] . $data['nickname']);
            cookie(md5(C('MEMBER_AUTH_KEY')), $str, intval(D("Variable")->getVariable("home_session_expire")));//设置cookie记录用户登录信息，提供给英语角同步登录 Adam 2013.09.27 @todo 安全性，下一步进行单点登录优化 
			
			echo "regOK";
		} else {
			Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
			echo "会员注册失败！";
		}
	}
}