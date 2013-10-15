<?php
/**
 * @name HomeMovieAction.class.php
 * @package Home
 * @desc 首页管理-推荐电影
 * @author Hiker UPDATE 2013-10-15
 * @version 0.0.1
 */

class HomeMovieAction extends CommonAction {

    /**
     * @desc 分组管理查询条件
     * @author Lee UPDATE 2013-09-05
     * @param array $map SQL条件数组
     * @param array $param 参数数组
     * @return array    
     */
    protected function _filter(&$map, &$param) {
        $name = $this->_param('name');
        if (!empty($name)) {
            $map['name'] = array('like', "%" . $name . "%");
        }
        $this->assign('name', $name);
        $param['name'] = $name;
    }

    /**
     * @desc 分组管理排序
     * @author Lee UPDATE 2013-09-05 
     */
    public function sort() {
        $sortId = $this->_param('sortId');
        $model = M("Group");
        $map = array();
        $map['status'] = 1;
        if (!empty($sortId)) {
            $map['id'] = array('in', $sortId);
        } else {
            $params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
            foreach ($params as &$value) {
                $temp = explode("=", $value);
                if (!empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order') {
                    $map[$temp[0]] = $temp[1];
                }
            }
        }
        $sortList = $model->where($map)->order('sort ASC')->select();
        foreach ($sortList as &$value) {
            $value['txt_show'] = $value['name'];
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
    }

}

?>
