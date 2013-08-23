<?php
/**
 * @name SuggestionAction
 * @desc 留言板
 * @package Member
 * @version 1.0
 * @author frank qian 2013-08-13
 */

import("@.Common.CommonAction");

class SuggestionAction extends CommonAction
{
	/**
	 * @name index
	 * @desc 我的留言建议页面
	 * @param int VAR_PAGE
	 * @return boolean
	 * @author Frank UPDATE 2013-08-18
	 */
	public function index()
	{
		$this->checkLog();
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		$pg = $this->_param(C('VAR_PAGE'));
		$mbrNow = M("Member")->getById($mid);
		
		$condition['pid'] = 0;
		$condition['mid'] = $mid;
		
		$listRows = 6;
		$pg = $pg ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$sugView = new SuggestionViewModel();
		$mysugs = $sugView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		foreach ($mysugs as &$value) {
			$value['create_time'] = date('Y-m-d h:i', $value['create_time']);
		}
		
		$count = $sugView->where($condition)->count('*');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		
		$this->assign("mbrNow", $mbrNow);
		$this->assign('mysugs', $mysugs);
		$this->assign("funcNow", "mySuggestion");
		
		$this->display();
	}
	
	/**
	 * @desc 编辑我的说说
	 * @name saveSuggestion
	 * @param int type
	 * @param string comment
	 * @return boolean
	 * @author Frank UPDATE 2013-08-18
	 */
	public function saveSuggestion() {
		$this->checkLog(1);
		$suggestion = M("Suggestion");
		
		if (empty($_POST['id'])) {
			$_POST['mid'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
			$_POST['type'] = 1;
			$_POST['create_time'] = time();
			
			if (false === $suggestion->add($_POST)) {
				Log::write('新增留言失败：' . $suggestion->getLastSql(), Log::SQL);
				echo "新增留言失败！";
			} else {
				echo "saveOK";
			}
		} else {
			if (false === $suggestion->save($_POST)) {
				Log::write('编辑留言失败：' . $suggestion->getLastSql(), Log::SQL);
				echo "编辑留言失败！";
			} else {
				echo "saveOK";
			}
		}
	}
}