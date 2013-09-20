<?php
/**
 * @desc 链接列表
 * @author Frank UPDATE 2013-9-10
 */
class LinksAction extends CommonAction {
	/**
	 * @desc 链接过滤
	 * @param array $map
	 * @param array $param
	 */
	protected function _filter(&$map, &$param){
		$title = $this->_param('title');
		$category = $this->_param('category');
		$grade = $this->_param('grade');
		$language = $this->_param('language');
		$status = $this->_param('status');
		
		if (isset($title) && !empty($title)) {
			$title = trim($title);
			$map['_string'] = "title like '%".$title."%' or link like '%".$title."%'";
		}
		$param['title'] = $title;
		
		if ( isset($category) && $category != '' ) {
			$map['category'] = array('in', $this->_getSubCats($category));
			$catNow = M("Category")->getById($category);
			$this->assign('cat_name', $catNow['cat_name']);
			$this->assign('rid', $catNow['prt_id']);
			$this->assign('cat_id', $catNow['id']);
		}
		$param['category'] = $category;
		
		if (isset($grade) && $grade != '') {
			$map['grade'] = $grade;
		}
		$param['grade'] = $grade;
		
		if (isset($language) && $language != '') {
			$map['language'] = $language;
			$this->assign('language', $map['language']);
		}
		$param['language'] = $map['language'];
		
		if (isset($status) && $status != '') {
			$map['status'] = $status;
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}else {
			$map['status'] = array('egt',0);
		}
		
		$this->assign('title', $title);
		$this->assign('category', $category);
		$this->assign('grade', $map['grade']);
	}
	
	/**
	 * @name getMyCats
	 * @desc 获取目录
	 * @author Frank 2013-09-10
	 */
	public function getMyCats($lan=null, $arychecked=null) {
		$cat = M("Category");
		$cats = $cat->field('id, cat_name, level')->where('status = 1 and level = 1')->order('sort ASC')->select();
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
	
	/**
	 * (non-PHPdoc)
	 * @see CommonAction::index()
	 */
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ($this, '_filter')) {
			$this->_filter($map, $param);
		}
		
		$model = new LinksViewModel();
		if (!empty ($model)) {
			$this->_list($model, $map, $param, 'id', false);
		}
		
