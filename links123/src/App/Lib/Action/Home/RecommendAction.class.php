<?php
/**
 * @name RecommendAction
 * @desc 推荐链接
 * @name RecommendAction.class.php
 * @package Home
 * @version 1.0
 * @author Frank UPDATE 2013-08-17
 */
import("@.Common.CommonAction");
class RecommendAction extends CommonAction {
	/**
	 * @name index
	 * @desc 推荐链接页面
	 * @param int id
	 * @param int id
	 * @author Frank UPDATE 2013-08-17
	 * @see RecommendAction::index()
	 */
	public function index() {
		$links = M("Links");
		$id = intval($this->_param('id'));
		$lan = intval($this->_param('lan'));
		$mid = $this->userService->getUserId();
		
		//会员中心的推荐链接编辑
		if ($id) {
			if (empty($mid)) {
				header("Location: " . __APP__ . "/");
				exit(0);
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $mid) {
					$catNow = M("Category")->getById($linkNow['category']);
					$linkNow['rid'] = $catNow['prt_id'];
					$this->assign('linkNow', $linkNow);
					$lan = $linkNow['language'];
				}
			}
			
		} else {
			if ($mid) {
				$last = $links->where("mid = '%d'", $mid)->order('id DESC')->limit(1)->select();
				$linkNow['category'] = $last[0]['category'];
				$linkNow['rid'] = M("Category")->where("id = '%d'", $last[0]['category'])->getField('prt_id');
				$linkNow['grade'] = $last[0]['grade'];
			}
			
			$linkNow['title'] = "请输入标题";
			$linkNow['link'] = "请输入链接";
			$linkNow['intro'] = "请输入简介";
			empty($lan) && $lan = 1;
			$linkNow['language'] = $lan;
			$this->assign('linkNow', $linkNow);
		}
		
		$this->getMyCats($lan);
		
		$this->assign('alt', $this->_param('alt'));
		
		$this->getHeaderInfo(array('title' => '推荐链接'));
		
		$this->display();
	}
	
	/**
	 * @name saveRecommend
	 * @desc 保存推荐链接
	 * @param string title
	 * @param string link
	 * @param string intro
	 * @return boolean
	 * @author Frank UPDATE 2013-08-21
	 */
	public function saveRecommend() {
		$_POST['title'] = cleanParam($this->_param('title'));
		$_POST['link'] = str_replace('http://', '', cleanParam($this->_param('link')));
		$_POST['intro'] = cleanParam($this->_param('intro'));
		//编辑的linkid
		$id = intval($this->_param('id'));
		$mid = $this->userService->getUserId();
		$links = M("Links");
		
		if ($id) {
			if (empty($mid)) {
				header("Location: " . __APP__ . "/");
				exit(0);
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $mid) {
					if ($links->save($_POST)) {
						echo 'addOK';
					} else {
						Log::write('链接编辑失败：' . $links->getLastSql(), Log::SQL);
						echo '链接编辑失败';
					}
				} else {
					echo '这不是你上传的链接';
				}
			}
		} else {
			$_POST['link'] = str_replace('http://', '', cleanParam($this->_param('link')));
			if ($links->where("category = '%s' and and link = '%s'", $_POST['category'],  $_POST['link'])->find()) {
				echo '该链接已存在';
				return false;
			}
			
			$_POST['status'] = 0;
			$_POST['create_time'] = time();
			$_POST['mid'] = $mid;
			if (empty($mid)) {
				$_POST['mid'] = -1;
				$_POST['recommended'] = "游客";
			} else {
				$_POST['recommended'] = getUserNickName($mid);
			}
			
			if ($links->add($_POST)) {
				echo 'addOK';
			} else {
				Log::write('链接提交失败：' . $links->getLastSql(), Log::SQL);
				echo '链接提交失败';
			}
		}
	}
}
