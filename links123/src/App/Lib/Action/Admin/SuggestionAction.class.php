<?php
/**
 * @name SuggestionAction.class.php
 * @package Admin
 * @desc 后台管理-留言板
 * @author Lee UPDATE 2013-08-20
 * @version 0.0.1
 */

class SuggestionAction extends CommonAction {
    
	/**
	 * @desc 留言板查询条件
     * @author Lee UPDATE 2013-08-20
	 * @param array $map SQL条件数组
     * @param array $param 参数数组
	 * @return array    
	 */    
	protected function _filter(&$map, &$param){
		if (isset($_REQUEST['suggest']) && !empty($_REQUEST['suggest'])) {
			$map['suggest'] = array('like', "%".$_REQUEST['suggest']."%");
			$this->assign("suggest", $_REQUEST['suggest']);
			$param['suggest'] = $_REQUEST['suggest'];
		}
        
		if (isset($_REQUEST['type']) && $_REQUEST['type']!='') {
			$map['type'] = $_REQUEST['type'];
			$this->assign('type', $_REQUEST['type']);
			$param['type'] = $_REQUEST['type'];
		}
        
        $map['status'] = array('egt', 0);
		if (isset($_REQUEST['status']) && $_REQUEST['status']!='') {
			$map['status'] = $_REQUEST['status'];
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}
	}
	     
	/**
	 * @desc 留言板首页
     * @author Lee UPDATE 2013-08-20  
	 */      
	public function index(){
		$map = array();
		$param = array();
		if (method_exists($this, '_filter')) {
			$this->_filter($map, $param);
		}
        
		$model = new SuggestionViewModel();
		if (!empty($model)) {
			$this->_list($model, $map, $param, 'id', 'DESC');
		}
        
		$this->display();
	}
	
	/**
	 * @desc 留言板回复页面
     * @author Lee UPDATE 2013-08-20  
	 */
	function edit() {
		$model = M("Suggestion");
              
        if (empty($_REQUEST["id"])) {
           $this->error('参数不能为空!'); 
        }

		$vo = $model->getById($_REQUEST["id"]);
        if (empty($vo)) {
            $this->error('参数错误!'); 
        }
        
		$vo["create_time"] = date('Y-m-d H:i:s', $vo["create_time"]);
		$this->assign('vo', $vo);
        
		$this->display();
	}
	
	/**
	 * @desc 留言板回复保存
     * @author Lee UPDATE 2013-08-26 
	 */
	function update() {        
        $model = D("Suggestion");

        $data['status'] = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 0;
        
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false !== $model->where('id='.$_REQUEST['pid'])->save($data)) {
            if (!empty($_REQUEST['suggest'])) {
                $model->__set('is_reply', 1);
                $model->__set('status', 0);
                $model->__set('create_time', time());
                $model->__set('mid', -1);
                
                $list = $model->add();   
                if (false !== $list) {
                    $this->assign('jumpUrl',cookie('_currentUrl_'));
                    $this->success('回复成功!');
                } else {
                $this->error('回复失败!');
                }             
            } else {
            $this->error('回复失败!');
            }   
        } else {
            $this->error('回复失败!');
        }
	}
	     
	/**
	 * @desc 留言板恢复指定的一条记录
     * @author Lee UPDATE 2013-08-26 
	 */     
	function resume() {
		$model = D("Suggestion");
        
		if (false !== $model->where('id='.$_REQUEST['id'])->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}
}
?>