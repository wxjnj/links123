<?php
/**
 * @name SuggestionViewModel
 * @desc 建立留言视图模型
 * @package Model
 * @author Frank UPDATE 2013-08-18
 */

class SuggestionViewModel extends ViewModel {

    public $viewFields = array(
    	'suggestion' => array('id', 'suggest', 'type', 'mid','nickname', 'pid', 'create_time', 'is_reply', 'status')
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
		$userService = D('User','Service');
		$guest_info = $userService->getUserInfo(0);
		$user_id = array();
    	foreach ($list as $key => &$value) {
    		$list[$key]['number'] = $total - $key;
    		$reply = $this->getSuggestionReplyList($value['id']);
    		!empty($reply) && $list[$key]['reply'] = $reply;
    		
    		$value['create_time'] = date('Y-m-d H:i', $value['create_time']);
    			
    		if ($value['mid'] == -1) {
    			$list[$key]['nickname'] = "另客";
				$value['avatar'] = $guest_info['avatar'];
    		}else if ($value['mid'] > 0 && $value['mid'] == $userService->getUserId()) {
				$userinfo = $userService->getUserInfo();
				$value['nickname'] = $userinfo['nickname'];
				$value['avatar'] = $userinfo['avatar'];
    			$value['editable'] = "1";
    		}else{
				$value['nickname'] = $guest_info['nickname'];
				$value['avatar'] = $guest_info['avatar'];
				if($value['mid']>0){
					$user_id[] = $value['mid'];
				}
			}
    	}
		if($user_id){
			$user_list = $userService->getUserInfo($user_id);
			foreach($list as $key=> &$value){
				if($value['mid']>0 && $user_list[$value['mid']]){
					$value['nickname'] = $user_list[$value['mid']]['nickname'];
					$value['avatar'] = $user_list[$value['mid']]['avatar'];
				}
			}
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
			$userService = D('User','Service');
			$guest_info = $userService->getUserInfo(0);
			$user_id = array();
        	foreach ($list as $key => &$value) {
        		$list[$key]['suggest'] = preg_replace("/<br\s\/>$/", "", $value['suggest']);
				if ($value['mid'] == -1) {
					$list[$key]['nickname'] = "另客";
					$value['avatar'] = $guest_info['avatar'];
				}else if ($value['mid'] > 0 && $value['mid'] == $userService->getUserId()) {
					$userinfo = $userService->getUserInfo();
					$value['nickname'] = $userinfo['nickname'];
					$value['avatar'] = $userinfo['avatar'];
				}else{
					$value['nickname'] = $guest_info['nickname'];
					$value['avatar'] = $guest_info['avatar'];
					if($value['mid']>0){
						$user_id[] = $value['mid'];
					}
				}
        	}
			if($user_id){
				$user_list = $userService->getUserInfo($user_id);
				foreach($list as $key=> &$value){
					if($value['mid']>0 && $user_list[$value['mid']]){
						$value['nickname'] = $user_list[$value['mid']]['nickname'];
						$value['avatar'] = $user_list[$value['mid']]['avatar'];
					}
				}
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