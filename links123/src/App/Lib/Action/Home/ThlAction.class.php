<?php

class ThlAction extends CommonAction {

	public function index() {
		//
		$tid = $this->_param('tid');
		if (empty($tid)) {
			$this->error("糖葫芦籽掉了！");
		} else {
			if (!is_numeric($tid)) {
				$this->error("非法参数tid！");
			}
		}
		M("Thl")->where("id={$tid}")->setInc("click_num");
		//
		$topParam = "thl/" . $this->_param('thl') . "/tid/" . $tid;
		$this->assign('topParam', $topParam);
		//
		$thlInfo = M("Thl")->find($tid);
		$url = str_replace("keyword", cleanParam($this->_param('q')), $thlInfo['url']);
		$this->assign('subUrl', $url);
		$this->assign("thlNow", $thlInfo['thl']);
		$this->assign("tidNow", $thlInfo['id']);
		//
		$this->display();
	}

	public function thl_count() {
		$tid = intval($this->_param('tid'));
		M("Thl")->where("id={$tid}")->setInc("click_num");
	}

	//
	public function top() {
		// 糖葫芦
		$this->assign('thl', $this->getThl());
		//
		$this->assign('thlNow', $this->_param('thl'));
		$this->assign('tidNow', $this->_param('tid'));
		//
		$this->display();
	}

	//
	public function foot() {
		//
		$this->display();
	}

}