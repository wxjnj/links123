<?php

// 我的地盘
class MyareaAction extends CommonAction {

    // 
    protected function _filter(&$map, &$param) {
        //
        if (isset($_REQUEST['web_name'])) {
            $web_name = $_REQUEST['web_name'];
        }
        if (!empty($web_name)) {
            $map['web_name'] = array('like', "%" . $web_name . "%");
        }
        $this->assign('web_name', $web_name);
        $param['web_name'] = $web_name;
        if (isset($_REQUEST['mid'])) {
            if ($_REQUEST['mid'] == "default") {
                $map['mid'] = 0;
            }
            $param['mid'] = "default";
        }
        //
//		$map['mid'] = 0;
    }

    // 列表
    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $model = M("Myarea");
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'sort', false);
            //echo $model->getLastSql();
        }
        $this->assign('param', $param);
        //
        $this->display();
        return;
    }

    //
    public function add() {
        //
        $this->display();
        return;
    }

    // 插入数据
    public function insert() {
        //
        $this->checkPost();
        // 创建数据对象
        $model = D("Myarea");
        //
        if ($model->where("mid=0 and url='" . $_POST['url'] . "'")->find()) {
            $this->error('该链接已存在！');
        }
        //
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 写入数据
        if (false !== $model->add()) {
            $this->success('我的地盘添加成功！');
        } else {
            Log::write('我的地盘添加失败：' . $model->getLastSql(), Log::SQL);
            $this->error('我的地盘添加失败！');
        }
    }

    //
    protected function checkPost() {
        // 安全验证
        $_POST['web_name'] = htmlspecialchars(trim($_POST['web_name']));
        $_POST['url'] = str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
    }

    //
    function edit() {
        $model = M("Myarea");
        $vo = $model->getById($_REQUEST['id']);
        $this->assign('vo', $vo);
        //
        $this->display();
        return;
    }

    // 更新数据
    public function update() {
        //
        $this->checkPost();
        //
        $model = D("Myarea");
        //
        if ($model->where("mid=0 and url='" . $_POST['url'] . "' and id!=" . $_POST['id'])->find()) {
            $this->error('该链接已存在！');
        }
        //
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //
        if (false !== $model->save()) {
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('我的地盘编辑成功!');
        } else {
            Log::write('我的地盘编辑失败：' . $model->getLastSql(), Log::SQL);
            $this->error('我的地盘编辑失败!');
        }
    }

    // 排序
    public function sort() {
        $model = M("Myarea");
        $map = array();
        $map['status'] = 1;
        $map['mid'] = 0;
        if (!empty($_GET['sortId'])) {
            $map['id'] = array('in', $_GET['sortId']);
        } else {
            $params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
            foreach ($params as &$value) {
                $temp = explode("=", $value);
                if (!empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order') {
                    $map[$temp[0]] = $temp[1];
                }
            }
        }
        $sortList = $model->where($map)->order('sort asc,create_time asc')->select();
        //echo $model->getLastSql();
        foreach ($sortList as &$value) {
            $value['txt_show'] = $value['web_name'] . "　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }

}

?>