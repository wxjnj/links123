<?php
// 糖葫芦籽
class ThlAction extends CommonAction {
	// 
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['name']) ) {
			$name = $_REQUEST['name'];
		}
		if ( !empty($name) ) {
			$map['name'] = array('like',"%".$name."%");
		}
		$this->assign('name', $name);
		$param['name'] = $name;
		//
		if ( isset($_REQUEST['thl']) ) {
			$thl = $_REQUEST['thl'];
		}
		if ( !empty($thl) ) {
			$map['thl'] = $thl;
		}
		$this->assign('thl', $thl);
		$param['thl'] = $thl;
		//
		if (isset($_REQUEST['needkey']) && $_REQUEST['needkey']!='') {
			$map['needkey'] = $_REQUEST['needkey'];
		}
		$this->assign('needkey', $map['needkey']);
		$param['needkey'] = $map['needkey'];
	}
	
	// 列表
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = array();
		$param = array();
		if ( method_exists ( $this, '_filter' ) ) {
			$this->_filter ( $map, $param );
		}
		$model = M("Thl");
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'sort', false );
			//echo $model->getLastSql();
		}
		//
		$this->getThl();
		//
		$this->display();
		return;
	}
	
	// 获取糖葫芦
	public function getThl() {
		$variable = M("Variable");
		$thl = $variable->getByVname('thl');
		$this->assign ( 'thls', explode(",", $thl['value_varchar']) );
	}
	
	//
	public function add() {
		//
		$model = M("Thl");
		$idnow = $model->max('id');
		$vo = $model->getById( $idnow );
		$this->assign( 'vo', $vo );
		//
		$this->getThl();
		//
		$this->display();
		return;
	}

	// 插入数据
	public function insert() {
		//
		$this->checkPost();
		// 创建数据对象
		$model = D("Thl");
		//
		if ( $model->where("url='".$_POST['url']."'")->find() ) {
			$this->error('该链接已存在！');
		}
		//
		if ( false === $model->create() ) {
			$this->error( $model->getError() );
		}
		// 写入数据
		if ( false !== $model->add() ) {
			$this->success('糖葫芦籽添加成功！');
		} 
		else {
			Log::write('糖葫芦籽添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('糖葫芦籽添加失败！');
		}
	}
	
	//
	protected function checkPost() {
		// 安全验证
        $_POST['name'] = htmlspecialchars(trim($_POST['name']));
        $_POST['url'] = str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
	}
	
	//
	function edit() {
		$model = M("Thl");
		$vo = $model->getById( $_REQUEST['id'] );
		$this->assign ( 'vo', $vo );
		//
		$this->getThl();
		//
		$this->display();
		return;
	}
    
	// 更新数据
	public function update() {
		//
        $this->checkPost();
		//
        $model = D("Thl");
        //
        if ( $model->where("url='".$_POST['url']."' and id!=".$_POST['id'])->find() ) {
        	$this->error('该链接已存在！');
        }
        //
		if ( false === $model->create () ) {
			$this->error( $model->getError() );
		}
		//
		if ( false !== $model->save() ) {
			$this->assign( 'jumpUrl', cookie('_currentUrl_') );
			$this->success('糖葫芦籽编辑成功!');
		} 
		else {
			Log::write('糖葫芦籽编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('糖葫芦籽编辑失败!');
		}
	}
	
	// 排序
	public function sort(){
		$model = M("Thl");
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
		$sortList = $model->where($map)->order('sort asc,create_time asc')->select();
		//echo $model->getLastSql();
		foreach ($sortList as &$value) {
			$value['txt_show'] = $value['name']."　　　　　";
		}
		$this->assign("sortList", $sortList);
		$this->display("../Public/sort");
		return;
	}


}
?>