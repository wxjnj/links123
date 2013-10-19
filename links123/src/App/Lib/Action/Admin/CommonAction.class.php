<?php
/**
 * @desc Admin组公共类
 * @name CommonAction
 * @version V1。0
 * @author Frank UPDATE 2013-09-04
 */
class CommonAction extends BaseAction {

    function _initialize() {
        parent::_initialize();
        import('@.ORG.Cookie');
        //根据cookie检查session是否过期
        $time = cookie(md5("manament_login_time"));
        //@@@ 临时绕过超时验证
        $time = 1;
        if (empty($time)) {
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
            unset($_SESSION['menu' . $_SESSION[C('USER_AUTH_KEY')]]);
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION['_ACCESS_LIST']);
        }
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('@.ORG.RBAC');
            if (!RBAC::AccessDecision()) {
                //检查认证识别号
                if (!$_SESSION[C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
    }

    //
    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }
        $this->display();
        return;
    }

    /**
      +----------------------------------------------------------
     * 取得操作成功后要返回的URL地址
     * 默认返回当前模块的默认操作
     * 可以在action控制器中重载
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    function getReturnUrl() {
        return __URL__ . '?' . C('VAR_MODULE') . '=' . MODULE_NAME . '&' . C('VAR_ACTION') . '=' . C('DEFAULT_ACTION');
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _list($model, $map, $param, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        $param['order'] = $order;
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'desc' : 'asc';
        }
        $param['sort'] = $sort;
//        dump($param);
        //取得满足条件的记录数
        if ($model->getModelName() == 'NewsView') {
            $count = $model->where($map)->count('news.id');
        } elseif ($model->getModelName() == 'ProductView') {
            $count = $model->where($map)->count('product.id');
        } elseif ($model->getModelName() == 'CasesView') {
            $count = $model->where($map)->count('cases.id');
        } elseif ($model->getModelName() == 'CategoryView') {
            $count = $model->where($map)->count('cat1.id');
        } elseif ($model->getModelName() == 'LinksView') {
            $count = $model->where($map)->count('links.id');
        } elseif ($model->getModelName() == 'AnnouncementView') {
            $count = $model->where($map)->count('announcement.id');
        } elseif ($model->getModelName() == 'SuggestionView') {
            $count = $model->where($map)->count('suggestion.id');
        } elseif ($model->getModelName() == 'CatPicView') {
            $count = $model->where($map)->count('catPic.id');
        } elseif ($model->getModelName() == 'EnglishQuestionView') {
            $count = $model->where($map)->count('englishQuestion.id');
        }elseif ($model->getModelName() == 'EnglishQuestionSpeakView') {
            $count = $model->where($map)->count('englishQuestionSpeak.id');
        } elseif ($model->getModelName() == 'EnglishMediaView') {
            $count = $model->where($map)->count('englishMedia.id');
        } else {
            $count = $model->where($map)->count('id');
        }
        //echo $model->getlastsql()."<br />";
        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '20';
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
//            echo $model->getlastsql();
            //分页跳转的时候保证查询条件
            foreach ($param as $key => $val) {
                //$p->parameter .= "$key=" . urlencode ( $val ) . "&";
                $p->parameter .= "$key=" . $val . "&";
            }
            $this->assign('param', $p->parameter);
            $_SESSION[C('SEARCH_PARAMS_KEY')] = $p->parameter . "p=" . $_REQUEST['p'];
            //分页显示
            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
        }
        cookie('_currentUrl_', __URL__ . '/index?' . $_SESSION[C('SEARCH_PARAMS_KEY')]);
        return;
    }

    function insert() {
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
            $this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }

    function read() {
        $this->edit();
    }

    public function add() {
        $this->display();
    }

    function edit() {
        $name = $this->getActionName();
        $model = M($name);
        $id = $_REQUEST [$model->getPk()];
        $vo = $model->getById($id);
        $this->assign('vo', $vo);
        $this->display();
        return;
    }

    function update() {
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $list = $model->save();
        if (false !== $list) {
            //成功提示
            $this->success('编辑成功!', cookie('_currentUrl_'));
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }

    /**
      +----------------------------------------------------------
     * 默认删除操作
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    public function delete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', - 1);
                if ($list !== false) {
                    $this->success('删除成功！', cookie('_currentUrl_'));
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function foreverdelete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                if (false !== $model->where($condition)->delete()) {
                    $this->success('删除成功！', cookie('_currentUrl_'));
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function clear() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            if (false !== $model->where('status=1')->delete()) {
                $this->success(L('_DELETE_SUCCESS_'), $this->getReturnUrl());
            } else {
                $this->error(L('_DELETE_FAIL_'));
            }
        }
    }

    /**
      +----------------------------------------------------------
     * 默认禁用操作
     *
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    public function forbid() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            $this->success('状态禁用成功', $this->getReturnUrl());
        } else {
            $this->error('状态禁用失败！');
        }
    }

    public function checkPass() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->checkPass($condition)) {
            $this->success('状态批准成功！', $this->getReturnUrl());
        } else {
            $this->error('状态批准失败！');
        }
    }

    public function recycle() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->recycle($condition)) {
            $this->success('状态还原成功！', $this->getReturnUrl());
        } else {
            $this->error('状态还原失败！');
        }
    }

    /**
      +----------------------------------------------------------
     * 默认恢复操作
     *
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    function resume() {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->resume($condition)) {
            $this->success('状态恢复成功！', $this->getReturnUrl());
        } else {
            $this->error('状态恢复失败！');
        }
    }

    /**
     * @name saveSort
     * @desc 保存排序
     * @param string seqNoList
     * @author Frank UPDATE 2013-08-21
     */
    function saveSort() {
        $seqNoList = $_POST ['seqNoList'];
        if (!empty($seqNoList)) {
            //更新数据对象
            $name = $this->getActionName();
            $model = D($name);
            $col = explode(',', $seqNoList);
            //启动事务
            $model->startTrans();
            $result = true;
            foreach ($col as $val) {
                $val = explode(':', $val);
                $sort = $model->where("id = '%s'", $val[0])->getField('sort');
                if ($sort == $val[1]) {
                    continue;
                }
                $model->id = $val[0];
                $model->sort = $val[1];
                $temp_result = $model->save();
                if (!$temp_result) {
                    $result = false;
                    Log::write('保存排序失败：' . $model->getLastSql(), Log::SQL);
                }
            }

            if ($result) {
                $model->commit();
                //采用普通方式跳转刷新页面
                $this->success('更新成功');
            } else {
                // 回滚事务
                $model->rollback();
                $this->error($model->getError());
            }
        }
    }

