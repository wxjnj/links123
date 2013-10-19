<?php
/**
* 英语角Action
* author reasono
*/
import("App.Logic.Admin.EnglishLevelnameLogic");
class EnglishLevelnameAction extends CommonAction {
	protected $cEnglishLevelnameLogic = null;

	public function _initialize() {
		$this->cEnglishLevelnameLogic = new EnglishLevelnameLogic();
		parent::_initialize();
	}

	protected function _filter(&$map, &$param) {

	}

	public function index() {
		$type  = isset($_REQUEST["type"])  ? intval($_REQUEST["type"])  : 1; 
		$title = isset($_REQUEST["title"]) ? $_REQUEST["title"] : ""; 

		$category = $this->cEnglishLevelnameLogic->getCategoryLevelListBy($type, $title);

		$this->assign('category', $category);
		$this->assign('type', $type);
		$this->assign('title', $title);
		$this->display();
    	return;
	}

	public function add() {
		$this->assign('type', $_GET['type']);
    	$this->display();
    	return;
	}

	public function edit() {
		$cate = $this->cEnglishLevelnameLogic->getCategoryInfoById($_GET['id']);
		$this->assign('category', $cate[0]);
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

	public function forbid() {
		$ret = $this->cEnglishLevelnameLogic->forbidCategoryById($_GET['id']);
		if ($ret !== false) {
            $this->success('状态禁用成功', __URL__ . "/index/type/" . $_GET['type']);
        } else {
            $this->error('状态禁用失败！');
        }
	}

	public function resume() {
		$ret = $this->cEnglishLevelnameLogic->resumeCategoryById($_GET['id']);
		if ($ret !== false) {
            $this->success('状态禁用成功', __URL__ . "/index/type/" . $_GET['type']);
        } else {
            $this->error('状态禁用失败！');
        }
	}

	public function update() {
		$category["id"]     = intval($_REQUEST["id"]);
		$category["name"]   = $_REQUEST["name"];
		$category["level"]  = $_REQUEST["level"];
		$category["sort"]   = $_REQUEST["sort"];
		$category["status"] = $_REQUEST["status"];
		$ret = $this->cEnglishLevelnameLogic->updateCatetory($category);
		if ($ret !== false) {
            $this->success('更新成功', __URL__ . "/index/type/" . $_REQUEST['type']);
        } else {
            $this->error('更新失败！');
        }
	}

	public function foreverdelete() {
		$ret = $this->cEnglishLevelnameLogic->deleteCategory($_REQUEST["id"]);
		if ($ret !== false) {
            $this->success('删除成功', __URL__ . "/index/type/" . $_REQUEST['type']);
        } else {
            $this->error('删除失败！');
        }
	}
}