<?php

/**
 * 英语角媒体TED分类后台管理控制类
 *
 * @author Adam $date2013.09.16$
 */
class EnglishMediaTedAction extends CommonAction {
    
    
    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        }
        if (!empty($name)) {
            $map['name'] = array('like', "%" . $name . "%");
        }
        $this->assign('name', $name);
        $param['name'] = $name;
        $this->assign("name", $name);
    }

    // 排序
    public function sort() {
        $model = D("EnglishMediaTed");
        $map = array();
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
        $sortList = $model->where($map)->order('sort asc')->select();
        foreach ($sortList as $key => $value) {
            $sortList[$key]['txt_show'] = $value['name'];
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }

}

?>
