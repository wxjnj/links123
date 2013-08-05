<?php
// 友情链接
class OutsideLinksAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['title']) ) {
			$title = $_REQUEST['title'];
		}
		if ( !empty($title) ) {
			$map['title'] = array('like',"%".$title."%");
		}
		//
		$this->assign('title', $title);
		//
		$param['title'] = $title;
	}
	
	// 列表
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = M("OutsideLinks");
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'sort', true );
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
		// 创建数据对象
		$model = D("OutsideLinks");
		if( false === $model->create() ) {
			$this->error( $model->getError() );
		}
		// 写入数据
		if( false !== $model->add() ) {
			$this->success('友情链接添加成功！');
		} 
		else {
			Log::write('友情链接添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('友情链接添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['title'] = htmlspecialchars(trim($_POST['title']));
        $_POST['url']	= str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
	}
	
	//
	function edit() {
		$model = M("OutsideLinks");
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
        $model = D("OutsideLinks");
        $OutsideLinksNow = $model->getById($_POST['id']);
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		//
		if ( false !== $model->save() ) {
			if ( $_POST['pic'] != $OutsideLinksNow['pic'] ) {
				$path = realpath('./Public/Uploads/OutsideLinks/'.$OutsideLinksNow['pic']);
				if ( !unlink($path) ) {
					Log::write('图片删除失败：'.$path, Log::FILE);
				}
			}
			//
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('友情链接编辑成功!');
		} 
		else {
			Log::write('友情链接编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('友情链接编辑失败!');
		}
	}
	
	//
	public function foreverdelete() {
		//删除指定记录
		$model = D("OutsideLinks");
		if (! empty ( $model )) {
			$id = $_REQUEST['id'];
			if ( isset($id) ) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				$rcds = $model->field('pic')->where($condition)->select();
				$pics = array();
				foreach ($rcds as &$value) {
					array_push($pics, $value['pic']);
				}
				//
				if ( false !== $model->where($condition)->delete() ) {
					foreach ($pics as &$value) {
						$path = realpath('./Public/Uploads/OutsideLinks/'.$value);
						if ( !unlink($path) ) {
							Log::write('图片删除失败：'.$path, Log::FILE);
						}
					}
					//
					$this->success('删除友情链接成功！',cookie('_currentUrl_'));
				}
				else {
					Log::write('删除友情链接失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('删除友情链接失败！');
				}
			}
			else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	// 排序
	public function sort(){
		$model = M("OutsideLinks");
		$map = array();
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
		$sortList = $model->where($map)->order('sort asc')->select();
		foreach ($sortList as &$value) {
			$value['txt_show'] = $value['title'];
		}
		$this->assign("sortList", $sortList);
		$this->display("../Public/sort");
		return;
	}

}
?>