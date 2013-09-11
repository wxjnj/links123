<?php
/**
 * @name GroupAction.class.php
 * @package Admin
 * @desc 后台管理-分组管理
 * @author Lee UPDATE 2013-09-05
 * @version 0.0.1
 */

class GroupAction extends CommonAction {
    
	/**
	 * @desc 分组管理查询条件
     * @author Lee UPDATE 2013-09-05
	 * @param array $map SQL条件数组
     * @param array $param 参数数组
	 * @return array    
	 */  
	protected function _filter(&$map, &$param){
        if (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
			$map['name'] =array('like', "%".$_REQUEST['name']."%");
		}
		$this->assign('name', $_REQUEST['name']);
		$param['name'] = $_REQUEST['name'];
	}

	/**
	 * @desc 分组管理排序
     * @author Lee UPDATE 2013-09-05 
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
    			if (!empty($temp[1]) && $temp[0]!='sort' && $temp[0]!='order') {
    				$map[$temp[0]] = $temp[1];
    			}
    		}
    	}
    	$sortList = $model->where($map)->order('sort ASC')->select();
    	foreach ($sortList as &$value) {
    		$value['txt_show'] = $value['name'];
    	}
    	$this->assign("sortList",$sortList);
    	$this->display("../Public/sort");
    }
}
?>