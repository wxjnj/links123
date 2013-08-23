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
	/**
	 * @desc 我的推荐页面
	 * @author Frank UPDATE 2013-08-18
	 * @see RecommendAction::index()
	 */
	public function index()
	{
		$this->checkLog();
		$mid = $_SESSION[C('MEMBER_AUTH_KEY')];
		$rid = intval($_REQUEST['rid']);
		$pg = intval($_REQUEST[C('VAR_PAGE')]);
		
		$mbrNow = M("Member")->getById($mid);
		
		$condition['mid'] = $mid;
		if (!empty($rid)) {
			$condition['category'] = array('in', $this->_getSubCats($rid));
			$this->assign('rid', $rid);
		}
		
		$listRows = 12;
		$pg = $pg ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$links = M("Links");
		$list = $links->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		
		$count = $links->where($condition)->count('*');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		
		$this->getRootCats();
		$this->assign("mbrNow", $mbrNow);
		$this->assign('recList', $list);
		$this->assign("funcNow", "myRecommend");
		
		$this->display();
	}
}