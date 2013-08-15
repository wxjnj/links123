<?php
/**
 * @desc 建立留言视图模型
 * @author frank UPDATE 2013-08-15
 */

class SuggestionViewModel extends ViewModel {

    public $viewFields = array(
    	'suggestion' => array('id', 'suggest', 'type', 'mid', 'pid', 'create_time', 'is_reply', 'status', '_type' => 'LEFT'),
        'member' => array('nickname', 'face', '_on' => 'suggestion.mid=member.id'),
    );

    /**
     * @desc 获取留言的回复/点评列表
     * @author adam
     * @param int $id [留言的id]
     * @return array 
     */
    public function getSuggestionReplyList($id) {
    	$list = $this->where("is_reply=1 and pid=%d", $id)->order('create_time DESC')->select();
        
        if (true === $list && !empty($list)) {
        	foreach ($list as $key => $value) {
        		$list[$key]['suggest'] = preg_replace("/<br\s\/>$/", "", $value['suggest']);
	            if (empty($value['face'])) {
	                $list[$key]['face'] = "face.jpg";
	            }
	            if ($value['mid']==-1) {
	                $list[$key]['nickname'] = "另客";
	            }else if ($value['mid'] == 0 || empty($value['nickname'])) {
	                $list[$key]['nickname'] = "游客";
	            }
        	}
        } else {
        	$list = array(); 
        }
        
        return $list;
    }

    /**
     * @desc 判断当天是否有新的留言
     * @return boolean
     */
    public function isTodayHasNewSuggestion() {
        $mod = D("Suggestion");
        $ret = $mod->where("TO_DAYS(FROM_UNIXTIME(create_time))-TO_DAYS(NOW())=0")->find();
        $flag = false === $ret || empty($ret) ? false: true;
        return $flag;
    }

}
?>