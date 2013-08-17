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
		$listRows = 20;
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
			
			$list[$key]['number'] = $total - $key;
			$list[$key]['reply'] = $sugView->getSuggestionReplyList($value['id']);
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
			
		}
		$this->assign('suglist', $list);
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