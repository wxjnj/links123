<?php
/**
* 英语角Action
* author reasono
*/
class EnglishLevelnameAction extends CommonAction {

	public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = $_REQUEST['name'];
        }
        if (!empty($name)) {
            $map['name'] = array('like', "%" . $name . "%");
        }
        $this->assign('name', $name);
        $param['name'] = $name;
        
        $this->assign("param", $param);
    }

	//
    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $type = intval($_GET['type']) > 0 ? intval($_GET['type']) : 1;
        $map['level'] = $type;
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
        }
        $this->assign("type", $type);
        $this->display();
        return;
    }

	public function add() {
		$this->assign('type', $_GET['type']);
    	$this->display();
    	return;
	}

	public function edit() {
        $cate = D("EnglishLevelname")->getInfoById($_GET['id']);
		$this->assign('category', $cate);
		$this->assign('type', $_GET['type']);
    	$this->display();
    	return;
	}

	public function insert() {
		$model = D("EnglishLevelname");
		if (false === $model->create()) {
            $this->error($model->getError());
        }
        $model->created = time();
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
            $this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('新增失败!');
        }
	}

     // 排序
    public function sort(){
    	$model = D("EnglishLevelname");
    	$map = array();
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
    	foreach ($sortList as $key=>$value) {
    		$sortList[$key]['txt_show'] = $value['name'];
    	}
    	$this->assign("sortList", $sortList);
    	$this->display("../Public/sort");
    	return;
    }
}