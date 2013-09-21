<?php

/**
 * @name UserAction.class.php
 * @package Admin
 * @desc 糖葫芦籽
 * @author lawrence UPDATE 2013-08-20
 * @version 1.0
 */
class ThlAction extends CommonAction {

	protected function _filter(&$map, &$param){

		$name = $this->_param('name');
		$thl = $this->_param('thl');
		$needkey = $this->_param('needkey');
		if (!empty($name)) {
			$map['name'] = array('like',"%".$name."%");
		}
		
		$this->assign('name',$name);
		$param['name'] = $name;
		
		if (!empty($thl)) {
			$map['thl'] = $thl;
		}
		
		$this->assign('thl',$thl);
		$param['thl'] = $thl;
		
		if ($needkey != '') {
			$map['needkey'] = $needkey;
		}
		$this->assign('needkey', $map['needkey']);
		$param['needkey'] = $map['needkey'];
	}
	
	/**
	 * @desc 默认首页
	 * @see ThlAction::index()
	 */
	public function index() {
		$map = array();
		$param = array();
		if (method_exists($this,'_filter' )) {
			$this->_filter($map,$param);
		}
		$model = M("Thl");
		if (!empty($model )) {
			$this->_list ($model,$map,$param,'sort', false);
		}
		$this->getThl();
		$this->display();
		return;
	}
	
	/**
	 * @desc 获取糖葫芦
	 * @see ThlAction::getThl()
	 */
	public function getThl() {
		$variable = M("Variable");
		$thl = $variable->getByVname('thl');
		$this->assign ('thls',explode(",",$thl['value_varchar']) );
	}
	
	/**
	 * @desc 添加页面
	 * @see ThlAction::add()
	 */
	public function add() {
		$model = M("Thl");
		$idnow = $model->max('id');
		$vo = $model->getById($idnow);
		$this->assign('vo',$vo);
		$this->getThl();
		$this->display();
		return;
	}

	/**
	 * @desc 添加操作
	 * @see ThlAction::insert()
	 */
	public function insert() {
		$url = $this->_param('url');
		$this->checkPost();
		// 创建数据对象
		$model = D("Thl");
		if ( $model->where("url = %s", $url)->find()) {
			$this->error('该链接已存在！');
			exit(0);
		}
		if ( false === $model->create()) {
			$this->error( $model->getError());
			exit(0);
		}
		// 写入数据
		if ( false !== $model->add()) {
			$this->success('糖葫芦籽添加成功！');
			exit(0);
		} 
		else {
			Log::write('糖葫芦籽添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('糖葫芦籽添加失败！');
			exit(0);
		}
	}
	
	/**
	 * @desc 安全验证
	 * @see ThlAction::checkPost()
	 */
	protected function checkPost() {
        $_POST['name'] = htmlspecialchars(trim($_POST['name']));
        $_POST['url'] = str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
	}
	
	/**
	 * @desc 编辑页面
	 * @see ThlAction::edit()
	 */
	function edit() {
		$id = $this->_param('id');
		$model = M("Thl");
		$vo = $model->getById($id);
		$this->assign('vo', $vo);
		$this->getThl();
		$this->display();
		return;
	}
    
	/**
	 * @desc 编辑操作
	 * @see ThlAction::update()
	 */
	public function update() {
		$url = $this->_param('url');
		$id = $this->_param('id');
		
        $this->checkPost();
        $model = D("Thl");
        if ($model->where("url = '%s' and id != '%d'", $url, $id)->find()) {
        	$this->error('该链接已存在！');
        	exit(0);
        }
		if ( false === $model->create()) {
			$this->error($model->getError());
			exit(0);
		}
		if ( false !== $model->save()) {
			$this->assign('jumpUrl', cookie('_currentUrl_'));
			$this->success('糖葫芦籽编辑成功!');
			exit(0);
		} else {
			Log::write('糖葫芦籽编辑失败：'.$model->getLastSql(),Log::SQL);
			$this->error('糖葫芦籽编辑失败!');
			exit(0);
		}
	}
	
	/**
	 * @desc 排序
	 * @see ThlAction::sort()
	 */
	public function sort(){
		$model = M("Thl");
		$map['status'] = 1;
		$sortId = $this->_param('sortId');
		if (!empty($sortId)) {
			$map['id'] = array('in', $sortId);
		}
		else {
			$params = explode("&",$_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach ($params as &$value){
				$temp = explode("=",$value);
				if (!empty($temp[1]) && $temp[0]!= 'sort' && $temp[0]!= 'order') {
					$map[$temp[0]] = $temp[1];
				}
			}
		}
		$sortList = $model->where($map)->order('sort ASC,create_time ASC')->select();
		foreach ($sortList as &$value) {
			$value['txt_show'] = $value['name']."　　　　　";
		}
		$this->assign("sortList", $sortList);
		$this->display("../Public/sort");
		return;
	}
}
?>