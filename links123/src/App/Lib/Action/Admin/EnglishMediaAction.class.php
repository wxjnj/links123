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
        if (intval($_REQUEST['voice']) > 0) {
            $map['englishMedia.voice'] = intval($_REQUEST['voice']);
            $param['voice'] = intval($_REQUEST['voice']);
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
            $key['englishMedia.id'] = intval($name);
            $key['englishMedia.name'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $key['englishMedia.play_code'] = array('like', "%" . $name . "%");
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
        $model->startTrans();
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
        if (intval($_REQUEST['special_recommend']) == 1) {
            $_REQUEST['recommend'] = 1;
        }
        if (intval($_REQUEST['recommend']) == 1) {
            $recommendId = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($_REQUEST['object'], $_REQUEST['subject']);
            if (!empty($recommendId)) {
                $model->recommend = $recommendId;
            }
        }
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
            $model->commit();
            $this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function edit() {
        $model = D("EnglishMedia");
        $id = intval($_REQUEST["id"]);
        $vo = $model->find($id);
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
        $model->startTrans();
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
        if (!empty($_FILES['img']['name'])) {
            import("@.ORG.UploadFile");
            $upload = new UploadFile();
            $upload->maxSize = 11000000; // 设置附件上传大小
            $upload->allowExts = array('jpeg', 'jpg', 'png', 'gif'); // 设置附件上传类型
            $upload->saveRule = time();
            $dir_name = date("Ym");
            $upload->savePath = C("ENGLISH_MEDIA_IMG_PATH") . "/" . $dir_name . "/"; // 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
                $model->media_thumb_img = $dir_name . "/" . $info[0]['savename'];
            }
        }
        if (intval($_REQUEST['special_recommend']) == 1) {
            $_REQUEST['recommend'] = 1;
        }
        if (intval($_REQUEST['recommend']) == 1) {
            $recommendId = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($_REQUEST['object'], $_REQUEST['subject']);
            if (false == $recommendId) {
                //错误提示
                $model->rollback();
                $this->error('编辑失败!');
            }
            if (intval($recommendId) > 0) {
                $model->recommend = $recommendId;
            }
        }
        // 更新数据
        $list = $model->save();
        if (false !== $list) {
            $model->commit();
            //成功提示
            $this->success('编辑成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //错误提示
            $this->error('编辑失败!');
        }
    }

    public function pointSubject() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $tartget = intval($_REQUEST['target']);
            if ($tartget >= 0) {
                $map['id'] = array("in", $id);
                $model = D("EnglishMedia");
                $model->startTrans();
                $info = $model->field("recommend,object,subject")->where($map)->find();
                if ($info['recommend'] > 0) {
                    $recommendId = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($info['object'], $tartget);
                    if (intval($recommendId) > 0) {
                        $data['recommend'] = intval($recommendId);
                    }
                }
                $data['subject'] = intval($tartget);
                $ret = $model->where($map)->save($data);
                if (false !== $ret) {
                    $model->commit();
                    $this->ajaxReturn($tartget, "操作成功", true);
                }
            }
            $model->rollback();
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
            //指定的专题
            if (intval($_REQUEST['targetSubject']) >= 0) {
                $data['subject'] = intval($_REQUEST['targetSubject']);
            }
            //指定特别推荐
            if (isset($_REQUEST['targetSpecialRecommend'])) {
                $data['special_recommend'] = intval($_REQUEST['targetSpecialRecommend']);
                if ($data['special_recommend'] == 1) {
                    $data['recommend'] = 1;
                }
            }
            if (isset($_REQUEST['targetRecommend'])) {
                $data['recommend'] = intval($_REQUEST['targetRecommend']);
                if ($data['recommend'] == 0) {
                    $data['special_recommend'] = 0;
                }
            }
            $englishMediaModel = D("EnglishMedia");
            $englishMediaRecommendModel = D("EnglishMediaRecommend");
            $map['id'] = array("in", $id);
            $data['updated'] = time();
            $medias_info = $englishMediaModel->field("id,recommend,special_recommend,object,subject")->where($map)->select();
            if (!empty($data) && !empty($medias_info)) {
                foreach ($medias_info as $value) {
                    $data['id'] = $value['id'];
                    $englishMediaModel->startTrans();
                    if ($data['subject'] > 0) {
                        $value['subject'] = $data['subject'];
                    }
                    if ($data['recommend'] > 0 || (!isset($data['recommend']) && $value['recommend'] > 0)) {
                        $data['recommend'] = $englishMediaRecommendModel->getRecommendIdByObjectOrSubject($value['object'], $value['subject']);
                    }
                    $ret = $englishMediaModel->save($data);
                    if (false === $ret) {
                        $englishMediaModel->rollback();
                        $this->ajaxReturn("", "操作失败", false);
                    }
                }
                $englishMediaModel->commit();
                $this->ajaxReturn("", "操作成功", true);
            } else {
                $englishMediaModel->rollback();
                $this->ajaxReturn("", "操作对象或记录不存在", false);
            }
        }
    }

    /**
     * 设置特别推荐
     * @author Adam $date2013.08.30$
     */
    public function setSpecialRecommend() {
        if ($this->isAjax()) {
            $model = D("EnglishMedia");
            $data = array();
            $data['id'] = $_REQUEST['id'];
            $info = $model->field("special_recommend,recommend,subject,object")->find($data['id']);
            if (empty($info)) {
                $this->ajaxReturn("", "操作记录不存在", false);
            }
            if (intval($info['special_recommend']) == 0) {
                //如果不是推荐，自动设置为推荐
                if (intval($info['recommend']) == 0) {
                    $model->startTrans();
                    $data['recommend'] = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($info['object'], $info['subject']);
                }
                $data['special_recommend'] = 1;
            } else {
                $data['special_recommend'] = 0;
            }
            $ret = $model->save($data);
            if (false === $ret) {
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $model->commit();
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
            $englishMediaModel = D("EnglishMedia");
            $model = D("EnglishMedia");
            $data = array();
            $data['id'] = $_REQUEST['id'];
            $info = $model->field("special_recommend,recommend,subject,object")->find($data['id']);
            
            if (empty($info)) {
                $this->ajaxReturn("", "操作记录不存在", false);
            }
            if (intval($info['recommend']) == 0) {
                $data['recommend'] = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($info['object'], $info['subject']);
            }else{
                $data['special_recommend'] = 0;
                $data['recommend'] = 0;
            }
            $ret = $model->save($data);
            if (false === $ret) {
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $model->commit();
                $this->ajaxReturn($data['special_recommend'], "操作成功", true);
            }
        }
    }

}

?>
