<?php

/**
 * 用户已查看题目模型类
 *
 * @author Adam $date2013.7.12$
 */
class EnglishViewRecordModel extends CommonModel {

    /**
     * 添加用户查看记录
     * @param int $question_id [题目id]
     * @param int $object [题目科目id,存在综合特殊情况故需要记录]
     * @return  void 
     * @author Adam 2013.7.13
     */
    public function addRecord($question_id, $object) {
        $map = array();
        //游客
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，获取最大游客id加1为新游客id
            if ($map['user_id'] == 0) {
                $englishTouristRecordModel = D("EnglishTouristRecord");
                $ret = $englishTouristRecordModel->field("MAX(user_id) as max_user_id")->find();
                if (false === $ret || empty($ret) || $ret['max_user_id'] == null) {
                    $ret['max_user_id'] = 0;
                }
                $map['user_id'] = intval($ret['max_user_id']) + 1;
            }
            cookie('english_tourist_id', $map['user_id']); //更新游客id到cookie
            $map['user_id'] = -$map['user_id']; //游客id在数据库表中记录为负数
        } else {
            $map['user_id'] = intval($_SESSION[C("MEMBER_AUTH_KEY")]); //用户id为登录用户的对应用户id
        }
        $map['object'] = intval($object);
        $map['question_id'] = $question_id;
        $ret = $this->where($map)->find();
        if (!empty($ret)) {
            return;
        }
        $data = array();
        $data['user_id'] = $map['user_id'];
        $data['question_id'] = $map['question_id'];
        $data['object'] = $map['object'];
        $data['created'] = time();
        $max_sort = $this->where('`user_id`=' . intval($map['user_id']))->field("MAX(sort) as max_sort")->find();
        if (false === $max_sort || empty($max_sort) || $max_sort['max_sort'] == null) {
            $max_sort['max_sort'] = 0;
        }
        $data['sort'] = $max_sort['max_sort'] + 1;
        $this->add($data);
    }

    /**
     * 获取用户看过的题目记录
     * 根据用户当前的题目id，根据上下题的方式获取用户看过的题目记录
     * @param int $now_question_id [用户当前题目id]
     * @param string $type [查看用户已看题目的方式next下一题/prev上一题]
     * @return max [用户看过的题目记录]
     */
    public function getViewedQuestionRecord($now_question_id, $type = "next") {
        $map = array();
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，返回的试题id为零
            if ($map['user_id'] == 0) {
                return 0;
            }
            $map['user_id'] = -$map['user_id'];
        } else {
            $map['user_id'] = intval($_SESSION[C("MEMBER_AUTH_KEY")]); //用户id为登录用户的对应用户id
        }
        $map['question_id'] = $now_question_id;
        $now_question_info = $this->where($map)->find(); //本次次的题目历史信息
        if (false === $now_question_info || empty($now_question_info)) {
            return array();
        }
        unset($map['question_id']);
        if ($type == "next") {
            $map['sort'] = array('gt', intval($now_question_info['sort']));
            $order = "`sort` ASC";
        } else {
            $map['sort'] = array('lt', intval($now_question_info['sort']));
            $order = "`sort` DESC";
        }
        $ret = $this->where($map)->order($order)->find();
//        dump($this->getLastSql());exit;.
        if (false === $ret) {
            $ret = array();
        }
        return $ret;
    }

}

?>
