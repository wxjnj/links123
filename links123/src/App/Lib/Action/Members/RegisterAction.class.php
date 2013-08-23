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
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		if ($mid) {
			header("Location: " . __APP__ . "/");
			exit(0);
		}
		$this->assign('banner', $this->getAdvs(5, "banner"));
		$this->assign('title', '还不是岛民？赶快注册另客吧，成为另客会员，你能获得会员专有的服务和资源！');
		$this->assign('Description', '注册成为另客会员，你能享受更多另客独有的资源和权利，你会不断有惊喜的发现！');
		
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
        $password = $this->_param('password');
        $verify = $this->_param('verify');
                
		$member = M("Member");

		if (!checkName($nickname)) {
			echo '用户名只能包含字符、数字、下划线和汉字';
			return false;
		}
		if (!checkStr($password)) {
			echo '密码应为6到20位数字或字母';
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
		
		import("@.ORG.String");
		$data['nickname'] = $nickname;
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
			$default_myarea = $myareaModel->field("web_name, url, sort")->where("mid = 0")->Group("url")->order("sort ASC")->limit(20)->select();

			foreach ($default_myarea as $value) {
				$value['create_time'] = &$data['create_time'];
				$value['mid'] = &$_SESSION[C('MEMBER_AUTH_KEY')];
				$myareaModel->add($value);
			}
			
			$home_session_expire = intval(D("Variable")->getVariable("home_session_expire"));
			cookie(md5("home_session_expire"), time(), $home_session_expire);
			
			echo "regOK";
		} else {
			Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
			echo "会员注册失败！";
		}
	}
}