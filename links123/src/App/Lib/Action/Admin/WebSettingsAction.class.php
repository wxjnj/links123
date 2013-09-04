<?php
/**
 * @name WebSettingsAction.class.php
 * @package Admin
 * @desc 后台管理-网站基本设置
 * @author Lee UPDATE 2013-09-04
 * @version 0.0.1
 */
class WebSettingsAction extends CommonAction {

	/**
	 * @desc 查询条件
     * @author Lee UPDATE 2013-09-04
	 * @param array $map SQL条件数组
     * @param array $param 参数数组
	 * @return array    
	 */
	protected function _filter(&$map, &$param){
		if (isset($_REQUEST['setting_name']) && !empty($_REQUEST['setting_name'])) {
			$map['setting_name'] = array('like', "%".$_REQUEST['setting_name']."%");
			$this->assign("setting_name", $_REQUEST['setting_name']);
			$param['setting_name'] = $_REQUEST['setting_name'];
		}
	}
	
	/**
	 * @desc 网站基本设置 首页
     * @author Lee UPDATE 2013-09-04  
	 */           
	public function index() {
		$map = array();
		$param = array();
        
		if (method_exists($this, '_filter')) {
			$this->_filter($map,$param);
		}
        
		$model = D("WebSettings");
		if (!empty($model)) {
			$this->_list($model, $map, $param, 'id', true);
		}
		$this->display();
	}
	
	
	/**
	 * @desc 网站基本设置 修改页面
	 * @author Lee UPDATE 2013-09-04
	 */
	function edit() {
		$model = M("WebSettings");
		$vo = $model->getById($this->_get("id"));
		$this->assign('vo',$vo);
		$this->display();
	}

	
	/**
	 * @desc 网站基本设置 修改保存
	 * @author Lee UPDATE 2013-09-04
	 */
	function update() {
		$model = D("WebSettings");
        
		if ($this->isPost()){
			if ($model->where("id=%d", array($this->_post("id")))->setField("setting_value", $this->_post("setting_value"))){
				R('/Admin/Public/clearCache', array(RUNTIME_PATH.'Temp'));
				$model->getwebSettings();
				$this->success ('修改成功!', __URL__);
			}else {
				$this->error ('修改失败!');
			}
		}
	}
}
?>