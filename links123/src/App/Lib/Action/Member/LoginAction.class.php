<?php 
/**
 * @name LoginAction.class.php
 * @package Member
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
}