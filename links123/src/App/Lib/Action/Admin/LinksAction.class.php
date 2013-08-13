<?php
// 链接
class LinksAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['title']) ) {
			$title = trim($_REQUEST['title']);
		}
		if ( !empty($title) ) {
			$map['_string'] = "title like '%".$title."%' or link like '%".$title."%'";
		}
		$this->assign('title', $title);
		$param['title'] = $title;
		//
		if ( isset($_REQUEST['category']) && $_REQUEST['category']!='' ) {
			$category = $_REQUEST['category'];
		}
		if ( !empty($category)) {
			$map['category'] = array('in', $this->_getSubCats($category));
			$catNow = M("Category")->getById($category);
			$this->assign('cat_name', $catNow['cat_name']);
			$this->assign('rid', $catNow['prt_id']);
		}
		$this->assign('category', $category);
		$param['category'] = $category;
		//
		if (isset($_REQUEST['grade']) && $_REQUEST['grade']!='') {
			$map['grade'] = $_REQUEST['grade'];
		}
		$this->assign('grade', $map['grade']);
		$param['grade'] = $map['grade'];
		//
		if (isset($_REQUEST['language']) && $_REQUEST['language']!='') {
			$map['language'] = $_REQUEST['language'];
		}
		$this->assign('language', $map['language']);
		$param['language'] = $map['language'];
		//
		if (isset($_REQUEST['status']) && $_REQUEST['status']!='') {
			$map['status'] = $_REQUEST['status'];
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}
		else {
			$map['status'] = array('egt',0);
		}
	}
	
	//
	private function getMyCats($lan=null, $arychecked=null) {
		$cat = M("Category");
		$cats = $cat->field('id, cat_name, level')->where('status=1 and level=1')->order('sort asc')->select();
		foreach ($cats as &$value) {
			switch ($value['id']) {
				case 1:
					$value['grades'] = array(
						array('name'=>'初级','value'=>'1'),
						array('name'=>'初级中级','value'=>'1,2'),
						array('name'=>'初级中级高级','value'=>'1,2,3'),
						array('name'=>'中级','value'=>'2'),
						array('name'=>'中级高级','value'=>'2,3'),
						array('name'=>'高级','value'=>'3')
					);
					break;
				case 4:
					$value['grades'] = array(
						array('name'=>'苹果','value'=>'1'),
						array('name'=>'安卓+','value'=>'2'),
						array('name'=>'苹果安卓+','value'=>'1,2')
					);
					break;
			}
			//
			if ( !empty($lan) ) {
				$value['subCats'] =  $cat->field('id, cat_name, level')->where('status=1 and flag='.$lan.' and prt_id='.$value['id'])->order('sort asc')->select();
				if ( !empty($arychecked) ) {
					foreach ($value['subCats'] as &$val) {
						foreach ($arychecked as &$item) {
							if ( $item['category'] == $val['id'] ) {
								$val['checked'] = 1;
								$value['grade_checked'] = $item['grade'];
							}
						}
					}
				}
			}
		}
		$this->assign("cats", $cats);
	}
	
	// 列表
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = new LinksViewModel();
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', false );
			//echo $model->getLastSql();
		}
		//
		$this->getMyCats($map['language']);
		//
		$this->display();
		return;
	}
	
	//
	public function add() {
		$model = M("Links");
		if ( isset($_POST['language']) && !empty($_POST['language']) ) {
			//
			$vo['title'] = $_POST['title'];
			$vo['link'] = $_POST['link'];
			$vo['logo'] = $_POST['logo'];
			$vo['language'] = $_POST['language'];
			//
			$this->getMyCats($_POST['language']);
		}
		else {
			$idnow = $model->where('uid='.$_SESSION[C('USER_AUTH_KEY')])->max('id');
			$vo = $model->getById( $idnow );
			$aryCats = array();
			//$alllinks = $model->field('category,grade')->where('link=\''.$vo['link'].'\'')->select();
			$alllinks = $model->field('category,grade')->where('link=\''.$vo['link'].'\'')->order('id desc')->limit(1)->select();
			foreach ($alllinks as &$value) {
				array_push($aryCats, $value['category']);
			}
			$cids = implode(",", $aryCats);
			$cats = M("Category")->field('cat_name')->where('id in('.$cids.')')->select();
			foreach ($cats as &$value) {
				if ( empty($vo['cat_name']) ) {
					$vo['cat_name'] = $value['cat_name'];
				}
				else {
					$vo['cat_name'] .= ",".$value['cat_name'];
				}
			}
			$vo['title'] = '';
			$vo['link'] = '';
			$vo['logo'] = '';
			//$vo['rid'] = $catNow['prt_id'];
			//
			$this->getMyCats($vo['language'], $alllinks);
		}
		//
		$this->assign ( 'vo', $vo );
		//
		$this->display();
		return;
	}

	// 插入数据
	public function insert() {
		//
		$model = M("Links");
		$this->checkPost();
		$_POST['uid'] = $_SESSION[C('USER_AUTH_KEY')];
		//
		if ( $model->where("status=1 and category in(".$_POST['categorys'].") and link='".$_POST['link']."'")->find() ) {
			$this->error('该链接已存在！');
		}
		//
		if ( !empty($_POST['tags']) ) {
			$ary = explode(',', $_POST['tags']);
			foreach ($ary as &$value) {
				$list = $model->field('tags')->where("status=1 and category in(".$_POST['categorys'].") and tags like '%".$value."%'")->select();
				foreach ($list as &$val) {
					$temp = explode(',', $val);
					foreach ($temp as &$item) {
						if ( $item == $value ) {
							$this->error('该标签已存在！');
						}
					}
				}
			}
		}
		//
		if (empty($_POST['categorys'])) {
			$this->error('请选择目录！');
		}
		//
		$cats = explode(',', $_POST['categorys']);
		$rids = explode(',', $_POST['rids']);
		$_POST['create_time'] = time();
		//
		$model->startTrans();
		$result = true;
		//
		foreach ($cats as $key => $value) {
			$_POST['category'] = $value;
			$_POST['grade'] = $_POST['grade'.$rids[$key]];
			if( false === $model->add($_POST) ) {
				$result = false;
				Log::write('链接添加失败：'.$model->getLastSql(), Log::SQL);
			}
		}
		if( $result ) {
			$model->commit();
			$this->success('链接添加成功！');
		} 
		else {
			$model->rollback();
			$this->error('链接添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['title']	= htmlspecialchars(trim($_POST['title']));
        $_POST['link']	= str_replace('http://', '', htmlspecialchars(trim($_POST['link'])));
        $_POST['link']	= str_replace('https://', '', $_POST['link']);
		$_POST['intro']	= htmlspecialchars(trim($_POST['intro']));
		$_POST['tags']	= str_replace('，', ',', htmlspecialchars(trim($_POST['tags'])));
		$_POST['nickname'] = trim($_POST['nickname']);
	}
	
	//
	function edit() {
		$model = M("Links");
		$vo = $model->getById( $_REQUEST['id'] );
		$catNow = M("Category")->getById($vo['category']);
		$vo['cat_name'] = $catNow['cat_name'];
		$vo['rid'] = $catNow['prt_id'];
		if ( isset($_POST['language']) && !empty($_POST['language']) ) {
			$vo['language'] = $_REQUEST['language'];
		}
		//
		if ( $vo['mid'] > 0 ) {
			$vo['nickname'] = M("Member")->where('id='.$vo['mid'])->getField('nickname');
		}
		//
		$this->assign ( 'vo', $vo );
		//
		$this->getMyCats($vo['language']);
		//
		$this->display();
		return;
	}
    
	// 更新数据
	public function update() {
		//
		$model = M("Links");
        $this->checkPost();
        //
        if ( $model->where("status=1 and id!=".$_POST['id']." and category=".$_POST['category']." and link='".$_POST['link']."'")->find() ) {
        	$this->error('该链接已存在！');
        }
        //
        if ( !empty($_POST['tags']) ) {
        	$ary = explode(',', $_POST['tags']);
        	foreach ($ary as &$value) {
        		$list = $model->field('tags')->where("status=1 and id!=".$_POST['id']." and category=".$_POST['category']." and tags like '%".$value."%'")->select();
        		foreach ($list as &$val) {
        			$temp = explode(',', $val);
        			foreach ($temp as &$item) {
        				if ( $item == $value ) {
        					$this->error('该标签已存在！');
        				}
        			}
        		}
        	}
        }
		//
        $linkNow = $model->getById($_POST['id']);
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		//
		if ( false !== $model->save() ) {
			if ( $_POST['logo'] != $linkNow['logo'] ) {
				if ( !$model->where("id<>".$linkNow['id']." and logo='".$linkNow['logo']."'")->find() ) {
					$path = realpath('./Public/Uploads/Links/'.$linkNow['logo']);
					if ( !unlink($path) ) {
						Log::write('logo删除失败：'.$path, Log::FILE);
					}
				}
			}
			//
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('链接编辑成功!');
		} 
		else {
			Log::write('链接编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('链接编辑失败!');
		}
	}
	
	// 批编辑
	function groupEdit() {
		//
		$this->assign ( 'id', $_REQUEST['id'] );
		//
		$language = $_REQUEST['language'];
		if ( empty($language) ) {
			$language = 1;
		}
		$this->assign("language", $language);
		//
		$this->getMyCats($language);
		//
		$this->display();
		return;
	}
	
	// 保存批编辑
	function groupUpdate() {
		$model = M("Links");
		$ids = explode(",", $_POST['id']);
		foreach ($ids as &$value) {
			$linkNow = $model->where('id='.$value)->getField('link');
			if ( $model->where("language=".$_POST['language']." and category=".$_POST['category']." and link='".$linkNow."'")->find() ) {
				$this->error('链接 '.$linkNow.' 已存在');
			}
		}
		//
		$data['language'] = $_POST['language'];
		$data['category'] = $_POST['category'];
		$data['grade'] = $_POST['grade'];
		if ( false === $model->where('id in('.$_POST['id'].')')->save($data) ) {
			$this->error('批量编辑失败！');
		}
		else {
			$this->success('批量编辑成功！',cookie('_currentUrl_'));
		}
	}
	
	// 删除
	public function delete() {
		//删除指定记录
		$model = D("Links");
		if (!empty($model)) {
			if (isset($_REQUEST['id'])) {
				$condition = array('id' => array('in', explode(',', $_REQUEST['id'])));
				$list = $model->where($condition)->setField('status', - 1);
				if ($list !== false) {
					$this->success('删除成功！',cookie('_currentUrl_'));
				} else {
					$this->error('删除失败！');
				}
			} else {
				$this->error('非法操作');
			}
		}
	}
	
	// 恢复
	public function recycle() {
		$model = D("Links");
		//foreach ($_GET['id'] as &$value) {
			$linkNow = $model->getById($_GET['id']);
			if ( $model->where('status=1 and category='.$linkNow['category'].' and link=\''.$linkNow['link'].'\'')->find() ) {
				$this->error('该链接已存在！');
			}
		//}
		$condition = array('id' => array('in', $_GET['id']));
		if (false !== $model->where($condition)->setField('status', 0)) {
			$this->success('状态还原成功！',cookie('_currentUrl_'));
		} else {
			$this->error('状态还原失败！');
		}
	}
	
	//
	public function foreverdelete() {
		//删除指定记录
		$model = M("Links");
		if (! empty ( $model )) {
			//
			$model->startTrans();
			$result = true;
			//
			$id = $_REQUEST['id'];
			if ( isset($id) ) {
				$ids = explode ( ',', $id );
				foreach ($ids as &$value) {
					$linkNow = $model->field('id,logo')->where('id='.$value)->find();
					if ( !empty($linkNow['logo']) ) {
						if ( !$model->where("id<>".$linkNow['id']." and logo='".$linkNow['logo']."'")->find() ) {
							$path = realpath('./Public/Uploads/Links/'.$linkNow['logo']);
							if ( !unlink($path) ) {
								Log::write('logo删除失败：'.$path, Log::FILE);
							}
						}
					}
					if ( false === $model->where('id='.$value)->delete() ) {
						$result = false;
						Log::write('删除链接失败：'.$model->getLastSql(), Log::SQL);
					}
				}
				//
				if ( $result ) {
					$model->commit();
					$this->success ('删除链接成功！');
				}
				else {
					$model->rollback();
					$this->error ('删除链接失败！');
				}
			}
			else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	// 检测链接是否存在
	public function checkLink() {
		if ( M("Links")->where("link='".$_POST['link']."'")->find() ) {
			echo true;
		}
		else {
			echo false;
		}
	}
	
	// 审核
	public function check() {
		//审核指定记录
		$model = M("Links");
		if (! empty ( $model )) {
			$id = $_REQUEST['id'];
			if ( isset($id) ) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				//
				if ( false !== $model->where($condition)->setField('status', 1) ) {
					$this->success ('审核成功！');
				}
				else {
					Log::write('审核失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('审核失败！');
				}
			}
			else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	// 批审核
	public function grpCheck() {
		//审核指定记录
		$model = M("Links");
		if (! empty ( $model )) {
			$ids = $_POST['ids'];
			if ( isset($ids) ) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $ids ) );
				//
				if ( false !== $model->where($condition)->setField('status', $_POST['flag']) ) {
					if ($_POST['flag'] == 1) {
						echo "checkOK|审核成功！";
					}
					else {
						echo "checkOK|取消审核成功！";
					}
				}
				else {
					Log::write('审核失败：'.$model->getLastSql(), Log::SQL);
					if ($_POST['flag'] == 1) {
						echo "审核失败！";
					}
					else {
						echo "取消审核失败！";
					}
				}
			}
			else {
				echo "非法操作";
			}
		}
	}
	
	// 排序
	public function sort(){
		$model = M("Links");
		$map = array();
		$map['status'] = 1;
		if (!empty($_GET['sortId'])) {
			$map['id'] = array('in', $_GET['sortId']);
		}
		else {
			$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach ($params as &$value) {
				$temp = explode("=", $value);
				if ( !empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
					if ($temp[0]=='category') {
						$map['category'] = array('in', $this->_getSubCats($temp[1]));
					}
					else {
						$map[$temp[0]] = $temp[1];
					}
				}
			}
		}
		$sortList = $model->where($map)->order('sort asc,category asc,create_time asc')->select();
		//echo $model->getLastSql();
		foreach ($sortList as &$value) {
			$value['txt_show'] = $value['title'];
		}
		$this->assign("sortList", $sortList);
		$this->display("../Public/sort");
		return;
	}
	
	

}
?>