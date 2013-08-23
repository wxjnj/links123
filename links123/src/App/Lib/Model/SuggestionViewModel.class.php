<?php
/**
 * @name SuggestionViewModel
 * @desc 建立留言视图模型
 * @package Model
 * @author Frank UPDATE 2013-08-18
 */

class SuggestionViewModel extends ViewModel {

    public $viewFields = array(
    	'suggestion' => array('id', 'suggest', 'type', 'mid', 'pid', 'create_time', 'is_reply', 'status', '_type' => 'LEFT'),
        'member' => array('nickname', 'face', '_on' => 'suggestion.mid=member.id'),
    );

    /**
     * @name getSuggestion
     * @desc 获取留言列表
     * @param array condition
     * @param int rst
     * @param int listRows
     * @return array list
     */
    
    public function getSuggestion($condition, $rst, $listRows) {
    	
    	$list = $this->where($condition)->order('create_time DESC')->limit($rst . ',' . $listRows)->select();
    	$total = count($list);
    	foreach ($list as $key => &$value) {
    		$list[$key]['number'] = $total - $key;
    		$reply = $this->getSuggestionReplyList($value['id']);
    		!empty($reply) && $list[$key]['reply'] = $reply;
    		
    		$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
    			
    		if ($value['mid'] == -1) {
    			$list[$key]['nickname'] = "另客";
    		} else if ($value['mid'] == 0 || empty($value['nickname'])) {
    			$list[$key]['nickname'] = "游客";
    		} else if ($value['mid'] == $_SESSION[C('MEMBER_AUTH_KEY')]) {
    			$value['editable'] = "1";
    		}
    		empty($value['face']) && $value['face'] = "face.jpg";
    	}
    	return $list;
    }
    /**
     * @name getSuggestionReplyList
     * @desc 获取留言的回复/点评列表
     * @param int $id [留言的id]
     * @return array
     * @author Frank
     */
    public function getSuggestionReplyList($id) {
    	$list = $this->where("is_reply = 1 and pid = '%d'", $id)->order('create_time DESC')->select();
        if (!empty($list)) {
        	foreach ($list as $key => $value) {
        		$list[$key]['suggest'] = preg_replace("/<br\s\/>$/", "", $value['suggest']);
        		empty($value['face']) && $list[$key]['face'] = 'face.jpg';
        		$value['mid'] == -1 && $list[$key]['nickname'] = '另客';
	            ($value['mid'] == 0 || empty($value['nickname'])) && $list[$key]['nickname'] = '游客';
        	}
        }
        return $list;
    }

    /**
     * @name isTodayHasNewSuggestion
     * @desc 判断当天是否有新的留言
     * @return boolean
     */
    public function isTodayHasNewSuggestion() {
        $mod = D("Suggestion");
        $ret = $mod->where("TO_DAYS(FROM_UNIXTIME(create_time))-TO_DAYS(NOW())=0")->find();
        $flag = (false === $ret || empty($ret)) ? false: true;
        return $flag;
    }

}
?>