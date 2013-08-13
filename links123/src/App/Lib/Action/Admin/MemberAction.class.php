<?php
// 会员
class MemberAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['nickname']) && !empty($_REQUEST['nickname']) ) {
			$map['nickname'] = array('like',"%".$_REQUEST['nickname']."%");
			$this->assign('nickname', $_REQUEST['nickname']);
			$param['nickname'] = $_REQUEST['nickname'];
		}
		//
		if (isset($_REQUEST['status']) && $_REQUEST['status']!='') {
			$map['status'] = $_REQUEST['status'];
		}
		else {
			$map['status'] = 1;
		}
		$this->assign('status', $map['status']);
		$param['status'] = $map['status'];
	}
	
	// 列表
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = M("Member");
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', false );
			//echo $model->getLastSql();
		}
		//
		$this->display();
	}

	// 插入数据
	/*
	public function insert() {
		// 创建数据对象
		$model = D("Member");
		$this->checkPost();
		import("@.ORG.String");
		$_POST['salt'] = String::rand_string();
		$_POST['password'] = md5(md5('123456').$_POST['salt']);
		//
		if( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		else {
			// 写入数据
			if( false !== $model->add() ) {
				$this->success('会员添加成功！');
			}else{
				$this->error('会员添加失败！');
			}
		}
	}
	*/
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['nickname']	= htmlspecialchars(trim($_POST['nickname']));
	}
	
	//
	function edit() {
		$model = M("Member");
		$vo = $model->getById( $_REQUEST['id'] );
		$this->assign ( 'vo', $vo );
		//
		$this->display();
	}
    
	// 更新数据
	public function update() {
		$model = D("Member");
        $this->checkPost();
		//
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		if ( false !== $model->save() ) {
			$this->assign( 'jumpUrl', __URL__.'/index?'.$_SESSION[C('SEARCH_PARAMS_KEY')] );
			$this->success('会员编辑成功!');
		} else {
			$this->error('会员编辑失败!');
		}
	}
	
	function resume() {
		//恢复指定记录
		$model = D("Member");
		$condition = array('id' => array('in', $_REQUEST['id']));
		if (false !== $model->where($condition)->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}

}
?>