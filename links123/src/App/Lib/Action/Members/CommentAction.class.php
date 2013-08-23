<?php 
/**
 * @name CommentAction
 * @package Member
 * @desc 我的说说
 * @version 1.0
 * @author frank qian 2013-08-13
 *
 */

import("@.Common.CommonAction");
class CommentAction extends CommonAction
{
	/**
	 * @desc 我的说说页面
	 * @package Members
	 * @name index
	 * @param int rid
	 * @param int VAR_PAGE
	 * @return boolean
	 * @author Frank UPDATE 2013-08-18
	 */
	public function index()
	{
		$this->checkLog();
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
		$rid = $this->_param('rid');
		$pg = intval($this->_param(C('VAR_PAGE')));
		
		$mbrNow = M("Member")->getById($mid);
		$condition['mid'] = $mid;
		
		if (!empty($rid)) {
			$condition['category'] = array('in', $this->_getSubCats($rid));
			$this->assign('rid', $rid);
		}
		
		$listRows = 12;
		$pg = $pg ? : 1;
		$rst = ($pg - 1) * $listRows;
		
		$commentView = new CommentViewModel();
		$mycmts = $commentView->where($condition)->order('create_time DESC')->limit($rst . ',' . $listRows)->select();
		foreach ($mycmts as &$value) {
			$value["comment"] = checkLinkUrl($value["comment"]);
			$value['create_time'] = date('Y-m-d h:i', $value['create_time']);
		}
		
		$count = $commentView->where($condition)->count('*');
		if ($count > 0) {
			import("@.ORG.Page");
			$p = new Page($count, $listRows);
			$page = $p->show_js2();
			$this->assign("page", $page);
		}
		
		$this->getRootCats();
		$this->assign('mycmts', $mycmts);
		$this->assign("mbrNow", $mbrNow);
		$this->assign("funcNow", "myComment");
		
		$this->display();
	}
	
	/**
	 * @desc 编辑我的说说
	 * @package Members
	 * @name editComment
	 * @param int id
	 * @param string comment
	 * @return boolean
	 * @author Frank UPDATE 2013-08-18
	 */
	public function editComment() {
		$this->checkLog();
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		$id = intval($this->_param('id'));
		$comment = htmlspecialchars(trim($this->_param('comment')));
		if (empty($id)) {
			echo '说说id丢失';
			return false;
		}
		$date['comment'] = $comment;
		$comment = M("Comment");
		if (false === $comment->where("id = '%d'", $id)->save($date)) {
			Log::write('编辑说说失败：' . $comment->getLastSql(), Log::SQL);
			echo '编辑说说失败';
		} else {
			echo 'editOK';
		}
	}
}