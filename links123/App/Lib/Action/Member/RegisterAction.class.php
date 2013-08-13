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
	
	public function saveReg() {
		
		if ($_SESSION['verify'] != md5($_POST['verify'])) {
			echo "验证码错误！";
			return false;
		}
		
		$member = M("Member");
		
		$data['nickname'] = trim($_POST['nickname']);
		if ($member->where('nickname = \'' . $data['nickname'] . '\'')->find()) {
			echo "该昵称已注册过了，请换一个！";
			return false;
		}
		
		import("@.ORG.String");
		$data['salt'] = String::randString();
		$data['password'] = md5(md5($_POST['password']) . $data['salt']);
		$data['status'] = 1;
		$data['create_time'] = time();
		if (false !== $member->add($data)) {
			$_SESSION[C('MEMBER_AUTH_KEY')] = $member->getLastInsID();
			//给新增用户添加默认自留地
			$myareaModel = D("Myarea");
			$default_myarea = $myareaModel->field("web_name,url,sort")->where("mid = 0")->Group("url")->order("`sort` asc")->limit(20)->select();
			foreach ($default_myarea as $value) {
				$value['create_time'] = $data['create_time'];
				$value['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
				$myareaModel->add($value);
			}
			$_SESSION['nickname'] = $data['nickname'];
			echo "regOK";
		} else {
			Log::write('会员注册失败：' . $member->getLastSql(), Log::SQL);
			echo "会员注册失败！";
		}
	}
}