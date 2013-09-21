<?php
/**
 * @desc 直达网址
 * @name DirectLinksAction.class.php
 * @package Admin
 * @author Frank UPDATE 2013-09-14
 * @version 1.0
 */
class DirectLinksAction extends CommonAction {
	
	protected function _filter(&$map, &$param){
		$tag = $this->_param('tag');
		$url = $this->_param('url');
		$status = $this->_param('status');
		$cn_tag = $this->_param('cn_tag');
		$checked = $this->_param('checked');
		
		if ($tag != '') {
			$map['tag'] = array('like', "%".$tag."%");
			$this->assign('tag', $tag);
			$param['tag'] = $tag;
		}
		
		if ($url != '') {
			$map['url'] = array('like', "%".$url."%");
			$this->assign('url', $url);
			$param['url'] = $url;
		}
		
		if ($status != '') {
			$map['status'] = $status;
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}
		
		if ($cn_tag != '') {
			$map['cn_tag'] = $cn_tag;
			$this->assign('cn_tag', $map['cn_tag']);
			$param['cn_tag'] = $map['cn_tag'];
		}
		
		if ($checked != '') {
			$map['checked'] = $checked;
			$this->assign('checked', $map['checked']);
			$param['checked'] = $map['checked'];
		}
	}
	
	/**
	 * @desc 列表过滤器，生成查询Map对象
	 * @author Frank UPDATE 2013-09-14
	 */
	public function index() {
		
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = M("DirectLinks");
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
		// 创建数据对象
		$model = D("DirectLinks");
		//
		if ($model->where("tag='".$_POST['tag']."'")->find()) {
			$this->error( "该标签已存在！" );
		}
		//
		if( false === $model->create() ) {
			$this->error( $model->getError() );
		}
		// 写入数据
		$pattern = '/[^\x00-\x80]/';
		if ( preg_match($pattern, $_POST['tag']) ) {
			$model->__set('cn_tag', 1);
		}
		if( false !== $model->add() ) {
			$this->success('直达网址添加成功！');
		} 
		else {
			Log::write('直达网址添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('直达网址添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['tag'] = htmlspecialchars(trim($_POST['tag']));
        $_POST['url']	= str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
	}
	
	//
	function edit() {
		$model = M("DirectLinks");
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
        $model = D("DirectLinks");
        //
        if ($model->where("id!=".$_POST['id']." and tag='".$_POST['tag']."'")->find()) {
        	$this->error( "该标签已存在！" );
        }
        //
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		//
		$pattern = '/[^\x00-\x80]/';
		if ( preg_match($pattern, $_POST['tag']) ) {
			$model->__set('cn_tag', 1);
		}
		else {
			$model->__set('cn_tag', 0);
		}
		if ( false !== $model->save() ) {
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('直达网址编辑成功!');
		} 
		else {
			Log::write('直达网址编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('直达网址编辑失败!');
		}
	}
	
	//
	public function foreverdelete() {
		//删除指定记录
		$model = D("DirectLinks");
		if (! empty ( $model )) {
			$id = $_REQUEST['id'];
			if ( isset($id) ) {
				$condition = array();
				$condition['id'] = array ('in', explode ( ',', $id ) );
				//
				if ( false !== $model->where($condition)->delete() ) {
					$this->success('删除直达网址成功！',cookie('_currentUrl_'));
				}
				else {
					Log::write('删除直达网址失败：'.$model->getLastSql(), Log::SQL);
					$this->error ('删除直达网址失败！');
				}
			}
			else {
				$this->error ( '非法操作！' );
			}
		}
	}
	
	// 批审核
	public function grpCheck() {
		//审核指定记录
		$model = M("DirectLinks");
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
	
	// 设置已查看
	public function setChecked() {
		if ( !empty($_REQUEST['link']) ) {
			$model = M('DirectLinks');
			if ( false !== $model->where("url='".$_REQUEST['link']."'")->setField('checked', 1) ) {
				$this->ajaxReturn(null,null,true);
			}
			else {
				$this->ajaxReturn(null,'设置已查看失败！',false);
			}
		}
	}


}
?>