<?php
class FriendLinkAction extends CommonAction {
	
    protected function _filter(&$map, &$param) {
        $web_name = $this->_param('web_name');
        if (!empty($web_name)) {
            $map['web_name'] = array('like', '%' . $web_name . '%');
        }
        $this->assign('web_name', $web_name);
        $param['web_name'] = $web_name;
    }

    public function index() {
        $map = array();
        $param = array();
        if (method_exists($this,'_filter')) {
            $this->_filter($map, $param);
        }
        $model = M("FriendLink");
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'sort', false);
        }
        $this->assign('param', $param);
        $this->display();
        return;
    }
    
    public function add() {
        $this->display();
        return;
    }

    public function insert() {
       
        // 创建数据对象
        $url = $this->_param('url');
        $model = D("FriendLink");
        if ($model->where("url = '%s'", $url)->find()) {
            $this->error('该链接已存在！');
        }
        
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        
        $model->created = $model->updated = time();
        // 写入数据
        if (false !== $model->add()) {
            $this->success('添加成功！');
        } else {
            Log::write('添加失败：'.$model->getLastSql(),Log::SQL);
            $this->error('添加失败！');
        }
    }

    protected function checkPost() {
        $_POST['web_name'] = htmlspecialchars(trim($_POST['web_name']));
        $_POST['url'] = str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
    }

    function edit() {
    	$id = $this->_param('id');
        $model = M("FriendLink");
        $vo = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
        return;
    }

    public function update() {
        
        $model = D("FriendLink");
        $url = $this->_param('url');
        $id = $this->_param('id');
        if ($model->where("url = '%s' and id != %d", $url, $id)->find()) {
            $this->error('该链接已存在！');
        }
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        
        $model->updated = time();
        if (false !== $model->save()) {
            $this->assign('jumpUrl', cookie('_currentUrl_'));
            $this->success('编辑成功!');
        } else {
            $this->error('编辑失败!');
        }
    }

    public function sort() {
    	$sortId = $this->_param('sortId');
    	
        $model = M("FriendLink");
        $map = array();
        $map['status'] = 0;
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
        $sortList = $model->where($map)->order('sort ASC, created ASC')->select();
        foreach ($sortList as &$value) {
            $value['txt_show'] = $value['web_name'] . "　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }
}

?>