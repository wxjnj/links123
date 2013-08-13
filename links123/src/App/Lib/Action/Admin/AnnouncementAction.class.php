<?php
// 公告
class AnnouncementAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['title']) ) {
			$title = $_REQUEST['title'];
		}
		if ( !empty($title) ) {
			$map['title'] = array('like',"%".$title."%");
		}
		$this->assign('title', $title);
		$param['title'] = $title;
		//
		if (isset($_REQUEST['status']) && $_REQUEST['status']!='') {
			$map['status'] = $_REQUEST['status'];
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}
		else {
			$map['status'] = 1;
		}
	}
	
	// 列表
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = new AnnouncementViewModel();
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', false );
			//echo $model->getLastSql();
		}
		//
		$this->display();
		return;
	}
	
	//
	public function add() {
		//
		$this->display();
		return;
	}

	// 插入数据
	public function insert() {
		//
		$this->checkPost();
		$_POST['uid'] = $_SESSION[C('USER_AUTH_KEY')];
		// 创建数据对象
		$model = D("Announcement");
		if( false === $model->create() ) {
			$this->error( $model->getError() );
		}
		// 写入数据
		if( false !== $model->add() ) {
			$this->success('公告添加成功！');
		} 
		else {
			Log::write('公告添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('公告添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['title'] 	= stripslashes(trim($_POST['title']));
		$_POST['content']	= stripslashes(trim($_POST['content']));
	}
	
	//
	function edit() {
		$model = M("Announcement");
		$vo = $model->getById( $_REQUEST['id'] );
		$this->assign ( 'vo', $vo );
		//
		$this->display();
		return;
	}
    
	// 更新数据
	public function update() {
		//
        $this->checkPost();
		//
        $model = D("Announcement");
        $AnnouncementNow = $model->getById($_POST['id']);
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		//
		if ( false !== $model->save() ) {
			//
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('公告编辑成功!');
		} 
		else {
			Log::write('公告编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('公告编辑失败!');
		}
	}
	
	// 删除
	public function delete() {
		//删除指定记录
		$model = D("Announcement");
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
	
	// 永久删除
	public function foreverdelete() {
		//删除指定记录
		$model = D("Announcement");
		if (! empty ( $model )) {
			$id = $_REQUEST['id'];
			if ( isset($id) ) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				//
				if ( false !== $model->where($condition)->delete() ) {
					$this->success ('删除公告成功！');
				}
				else {
					Log::write('删除公告失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('删除公告失败！');
				}
			}
			else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	// 排序
	public function sort(){
		$model = M("Announcement");
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
					$map[$temp[0]] = $temp[1];
				}
			}
		}
		$sortList = $model->where($map)->order('sort asc,create_time desc')->select();
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