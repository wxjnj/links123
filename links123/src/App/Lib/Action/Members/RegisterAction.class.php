<?php 
/**
 * @name RegisterAction.class.php
 * @package Member
 * @desc 用户注册
 * @author frank qian 2013-08-12
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class RegisterAction extends CommonAction
{
	public function index()
	{
		$this->assign('banner', $this->getAdvs(5, "banner"));
		$this->assign('title', '还不是岛民？赶快注册另客吧，成为另客会员，你能获得会员专有的服务和资源！');
		$this->assign('Description', '注册成为另客会员，你能享受更多另客独有的资源和权利，你会不断有惊喜的发现！');
		
		$this->display();
	}
	
	public function saveReg() 
	{
		extract($_POST);
		$member = M("Member");
		$error = array();
		if ($_SESSION['verify'] != md5($verify)) {
			$error['verfy'] = '验证码错误';
		}
		
		if (!checkName($nickname)){
			$error['nickname'] = '用户名只能包含字符、数字、下划线和汉字';
		}else if ($member->where("nickname = '" . $nickname . "'")->find()){
			$error['nickname'] = '该昵称已注册过了，请换一个！';
		}
		
		if (!checkStr($password)){
			$error['password'] = '密码应为6到20位数字或字母';
		}
		
		if (count($error)>0) {
			//打印出验证错误失败信息
			//print_r($error);
			echo "请检查昵称、密码、验证码或昵称已经存在";
		} else {
			import("@.ORG.String");
			$data['nickname'] = $nickname;
			$data['salt'] = String::randString();
			$data['password'] = md5(md5($password) . $data['salt']);
			$data['status'] = 1;
			$data['create_time'] = time();
			
			if (false !== $member->add($data)) {
				$_SESSION[C('MEMBER_AUTH_KEY')] = $member->getLastInsID();
				//给新增用户添加默认自留地
				$myareaModel = D("Myarea");
				$default_myarea = $myareaModel->field("web_name,url,sort")->where("mid = 0")->Group("url")->order("`sort` asc")->limit(20)->select();

				foreach ($default_myarea as $value) {
					$value['create_time'] = &$data['create_time'];
					$value['mid'] = &$_SESSION[C('MEMBER_AUTH_KEY')];
					$myareaModel->add($value);
				}
				$_SESSION['nickname'] = $data['nickname'];
				session_unset('verify');
				echo "regOK";
			} else {
				Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
				echo "会员注册失败！";
			}
		}
	}
}