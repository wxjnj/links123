<?php
/**
 * @name CollectionAction.class.php
 * @package Member
 * @desc 我的收藏
 * @author frank qian 2013-08-13
 * @version 0.0.1
 */

import("@.Common.CommonAction");

class CollectionAction extends CommonAction
{
	/**
	 * @desc 收藏页面
	 * @author Frank UPDATE 2013-08-18
	 * @see CommonAction::index()
	 */
	public function index()
	{
		$this->checkLog();
		$rid = $_REQUEST['rid'];
		$pg = intval($_REQUEST[C('VAR_PAGE')]);
		if (!empty($rid)) {
			$condition['category'] = array('in', $this->_getSubCats($rid));
			$this->assign('rid', $rid);
		}
		
		$mid = $_SESSION[C('MEMBER_AUTH_KEY')];
		$mbrNow = M("Member")->getById($mid);
		$this->assign("mbrNow", $mbrNow);
		$condition['mid'] = $mid;
		
		$listRows = 12;
		$pg = $pg ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$collectionView = new CollectionViewModel();
		$list = $collectionView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->group("link")->select();
		$count = $collectionView->where($condition)->count('lnk_id');
		
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		
		$this->getRootCats();
		$this->assign('collList', $list);
		$this->assign("funcNow", "myCollection");
		
		$this->display();
	}
	
	/**
	 * @desc 取消收藏
	 * @name savePassword
	 * @package Members
	 * @param lnk_id
	 * @return string
	 */
	public function del_collect() {
		$this->checkLog(1);
		$lnkId = intval($_POST["lnk_id"]);
		if (empty($lnkId)) {
			echo "链接id丢失！";
			return false;
		}
	
		$collection = M("Collection");
		$condition['lnk_id'] = $lnkId;
		$condition['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
	
		if (!$collection->where($condition)->find()) {
			echo "无此收藏！";
			return false;
		}
	
		if (false !== $collection->where($condition)->delete()) {
			$links = M("Links");
			$linkNow = $links->getById($lnkId);
			if (false !== $links->where("link = '%s'", $linkNow['link'])->setDec('collect_num')) {
				Log::write('减少链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
			}
			echo "delOK";
		} else {
			Log::write('取消收藏失败：' . $collection->getLastSql(), Log::SQL);
			echo "取消收藏失败！";
		}
	}
}