<?php
/**
 * @name SuggestionAction.class.php
 * @package Admin
 * @desc 后台管理-留言板
 * @author Lee UPDATE 2013-08-20
 * @version 1.0
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
		$suggest = $this->_param('suggest');
		$type = $this->_param('type');
		$status = $this->_param('status');
		
		if (!empty($suggest)) {
			$map['suggest'] = array('like', "%".$suggest."%");
			$this->assign("suggest", $suggest);
			$param['suggest'] = $suggest;
		}
        
		if (!empty($type)) {
			$map['type'] = $type;
			$this->assign('type', $type);
			$param['type'] = $type;
		}
        
        $map['status'] = array('egt', 0);
		if (!empty($status)) {
			$map['status'] = $status;
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
		$id = $this->_param('id');
		$model = M("Suggestion");
              
        if (empty($id)) {
           $this->error('参数不能为空!');
           exit(0); 
        }

		$vo = $model->getById($id);
        if (empty($vo)) {
            $this->error('参数错误!');
            exit(0);
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
		$status = $this->_param('status');
		$pid = $this->_param('pid');
		$suggest = $this->_param('suggest');
		
        $model = D("Suggestion");
        $data['status'] = !empty($status) ? $status : 0;
        
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false !== $model->where('id = '.$pid)->save($data)) {
            if (!empty($suggest)) {
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
		$id = $this->_param('id');
		$model = D("Suggestion");
		if (false !== $model->where('id = '.$id)->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}
}
?>