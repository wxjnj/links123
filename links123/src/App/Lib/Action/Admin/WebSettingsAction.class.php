<?php
/**
 * @name WebSettingsAction.class.php
 * @package Admin
 * @desc 非法关健字管理
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class WebSettingsAction extends CommonAction {

	protected function _filter(&$map, &$param){
		if (isset($_REQUEST['setting_name']) && !empty($_REQUEST['setting_name'])) {
			$map['setting_name'] = array('like',"%".$_REQUEST['setting_name']."%");
			$this->assign("setting_name", $_REQUEST['setting_name']);
			$param['setting_name'] = $_REQUEST['setting_name'];
		}
	}
	
	/**
	 * @desc 默认主页
	 * @see WebSettingsAction::index()
	 */
	public function index() {
		$map = array();
		$param = array();
		if (method_exists ($this,'_filter')) {
			$this->_filter($map,$param);
		}
		$model = D("WebSettings");
		if (!empty($model)) {
			$this->_list($model,$map,$param,'id',true);
		}
		$this->display();
		return;
	}
	
	
	/**
	 * @desc 编辑页面
	 * @see WebSettingsAction::edit()
	 */
	function edit() {
		$model = M("WebSettings");
		$vo = $model->getById($this->_get("id"));
		$this->assign('vo',$vo);
		$this->display();
	}

	
	/**
	 * @desc 编辑操作
	 * @see WebSettingsAction::update()
	 */
	function update() {
		$model = D("WebSettings");
		if ($this->isPost()){
			if ($model->where("id=%d",array($this->_post("id")))->setField("setting_value",$this->_post("setting_value"))){
				R('/Admin/Public/clearCache',array(RUNTIME_PATH.'Temp'));
				$model->getwebSettings();
				$this->success ('修改成功!',__URL__);
			}else {
				$this->error ('修改失败!');
			}
		}
	}
}
?>