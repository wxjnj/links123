<?php
/**
 * @name ThlAction.class.php
 * @package Home
 * @desc 糖葫芦搜索
 * @version 1.0
 * @author frank UPDATE 2013-08-17
 */

import("@.Common.CommonAction");
class ThlAction extends CommonAction {

	/**
	 * @desc 糖葫芦页面
	 * @see ThlAction::index()
	 */
	public function index() {
		if ($user_id) {
			$memberModel = M("Member");
			$mbrNow = $memberModel->where(array('id' => $user_id))->find();

		} else {
			
			$this->get_member_guest();

		}

		//取出皮肤ID和模板ID
		$skinId = session('skinId');
		if (!$skinId) {
			$skinId = cookie('skinId');
		}
		$themeId = session('themeId');
		if (!$themeId) {
			$themeId = cookie('themeId');
		}
		
		//快捷皮肤
		$skins = $this->getSkins();
		if ($skinId) {
			$skin = $skins['skin'][$skinId]['skinId'];
			if (!$skin) $skinId = '';
			$themeId = $skins['skin'][$skinId]['themeId'];
			$this->assign("skinId", $skinId);
			$this->assign("skin", $skin);
		}
		$this->assign("skinList", $skins['list']);
		$this->assign("skinCategory", $skins['category']);
		
		$theme = $this->getTheme($themeId);
		$this->assign('theme', $theme);
		
		$tid = intval($this->_param('tid'));
		$thl = $this->_param('thl');
		if (empty($tid)) {
			$this->error("糖葫芦籽掉了！");
			exit(0);
		}
		
		M("Thl")->where("id = '%d'", $tid)->setInc("click_num");
		$topParam = "thl/" . $thl . "/tid/" . $tid;
		
		$thlInfo = M("Thl")->find($tid);
		
		//TODO 少儿关键词参数被替换临时修复
		if ($tid == 21) {
			$url = str_replace("{keyword}", cleanParam($this->_param('q')), $thlInfo['url']);
		} else {
			$url = str_replace("keyword", cleanParam($this->_param('q')), $thlInfo['url']);
		}
		
		$this->assign('topParam', $topParam);
		$this->assign('subUrl', $url);
		$this->assign("thlNow", $thlInfo['thl']);
		$this->assign("tidNow", $thlInfo['id']);
		
		$this->getHeaderInfo();
		
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