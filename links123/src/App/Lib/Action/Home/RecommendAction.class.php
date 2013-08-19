<?php
/**
 * @name RecommendAction.class.php
 * @package Home
 * @desc 推荐链接
 * @author frank qian 2013-08-17
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class RecommendAction extends CommonAction {
	/**
	 * @desc 推荐链接页面
	 * @author Frank UPDATE 2013-08-17
	 * @see RecommendAction::index()
	 */
	public function index() {
		//
		$links = M("Links");
		$id = $_REQUEST['id'];
		$lan = $_REQUEST['lan'];
		if (!empty($id)) {
			if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				header("Location: " . __APP__ . "/");
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					$catNow = M("Category")->getById($linkNow['category']);
					$linkNow['rid'] = $catNow['prt_id'];
					$this->assign('linkNow', $linkNow);
					$lan = $linkNow['language'];
				}
			}
		} else {
			if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				$last = $links->where('mid=' . $_SESSION[C('MEMBER_AUTH_KEY')])->order('id desc')->limit(1)->select();
				$linkNow['category'] = $last[0]['category'];
				$linkNow['rid'] = M("Category")->where('id=' . $last[0]['category'])->getField('prt_id');
				$linkNow['grade'] = $last[0]['grade'];
			}
			$linkNow['title'] = "请输入标题";
			$linkNow['link'] = "请输入链接";
			$linkNow['intro'] = "请输入简介";
			if (empty($lan)) {
				$lan = 1;
			}
			$linkNow['language'] = $lan;
			$this->assign('linkNow', $linkNow);
		}
		//
		$this->getMyCats($lan);
		//
		$this->assign('alt', $_REQUEST['alt']);
		//
		$this->assign('title', '好东西就应该和大家分享。您推荐的好东西会让另客的内容更加丰富！');
		$this->assign('Description', '分享您发现的好东西，别人也会和您分享他们的好东西，互动共享让另客教育社区更加生气蓬勃！');
		$this->display();
	}
	
	// 获取目录
	private function getMyCats($flag = 1) {
		$cat = M("Category");
		$cats = $cat->field('id, cat_name, level')->where('status=1 and level=1')->order('sort asc')->select();
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
			//
			$value['subCats'] = $cat->field('id, cat_name, level')->where('status=1 and flag=' . $flag . ' and prt_id=' . $value['id'])->order('sort asc')->select();
		}
		$this->assign("cats", $cats);
	}
	
	public function saveRecommend() {
		//
		$_POST['title'] = cleanParam($_POST['title']);
		$_POST['link'] = str_replace('http://', '', cleanParam($_POST['link']));
		$_POST['intro'] = cleanParam($_POST['intro']);
		//
		$id = $_REQUEST['id'];
		$links = M("Links");
		if (!empty($id)) {
			if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				header("Location: " . __APP__ . "/");
			} else {
				$linkNow = $links->getById($id);
				if ($linkNow['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
					//
					if (false === $links->save($_POST)) {
						Log::write('链接编辑失败：' . $links->getLastSql(), Log::SQL);
						echo '链接编辑失败';
					} else {
						echo 'editOK';
					}
				} else {
					echo '这不是你上传的链接';
				}
			}
		} else {
			//
			if ($links->where('category=' . $_POST['category'] . ' and link=\'' . $_POST['link'] . '\'')->find()) {
				echo '该链接已存在';
				return false;
			}
			//
			$_POST['status'] = 0;
			$_POST['create_time'] = time();
			$_POST['mid'] = $_SESSION[C('MEMBER_AUTH_KEY')];
			if (empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
				$_POST['mid'] = -1; //游客推荐
				$_POST['recommended'] = "游客";
			} else {
				$_POST['recommended'] = getUserNickName($_SESSION[C('MEMBER_AUTH_KEY')]);
			}
			//
			if (false === $links->add($_POST)) {
				Log::write('链接提交失败：' . $links->getLastSql(), Log::SQL);
				echo '链接提交失败';
			} else {
				echo 'addOK';
			}
		}
	}
}
