<?php

/**
 * @name MyareaAction.class.php
 * @package Admin
 * @desc 我的地盘
 * @author lawrence UPDATE 2013-08-20
 * @version 0.0.1
 */
class MyareaAction extends CommonAction {
	
    protected function _filter(&$map, &$param) {
        $web_name = $this->_param('web_name');
        $mid = $this->_param('mid');
        if (!empty($web_name)) {
            $map['web_name'] = array('like', '%' . $web_name . '%');
        }
        $this->assign('web_name', $web_name);
        $param['web_name'] = $web_name;
        if (isset($mid)) {
            if ($mid == "default") {
                $map['mid'] = 0;
            }
            $param['mid'] = "default";
        }
    }

	/**
	 * @desc 列表
	 * @see MyareaAction::index()
	 */
    public function index() {
        $map = array();
        $param = array();
        if (method_exists($this,'_filter')) {
            $this->_filter($map, $param);
        }
        $model = M("Myarea");
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'sort', false);
        }
        $this->assign('param', $param);
        $this->display();
        return;
    }

	/**
	 * @desc 添加页面
	 * @see MyareaAction::add()
	 */
    public function add() {
        $this->display();
        return;
    }

	/**
	 * @desc 添加操作
	 * @see MyareaAction::insert()
	 */
    public function insert() {
        $this->checkPost();
        // 创建数据对象
        $url = $this->_param('url');
        $model = D("Myarea");
        if ($model->where("mid = 0 and url = '%s'", $url)->find()) {
            $this->error('该链接已存在！');
        }
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 写入数据
        if (false !== $model->add()) {
            $this->success('我的地盘添加成功！');
        } else {
            Log::write('我的地盘添加失败：'.$model->getLastSql(),Log::SQL);
            $this->error('我的地盘添加失败！');
        }
    }

	/**
	 * @desc 安全验证
	 * @see MyareaAction::checkPost()
	 */
    protected function checkPost() {
        $_POST['web_name'] = htmlspecialchars(trim($_POST['web_name']));
        $_POST['url'] = str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
    }

	/**
	 * @desc 编辑页面
	 * @see MyareaAction::edit()
	 */
    function edit() {
    	$id = $this->_param('id');
        $model = M("Myarea");
        $vo = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
        return;
    }

	/**
	 * @desc 编辑操作
	 * @see MyareaAction::update()
	 */
    public function update() {
        $this->checkPost();
        $model = D("Myarea");
        $url = $this->_param('url');
        $id = $this->_param('id');
        if ($model->where("mid = 0 and url = '%s' and id != %d", $url, $id)->find()) {
            $this->error('该链接已存在！');
        }
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false !== $model->save()) {
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('我的地盘编辑成功!');
        } else {
            Log::write('我的地盘编辑失败：' .$model->getLastSql(),Log::SQL);
            $this->error('我的地盘编辑失败!');
        }
    }

	/**
	 * @desc 排序
	 * @see MyareaAction::sort()
	 */
    public function sort() {
    	$sortId = $this->_param('sortId');
    	
        $model = M("Myarea");
        $map = array();
        $map['status'] = 1;
        $map['mid'] = 0;
        if (!empty($_GET['sortId'])) {
            $map['id'] = array('in', $sortId);
        } else {
            $params =explode("&",$_SESSION[C('SEARCH_PARAMS_KEY')]);
            foreach ($params as &$value) {
                $temp = explode("=",$value);
                if (!empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order') {
                    $map[$temp[0]] = $temp[1];
                }
            }
        }
        $sortList = $model->where($map)->order('sort ASC, create_time ASC')->select();
        foreach ($sortList as &$value) {
            $value['txt_show'] = $value['web_name'] . "　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }
}

?>