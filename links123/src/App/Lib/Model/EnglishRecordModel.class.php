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
        $data['object'] = $question_info['object'];
        $data['level'] = $question_info['level'];
        $data['subject'] = $question_info['subject'];
        $data['recommend'] = $question_info['recommend'];
        $data['ted'] = $question_info['ted'];
        $data['difficulty'] = $question_info['difficulty'];
        $data['special_recommend'] = $question_info['special_recommend'];
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
        $map['question_id'] = $question_info['id'];
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $map['user_id'] = intval(cookie('english_tourist_id')); //从cookie获取游客id
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
            cookie('english_tourist_id', $map['user_id'], 60 * 60 * 24 * 30); //更新游客id到cookie
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]); //用户id
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
     * @param int $question_id [题目id]
     * @return max [做题记录数组] 
     */
    public function getQuestionUserRecord($question_id) {
        $record = array();
        $question_id = intval($question_id);
        $map['question_id'] = $question_id;
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $tourist_id = intval(cookie("english_tourist_id"));
            $map['user_id'] = $tourist_id;
            $englishTourishRecordModel = D("EnglishTouristRecord");
            $ret = $englishTourishRecordModel->where($map)->find();
            if (false !== $ret) {
                $record = $ret;
            }
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
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
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $recommend [推荐id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param int $special_recommend [特别推荐，1是，0不是，-1全部]
     * @param string $extend_condition [额外条件]
     * @return array
     * @author Adam $date2013.08.30$
     */
    public function getUserTestQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $special_recommend = -1, $extend_condition = "") {
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
        if (intval($subject) > 0) {
            $map['subject'] = intval($subject);
        }
        if (intval($recommend) > 0) {
            $map['media.recommend'] = $recommend;
        }
        if (intval($difficulty) > 0) {
            $map['difficulty'] = intval($difficulty);
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
        if (intval($special_recommend) > 0) {
            $map['special_recommend'] = intval($special_recommend);
        }
        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
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
     * 获取用户做过的题目id列表
     * @param array $params [条件数组]
     * @param string $extend_condition [额外条件]
     * @return array
     * @author Adam $date2013.08.30$
     */
    public function getUserTestedQuestionIdList($viewType, $params, $extend_condition = "") {
        $question_ids = array();
        $question_ids[0] = 0;
        if ($viewType == 1) {
            if (intval($params['object'])) {
                if (D("EnglishObject")->where("id=" . intval($params['object']))->getField("name") == "综合") {
                    $params['object'] = 0;
                }
            }
            if (intval($params['object']) > 0) {
                $map['object'] = intval($params['object']);
            }
            if (intval($params['level']) > 0) {
                $map['level'] = intval($params['level']);
            }
        } else if ($viewType == 2) {
            if (intval($params['subject']) > 0) {
                $map['subject'] = intval($params['subject']);
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = intval($params['difficulty']);
            }
        } else if ($viewType == 3) {
            if (intval($params['recommend']) > 0) {
                $map['recommend'] = intval($params['recommend']);
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = intval($params['difficulty']);
            }
        } else if ($viewType == 4) {
            $map['special_recommend'] = 1;
        } else if ($viewType == 5) {
            if (intval($params['ted']) > 0) {
                $map['media.ted'] = $params['ted'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = intval($params['difficulty']);
            }
        }
        if (intval($params['voice']) > 0) {
            $map['voice'] = intval($params['voice']);
        }
        if (intval($params['target']) > 0) {
            $map['target'] = intval($params['target']);
        }
        if (intval($params['pattern']) > 0) {
            $map['pattern'] = intval($params['pattern']);
        }
        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
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
     * @param int $question_id
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

    /**
     * 获取用户没有做过的题目的数量
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $recommend [推荐id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param int $special_recommend [特别推荐，1是，0不是，-1全部]
     * @return int
     * @author Adam $date2013.08.30$
     */
    public function getUserUntestedQuestionNum($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $special_recommend = -1) {
        if (intval($object)) {
            $object_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
            if ($object_name == "综合") {
                $object = 0;
            }
        }
        if (intval($object) > 0) {
            $map['object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['level'] = intval($level);
        }
        if (intval($subject) > 0) {
            $map['subject'] = intval($subject);
        }
        if (intval($recommend) > 0) {
            $map['media.recommend'] = $recommend;
        }
        if (intval($difficulty) > 0) {
            $map['difficulty'] = intval($difficulty);
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
        if (intval($special_recommend) > 0) {
            $map['special_recommend'] = intval($special_recommend);
        }
        //用户做过的题目去除
        $user_question_ids = $this->getUserTestQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
        $map['status'] = 1;
        $map['_string'] = "`id` not in(" . implode(",", $user_question_ids) . ")";
        $englishQuestionModel = D("EnglishQuestion");
        $count = $englishQuestionModel->where($map)->count();
        return intval($count);
    }

    /**
     * 获取用户没有做过的题目的数量
     * @param int $viewType
     * @param array $params
     * @return int
     * @author Adam $date2013.09.17$
     */
    public function getUserUntestedQuestionNumber($viewType, $params) {
        //用户做过的题目去除
        $user_question_ids = $this->getUserTestedQuestionIdList($viewType, $params);
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
     * @param int $subject [专题id]
     * @param int $recommend [推荐id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [形式]
     * @param int $question_id [题目id]
     * @return void
     * @author Adam $date2013.7.2$
     */
    public function clearUserRecord($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $question_id) {
        $map = array();
        if (intval($question_id > 0)) {
            $map['question_id'] = $question_id;
        } else {
            if (intval($object)) {
                $object_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
                if ($object_name == "综合") {
                    $object = 0;
                }
            }
            if (intval($object) > 0) {
                $map['object'] = intval($object);
            }
            if (intval($level) > 0) {
                $map['level'] = intval($level);
            }
            if (intval($subject) > 0) {
                $map['subject'] = intval($subject);
            }
            if (intval($difficulty) > 0) {
                $map['difficulty'] = intval($difficulty);
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
            if ($map['user_id'] > 0) {
                $ret = $this->where($map)->delete();
            }
        } else {
            $map['user_id'] = intval(cookie("english_tourist_id"));
            if ($map['user_id'] > 0) {
                $englishTouristRecordModel = D("EnglishTouristRecord");
                $ret = $englishTouristRecordModel->where($map)->delete();
            }
        }
    }

}

?>
