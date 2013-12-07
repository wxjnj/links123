<?php
/**
 * @name SuggestionAction
 * @package Home
 * @desc 留言建议
 * @author frank qian 2013-08-12
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class SuggestionAction extends CommonAction {
	/**
	 * @desc 留言建议页面
	 * @author Frank UPDATE 2013-08-18
	 * @see SuggestionAction::index()
	 */
	public function index() {
		import("@.ORG.String");
		$pg = intval($this->_param(C('VAR_PAGE'))) ? intval($this->_param(C('VAR_PAGE'))) : 1;
		$listRows = 20;
		$rst = ($pg - 1) * $listRows;
		
		$condition['pid'] = 0;
		$condition['status'] = array('egt', 0);
		
		$sugView = new SuggestionViewModel();
		$list = $sugView->getSuggestion($condition, $rst, $listRows);
		
		$count = $sugView->where($condition)->count('*');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_front();
			$this->assign("page", $page);
			$this->assign("count", $count);
		}
		
		$this->assign('suglist', $list);
		$this->assign('banner', $this->getAdvs(3, "banner"));
		
		$this->getHeaderInfo(array('title' => '留言板'));
		
		$this->display();
	}

	/**
	 * @desc 保存建议投诉
	 * @name saveSuggestion
	 * @param reply_id [回复留言的id]
	 * @param suggest [留言内容]
	 * @author Frank UPDATE 2013-08-18
	 */
	public function saveSuggestion() {
		
		$suggestion = M("Suggestion");
		$replyId = intval($this->_param('reply_id'));
		$suggest = stripslashes($_POST['suggest']);
		
		$operate = "留言";
		if ($replyId > 0) {
			$data['pid'] = $replyId;
			$data['is_reply'] = 1;
			$operate = "点评";
		}
		$data['mid'] = $this->userService->getUserId();
		$data['suggest'] = $suggest;
		$data['create_time'] = time();
		
		if ($suggestion->add($data)) {
			if ($data['pid'] > 0) {
				$suggestion->where("id = '%d'", $data['pid'])->setField("create_time", time());
			}
			$msg = $operate . "成功";
		} else {
			$msg = $operate . "提交失败";
			Log::write($msg . $suggestion->getLastSql(), Log::SQL);
		}
		
		$this->ajaxReturn("", $msg, true);
	}
	
	/**
	 * @name updateSuggestion
	 * @desc 更新留言
	 * @param int id [留言id]
	 * @param content [留言内容]
	 * @author Frank UPDATE 2013-08-18
	 */
	public function updateSuggestion() {
		if ($this->isPost()) {
			$id = intval($this->_param('id'));
			$content = stripslashes($_POST['content']);
			
			$mid = $this->userService->getUserId();
			
			$data['id'] = $id;
			$data['create_time'] = time();
			$data['suggest'] = $content;
			
			$mod = M("Suggestion");
			$suggestion_info = $mod->find($data['id']);
			if (false === $suggestion_info || empty($suggestion_info)) {
				$this->ajaxReturn("", "留言不存在", false);
			}
			if ($mid != $suggestion_info['mid']) {
				$this->ajaxReturn("", "对不起，你不能编辑他人的留言", false);
			}
			if (empty($data['suggest'])) {
				$this->ajaxReturn("", "留言不能为空", false);
			}
			if (false === $mod->save($data)) {
				$this->ajaxReturn("", "编辑失败", false);
			}
			$this->ajaxReturn("", "编辑成功", true);
		}
	}
}