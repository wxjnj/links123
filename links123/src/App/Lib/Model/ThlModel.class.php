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
        //$thl_list = S("thl_list");
        
        //修改糖葫芦未能从缓存中取得，判断无效问题 @author slate date:2013-09-12
        if (!$thl_list['thl']) {
            $variable = M("Variable");
            $ret = $variable->getByVname('thl');
            $thl = array_flip(explode(",", $ret['value_varchar']));
//             foreach ($thl as $key => $value) {
//                 $thl_list[$key]['thl']      = $value;
//                 $thl_list[$key]['thlz']     = $this->where("thl='" . $value . "'")->order('sort')->select();
//                 $thl_list[$key]['thlz_len'] = count($thl_list[$key]['thlz']);
//             }

            //初步优化糖葫芦遍历sql查询 @author slate
            $result = $this->order('sort,id')->select();
            foreach ($result as $k => $v) {
            	$key = $thl[$v['thl']];
            	if (!$v['tip']) $v['tip'] = $v['name'];
            	
            	$thl_list[$key]['thl']      = $v['thl'];
                $thl_list[$key]['thlz'][]     = $v;
               
                if ( $v['sort'] > $thl_list[$key]['thlz_len']) {
                	$thl_list[$key]['thlz_len'] = $v['sort'];
                }
            }
            S("thl_list", $thl_list);
        }
        return $thl_list;
    }

}

?>
