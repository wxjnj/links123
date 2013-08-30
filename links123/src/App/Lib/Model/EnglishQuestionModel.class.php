<?php

class EnglishQuestionModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
//        array("voice", "require", "口音（美音/英音）必须"),
        array("target", "require", "目标（听力/说力）必须"),
//        array("pattern", "require", "形式（视频/音频）必须"),
//        array("object", "require", "学科必须"),
//        array("level", "require", "等级必须"),
        array("content", "require", "试题必须")
    );
    protected $_auto = array(
        array("created", "time", 1, "function"),
        array("updated", "time", 3, "function")
    );

    /**
     * 获取题目到首页
     * @author adam 2013.5.29
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param string $extend_condition [额外条件]
     * return array [题目数组]
     */
    public function getQuestionToIndex($object, $level, $subject, $difficulty, $voice = 1, $target = 1, $pattern = 1, $extend_condition = "") {
        $map = array();
        //
        //获取科目条件
        if (intval($object) > 0) {
            //检测科目是否为综合
            $object_name = D("EnglishObject")->where(array("id" => $object, "status" => 1))->getField("name");
            //科目名不为综合
            if ($object_name != "综合") {
                $map['media.object'] = $object;
            }
        }
        if (intval($level) > 0) {
            $map['media.level'] = $level;
        }
        if (intval($subject) > 0) {
            $map['media.subject'] = $subject;
        }
        if (intval($level) > 0) {
            $map['media.level'] = $level;
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = $difficulty;
        }
        if (intval($voice) > 0) {
            $map['media.voice'] = $voice;
        }
        if (intval($pattern) > 0) {
            $map['media.pattern'] = $pattern;
        }
        $map['media.status'] = 1;
        $map['question.status'] = 1;
        if (intval($target) > 0) {
            $map['question.target'] = $target;
        }

        if (!empty($extend_condition)) {
            $map['_string'] = $extend_condition;
        }
        //
        //优先获取用户没看过的试题
        $user_view_question_ids = D("EnglishViewRecord")->getUserViewQuestionIdList($object, $level, $subject, $difficulty, $voice, $target, $pattern);
        if (!empty($user_view_question_ids)) {
            $map['question.id'] = array("not in", $user_view_question_ids);
            $count = $this->alias("question")->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
            //$count = $this->where($condition . $record_view_condition)->count(); //用于随机
            if ($count > 0) {
                $limit = rand(0, $count - 1);
                //去除用户看过的题目
                $ret = $this->alias("question")
                        ->field("question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*")
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)
                        ->limit("{$limit},1")
                        ->select();
//                $ret = $this->where($condition . $record_view_condition)->limit("{$limit},1")->select(); 
                if (!empty($ret)) {
                    $ret = $ret[0];
                }
            }
        }
        $englishRecordModel = D("EnglishRecord");
        if (empty($ret)) {
            //
            //优先获取用户没做过的题目
            $user_question_ids = $englishRecordModel->getUserTestQuestionIdList($object, $level, $subject, $difficulty, $voice, $target, $pattern); //用户做过的题目id数组
            $map['question.id'] = array("not in", $user_question_ids);
            $count = $this->alias("question")->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
            if ($count > 0) {
                $limit = rand(0, $count - 1);
                //去除用户看过的题目
                $ret = $this->alias("question")
                        ->field("question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*")
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)
                        ->limit("{$limit},1")
                        ->select();
//                $ret = $this->where($condition . $record_view_condition)->limit("{$limit},1")->select(); 
                if (!empty($ret)) {
                    $ret = $ret[0];
                }
            }
            //
            //用户题目都做过，视作未做过一题
            if (empty($ret)) {
                $count = $this->alias("question")->join("RIGHT JOIN " . C("D_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
                if ($count > 0) {
                    $limit = rand(0, $count - 1);
                    $ret = $this->alias("question")
                            ->field("question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*")
                            ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                            ->where($map)
                            ->limit("{$limit},1")
                            ->select();
                    if (!empty($ret)) {
                        $ret = $ret[0];
                        $ret['tested'] = true;
                    }
                }
            }
            if (false === $ret) {
                return array();
            }
        }
        $ret['id'] = $ret['question_id'];
        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($object, $level, $subject, $difficulty, $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);
        $ret['media_path'] = htmlspecialchars_decode($ret['path']);
//        $ret['media_url'] = htmlspecialchars_decode($ret['media_url']);
        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
//        $ret['option'] = D("EnglishOptions")->where("question_id={$ret['id']} and status=1")->order("sort asc")->select();
        foreach ($ret['option'] as $key => $value) {
            $ret['option'][$key]['content'] = ftrim($value['content']);
        }
        return $ret;
    }

    public function getQuestionNum($object, $level, $voice = 1, $target = 1, $pattern = 1, $extend_condition = "") {
        $object_info = D("EnglishObject")->find($object);
        if ($object_info['name'] == "综合") {
            $condition = "status=1 and voice={$voice} and target={$target} and pattern={$pattern} and level={$level} ";
        } else {
            $condition = "status=1 and voice={$voice} and target={$target} and pattern={$pattern} and level={$level} and object={$object} ";
        }
        if (!empty($extend_condition)) {
            $condition.=" and " . $extend_condition;
        }
        $num = $this->where($condition)->count();
        if (false === $num) {
            return 0;
        }
        return intval($num);
    }

    /*
     * 增加答题的数量
     * @param int $question_id [答题的id ]
     */

    public function addQuestionAnswerNum($question_id) {
        $question_id = intval($question_id);
        if (false === $this->where("id={$question_id}")->setInc("answer_num")) {
            Log::write('增加题目回答数失败：' . $this->getLastSql(), Log::SQL);
        }
    }

    /**
     * 根据条件获取拥有选项的题目
     * @param string/array $condition [条件]
     * @return array [题目信息数据]
     * @throws
     * @author Adam $date:2013.6.16$
     */
    public function getQuestionWithOption($condition) {
        $ret = $this->where($condition)->find();
        if (!empty($ret)) {
            $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        }
        return $ret;
    }

}

?>
