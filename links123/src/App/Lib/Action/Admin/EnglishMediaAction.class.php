<?php

/**
 * 英语角媒体管理控制类
 *
 * @author Adam $date2013.08.26$
 */
class EnglishMediaAction extends CommonAction {

    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        }
        if (intval($_REQUEST['pattern']) > 0) {
            $map['englishMedia.pattern'] = intval($_REQUEST['pattern']);
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        if (intval($_REQUEST['object']) > 0) {
            $map['englishMedia.object'] = intval($_REQUEST['object']);
            $object_info = D("EnglishObject")->find($map['englishMedia.object']);
            if ($object_info['name'] == "综合") {
                unset($map['englishMedia.object']);
            }
            $param['object'] = intval($_REQUEST['object']);
        }
        if (intval($_REQUEST['subject']) > 0) {
            $map['englishMedia.subject'] = intval($_REQUEST['subject']);
            $param['subject'] = intval($_REQUEST['subject']);
        }
        if (intval($_REQUEST['difficulty']) > 0) {
            $map['englishMedia.difficulty'] = intval($_REQUEST['difficulty']);
            $param['difficulty'] = intval($_REQUEST['difficulty']);
        }
        if (intval($_REQUEST['level']) > 0) {
            $map['englishMedia.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        if (isset($_REQUEST['status'])) {
            $map['englishMedia.status'] = intval($_REQUEST['status']);
            $param['status'] = intval($_REQUEST['status']);
        }
        if (isset($_REQUEST['recommend'])) {
            if (intval($_REQUEST['recommend']) == 0) {
                $map['englishMedia.recommend'] = 0;
            } else {
                $map['englishMedia.recommend'] = array("neq", 0);
            }
            $param['recommend'] = intval($_REQUEST['recommend']);
        }
        if (isset($_REQUEST['special_recommend'])) {
            $map['englishMedia.special_recommend'] = intval($_REQUEST['special_recommend']);
            $param['special_recommend'] = intval($_REQUEST['special_recommend']);
        }
        if (isset($_REQUEST['created']) && strtotime($_REQUEST['created'])) {
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(englishMedia.created),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
            $param['created'] = $_REQUEST['created'];
        }
        if (!empty($name)) {
            $key['englishMedia.name'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $key['englishMedia.path'] = array('like', "%" . $name . "%");
            $key['_logic'] = 'or';
        }
        if (!empty($key)) {
            $map['_complex'] = $key;
        }
        $this->assign('name', $name);
        $param['name'] = $name;
        $this->assign("name", $name);
    }

    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $model = new EnglishMediaViewModel();
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }
        //
        //科目列表
        $object_list = D("EnglishObject")->getList("status=1", "`sort` ASC");
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->getList("status=1", "`sort` ASC");
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->getList("status=1", "`sort` ASC");
        $this->assign("subject_list", $subject_list);
        //推荐列表
        $recommend_list = D("EnglishMediaRecommend")->getList("status=1", "`sort` ASC");
        $this->assign("recommend_list", $recommend_list);
        //
        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }

    public function foreverdelete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $ids = explode(',', $id);
                $model->startTrans();
                $condition = array();
                foreach ($ids as $key => $value) {
                    $condition[$pk] = $value;
                    $path = "./Public/Uploads/Video/" . $model->where($condition)->getField("path");
                    if (false !== $model->where($condition)->delete()) {
                        @unlink($path);
                    } else {
                        $model->rollback();
                        $this->error("删除失败！");
                    }
                }
                $model->commit();
                $this->success('删除成功！', cookie('_currentUrl_'));
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function add() {
        //科目列表
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);
        //推荐分类列表
        $recommend_list = D("EnglishMediaRecommend")->where("`status`=1")->order("`sort`")->select();
        $this->assign("recommend_list", $recommend_list);

        $this->display();
    }

    public function insert() {
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $levels = D("EnglishLevel")->order("`sort` ASC")->select();
        foreach ($levels as $key => $value) {
            $level_list[$value['id']] = $value;
            $level_name_list_info[$value['name']] = $value;
        }
        if ($level_list[intval($_REQUEST['level'])]['sort'] <= $level_name_list_info['小六']['sort']) {
            $model->difficulty = 1;
        } else if ($level_list[intval($_REQUEST['level'])]['sort'] >= $level_name_list_info['大一']['sort']) {
            $model->difficulty = 3;
        } else {
            $model->difficulty = 2;
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

    public function edit() {
        $name = $this->getActionName();
        $model = M($name);
        $id = intval($_REQUEST [$model->getPk()]);
        $vo = $model->getById($id);
        $option_list = D("EnglishOptions")->getQuestionOptionList($id);
        $this->assign('option_list', $option_list);
        $this->assign('vo', $vo);

        //科目列表
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);
        //推荐分类列表
        $recommend_list = D("EnglishMediaRecommend")->where("`status`=1")->order("`sort`")->select();
        $this->assign("recommend_list", $recommend_list);

        $this->display();
    }

    public function update() {
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $levels = D("EnglishLevel")->order("`sort` ASC")->select();
        foreach ($levels as $key => $value) {
            $level_list[$value['id']] = $value;
            $level_name_list_info[$value['name']] = $value;
        }
        if ($level_list[intval($_REQUEST['level'])]['sort'] <= $level_name_list_info['小六']['sort']) {
            $model->difficulty = 1;
        } else if ($level_list[intval($_REQUEST['level'])]['sort'] >= $level_name_list_info['大一']['sort']) {
            $model->difficulty = 3;
        } else {
            $model->difficulty = 2;
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

    public function pointSubject() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $tartget = $_REQUEST['target'];
            if (intval($tartget) > 0) {
                $map['id'] = array("in", $id);
                $data['subject'] = intval($tartget);
                $ret = D("EnglishMedia")->where($map)->save($data);
                if (false !== $ret) {
                    $this->ajaxReturn($tartget, "操作成功", true);
                }
            }
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    public function pointRecommend() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $tartget = $_REQUEST['target'];
            if (intval($tartget) > 0) {
                $map['id'] = array("in", $id);
                $data['recommend'] = intval($tartget);
                $ret = D("EnglishMedia")->where($map)->save($data);
                if (false !== $ret) {
                    $this->ajaxReturn($tartget, "操作成功", true);
                }
            }
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    /**
     * 批量设置
     * @return
     * @author  Adam $date2013.08.30$
     */
    public function groupSet() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            if (intval($_REQUEST['targetSubject']) > 0) {
                $data['subject'] = intval($_REQUEST['targetSubject']);
            }
            if (isset($_REQUEST['targetSpecialRecommend'])) {
                $data['special_recommend'] = intval($_REQUEST['targetSpecialRecommend']);
            }
            if (!empty($data)) {
                $map['id'] = array("in", $id);
                $ret = D("EnglishMedia")->where($map)->save($data);
                if (false !== $ret) {
                    $this->ajaxReturn($data, "操作成功", true);
                }
            } else {
                $this->ajaxReturn("", "请选择目标", false);
            }
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    /**
     * 设置特别推荐
     * @author Adam $date2013.08.30$
     */
    public function setSpecialRecommend() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $model = D("EnglishMedia");
            $data = array();
            $ret = $model->field("special_recommend,recommend,object,subject")->find($id);
            if (false == $ret) {
                $this->ajaxReturn("", "操作失败", false);
            }
            $special_recommend = intval($ret['special_recommend']);
            if ($special_recommend == 0) {
                //如果不是推荐，自动设置为推荐
                if (intval($ret['recommend']) == 0) {
                    $ret = $model->setRecommend(intval($id), intval($ret['object']), intval($ret['subject']));
                    if (false === $ret) {
                        $this->ajaxReturn("", "操作失败", false);
                    }
                    $data['recommend'] = $id;
                    $data['object'] = $ret['object'];
                    $data['subject'] = $ret['subject'];
                }
                $special_recommend = 1;
            } else {
                $special_recommend = 0;
            }
            if (false === $model->where(array("id" => $id))->setField("special_recommend", $special_recommend)) {
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $data['special_recommend'] = $special_recommend;
                $this->ajaxReturn($data, "操作成功", true);
            }
        }
    }

    /**
     * 设置媒体优先播放类型
     * @author Adam $date2013.09.1$
     */
    public function setPriorityType() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $model = D("EnglishMedia");
            $priority_type = $model->where(array("id" => $id))->getField("priority_type");
            if (intval($priority_type) == 1) {
                $priority_type = 2;
            } else {
                $priority_type = 1;
            }
            if (false === $model->where(array("id" => $id))->setField("priority_type", $priority_type)) {
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $this->ajaxReturn($priority_type, "操作成功", true);
            }
        }
    }

    /**
     * 设置推荐
     * @author Adam $date2013.08.31$
     */
    public function setRecommend() {
        if ($this->isAjax()) {
            $ret = D("EnglishMedia")->setRecommend(intval($_REQUEST['id']), intval($_REQUEST['object']), intval($_REQUEST['subject']));
            if (false === $ret) {
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $this->ajaxReturn($ret, "操作成功", true);
            }
        }
    }

}

?>
