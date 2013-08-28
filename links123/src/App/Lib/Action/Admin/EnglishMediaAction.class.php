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
        if (intval($_REQUEST['level']) > 0) {
            $map['englishMedia.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        if (isset($_REQUEST['status'])) {
            if (intval($_REQUEST['status']) > 0) {
                $map['englishMedia.status'] = intval($_REQUEST['status']);
                $param['status'] = intval($_REQUEST['status']);
            }
        }
        if (isset($_REQUEST['recommend'])) {
            $map['englishMedia.recommend'] = intval($_REQUEST['recommend']);
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
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);

        $this->display();
    }

    public function edit() {
        $name = $this->getActionName();
        $model = M($name);
        $id = intval($_REQUEST [$model->getPk()]);
        $vo = $model->getById($id);
        $option_list = D("EnglishOptions")->getQuestionOptionList($id);
        $this->assign('option_list', $option_list);
        $this->assign('vo', $vo);

        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);

        $this->display();
    }

    public function pointSubject() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $tartgetSubject = $_REQUEST['targetSubject'];
            if ($tartgetSubject > 0) {
                $map['id'] = array("in", $id);
                $data['subject'] = $tartgetSubject;
                $ret = D("EnglishMedia")->where($map)->save($data);
                if (false !== $ret) {
                    $this->ajaxReturn("", "操作成功", true);
                }
            }
            $this->ajaxReturn("", "操作失败", false);
        }
    }

}

?>
