<?php 
/**
 * @name IndexAction.class.php
 * @package Member
 * @desc 会员中心
 * @author frank qian 2013-08-13
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class IndexAction extends CommonAction
{
	// 检查登录
	protected function checkLog($ajax = 0) {
		if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
			if ($ajax == 1) {
				echo "请先登录！";
				return false;
			} else {
				header("Location: " . __APP__ . "/");
			}
		} else {
			return true;
		}
	}
	
	public function index()
	{
		//
		$this->checkLog();
		//
		$mbrNow = M("Member")->getById($_SESSION[C('MEMBER_AUTH_KEY')]);
		if (empty($mbrNow['face'])) {
			$mbrNow['face'] = 'face.jpg';
		}
		$this->assign("mbrNow", $mbrNow);
		//
		$faces = array();
		for ($i = 1; $i != 120; ++$i) {
			$faces[] = $i . ".jpg";
		}
		$this->assign("faces", $faces);
		//
		$this->assign("funcNow", "index");
		//
		$this->display();
	}
}