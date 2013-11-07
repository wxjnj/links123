<?php

/**
 * @name IllegalKeywordsAction.class.php
 * @package Admin
 * @desc 非法关健字管理模块
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class IllegalKeywordsAction extends CommonAction {

	protected function _filter(&$map, &$param){
		$keyword_name = $this->_param('keyword_name');
		$status = $this->_param('status');
		
		if (!empty($keyword_name) ) {
			$map['keyword_name'] = array('like',"%".$keyword_name."%");
			$this->assign("keyword_name", $keyword_name);
			$param['keyword_name'] = $keyword_name;
		}
		if (!empty($status)) {
			$map['status'] = $status;
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		} else {
			$map['status'] = array('egt', 0);
		}
	}
	
	/**
	 * @desc 列表
	 * @see IllegalKeywordsAction::index()
	 */
	public function index() {
		$map = array();
		$param = array();
		if (method_exists($this,'_filter')) {
			$this->_filter($map,$param);
		}
		$model = D("IllegalKeywords");
		if (!empty($model)) {
			$this->_list($model, $map, $param, 'id', true);
		}
		$this->display();
		return;
	}

	/**
	 * @desc 添加操作
	 * @see IllegalKeywordsAction::add()
	 */
	function add() {
		if ($this->isPost()){
			$model =M("IllegalKeywords");
			if (false === $model->create()) {
				$this->error($model->getError());
			}
	        $model->__set('create_time', time());
			$list = $model->add();
			if (false !== $list) {
				$this->assign('jumpUrl', cookie('_currentUrl_'));
				$this->success('新增成功!', __URL__);
			} else {
				$this->error('新增失败!');
			}
		}
		$this->display();
	}
	
	/**
	 * @desc 编辑页面
	 * @see IllegalKeywordsAction::edit()
	 */
	function edit() {
		$id = $this->_param("id");
		$model= M("IllegalKeywords");
		$vo=$model->getById($id);
		$vo["create_time"]=date('Y-m-d H:i:s', $vo["create_time"]);
		$this->assign('vo', $vo);
		$this->display();
	}

	
	/**
	 * @desc 编辑操作
	 * @see IllegalKeywordsAction::update()
	 */
	function update() {
		$model = D("IllegalKeywords");
		if ($this->isPost()){
			$id = $this->_post("id");
			$keyword_name = $this->_post("keyword_name");
			if ($model->where("id = %d", $id)->setField("keyword_name", $keyword_name)){
				$this->success('修改成功!',__URL__);
			}else {
				$this->error('修改失败!');
			}
		}
	}
	
	/**
	 * @desc 恢复记录
	 * @see IllegalKeywordsAction::resume()
	 */
	function resume() {
		$id = $this->_param("id");
		$model = D("IllegalKeywords");
		$condition = array('id'=>array('in', $id));
		if (false !== $model->where($condition)->setField('status', 1)) {
			$this->success('状态恢复成功！', cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}
}
?>