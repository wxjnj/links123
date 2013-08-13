<?php
// 非法关健字管理
class IllegalKeywordsAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if (isset($_REQUEST['keyword_name']) && !empty($_REQUEST['keyword_name']) ) {
			$map['keyword_name'] = array('like',"%".$_REQUEST['keyword_name']."%");
			$this->assign("keyword_name", $_REQUEST['keyword_name']);
			$param['keyword_name'] = $_REQUEST['keyword_name'];
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
		$model = D("IllegalKeywords");
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', true );
		}
		$this->display();
		return;
	}
	
	function add() {
		if ($this->isPost()){
			$model = M ( "IllegalKeywords" );
			if (false === $model->create ()) {
				$this->error ( $model->getError () );
			}
	        $model->__set('create_time', time());
			$list = $model->add();
			if (false !== $list) {
				//成功提示
				$this->assign ( 'jumpUrl', cookie('_currentUrl_') );
				$this->success ('新增成功!',__URL__);
			} else {
				//错误提示
				$this->error ('新增失败!');
			}
		}
		$this->display();
	}
	
	//
	function edit() {
		$model = M ( "IllegalKeywords" );
		$vo = $model->getById ( $_REQUEST["id"] );
		$vo["create_time"] = date('Y-m-d H:i:s', $vo["create_time"]);
		$this->assign ( 'vo', $vo );
		$this->display();
	}

	
	//
	function update() {
		$model = D( "IllegalKeywords" );
		if ($this->isPost()){
			if ($model->where("id=%d",array($this->_post("id") ))
				->setField("keyword_name",$this->_post("keyword_name"))){
				$this->success ('修改成功!',__URL__);
			}else {
				$this->error ('修改失败!');
			}
			
		}
	}
	
	function resume() {
		//恢复指定记录
		$model = D("IllegalKeywords");
		$condition = array('id' => array('in', $_REQUEST['id']));
		if (false !== $model->where($condition)->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}

}
?>