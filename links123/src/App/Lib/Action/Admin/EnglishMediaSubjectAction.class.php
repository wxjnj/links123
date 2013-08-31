<?php

/**
 * 英语角媒体专题后台管理控制类
 *
 * @author Adam $date2013.08.26$
 */
class EnglishMediaSubjectAction extends CommonAction {

    public function add() {
        //
        //获取顶级专题列表
        $model = D($this->getActionName());
        $top_subject_list = $model->field("id,name")->where("pid=0 and status=1")->order("`sort` ASC")->select();

        $this->assign("top_subject_list", $top_subject_list);
        $this->display();
    }

    public function edit() {
        //
        //查询当前对象信息
        $name = $this->getActionName();
        $model = M($name);
        $id = $_REQUEST [$model->getPk()];
        $vo = $model->getById($id);
        //
        //获取顶级专题列表
        $top_subject_list = $model->field("id,name")->where("pid=0 and status=1")->order("`sort` ASC")->select();

        $this->assign("top_subject_list", $top_subject_list);
        $this->assign('vo', $vo);
        $this->display();
    }

}

?>
