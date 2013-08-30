<?php

/**
 * 用户已查看题目模型类
 *
 * @author Adam $date2013.7.12$
 */
class EnglishViewRecordModel extends CommonModel {

    /**
     * 添加用户查看记录
     * @param int $question_id [试题id]
     * @param int $level [等级]
     * @param int $object [科目]
     * @param int $diffculty [难度值]
     * @param int $subject [专题]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [类型]
     * @return
     */
    public function addRecord($question_id, $level, $object, $diffculty, $subject, $voice = 1, $target = 1, $pattern = 1) {
        $map = array();
        //游客
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，获取最大游客id加1为新游客id
            if ($map['user_id'] == 0) {
                $map['user_id'] = $this->getNewTouristId();
            }
            cookie('english_tourist_id', $map['user_id'], 24 * 60 * 60 * 30); //更新游客id到cookie
            $map['user_id'] = -$map['user_id']; //游客id在数据库表中记录为负数
        } else {
            $map['user_id'] = intval($_SESSION[C("MEMBER_AUTH_KEY")]); //用户id为登录用户的对应用户id
        }
        $map['object'] = intval($object);
        $map['level'] = intval($level);
        $map['subject'] = intval($subject);
        $map['difficulty'] = intval($difficulty);
        $map['voice'] = intval($voice);
        $map['target'] = intval($target);
        $map['pattern'] = intval($pattern);
        $map['question_id'] = $question_id;
        $ret = $this->where($map)->find();
        if (!empty($ret)) {
            return;
        }
//        $data = array();
//        $data['user_id'] = $map['user_id'];
//        $data['question_id'] = $map['question_id'];
//        $data['object'] = $map['object'];
        $map['created'] = time();
        $max_sort = $this->where('`user_id`=' . intval($map['user_id']))->field("MAX(sort) as max_sort")->find();
        if (false === $max_sort || empty($max_sort) || $max_sort['max_sort'] == null) {
            $max_sort['max_sort'] = 0;
        }
        $map['sort'] = $max_sort['max_sort'] + 1;
        $this->add($map);
    }

    /**
     * 获取用户看过的题目记录
     * 根据用户当前的题目id，根据上下题的方式获取用户看过的题目记录
     * @param int $question_id [用户当前题目id]
     * @param string $type [查看用户已看题目的方式next下一题/prev上一题]
     * @param int $object [当前科目id]
     * @param int $level [当前等级id]
     * @param int $subject [当前专题id]
     * @param int $difficulty [当前难度]
     * @param int $voice [当前口音]
     * @param int $target [当前目标]
     * @param int $pattern [当前类型]
     * @return array
     */
    public function getViewedQuestionRecord($question_id, $type = "next", $object, $level, $subject, $difficulty, $voice, $target, $pattern) {
        $map = array();
        //
        //获取用户id
        $user_id = 0;
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $user_id = intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，返回空
            if ($user_id == 0) {
                return array();
            }
            $user_id = -$user_id;
        } else {
            $user_id = intval($_SESSION[C("MEMBER_AUTH_KEY")]); //用户id为登录用户的对应用户id
        }
        //如果不存在用户id，返回空
        if ($user_id == 0) {
            return array();
        }
        $map['question_id'] = $question_id;
        if ($type == "next") {
            if (intval($object) > 0) {
                $map['object'] = $object; //科目由于综合包含所有题目，需要区别科目
            }
        }
        //
        //本次次的题目历史信息
        $now_question_info = $this->where($map)->find();
        if (false === $now_question_info || empty($now_question_info)) {
            return array();
        }
        $map = array();
        $map['user_id'] = $user_id;
        if ($type == "next") {
            $map['object'] = intval($object);
            $map['level'] = intval($level);
            $map['subject'] = intval($subject);
            $map['difficulty'] = intval($difficulty);
            $map['voice'] = intval($voice);
            $map['target'] = intval($target);
            $map['pattern'] = intval($pattern);
            $map['sort'] = array('gt', intval($now_question_info['sort']));
            $order = "`sort` ASC";
        } else {
            if (intval($object) > 0) {
                $map['object'] = intval($object);
                $map['level'] = intval($level);
            }
            if (intval($subject) > 0) {
                $map['subject'] = intval($subject);
                $map['difficulty'] = intval($difficulty);
            }
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

    /**
     * 获取用户看过的题目id列表
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param string $extend_condition [额外条件]
     * @return array
     */
    public function getUserViewQuestionIdList($object, $level, $subject, $difficulty, $voice, $target, $pattern, $extend_condition = "") {
        //试题id数组初始化
        $question_ids = array();
        if (intval($object)) {
            if (D("EnglishObject")->where("id=" . intval($object))->getField("name") == "综合") {
                $object = 0;
            }
        }
        if (intval($object) > 0) {
            $map['media.object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['media.level'] = intval($level);
        }
        if (intval($subject) > 0) {
            $map['media.subject'] = intval($subject);
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = intval($difficulty);
        }
        if (intval($voice) > 0) {
            $map['media.voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['question.target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['media.pattern'] = intval($pattern);
        }
        if (!empty($extend_condition)) {
            $map['_string'] = $extend_condition;
        }
        if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && intval($_SESSION[C('MEMBER_AUTH_KEY')]) > 0) {
            $map['record.user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
        } else {
            $map['record.user_id'] = intval(cookie("english_tourist_id")) > 0 ? -intval(cookie("english_tourist_id")) : 0;
        }
        $ret = $this->alias("record")
                        ->field("record.question_id")
                        ->join(C("DB_PREFIX") . "english_question question on record.question_id=question.id")
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                        ->where($map)->select();
        if (false !== $ret && !empty($ret)) {
            foreach ($ret as $key => $value) {
                $question_ids[] = intval($value['question_id']);
            }
        }
        return $question_ids;
    }

    /**
     * 获取新游客id
     * @return int
     * @author Adam $date2013-08-13$
     */
    public function getNewTouristId() {
        $ret = 0;
        $viewRecordUser = $this->field("MIN(user_id) as max_user_id")->find();
        if (false === $viewRecordUser || empty($viewRecordUser) || $viewRecordUser['max_user_id'] == null || $viewRecordUser['max_user_id'] > 0) {
            $viewRecordUser['max_user_id'] = 0;
        } else {
            $viewRecordUser['max_user_id'] = -$viewRecordUser['max_user_id'];
        }
        $touristRecordUser = D("EnglishTouristRecord")->field("MAX(user_id) as max_user_id")->find();
        if (false === $touristRecordUser || empty($touristRecordUser) || $touristRecordUser['max_user_id'] == null) {
            $touristRecordUser['max_user_id'] = 0;
        }
        $ret = intval($viewRecordUser['max_user_id'] > $touristRecordUser['max_user_id'] ? $viewRecordUser['max_user_id'] : $touristRecordUser['max_user_id']) + 1;
        return $ret;
    }

}

?>
