<?php
/**
 * @desc 糖葫芦模型
 * @author frank UPDATE 2013-8-15
 *
 */
class ThlModel extends CommonModel {

    public $_validate = array(
        array('name', 'require', '名称必须'),
        array('url', 'require', '链接必须'),
    );
    public $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    
    /**
     * @desc 获取包含糖葫芦籽的糖葫芦列表
     * @author frank UPDATE 2013-08-15
     * @return array
     */
    public function getThlListWithThlz() {
        $thl_list = S("thl_list");
        if (empty($thl_list)) {
            $variable = M("Variable");
            $ret = $variable->getByVname('thl');
            $thl = explode(",", $ret['value_varchar']);
            foreach ($thl as $key => $value) {
                $thl_list[$key]['thl']      = $value;
                $thl_list[$key]['thlz']     = $this->where("thl='" . $value . "'")->order('sort')->select();
                $thl_list[$key]['thlz_len'] = count($thl_list[$key]['thlz']);
            }
            S("thl_list", $thl_list);
        }
        return $thl_list;
    }

}

?>
