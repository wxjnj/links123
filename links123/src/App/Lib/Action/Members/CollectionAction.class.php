<?php
/**
 * @name CollectionAction
 * @package Member
 * @desc 我的收藏
 * @version 1.0
 * @author frank qian 2013-08-13
 */

import("@.Common.CommonAction");

class CollectionAction extends CommonAction
{
	/**
	 * @name index
	 * @desc 收藏页面
	 * @author Frank UPDATE 2013-08-18
	 * @see CollectionAction::index()
	 */
	public function index()
	{
		$this->checkLog();
		$rid = $this->_param('rid');
		$pg = intval($this->_param(C('VAR_PAGE')));
		if (!empty($rid)) {
			$condition['category'] = array('in', $this->_getSubCats($rid));
			$this->assign('rid', $rid);
		}
		
		$mid = $this->userService->getUserId();
		$mbrNow = M("Member")->getById($mid);
		$condition['mid'] = $mid;
		
		$listRows = 12;
		$pg = $pg ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$collectionView = new CollectionViewModel();
		$list = $collectionView->where($condition)->order('create_time DESC')->limit($rst . ',' . $listRows)->group("link")->select();
		$count = $collectionView->where($condition)->count('*');
		
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		
		$this->getRootCats();
		
		$this->assign("title", '我的收藏');
		$this->assign("mbrNow", $mbrNow);
		$this->assign('collList', $list);
		$this->assign("funcNow", "myCollection");
		
		$this->display();
	}
	
	/**
	 * @name saveCollect
	 * @desc 保存收藏
	 * @param int lnk_id
	 * @return boolean
	 * @author Frank UPDATE 2013-08-21
	 */
	public function saveCollect() {
		$this->checkLog(1);
		$lnk_id = intval($this->_post("lnk_id"));
		$link = $this->_param("link");
		if (empty($lnk_id) || empty($link)) {
			echo "链接丢失！";
			return false;
		}
		$collection = M("Collection");
		$data = array();
		$data['link'] = $link;
		$data['mid'] = $this->userService->getUserId();
		
		if ($collection->where($data)->find()) {
			echo "已经收藏过了！";
			return false;
		}
		
		$data['lnk_id'] = $lnk_id;
		$data['create_time'] = time();
		if (false !== $collection->add($data)) {
			$links = M("Links");
			if (false !== $links->where("link = '%s'", $link)->setInc('collect_num')) {
				Log::write('增加链接收藏数量失败：' . $links->getLastSql(), Log::SQL);
			}
			echo "saveOK";
		} else {
			Log::write('收藏失败：' . $collection->getLastSql(), Log::SQL);
			echo "收藏失败！";
		}
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
		$lnkId = intval($this->_param("lnk_id"));
		if (empty($lnkId)) {
			echo "链接id丢失！";
			return false;
		}
		
		$collection = M("Collection");
		$condition['lnk_id'] = $lnkId;
		$condition['mid'] = $this->userService->getUserId();
	
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