<?php
/**
 * @name RecommendAction.class.php
 * @package Member
 * @desc 我的推荐
 * @author frank qian 2013-08-13
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class RecommendAction extends CommonAction
{
	public function index()
	{
		//
		$this->checkLog();
		//
		$mbrNow = M("Member")->getById($_SESSION[C('MEMBER_AUTH_KEY')]);
		$this->assign("mbrNow", $mbrNow);
		//
		$condition['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
		//
		$rid = $_REQUEST['rid'];
		if (!empty($rid)) {
			$condition['category'] = array('in', $this->_getSubCats($rid));
			$this->assign('rid', $rid);
		}
		//
		$listRows = 12;
		$pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
		$rst = ($pg - 1) * $listRows;
		//
		$links = M("Links");
		$list = $links->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		//echo $links->getLastSql();
		$this->assign('recList', $list);
		// 分页
		$count = $links->where($condition)->count('id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		//
		$this->getRootCats();
		//
		$this->assign("funcNow", "myRecommend");
		//
		$this->display();
	}
}