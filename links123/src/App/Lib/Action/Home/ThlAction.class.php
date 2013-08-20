<?php
/**
 * @name ThlAction.class.php
 * @package Home
 * @desc 糖葫芦搜索
 * @author frank UPDATE 2013-08-17
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class ThlAction extends CommonAction {

	/**
	 * @desc 糖葫芦页面
	 * @see ThlAction::index()
	 */
	public function index() {
		$tid = intval($this->_param('tid'));
		$thl = $this->_param('thl');
		if (empty($tid)) {
			$this->error("糖葫芦籽掉了！");
		}
		
		M("Thl")->where("id = '%d'", $tid)->setInc("click_num");
		$topParam = "thl/" . $thl . "/tid/" . $tid;
		
		$thlInfo = M("Thl")->find($tid);
		$url = str_replace("keyword", cleanParam($this->_param('q')), $thlInfo['url']);
		
		$this->assign('topParam', $topParam);
		$this->assign('subUrl', $url);
		$this->assign("thlNow", $thlInfo['thl']);
		$this->assign("tidNow", $thlInfo['id']);
		
		$this->display();
	}

	public function thl_count() {
		$tid = intval($this->_param('tid'));
		M("Thl")->where("id = '%d'", $tid)->setInc("click_num");
	}

	public function top() {
		$this->assign('thl', $this->getThl());
		$this->assign('thlNow', $this->_param('thl'));
		$this->assign('tidNow', $this->_param('tid'));
		
		$this->display();
	}

	public function foot() {
		$this->display();
	}

}