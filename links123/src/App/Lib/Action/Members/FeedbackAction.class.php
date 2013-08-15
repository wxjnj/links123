<?php
/**
 * @name FeedbackAction.class.php
 * @package Member
 * @desc 留言板
 * @author frank qian 2013-08-13
 * @version 0.0.1
 */

import("@.Common.CommonAction");

class FeedbackAction extends CommonAction
{
	public function index()
	{
		//
		$this->checkLog();
		//$aryType = array('','留言板','申请取消链接','其他');
		//
		$mbrNow = M("Member")->getById($_SESSION[C('MEMBER_AUTH_KEY')]);
		$this->assign("mbrNow", $mbrNow);
		//
		$condition['pid'] = 0;
		$condition['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
		//
		$listRows = 12;
		$pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
		$rst = ($pg - 1) * $listRows;
		//
		$sugView = new SuggestionViewModel();
		$mysugs = $sugView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		foreach ($mysugs as &$value) {
			$value['create_time'] = date('Y-m-d h:i', $value['create_time']);
			//$value['typeName'] = $aryType[$value['type']];
			//$value["suggest"] = checkLinkUrl($value["suggest"]);
			//$value["reply"] = checkLinkUrl($value["reply"]);
			/*
			 $value['subsug'] = $sugView->where('pid='.$value['id'])->order('create_time asc')->select();
			foreach ($value['subsug'] as &$val) {
			$val['create_time'] = date('Y-m-d h:i', $val['create_time']);
			$val['typeName'] = $aryType[$val['type']];
			$val["suggest"] = checkLinkUrl($val["suggest"]);
			$val["reply"] = checkLinkUrl($val["reply"]);
			}
			*/
		}
		$this->assign('mysugs', $mysugs);
		// 分页
		$count = $sugView->where($condition)->count('id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		//
		$this->assign("funcNow", "mySuggestion");
		//
		$this->display();
	}
	
	//
	public function saveSuggestion() {
		//
		if (!$this->checkLog(1)) {
			return false;
		}
		//
		$suggestion = M("Suggestion");
		//
		if (empty($_POST['id'])) {
			$_POST['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
			$_POST['type'] = 1;
			$_POST['create_time'] = time();
			//
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