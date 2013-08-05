<?php
// 非法关健字管理
class WebSettingsAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if (isset($_REQUEST['setting_name']) && !empty($_REQUEST['setting_name']) ) {
			$map['setting_name'] = array('like',"%".$_REQUEST['setting_name']."%");
			$this->assign("setting_name", $_REQUEST['setting_name']);
			$param['setting_name'] = $_REQUEST['setting_name'];
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
		$model = D("WebSettings");
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', true );
		}
		$this->display();
		return;
	}
	
	
	//
	function edit() {
		$model = M ( "WebSettings" );
		$vo = $model->getById ( $this->_get("id") );
		$this->assign ( 'vo', $vo );
		$this->display();
	}

	
	//
	function update() {
		$model = D( "WebSettings" );
		if ($this->isPost()){
			if ($model->where("id=%d",array($this->_post("id") ))
				->setField("setting_value",$this->_post("setting_value"))){
				//清除缓存
				R('/Admin/Public/clearCache',array(RUNTIME_PATH.'Temp')) ;
				//生成新缓存
				$model->getwebSettings();
				$this->success ('修改成功!',__URL__);
			}else {
				$this->error ('修改失败!');
			}
			
		}
	}
}
?>