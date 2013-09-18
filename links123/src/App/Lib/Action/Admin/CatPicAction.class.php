<?php
/**
 * @desc 目录图管理
 * @author Frank UPDATE 2013-9-14
 */
class CatPicAction extends CommonAction {
	
	protected function _filter(&$map, &$param){
		$name = $this->_param('name');
		$rid = $this->_param('rid');
		if (!empty($name)) {
			$map['name'] = array('like',"%".$name."%");
		}
		$this->assign('name', $name);
		$param['name'] = $name;
		
		if (!empty($rid)) {
			$map['rid'] = $rid;
		}
		$this->assign('rid', $map['rid']);
		$param['rid'] = $map['rid'];
	}
	
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter( $map, $param );
		}
		$model = new CatPicViewModel();
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', true );
			//echo $model->getLastSql();
		}
		$this->getRootCats();
		
		$this->display();
		return;
	}
	
	/**
	 * @desc 添加
	 * @author Frank UPDATE 2013-09-14
	 */
	public function add() {
		$this->getRootCats();
		$this->display();
		return;
	}
	
	/**
	 * @desc 插入数据
	 * @author Frank UPDATE 2013-09-14
	 */
	public function insert() {
		$this->checkPost();
		// 创建数据对象
		$model = D("CatPic");
		if( false === $model->create() ) {
			$this->error($model->getError());
		}
		// 写入数据
		if ( false !== $model->add() ) {
			$this->success('图片添加成功！');
		}else {
			Log::write('图片添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('图片添加失败！');
		}
	}
	
	/**
	 * @desc 安全验证
	 * @author Frank UPDATE 2013-09-14
	 */
	protected function checkPost() {
		// 安全验证
		$_POST['name'] = htmlspecialchars($_POST['name']);
	}
	
	/**
	 * @desc 编辑
	 * @author Frank UPDATE 2013-09-14
	 */
	function edit() {
		$id = $this->_param('id');
		$model = M("CatPic");
		$vo = $model->getById($id);
		$this->assign( 'vo', $vo );
		$this->getRootCats();
		$this->display();
		return;
	}
	
	/**
	 * @desc 更新数据
	 * @author Frank UPDATE 2013-09-14
	 */
	public function update() {
		$id = $this->_post('id');
		$pic_big = $this->_post('pic_big');
		$pic = $this->_post('pic');
		
		$model = D("CatPic");
		$cpNow = $model->getById($id);
		
		if ( false === $model->create() ) {
			$this->error( $model->getError () );
			exit(0);
		}
		if ( false !== $model->save() ) {
			if ( $pic != $cpNow['pic'] ) {
				$path = realpath('./Public/Uploads/CatPics/'.$cpNow['pic']);
				if ( !unlink($path) ) {
					Log::write('缩略图删除失败：'.$path, Log::FILE);
				}
			}
			if ( $pic_big != $cpNow['pic_big'] ) {
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
	
	/**
	 * @desc 删除指定记录
	 * @author Frank UPDATE 2013-09-14
	 */
	public function foreverdelete() {
		$model = D("CatPic");
		if (! empty ( $model )) {
			$id = $this->_param('id');
			if (isset ( $id )) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				$rcds = $model->field('pic, pic_big')->where($condition)->select();
				$pics = array();
				foreach ($rcds as &$value) {
					array_push($pics, $value['pic']);
					array_push($pics, $value['pic_big']);
				}
				
				if (false !== $model->where($condition)->delete ()) {
					foreach ($pics as &$value) {
						$path = realpath('./Public/Uploads/CatPics/'.$value);
						if ( !unlink($path) ) {
							Log::write('图片删除失败：'.$path, Log::FILE);
						}
					}
					$this->success ('图片删除成功！');
				}else {
					Log::write('图片删除失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('图片删除失败！');
				}
			}else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	/**
	 * @desc 排序
	 * @author Frank UPDATE 2013-09-14
	 */
	public function sort(){
		$sortId = $this->_param('sortId');
		$model = M("CatPic");
		$map = array();
        if(!empty($sortId)) {
            $map['id'] = array('in', $sortId);
        }else{
			$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach ($params as &$value) {
				$temp = explode("=", $value);
				if ( !empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
					$map[$temp[0]] = $temp[1];
				}
			}
        }
        $sortList = $model->where($map)->order('sort ASC')->select();
        foreach ($sortList as &$value) {
        	$value['txt_show'] = $value['name']."　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }
    

}
?>