		$this->getMyCats($map['language']);
		$this->display();
		return;
	}
	
	/**
	 * @desc 添加链接
	 * @see CommonAction::add()
	 */
	public function add() {
		$language = $this->_post('language');
		$uid = $_SESSION[C('USER_AUTH_KEY')];
		
		$model = M("Links");
		if(isset($language) && !empty($language)) {
			$vo['title'] = $this->_post('title');
			$vo['link'] = $this->_post('link');
			$vo['logo'] = $this->_post('logo');
			$vo['language'] = $this->_post('language');
			$this->getMyCats($language);
		}else {
			$idnow = $model->where("uid = '%d'", $uid)->max('id');
			$vo = $model->getById($idnow);
			$aryCats = array();
			//$alllinks = $model->field('category,grade')->where('link=\''.$vo['link'].'\'')->select();
			$alllinks = $model->field('category, grade')->where("link = '%s'", $vo['link'])->order('id DESC')->limit(1)->select();
			foreach ($alllinks as &$value) {
				array_push($aryCats, $value['category']);
			}
			$cids = implode(",", $aryCats);
			$cats = M("Category")->field('cat_name')->where('id in('.$cids.')')->select();
			foreach($cats as &$value) {
				if(empty($vo['cat_name'])) {
					$vo['cat_name'] = $value['cat_name'];
				}else {
					$vo['cat_name'] .= ",".$value['cat_name'];
				}
			}
			
			$vo['title'] = '';
			$vo['link'] = '';
			$vo['logo'] = '';
			
			$this->getMyCats($vo['language'], $alllinks);
		}
		$this->assign ( 'vo', $vo );
		$this->display();
		return;
	}

	/**
	 * @desc 插入数据
	 * @see CommonAction::insert()
	 */
	public function insert() {
		$model = M("Links");
		$this->checkPost();
		$_POST['uid'] = $_SESSION[C('USER_AUTH_KEY')];
		
		if($model->where("status = 1 and category in(".$_POST['categorys'].") and link = '".$_POST['link']."'")->find()) {
			$this->error('该链接已存在！');
			exit(0);
		}
		
		if (!empty($_POST['tags'])) {
			$ary = explode(',', $_POST['tags']);
			foreach ($ary as &$value) {
				$list = $model->field('tags')->where("status = 1 and category in(".$_POST['categorys'].") and tags like '%".$value."%'")->select();
				foreach ($list as &$val) {
					$temp = explode(',', $val);
					foreach ($temp as &$item) {
						if ( $item == $value ) {
							$this->error('该标签已存在！');
							exit(0);
						}
					}
				}
			}
		}
		
		if(empty($_POST['categorys'])) {
			$this->error('请选择目录！');
		}
		
		$cats = explode(',', $_POST['categorys']);
		$rids = explode(',', $_POST['rids']);
		$_POST['create_time'] = time();
		
		$model->startTrans();
		$result = true;		
		foreach ($cats as $key => $value) {
			$_POST['category'] = $value;
			$_POST['grade'] = $_POST['grade'.$rids[$key]];
			if( false === $model->add($_POST) ) {
				$result = false;
				Log::write('链接添加失败：'.$model->getLastSql(), Log::SQL);
			}
		}
		
		if($result) {
			$model->commit();
			$this->success('链接添加成功！');
		}else {
			$model->rollback();
			$this->error('链接添加失败！');
		}
	}
	
	/**
	 * @desc 安全验证
	 */
	protected function checkPost() {
        $_POST['title']	= htmlspecialchars(trim($_POST['title']));
        $_POST['link']	= str_replace('http://', '', htmlspecialchars(trim($_POST['link'])));
        $_POST['link']	= str_replace('https://', '', $_POST['link']);
		$_POST['intro']	= htmlspecialchars(trim($_POST['intro']));
		$_POST['tags']	= str_replace('，', ',', htmlspecialchars(trim($_POST['tags'])));
		$_POST['nickname'] = trim($_POST['nickname']);
	}
	/**
	 * @desc 编辑链接
	 * @see CommonAction::edit()
	 */
	function edit() {
		$id = $this->_get('id');
		$language = $this->_get('language');
		
		$model = M("Links");
		$vo = $model->getById($id);
		$catNow = M("Category")->getById($vo['category']);
		$vo['cat_name'] = $catNow['cat_name'];
		$vo['rid'] = $catNow['prt_id'];
		
		if(isset($language) && !empty($language)) {
			$vo['language'] = $language;
		}
		
		if($vo['mid']>0) {
			$vo['nickname'] = M("Member")->where("id = '%s'", $vo['mid'])->getField('nickname');
		}
		
		$this->assign('vo', $vo);
		$this->getMyCats($vo['language']);
		$this->display();
		return;
	}
    
	/**
	 * (non-PHPdoc)
	 * @see CommonAction::update()
	 */
	public function update() {
		$model = M("Links");
        $this->checkPost();
        if($model->where("status = 1 and id != '%d' and category = '%s' and link = '%s'", $_POST['id'], $_POST['category'], $_POST['link'])->find()) {
        	$this->error('该链接已存在！');
        	exit(0);
        }
        
        if(!empty($_POST['tags'])) {
        	$ary = explode(',', $_POST['tags']);
        	foreach ($ary as &$value) {
        		$list = $model->field('tags')->where("status = 1 and id != '%d' and category = '%s' and tags like '%".$value."%'", $_POST['link'], $_POST['category'])->select();
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
        
        $linkNow = $model->getById($_POST['id']);
		if(false === $model->create()) {
			$this->error($model->getError());
		}
		
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
	 
	/**
	 * @desc 批编辑
	 * @author Frank UPDATE 2013-09-10
	 */
	function groupEdit() {
		$id = $this->_param('id');
		$language = $this->_param('language');
		
		$this->assign ('id', $id);
		if(empty($language)) {
			$language = 1;
		}
		$this->assign("language", $language);
		$this->getMyCats($language);
		$this->display();
		return;
	}
	
	/**
	 * @desc 保存批编辑
	 * @author Frank UPDATE 2013-09-10
	 */
	function groupUpdate() {
		$model = M("Links");
		$id = $this->_post('id');
		$language = $this->_post('language');
		$category = $this->_post('category');
		
		$ids = explode(",", $id);
		foreach($ids as &$value) {
			$linkNow = $model->where("id = '%s'", $value)->getField('link');
			if($model->where("language = '%s' and category = '%s' and link = '%s'", $language, $category, $linkNow)->find()) {
				$this->error('链接 '.$linkNow.' 已存在');
				exit(0);
			}
		}
		
		$data['language'] = $language;
		$data['category'] = $category;
		$data['grade'] = $this->_post('grade');
		
		if(false === $model->where('id in('.$id.')')->save($data)) {
			$this->error('批量编辑失败！');
			exit(0);
		}else {
			$this->success('批量编辑成功！',cookie('_currentUrl_'));
			exit(0);
		}
	}
	
	/**
	 * @desc 删除
	 * @author Frank UPDATE 2013-09-10
	 */
	public function delete() {
		//删除指定记录
		$model = D("Links");
		$id = $this->_param('id');
		if(!empty($model)) {
			if(isset($id)) {
				$condition = array('id' => array('in', explode(',', $id)));
				$list = $model->where($condition)->setField('status', - 1);
				if($list !== false) {
					$this->success('删除成功！',cookie('_currentUrl_'));
					exit(0);
				}else {
					$this->error('删除失败！');
					exit(0);
				}
			}else {
				$this->error('非法操作');
				exit(0);
			}
		}
	}
	
	/**
	 * @desc 恢复
	 * @author Frank UPDATE 2013-09-10
	 */
	public function recycle() {
		$model = D("Links");
		//foreach ($_GET['id'] as &$value) {
		$id = $this->_param('id');
		$linkNow = $model->getById($id);
		if($model->where("status = 1 and category = '%s' and link = '%s'", $linkNow['category'], $linkNow['link'])->find()) {
			$this->error('该链接已存在！');
			exit(0);
		}
		//}
		$condition = array('id' => array('in', $id));
		if(false !== $model->where($condition)->setField('status', 0)) {
			$this->success('状态还原成功！',cookie('_currentUrl_'));
			exit(0);
		}else {
			$this->error('状态还原失败！');
			exit(0);
		}
	}
	
	/**
	 * @desc 删除指定记录
	 * @author Frank UPDATE 2013-09-10
	 */
	public function foreverdelete() {
		$model = M("Links");
		if (!empty($model)) {
			$model->startTrans();
			$result = true;
			$id = $this->_param('id');
			if(isset($id)) {
				$ids = explode(',', $id);
				foreach($ids as &$value) {
					$linkNow = $model->field('id, logo')->where("id = '%s'", $value)->find();
					if(!empty($linkNow['logo'])) {
						if(!$model->where("id <> '%d' and logo = '%s'", $linkNow['id'], $linkNow['logo'])->find()) {
							$path = realpath('./Public/Uploads/Links/'.$linkNow['logo']);
							if(!unlink($path)) {
								Log::write('logo删除失败：'.$path, Log::FILE);
							}
						}
					}
					if(false === $model->where("id = '%s'", $value)->delete()) {
						$result = false;
						Log::write('删除链接失败：'.$model->getLastSql(), Log::SQL);
					}
				}
				
				if($result) {
					$model->commit();
					$this->success ('删除链接成功！');
					exit(0);
				}else {
					$model->rollback();
					$this->error ('删除链接失败！');
					exit(0);
				}
			}else {
				$this->error ( '非法操作' );
				exit(0);
			}
		}
	}
	
	/**
	 * @desc 检测链接是否存在
	 * @author Frank UPDATE 2013-09-10
	 */
	public function checkLink() {
		if(M("Links")->where("link = '%s'", $this->_post('link'))->find()) {
			echo true;
		}else {
			echo false;
		}
	}
	
	/**
	 * @desc 审核
	 * @author Frank UPDATE 2013-09-10
	 */
	public function check() {
		//审核指定记录
		$model = M("Links");
		if(!empty($model)) {
			$id = $this->_post('id');
			if(isset($id)) {
				$condition['id'] = array ('in', explode ( ',', $id ) );
				if(false !== $model->where($condition)->setField('status', 1)) {
					$this->success('审核成功！');
					exit(0);
				}else {
					Log::write('审核失败：'.$model->getLastSql(), Log::SQL);
					$this->error('审核失败！');
					exit(0);
				}
			}else {
				$this->error('非法操作');
				exit(0);
			}
		}
	}
	
	/**
	 * @desc 批审核
	 * @author Frank UPDATE 2013-09-10
	 */
	public function grpCheck() {
		//审核指定记录
		$model = M("Links");
		if (! empty ( $model )) {
			$ids = $this->_post('ids');
			$flag  = $this->_post('flag');
			if(isset($ids)) {
				$condition['id'] = array('in', explode( ',', $ids ));
				if(false !== $model->where($condition)->setField('status', $flag)) {
					echo $flag == 1 ? "checkOK|审核成功！" : "checkOK|取消审核成功！";
				}else {
					Log::write('审核失败：'.$model->getLastSql(), Log::SQL);
					echo $flag == 1 ? "审核失败！" : "取消审核失败！";
				}
			}else {
				echo "非法操作";
			}
		}
	}
	
	/**
	 * @desc 排序
	 * @author Frank UPDATE 2013-9-10
	 */
	public function sort(){
		$sortId = $_GET['sortId'];
		
		$model = M("Links");
		$map = array();
		$map['status'] = 1;
		if(!empty($sortId)) {
			$map['id'] = array('in', $sortId);
		}else {
			$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach($params as &$value) {
				$temp = explode("=", $value);
				if(!empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
					if ($temp[0]=='category') {
						$map['category'] = array('in', $this->_getSubCats($temp[1]));
					}else {
						$map[$temp[0]] = $temp[1];
					}
				}
			}
		}
		$sortList = $model->where($map)->order('sort ASC,category ASC,create_time ASC')->select();
		//echo $model->getLastSql();
		foreach($sortList as &$value) {
			$value['txt_show'] = $value['title'];
		}
		$this->assign("sortList", $sortList);
		$this->display("../Public/sort");
		return;
	}
}
?>