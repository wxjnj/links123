<?php

/**
 * @name GroupAction.class.php
 * @package Admin
 * @desc 配置类型模块
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class GroupAction extends CommonAction {

	protected function _filter(&$map, &$param){
		if (isset($_REQUEST['name'])) {
			$name = $_REQUEST['name'];
		}
		if (!empty($name)) {
			$map['name'] =array('like',"%".$name."%");
		}
		$this->assign('name',$name);
		$param['name'] = $name;
	}

	/**
	 * @desc 排序
	 * @see GroupAction::sort()
	 */
    public function sort(){
    	$model = M("Group");
    	$map = array();
    	$map['status'] = 1;
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
    		$value['txt_show'] = $value['name'];
    	}
    	$this->assign("sortList",$sortList);
    	$this->display("../Public/sort");
    	return;
    }
}
?>