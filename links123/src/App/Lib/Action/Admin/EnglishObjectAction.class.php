<?php
/**
 * 英语角科目管理控制类
 * @author Adam <foureyed@qq.com> 2013.5.12
 */
class EnglishObjectAction extends CommonAction {

    public function _filter(&$map, &$param) {
    	$name = $this->_param('name');
        if (!empty($name)) {
            $map['name'] = array('like', "%" . $name . "%");
        }
        $this->assign('name', $name);
        $param['name'] = $name;
    }

    /**
     * @desc 设置默认
     * @author Frank 2013-09-14
     */
    public function setDefault() {
        if ($this->isAjax()) {
            $id = $this->_param('id');
            $model = D("EnglishObject");
            $info = $model->find($id);
            if ($info['status'] == 0) {
                $this->ajaxReturn("", "无法设置不可用记录", false);
            }
            $model->startTrans();
            $ret = $model->where("id = %d and `status` = 1", $id)->setField("default", 1);
            if ($ret !== false) {
                $list = $model->where("id != %d", $id)->setField("default", 0);
                if (false === $list) {
                    $model->rollback();
                    $this->ajaxReturn("", "操作失败", false);
                }
            } else {
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            }
            $model->commit();
            $this->ajaxReturn("", "操作成功", true);
        }
    }
    /**
	 * @desc 排序
	 * @author Frank 2013-09-14
	 */
    public function sort(){
    	$model = D("EnglishObject");
    	$map = array();
    	$sortId = $this->_param('sortId');
    	if (!empty($sortId)) {
    		$map['id'] = array('in', $sortId);
    	} else {
    		$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
    		foreach ($params as &$value) {
    			$temp = explode("=", $value);
    			if ( !empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
    				$map[$temp[0]] = $temp[1];
    			}
    		}
    	}
    	$sortList = $model->where($map)->order('sort ASC')->select();
    	foreach ($sortList as $key=>$value) {
    		$sortList[$key]['txt_show'] = $value['name'];
    	}
    	$this->assign("sortList", $sortList);
    	$this->display("../Public/sort");
    	return;
    }

}

?>
