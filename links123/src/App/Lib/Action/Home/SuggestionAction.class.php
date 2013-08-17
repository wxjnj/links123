<?php
/**
 * @name FeedbackAction.class.php
 * @package Home
 * @desc 留言建议
 * @author frank qian 2013-08-12
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class SuggestionAction extends CommonAction {
	/**
	 * @desc 留言建议页面
	 * @see SuggestionActionAction::index()
	 */
	public function index() {

		//
		import("@.ORG.String");
		//
		$listRows = 100;
		$pg = !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1;
		$rst = ($pg - 1) * $listRows;
		//
		//        $condition['_string'] = 'pid is null';
		$condition['pid'] = 0;
		$condition['status'] = array('egt', 0);
		//
		$sugView = new SuggestionViewModel();
		$list = $sugView->where($condition)->order('create_time desc')->limit($rst . ',' . $listRows)->select();
		$total = count($list);
		foreach ($list as $key => &$value) {
			/*
			 if ( mb_strlen($value['suggest'], 'utf-8') > 240 ) {
			//$value['ssuggest'] = String::msubstr($value["suggest"], 0, 112);
			//$value["ssuggest"] = nl2br($value["ssuggest"]);
			$value["ssuggest"] = $value["suggest"];
			$value["ssuggest"] = checkLinkUrl($value["ssuggest"]);
			}
			*/
			//$value["suggest"] = nl2br($value["suggest"]);
			//$value["suggest"] = checkLinkUrl($value["suggest"]);
			$list[$key]['number'] = $total - $key;
			$list[$key]['reply'] = $sugView->getSuggestionReplyList($value['id']);
			//                    where("is_reply=1 and pid={$list[$key]['id']}")->order('create_time desc')->select();
			if (false === $list[$key]['reply']) {
				unset($list[$key]['reply']);
			}
			$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
			if ($value['mid'] == -1) {
				$list[$key]['nickname'] = "另客";
			} else if ($value['mid'] == 0 || empty($value['nickname'])) {
				$list[$key]['nickname'] = "游客";
			} else {
				if ($value['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					$value['editable'] = "1";
				}
			}
			if (empty($value['face'])) {
				$value['face'] = "face.jpg";
			}
			/*
			 $value['goon'] = 0;
			if ( isset($_SESSION[C('MEMBER_AUTH_KEY')]) && $_SESSION[C('MEMBER_AUTH_KEY')] == $value['mid'] ) {
			$value['goon'] = 1;
			}
			//
			$value['subsug'] = $sugView->where('suggestion.status>=0 and pid='.$value['id'])->order('create_time asc')->select();
			if ( !empty($value['subsug']) ) {
			foreach ($value['subsug'] as &$val) {
			if ( mb_strlen($val['suggest'], 'utf-8') > 240 ) {
			$val['ssuggest'] = String::msubstr($val["suggest"], 0, 112);
			//$val["ssuggest"] = nl2br($val["ssuggest"]);
			}
			//$val["suggest"] = nl2br($val["suggest"]);
			$val["suggest"] = checkLinkUrl($val["suggest"]);
			$val['create_time'] = date('Y-m-d H:i', $val['create_time']);
			if ( empty($val['nickname']) ) {
			$val['nickname'] = "游客";
			}
			if ( empty($val['face']) ) {
			$val['face'] = "face.jpg";
			}
			if ( $_SESSION[C('MEMBER_AUTH_KEY')] == $val['mid'] ) {
			$val['goon'] = 1;
			}
			}
			}
			*/
		}
		$this->assign('suglist', $list);
		// 分页
		$count = $sugView->where($condition)->count('suggestion.id');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_front();
			$this->assign("page", $page);
			$this->assign("count", $count);
		}
		//
		$this->assign('banner', $this->getAdvs(2, "banner"));
		//
		$this->assign('title', '您的意见对另客非常重要！让我们一起努力，让另客变得更好！');
		$this->assign('Description', '另客的成功离不开您的意见和建议。我们欢迎您给我们提意见，我们将据此不断改进，为您提供更好的服务。');
		//
		$this->display();
	}
}