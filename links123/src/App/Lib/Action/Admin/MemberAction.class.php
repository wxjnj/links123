<?php

/**
 * @name MemberAction.class.php
 * @package Admin
 * @desc 会员模块
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class MemberAction extends CommonAction {

	protected function _filter(&$map, &$param){
		
		$nickname = $this->_param('nickname');
		$status = $this->_param('status');
		if ( !empty($nickname) ) {
			$map['nickname'] = array('like', '%'.$nickname.'%');
			$this->assign('nickname', $nickname);
			$param['nickname'] = $nickname;
		}
		if (!empty($status)) {
			$map['status'] = $status;
		}else {
			$map['status'] = 1;
		}
		$this->assign('status', $map['status']);
		$param['status'] = $map['status'];
	}
	
	/**
	 * @desc 列表
	 * @see MemberAction::index()
	 */
	public function index() {
		$map=array();
		$param=array();
		if (method_exists($this,'_filter')) {
			$this->_filter($map, $param);
		}
		$model = M("Member");
		if (!empty($model)) {
			$this->_list($model, $map, $param, 'id', false);
		}
		$this->display();
	}

	// 插入数据
	/*
	public function insert() {
		// 创建数据对象
		$model = D("Member");
		$this->checkPost();
		import("@.ORG.String");
		$_POST['salt'] = String::rand_string();
		$_POST['password'] = md5(md5('123456').$_POST['salt']);
		//
		if( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		else {
			// 写入数据
			if( false !== $model->add() ) {
				$this->success('会员添加成功！');
			}else{
				$this->error('会员添加失败！');
			}
		}
	}
	*/
	
	/**
	 * @desc 安全验证
	 * @see MemberAction::checkPost()
	 */
	protected function checkPost() {
        $_POST['nickname']	= htmlspecialchars(trim($_POST['nickname']));
	}
	
	/**
	 * @desc 编辑页面
	 * @see MemberAction::edit()
	 */
	function edit() {
		$id = $this->_param('id');
		$model = M("Member");
		$vo = $model->getById($id);
		$this->assign('vo', $vo);
		$this->display();
	}
    
	/**
	 * @desc 编辑操作
	 * @see MemberAction::update()
	 */
	public function update() {
		$model = D("Member");
        $this->checkPost();
		if (false === $model->create()) {
			$this->error($model->getError());
			exit(0);
		}
		if (false !== $model->save()) {
			$this->assign('jumpUrl', __URL__.'/index?'.$_SESSION[C('SEARCH_PARAMS_KEY')]);
			$this->success('会员编辑成功!');
		} else {
			$this->error('会员编辑失败!');
		}
	}
	
	/**
	 * @desc 恢复记录
	 * @see MemberAction::resume()
	 */
	function resume() {
		$id = $this->_param('id');
		$model = D("Member");
		$condition = array('id' =>array('in',$id));
		if (false !== $model->where($condition)->setField('status', 1)) {
			$this->success('状态恢复成功！',cookie('_currentUrl_'));
		} else {
			$this->error('状态恢复失败！');
		}
	}
}
?>