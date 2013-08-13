<?php
// 建议投诉
class SuggestionAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if (isset($_REQUEST['suggest']) && !empty($_REQUEST['suggest']) ) {
			$map['suggest'] = array('like',"%".$_REQUEST['suggest']."%");
			$this->assign("suggest", $_REQUEST['suggest']);
			$param['suggest'] = $_REQUEST['suggest'];
		}
		//
		if (isset($_REQUEST['type']) && $_REQUEST['type']!='') {
			$map['type'] = $_REQUEST['type'];
			$this->assign('type', $_REQUEST['type']);
			$param['type'] = $_REQUEST['type'];
		}
		//
		if (isset($_REQUEST['status']) && $_REQUEST['status']!='') {
			$map['status'] = $_REQUEST['status'];
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}
		else {
			$map['status'] = array('egt', 0);
		}
	}
	
	//
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = new SuggestionViewModel();
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', false );
			//echo $model->getLastSql());
		}
		$this->display();
		return;
	}
	
	//
	function edit() {
		$model = M ( "Suggestion" );
		$vo = $model->getById ( $_REQUEST["id"] );
		$vo["create_time"] = date('Y-m-d H:i:s', $vo["create_time"]);
		$this->assign ( 'vo', $vo );
		$this->display();
	}
	
	//
	function update() {
		$model = D( "Suggestion" );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
                $model->__set('is_reply', 1);
                $model->__set('create_time', time());
                $model->__set('mid', -1);
		$list = $model->add();
		if (false !== $list) {
			//成功提示
			$this->assign ( 'jumpUrl', cookie('_currentUrl_') );
			$this->success ('回复成功!');
		} else {
			//错误提示
			$this->error ('回复失败!');
		}
	}
	
	function resume() {
		//恢复指定记录
		$model = D("Suggestion");
		$condition = array('id' => array('in', $_REQUEST['id']));
		if (false !== $model->where($condition)->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}

}
?>