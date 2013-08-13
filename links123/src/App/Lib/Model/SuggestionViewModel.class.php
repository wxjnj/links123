<?php

// 建议投诉视图模型
class SuggestionViewModel extends ViewModel {

    public $viewFields = array(
        'suggestion' => array('id', 'suggest', 'type', 'mid', 'pid', 'create_time', 'is_reply', 'status', '_type' => 'LEFT'),
        'member' => array('nickname', 'face', '_on' => 'suggestion.mid=member.id'),
    );

    /**
     *  获取留言的回复/点评列表
     * @author adam
     * @param int $id [留言的id]
     * @return array 
     */
    public function getSuggestionReplyList($id) {
        $list = $this->where("is_reply=1 and pid={$id}")->order('create_time desc')->select();
        if (false === $list || empty($list)) {
            return array();
        }
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
        return $list;
    }

    /**
     * 判断当天是否有新的留言
     * @return boolean
     */
    public function isTodayHasNewSuggestion() {
        $mod = D("Suggestion");
        $ret = $mod->where("TO_DAYS(FROM_UNIXTIME(create_time))-TO_DAYS(NOW())=0")->find();
        if (false === $ret || empty($ret)) {
            return false;
        }
        return true;
    }

}
?>