    /**
     * @name getCats
     * @desc 获取所有目录
     * @author Frank UPDATE 2013-08-21
     */
    protected function getCats($flag = 0) {
        $condition['status'] = 1;
        $flag > 0 && $condition['flag'] = $flag;

        $cats = M("Category")->field('id, cat_name, level')->where($condition)->order('path ASC, sort ASC')->select();
        foreach ($cats as &$value) {
            for ($i = 0; $i != $value['level']; ++$i) {
                $value['cat_name'] = '　' . $value['cat_name'];
            }
        }
        $this->assign("cats", $cats);
    }

    /**
     * @name _getSubCats
     * @desc 获取所有下级目录
     * @param int pid
     * @return array
     * @author Frank UPDATE 2013-08-21
     */
    protected function _getSubCats($pid) {
        $pids = array();
        array_push($pids, $pid);
        $cat = M("Category");
        $list = $cat->field('id')->where("status = 1 and prt_id = '%d'", $pid)->select();
        if (count($list) > 0) {
            foreach ($list as &$value) {
                $pids = array_merge($pids, $this->_getSubCats($value['id']));
            }
        }
        return $pids;
    }

    /**
     * @name getRoot
     * @desc 获取根目录
     * @param int cid
     * @return array
     * @author Frank UPDATE 2013-08-21
     */
    protected function getRoot($cid) {
        $cat = M("Category");
        $catNow = $cat->getById($cid);
        if ($catNow['prt_id'] == 0) {
            return $catNow['id'];
        } else {
            return $this->getRoot($catNow['prt_id']);
        }
    }

    /**
     * @name getRootCats
     * @desc 获取所有的根目录
     * @author Frank UPDATE 2013-08-21
     */
    protected function getRootCats() {
        $cats = M("Category")->field('id, cat_name, level')->where('status=1 and level=1')->order('sort ASC')->select();
        foreach ($cats as &$value) {
            for ($i = 0; $i != $value['level']; ++$i) {
                $value['cat_name'] = '　' . $value['cat_name'];
            }
        }
        $this->assign("cats", $cats);
    }

}