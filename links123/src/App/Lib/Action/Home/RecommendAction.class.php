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
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		
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
		$this->assign('title', '好东西就应该和大家分享。您推荐的好东西会让另客的内容更加丰富！');
		$this->assign('Description', '分享您发现的好东西，别人也会和您分享他们的好东西，互动共享让另客教育社区更加生气蓬勃！');
		$this->display();
	}
	
	/**
	 * @name getMyCats
	 * @desc 获取目录
	 * @author Frank UPDATE 2013-08-21
	 */
	private function getMyCats($flag = 1) {
		$cat = M("Category");
		$cats = $cat->field('id, cat_name, level')->where('status = 1 and level = 1')->order('sort ASC')->select();
		foreach ($cats as &$value) {
			switch ($value['id']) {
				case 1:
					$value['grades'] = array(
					array('name' => '初级', 'value' => '1'),
					array('name' => '初级中级', 'value' => '1,2'),
					array('name' => '初级中级高级', 'value' => '1,2,3'),
					array('name' => '中级', 'value' => '2'),
					array('name' => '中级高级', 'value' => '2,3'),
					array('name' => '高级', 'value' => '3')
					);
					break;
				case 4:
					$value['grades'] = array(
					array('name' => '苹果', 'value' => '1'),
					array('name' => '安卓+', 'value' => '2'),
					array('name' => '苹果安卓+', 'value' => '1,2')
					);
					break;
			}
			$value['subCats'] = $cat->field('id, cat_name, level')->where("status = 1 and flag = '%s' and prt_id = '%s'", $flag, $value['id'])->order('sort ASC')->select();
		}
		$this->assign("cats", $cats);
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
		$mid = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
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
