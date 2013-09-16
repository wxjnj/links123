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
     * @param int $viewType [查看方式，1科目等级，2专题难度，3推荐难度]
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $recommend [推荐id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param int $media_id [视频id]
     * @param string $extend_condition [额外条件]
     * return array [题目数组]
     */
    public function getQuestionToIndex($viewType = 1, $object, $level, $subject, $recommend, $difficulty, $voice = 1, $target = 1, $pattern = 1, $media_id = 0, $extend_condition = "") {
        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();
        //
        //特别推荐图片直接点击获取
        if ($viewType == 4 && (intval($media_id) > 0)) {
            $ret = $this->alias("question")
                    ->field($needField)
                    ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                    ->where(array("media.id" => $media_id))
                    ->find();
        } else {
            if ($target == 2) {
                $ret = D("EnglishQuestionSpeak")->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $pattern);
                return $ret;
            }
            $order = "`special_recommend` DESC,`recommend` DESC";
            if ($viewType == 1) {
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
            } else if ($viewType == 2) {
                if (intval($subject) > 0) {
                    $map['media.subject'] = $subject;
                } else {
                    return $ret;
                }
                if (intval($difficulty) > 0) {
                    $map['media.difficulty'] = $difficulty;
                }
            } else if ($viewType == 3) {
                if (intval($recommend) > 0) {
                    $map['media.recommend'] = $recommend;
                } else {
                    return $ret;
                }
                if (intval($difficulty) > 0) {
                    $map['media.difficulty'] = $difficulty;
                }
            } else if ($viewType == 4) {
                $map['media.special_recommend'] = 1;
            }
            if (intval($voice) > 0) {
                $map['media.voice'] = $voice;
            }
            if (intval($pattern) > 0) {
                $map['media.pattern'] = $pattern;
            }
            if (intval($target) > 0) {
                $map['question.target'] = $target;
            }
            $map['media.status'] = 1;
            $map['question.status'] = 1;

            if (!empty($extend_condition)) {
                $map['_string'] = $extend_condition;
            }
            //
            //优先获取用户没看过的试题
            $user_view_question_ids = D("EnglishViewRecord")->getUserViewQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
            if (!empty($user_view_question_ids)) {
                $map['question.id'] = array("not in", $user_view_question_ids);
                $count = $this->alias("question")->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
                if ($count > 0) {
                    $limit = rand(0, $count - 1);
                    //去除用户看过的题目
                    $ret = $this->alias("question")
                            ->field($needField)
                            ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                            ->where($map)
                            ->order($order)
                            ->limit("{$limit},1")
                            ->select();
                    if (!empty($ret)) {
                        $ret = $ret[0];
                    }
                }
            }
            if (empty($ret)) {
                unset($map['question.id']);
                //
                //优先获取用户没做过的题目
                $user_question_ids = $englishRecordModel->getUserTestQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $map['media.special_recommend']); //用户做过的题目id数组
                $map['question.id'] = array("not in", $user_question_ids);
                //$question_ids = array_diff($user_view_question_ids, $user_question_ids); //剔除看过的题目里面做过的题目
                $count = $this->alias("question")->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
                if ($count > 0) {
                    $limit = rand(0, $count - 1);
                    //去除用户看过的题目
                    $ret = $this->alias("question")
                            ->field($needField)
                            ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                            ->where($map)
                            ->order($order)
                            ->limit("{$limit},1")
                            ->select();
                    if (!empty($ret)) {
                        $ret = $ret[0];
                    }
                }
            }
            //
            //用户题目都做过，视作未做过一题
            if (empty($ret)) {
                unset($map['question.id']);
                /*
                  //用户当前类别题目都看过,获取没做过的第一个
                  if (!empty($question_ids) && intval($question_ids[0]) > 0) {
                  $map['question.id'] = intval($question_ids[0]);
                  } else if (!empty($user_view_question_ids) && intval($user_view_question_ids[0]) > 0) {
                  $map['question.id'] = intval($user_view_question_ids[0]); //用户当前都看过且都做过，获取看过的第一个
                  } */
                //
                $count = $this->alias("question")->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")->where($map)->count();
                if ($count > 0) {
                    $limit = rand(0, $count - 1);
                    $ret = $this->alias("question")
                            ->field($needField)
                            ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                            ->where($map)
                            ->order($order)
                            ->limit("{$limit},1")
                            ->select();
                    if (!empty($ret)) {
                        $ret = $ret[0];
                        if (!empty($user_view_question_ids)) {
                            //$ret['viewed'] = true;
                            if (empty($question_ids)) {
                                $ret['tested'] = true;
                            }
                        }
                    }
                }
            }
            if (false === $ret) {
                return array();
            }
        }
        //echo $this->getLastSql();
        $ret['id'] = $ret['question_id'];
        if ($viewType == 3) {
            $ret['recommend'] = $recommend;
        }
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }
        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);
        //$ret['media_url'] = htmlspecialchars_decode($ret['media_url']);
        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        foreach ($ret['option'] as $key => $value) {
            $ret['option'][$key]['content'] = ftrim($value['content']);
        }
        return $ret;
    }

    /**
     * 获取题目数量
     * @param int $object
     * @param int $level
     * @param int $subject
     * @param int $recommend
     * @param int $difficulty
     * @param int $voice
     * @param int $target
     * @param int $pattern
     * @param int $extend_condition
     * @return int
     */
    public function getQuestionNum($object, $level, $subject, $recommend, $difficulty, $voice = 1, $target = 1, $pattern = 1, $extend_condition = "") {

        $map = array();
        $map['media.status'] = 1;
        $map['question.status'] = 1;
        if ($voice > 0) {
            $map['media.voice'] = $voice;
        }
        if ($pattern > 0) {
            $map['media.pattern'] = $pattern;
        }
        if ($target > 0) {
            $map['question.target'] = $target;
        }
        if (intval($object) > 0) {
            $object_info = D("EnglishObject")->find($object);
            if ($object_info['name'] != "综合") {
                $map['media.object'] = $object;
            }
        }
        if (intval($level) > 0) {
            $map['media.level'] = $level;
        }
        if (intval($subject) > 0) {
            $map['media.subject'] = $subject;
        }
        if (intval($recommend) > 0) {
            $map['media.recommend'] = $recommend;
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = $difficulty;
        }

        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
        }
        $num = $this->alias("question")
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                        ->where($map)->count("question.id");
        if (false === $num) {
            $num = 0;
        }
        return intval($num);
    }

    /**
     * 获取题目数量
     * @param array $params
     * @return int
     */
    public function getQuestionNumber($params, $extend_condition) {

        $map = array();
        $map['media.status'] = 1;
        $map['question.status'] = 1;
        if ($params['voice'] > 0) {
            $map['media.voice'] = $params['voice'];
        }
        if ($params['pattern'] > 0) {
            $map['media.pattern'] = $params['pattern'];
        }
        if ($params['target'] > 0) {
            $map['question.target'] = $params['target'];
        }
        if (intval($params['object']) > 0) {
            $object_info = D("EnglishObject")->find($params['object']);
            if ($object_info['name'] != "综合") {
                $map['media.object'] = $params['object'];
            }
        }
        if (intval($params['level']) > 0) {
            $map['media.level'] = $params['level'];
        }
        if (intval($params['subject']) > 0) {
            $map['media.subject'] = $params['subject'];
        }
        if (intval($params['recommend']) > 0) {
            $map['media.recommend'] = $params['recommend'];
        }
        if (intval($params['difficulty']) > 0) {
            $map['media.difficulty'] = $params['difficulty'];
        }
        if (intval($params['ted']) > 0) {
            $map['media.ted'] = $params['ted'];
        }

        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
        }
        $num = $this->alias("question")
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                        ->where($map)->count("question.id");
        if (false === $num) {
            $num = 0;
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
        $ret = $this->alias("question")
                ->field("question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where($condition)
                ->find();
        if (!empty($ret)) {
            $ret['id'] = $ret['question_id'];
            $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        }
        return $ret;
    }

    /**
     * 获取困难值列表
     * @param int $viewType
     * @param int $subject
     * @param int $recommend
     * @param int $voice
     * @param int $target
     * @param int $pattern
     * @return array
     * @author Adam $date2013.09.03$
     */
    public function getDifficultyList($viewType = 2, $subject, $recommend, $voice = 1, $target = 1, $pattern = 1, $init_num = 0) {
        if (intval($recommend) > 0 || intval($subject) > 0) {
            $map = array();
            $map['media.voice'] = $voice;
            $map['media.pattern'] = $pattern;
            $map['question.target'] = $target;
            $map['question.status'] = 1;
            $map['media.status'] = 1;
            if ($viewType == 2) {
                $map['media.subject'] = intval($subject) > 0 ? intval($subject) : 1;
            } else if ($viewType == 3) {
                $map['media.recommend'] = $recommend;
            }
            $ret = $this->alias("question")
                    ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                    ->field("count(question.id) as question_num,media.difficulty")
                    ->where($map)
                    ->group("difficulty")
                    ->select();
        }
        $difficultyList = array(
            array("id" => 1, "name" => "初级", "question_num" => $init_num),
            array("id" => 2, "name" => "中级", "question_num" => $init_num),
            array("id" => 3, "name" => "高级", "question_num" => $init_num)
        );
        if ($ret) {
            foreach ($ret as $value) {
                if (in_array($value['difficulty'] - 1, array(0, 1, 2))) {
                    $difficultyList[$value['difficulty'] - 1]['question_num'] = $value['question_num'];
                }
            }
        }
        return $difficultyList;
    }

    public function getDefaultDifficulty($viewType = 2, $subject, $recommend, $voice = 1, $target = 1, $pattern = 1) {
        $map = array();
        $map['media.voice'] = $voice;
        $map['media.pattern'] = $pattern;
        $map['question.target'] = $target;
        $map['question.status'] = 1;
        $map['media.status'] = 1;
        if ($viewType == 2) {
            $map['media.subject'] = intval($subject);
        } else if ($viewType == 3) {
            $map['media.recommend'] = $recommend;
        }
        $ret = $this->alias("question")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->field("media.difficulty as difficulty")
                ->where($map)
                ->group("media.difficulty")
                ->order("media.difficulty asc")
                ->find();
        if (in_array(intval($ret['difficulty']), array(1, 2, 3))) {
            $difficulty = intval($ret['difficulty']);
        } else {
            $difficulty = 1;
        }
        return $difficulty;
    }

    public function getDefaultDifficultyId($viewType = 2, $params) {
        $map = array();
        $map['media.voice'] = $params['voice'];
        $map['media.pattern'] = $params['pattern'];
        $map['question.target'] = $params['target'];
        $map['question.status'] = 1;
        $map['media.status'] = 1;
        if ($viewType == 2) {
            $map['media.subject'] = intval($params['subject']);
        } else if ($viewType == 3) {
            $map['media.recommend'] = $params['recommend'];
        } else if ($viewType == 5) {
            $map['media.ted'] = $params['ted'];
        }
        $ret = $this->alias("question")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->field("media.difficulty as difficulty")
                ->where($map)
                ->group("media.difficulty")
                ->order("media.difficulty asc")
                ->find();
        if (in_array(intval($ret['difficulty']), array(1, 2, 3))) {
            $difficulty = intval($ret['difficulty']);
        } else {
            $difficulty = 1;
        }
        return $difficulty;
    }

    /**
     * 获取困难值列表
     * @param int $viewType
     * @param array $params
     * @return array
     * @author Adam $date2013.09.03$
     */
    public function getDefaultDifficultyList($viewType = 2, $params) {
        if (intval($params['ted']) > 0 || intval($params['recommend']) > 0 || intval($params['subject']) > 0) {
            $map = array();
            $map['media.voice'] = $params['voice'];
            $map['media.pattern'] = $params['pattern'];
            $map['question.target'] = $params['target'];
            $map['question.status'] = 1;
            $map['media.status'] = 1;
            if ($viewType == 2) {
                $map['media.subject'] = intval($params['subject']) > 0 ? intval($params['subject']) : 1;
            } else if ($viewType == 3) {
                $map['media.recommend'] = $params['recommend'];
            } else if ($viewType == 5) {
                $map['media.ted'] = $params['ted'];
            }
            $ret = $this->alias("question")
                    ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                    ->field("count(question.id) as question_num,media.difficulty")
                    ->where($map)
                    ->group("difficulty")
                    ->select();
        }
        $difficultyList = array(
            array("id" => 1, "name" => "初级", "question_num" => $params['init_num']),
            array("id" => 2, "name" => "中级", "question_num" => $params['init_num']),
            array("id" => 3, "name" => "高级", "question_num" => $params['init_num'])
        );
        if ($ret) {
            foreach ($ret as $value) {
                if (in_array($value['difficulty'] - 1, array(0, 1, 2))) {
                    $difficultyList[$value['difficulty'] - 1]['question_num'] = $value['question_num'];
                }
            }
        }
        return $difficultyList;
    }

    public function getInfoById($id) {
        $ret = $this->alias("question")
                ->field("question.*,media.*")
                ->join(C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where(array("question.id" => $id))
                ->find();
        if (false == $ret || empty($ret)) {
            $ret = array();
        } else {
            $ret['id'] = $id;
        }
        return $ret;
    }

    /**
     * 
     * @author slate date:2013-09-11
     */
    public function getSubjectQuestion($object, $level, $voice = 1, $target = 1, $pattern = 1, $type, $questionid = 0, $nowQuestionId = 0) {
        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();

        $order = "`special_recommend` DESC,`recommend` DESC";

        //获取科目条件
        if ($object > 0) {
            //检测科目是否为综合
            $object_name = D("EnglishObject")->where(array("id" => $object, "status" => 1))->getField("name");
            //科目名不为综合
            if ($object_name != "综合") {
                $map['media.object'] = $object;
            }
        }
        if ($level > 0) {
            $map['media.level'] = $level;
        }

        if ($voice > 0) {
            $map['media.voice'] = $voice;
        }
        if ($pattern > 0) {
            $map['media.pattern'] = $pattern;
        }
        if ($target > 0) {
            $map['question.target'] = $target;
        }

        if ($type == "quick_select_prev") {

            $map['question.id'] = array('lt', $nowQuestionId);
            $order = "question.id DESC";
        } elseif ($type == 'quick_select_next') {

            $map['question.id'] = array('gt', $nowQuestionId);
            $order = "question.id ASC";
        } else {

            if ($questionid) {
                $map['question.id'] = $questionid;
            }
            $order = "question.id ASC";
        }

        $map['media.status'] = 1;
        $map['question.status'] = 1;

        $result = $this->alias("question")->field($needField)
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)->order($order)->limit(1)->select();

        $ret = $result[0];
        if ($ret) {

//     		$user_view_question_ids = D("EnglishViewRecord")->getUserViewQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
//     		if (!empty($user_view_question_ids)) {
//     			//$ret['viewed'] = true;
//     			if (empty($question_ids)) {
//     				$ret['tested'] = true;
//     			}
//     		}
        } else {

            $ret['max'] = $ret['min'] = 0;

            if ($type == "quick_select_prev") {

                $ret['min'] = 1;
            } elseif ($type == 'quick_select_next') {

                $ret['max'] = 1;
            }
            return $ret;
        }

        $ret['id'] = $ret['question_id'];
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }

        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($object, $level, '', '', '', $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);

        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);

        return $ret;
    }

    /**
     * 获取专题题目
     * 
     * @author slate date:2013-09-11
     */
    public function getSpecialSubjectQuestion($subject, $difficulty, $voice = 1, $target = 1, $pattern = 1, $type, $questionid = 0, $nowQuestionId = 0) {

        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();

        $order = "`special_recommend` DESC,`recommend` DESC";

        if ($voice > 0) {
            $map['media.voice'] = $voice;
        }
        if ($pattern > 0) {
            $map['media.pattern'] = $pattern;
        }
        if ($subject > 0) {
            $map['media.subject'] = $subject;
        } else {
            return $ret;
        }
        if ($difficulty > 0) {
            $map['media.difficulty'] = $difficulty;
        }

        if ($type == "quick_select_prev") {

            $map['question.id'] = array('lt', $nowQuestionId);
            $order = "question.id DESC";
        } elseif ($type == 'quick_select_next') {

            $map['question.id'] = array('gt', $nowQuestionId);
            $order = "question.id ASC";
        } else {

            if ($questionid) {
                $map['question.id'] = $questionid;
            }
            $order = "question.id ASC";
        }

        $map['media.status'] = 1;
        $map['question.status'] = 1;

        $result = $this->alias("question")->field($needField)
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)->order($order)->limit(1)->select();

        $ret = $result[0];
        if ($ret) {
            
        } else {

            $ret['max'] = $ret['min'] = 0;

            if ($type == "quick_select_prev") {

                $ret['min'] = 1;
            } elseif ($type == 'quick_select_next') {

                $ret['max'] = 1;
            }
            return $ret;
        }

        $ret['id'] = $ret['question_id'];
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }

        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum('', '', $subject, '', $difficulty, $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);

        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);

        return $ret;
    }

    /**
     * 获取推荐题目
     */
    public function getRecommendQuestion($recommend, $difficulty, $voice = 1, $target = 1, $pattern = 1, $type, $questionid = 0, $nowQuestionId = 0) {

        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();

        $order = "`special_recommend` DESC,`recommend` DESC";

        if ($voice > 0) {
            $map['media.voice'] = $voice;
        }
        if ($pattern > 0) {
            $map['media.pattern'] = $pattern;
        }
        if ($recommend > 0) {
            $map['media.recommend'] = $recommend;
        } else {
            return $ret;
        }
        if ($difficulty > 0) {
            $map['media.difficulty'] = $difficulty;
        }

        if ($type == "quick_select_prev") {

            $map['question.id'] = array('lt', $nowQuestionId);
            $order = "question.id DESC";
        } elseif ($type == 'quick_select_next') {

            $map['question.id'] = array('gt', $nowQuestionId);
            $order = "question.id ASC";
        } else {

            if ($questionid) {
                $map['question.id'] = $questionid;
            }
            $order = "question.id ASC";
        }

        $map['media.status'] = 1;
        $map['question.status'] = 1;

        $result = $this->alias("question")->field($needField)
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)->order($order)->limit(1)->select();

        $ret = $result[0];
        if ($ret) {
            
        } else {

            $ret['max'] = $ret['min'] = 0;

            if ($type == "quick_select_prev") {

                $ret['min'] = 1;
            } elseif ($type == 'quick_select_next') {

                $ret['max'] = 1;
            }
            return $ret;
        }

        $ret['id'] = $ret['question_id'];

        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum('', '', '', $recommend, $difficulty, $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);

        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        foreach ($ret['option'] as $key => $value) {
            $ret['option'][$key]['content'] = ftrim($value['content']);
        }
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }
        return $ret;
    }

    /**
     * 获取特别推荐视频
     */
    public function getSpecialRecommend($media_id = 0, $type, $nowQuestionId) {

        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();
        $map['media.special_recommend'] = 1;
        $map['media.status'] = 1;
        $map['question.status'] = 1;
        if ($type == "quick_select_prev") {

            $map['question.id'] = array('lt', $nowQuestionId);
            $order = "question.id DESC";
        } elseif ($type == 'quick_select_next') {

            $map['question.id'] = array('gt', $nowQuestionId);
            $order = "question.id ASC";
        } else {

            if ($media_id) {
                $map['media.id'] = $media_id;
            }
            $order = "question.id ASC";
        }
        $result = $this->alias("question")
                        ->field($needField)
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)
                        ->order($order)->limit(1)->select();
        $ret = $result[0];
        if (empty($ret)) {

            $ret['max'] = $ret['min'] = 0;

            if ($type == "quick_select_prev") {

                $ret['min'] = 1;
            } elseif ($type == 'quick_select_next') {

                $ret['max'] = 1;
            }
            return $ret;
        }

        $ret['id'] = $ret['question_id'];

        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum('', '', '', '', '', $ret['voice'], $ret['target'], $ret['pattern'], 1);
        $ret['content'] = ftrim($ret['content']);

        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        foreach ($ret['option'] as $key => $value) {
            $ret['option'][$key]['content'] = ftrim($value['content']);
        }
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }
        return $ret;
    }

    /**
     * 获取说力题目
     */
    public function getSpeakQuestion($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $pattern) {

        $ret = D("EnglishQuestionSpeak")->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $pattern);
        return $ret;
    }

    /**
     * 获取推荐题目
     */
    public function getTedQuestion($ted, $difficulty, $voice = 1, $target = 1, $pattern = 1, $type, $questionid = 0, $nowQuestionId = 0) {

        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.target,question.content,question.answer,question.media_id,media.*";
        $ret = array();

        $order = "`special_recommend` DESC,`recommend` DESC";

        if ($voice > 0) {
            $map['media.voice'] = $voice;
        }
        if ($pattern > 0) {
            $map['media.pattern'] = $pattern;
        }
        if ($ted > 0) {
            $map['media.ted'] = $ted;
        } else {
            return $ret;
        }
        if ($difficulty > 0) {
            $map['media.difficulty'] = $difficulty;
        }

        if ($type == "quick_select_prev") {

            $map['question.id'] = array('lt', $nowQuestionId);
            $order = "question.id DESC";
        } elseif ($type == 'quick_select_next') {

            $map['question.id'] = array('gt', $nowQuestionId);
            $order = "question.id ASC";
        } else {

            if ($questionid) {
                $map['question.id'] = $questionid;
            }
            $order = "question.id ASC";
        }

        $map['media.status'] = 1;
        $map['question.status'] = 1;

        $result = $this->alias("question")->field($needField)
                        ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media ON question.media_id=media.id")
                        ->where($map)->order($order)->limit(1)->select();

        $ret = $result[0];
        if ($ret) {
            
        } else {

            $ret['max'] = $ret['min'] = 0;

            if ($type == "quick_select_prev") {

                $ret['min'] = 1;
            } elseif ($type == 'quick_select_next') {

                $ret['max'] = 1;
            }
            return $ret;
        }

        $ret['id'] = $ret['question_id'];

        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum('', '', '', $recommend, $difficulty, $voice, $target, $pattern);
        $ret['content'] = ftrim($ret['content']);

        $ret['option'] = D("EnglishOptions")->getQuestionOptionList($ret['id']);
        foreach ($ret['option'] as $key => $value) {
            $ret['option'][$key]['content'] = ftrim($value['content']);
        }
        if ($ret['local_path']) {
            $ret['media_local_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
        }
        return $ret;
    }

}

?>
