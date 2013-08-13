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
}