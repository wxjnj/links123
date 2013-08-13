<?php
// 目录图管理
class CatPicAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if (isset($_REQUEST['name'])) {
			$name = $_REQUEST['name'];
		}
		if (!empty($name)) {
			$map['name'] = array('like',"%".$name."%");
		}
		$this->assign('name', $name);
		$param['name'] = $name;
		//
		if (isset($_REQUEST['rid']) && $_REQUEST['rid']!='') {
			$map['rid'] = $_REQUEST['rid'];
		}
		$this->assign('rid', $map['rid']);
		$param['rid'] = $map['rid'];
	}
	
	//
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = new CatPicViewModel();
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', true );
			//echo $model->getLastSql();
		}
		//
		$this->getRootCats();
		//
		$this->display();
		return;
	}
	
	//
	public function add() {
		//
		$this->getRootCats();
		//
		$this->display();
		return;
	}
	
	// 插入数据
	public function insert() {
		//
		$this->checkPost();
		// 创建数据对象
		$model = D("CatPic");
		if( false === $model->create() ) {
			$this->error($model->getError());
		}
		// 写入数据
		if ( false !== $model->add() ) {
			$this->success('图片添加成功！');
		}
		else {
			Log::write('图片添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('图片添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
		$_POST['name'] = htmlspecialchars($_POST['name']);
	}
	
	//
	function edit() {
		$model = M("CatPic");
		$vo = $model->getById( $_REQUEST['id'] );
		$this->assign( 'vo', $vo );
		//
		$this->getRootCats();
		//
		$this->display();
		return;
	}

	// 更新数据
	public function update() {
		$model = D("CatPic");
		$cpNow = $model->getById($_POST['id']);
		//
		if ( false === $model->create() ) {
			$this->error( $model->getError () );
		}
		if ( false !== $model->save() ) {
			if ( $_POST['pic'] != $cpNow['pic'] ) {
				$path = realpath('./Public/Uploads/CatPics/'.$cpNow['pic']);
				if ( !unlink($path) ) {
					Log::write('缩略图删除失败：'.$path, Log::FILE);
				}
			}
			if ( $_POST['pic_big'] != $cpNow['pic_big'] ) {
				$path = realpath('./Public/Uploads/CatPics/'.$cpNow['pic_big']);
				if ( !unlink($path) ) {
					Log::write('大图删除失败：'.$path, Log::FILE);
				}
			}
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('图片编辑成功!');
		} else {
			$this->error('图片编辑失败!');
		}
	}
	
	//
	public function foreverdelete() {
		//删除指定记录
		$model = D("CatPic");
		if (! empty ( $model )) {
			$id = $_REQUEST ['id'];
			if (isset ( $id )) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				$rcds = $model->field('pic,pic_big')->where($condition)->select();
				$pics = array();
				foreach ($rcds as &$value) {
					array_push($pics, $value['pic']);
					array_push($pics, $value['pic_big']);
				}
				//
				if (false !== $model->where($condition)->delete ()) {
					foreach ($pics as &$value) {
						$path = realpath('./Public/Uploads/CatPics/'.$value);
						if ( !unlink($path) ) {
							Log::write('图片删除失败：'.$path, Log::FILE);
						}
					}
					//
					$this->success ('图片删除成功！');
				}
				else {
					Log::write('图片删除失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('图片删除失败！');
				}
			}
			else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	// 排序
	public function sort(){
		$model = M("CatPic");
		$map = array();
        if(!empty($_GET['sortId'])) {
            $map['id'] = array('in', $_GET['sortId']);
        }else{
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
        	$value['txt_show'] = $value['name']."　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }
    

}
?>