<?php

class EnglishRecordModel extends CommonModel {

    protected $_auto = array(
        array("created", "time", 3, "function")
    );

    /**
     * 记录用户答题记录
     * @author adam 2013.5.31
     * @param array $question_info [所答题目的信息]
     * @param int $answer [用户选项id]
     * @return max [更新后的记录]
     */
    public function record($question_info, $answer) {
        $record = $this->getQuestionUserRecord($question_info['id']); //获取当前的记录信息
        /** $  更新的信息数组 */
        $data = array();
        $data['question_id'] = $question_info['id'];
        $data['voice'] = $question_info['voice'];
        $data['target'] = $question_info['target'];
        $data['pattern'] = $question_info['pattern'];
        $data['object'] = intval($question_info['real_object']) > 0 ? intval($question_info['real_object']) : $question_info['object']; //排除科目为综合的影响
        $data['level'] = $question_info['level'];
        $data['user_answer'] = $answer;
        $data['is_right'] = $answer === intval($question_info['answer']) ? 1 : 0; //用户是否答对，1是0否
        $data['created'] = time();
        $data['test_num'] = intval($record['test_num']) + 1;
        //更新答对和答错次数
        if ($answer === intval($question_info['answer'])) {
            $data['right_num'] = intval($record['right_num']) + 1;
            $data['error_num'] = intval($record['error_num']);
        } else {
            $data['error_num'] = intval($record['error_num']) + 1;
            $data['right_num'] = intval($record['right_num']);
        }
        /* 更新的信息数组 $ */
        //
        //游客记录的更新保存
        $map = array();
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval(cookie('english_tourist_id')); //从cookie获取游客id
            $map['question_id'] = $question_info['id'];
            $englishTouristRecordModel = D("EnglishTouristRecord");
            $ret = $englishTouristRecordModel->where($map)->save($data); //保存游客记录
            //记录不存在的情况
            if (false !== $ret && $ret < 1) {
                //游客有过游客id
                if ($map['user_id'] == 0) {
                    //获取当前游客最大的id数,当前游客id为最大数加一
                    $map['user_id'] = D("EnglishViewRecord")->getNewTouristId();
                }
                $data['user_id'] = $map['user_id'];
                $englishTouristRecordModel->add($data); //新增游客记录
            }
            cookie('english_tourist_id', $map['user_id']); //更新游客id到cookie
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]); //用户id
            $map['question_id'] = $question_info['id'];
            $ret = $this->where($map)->save($data); //更新记录
            //不存在记录，添加记录
            if (false !== $ret && $ret < 1) {
                $data['user_id'] = $map['user_id'];
                $this->add($data);
            }
        }
        return $data;
    }

    /**
     * 获取用户题目的作答记录
     * @param int $question_id
     * @return max [做题记录数组] 
     */
    public function getQuestionUserRecord($question_id) {
        $record = array();
        $question_id = intval($question_id);
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $tourist_id = intval(cookie("english_tourist_id"));
            $map['user_id'] = $tourist_id;
            $map['question_id'] = $question_id;
            $englishTourishRecordModel = D("EnglishTouristRecord");
            $ret = $englishTourishRecordModel->where($map)->find();
            if (false !== $ret) {
                $record = $ret;
            }
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $map['question_id'] = $question_id;
            $ret = $this->where($map)->find();
            if (false !== $ret) {
                $record = $ret;
            }
        }
        $record['test_num'] = intval($record['test_num']);
        $record['right_num'] = intval($record['right_num']);
        $record['error_num'] = intval($record['error_num']);
        return $record;
    }

    /**
     * 获取用户做过的题目id列表
     * @author Adam $date2013.6.15
     * @return array [题目id数组] 
     */
    public function getUserTestQuestionIdList($object, $level, $voice, $target, $pattern, $extend_condition = "") {
        $question_ids = array();
        $question_ids[0] = 0;
        if (intval($object)) {
            if (D("EnglishObject")->where("id=" . intval($object))->getField("name") == "综合") {
                $object = 0;
            }
        }
        if (intval($object) > 0) {
            $map['object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['level'] = intval($level);
        }
        if (intval($voice) > 0) {
            $map['voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['pattern'] = intval($pattern);
        }
        if (!empty($extend_condition)) {
            $map['_string'] = $extend_condition;
        }
        if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && intval($_SESSION[C('MEMBER_AUTH_KEY')]) > 0) {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $ret = $this->where($map)->select();
            if (!empty($ret) && false !== $ret) {
                foreach ($ret as $value) {
                    $question_ids[] = intval($value['question_id']);
                }
            }
        } else {
            $map['user_id'] = intval(cookie("english_tourist_id"));
            $englishTourishRecordModel = D("EnglishTouristRecord");
            $ret = $englishTourishRecordModel->where($map)->select();
            if (!empty($ret) && false !== $ret) {
                foreach ($ret as $value) {
                    $question_ids[] = intval($value['question_id']);
                }
            }
        }
        return $question_ids;
    }

    /**
     * 判断用户是否答过此题
     * @param type $question_id
     * @return boolean
     * @author Adam $date2013.6.27$
     */
    public function isUserTestQuestion($question_id) {
        if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $ret = $this->where("`user_id`=" . intval($_SESSION[C('MEMBER_AUTH_KEY')]) . " AND `question_id`={$question_id}")->find();
            if (false !== $ret && !empty($ret)) {
                $ret = true;
            } else {
                $ret = false;
            }
        } else {
            $ret = D("EnglishTouristRecord")->where("`user_id`=" . intval(cookie("english_tourist_id")) . " AND `question_id`={$question_id}")->find();
            if (false !== $ret && !empty($ret)) {
                $ret = true;
            } else {
                $ret = false;
            }
        }
        return $ret;
    }

    public function getUserUntestedQuestionNum($object, $level, $voice, $target, $pattern) {
        $object_info = D("EnglishObject")->find($object);
        if ($object_info['name'] == "综合") {
            $object = 0;
        }
        if (intval($object) > 0) {
            $map['object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['level'] = intval($level);
        }
        if (intval($voice) > 0) {
            $map['voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['pattern'] = intval($pattern);
        }
        //用户做过的题目去除
        $user_question_ids = $this->getUserTestQuestionIdList($object, $level, $voice, $target, $pattern);
        $map['status'] = 1;
        $map['_string'] = "`id` not in(" . implode(",", $user_question_ids) . ")";
        $englishQuestionModel = D("EnglishQuestion");
        $count = $englishQuestionModel->where($map)->count();
        return intval($count);
    }

    /**
     * 清除用户的做题记录
     * @param int $object [科目]
     * @param int $level [等级]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [形式]
     * @param int $question_id [题目id]
     * @return void
     * @author Adam $date2013.7.2$
     */
    public function clearUserRecord($object, $level, $voice, $target, $pattern, $question_id) {
        $map = array();
        if (intval($question_id > 0)) {
            $map['question_id'] = $question_id;
        } else {
            $object_info = D("EnglishObject")->find($object);
            if ($object_info['name'] == "综合") {
                $object = 0;
            }
            if (intval($object) > 0) {
                $map['object'] = intval($object);
            }
            if (intval($level) > 0) {
                $map['level'] = intval($level);
            }
            if (intval($voice) > 0) {
                $map['voice'] = intval($voice);
            }
            if (intval($target) > 0) {
                $map['target'] = intval($target);
            }
            if (intval($pattern) > 0) {
                $map['pattern'] = intval($pattern);
            }
        }
        if (isset($_SESSION[C('MEMBER_AUTH_KEY')]) && !empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $ret = $this->where($map)->delete();
        } else {
            $map['user_id'] = intval(cookie("english_tourist_id"));
            $englishTouristRecordModel = D("EnglishTouristRecord");
            $ret = $englishTouristRecordModel->where($map)->delete();
//            dump($englishTouristRecordModel->getLastSql());exit;
        }
    }

}

?>
