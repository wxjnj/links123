<?php

/**
 * 英语角说力试题模型类
 *
 * @author Adam $date2013-08-06$
 */
class EnglishQuestionSpeakModel extends CommonModel {
    
    protected $_validate = array(
        array("name", "require", "名称必须")
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
     * @param string $extend_condition [额外条件]
     * @param array $user_ID_cache [用户身份信息缓存数据]
     *        $user_ID_cache = array(C('MEMBER_AUTH_KEY') => $_SESSION[C('MEMBER_AUTH_KEY')],
     *                               C('ENGLISH_TOURIST_ID') => cookie(C('ENGLISH_TOURIST_ID')));
     * return array [题目数组]
     */
    public function getQuestionToIndex($viewType = 1, $object, $level, $subject, 
                                        $recommend, $difficulty, $voice = 1, $pattern = 1, 
                                        $extend_condition = "", 
                                        $user_ID_cache
                                    ) {
        $target = 2; //听力表指定训练对象
        $map = array();
        $englishRecordModel = D("EnglishRecord");
        $needField = "question.id as question_id,question.media_id,media.*";
        $ret = array();
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
        $map['media.status'] = 1;
        $map['question.status'] = 1;

        if (!empty($extend_condition)) {
            $map['_string'] = $extend_condition;
        }
        //
        //优先获取用户没看过的试题
        $user_view_question_ids = D("EnglishViewRecord")->getUserViewQuestionIdList($object, 
                                                                                    $level, 
                                                                                    $subject, 
                                                                                    $recommend, 
                                                                                    $difficulty, 
                                                                                    $voice, 
                                                                                    $target, 
                                                                                    $pattern,
                                                                                    $user_ID_cache
                                                                                );
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
            $user_question_ids = $englishRecordModel->getUserTestQuestionIdList($object, $level, $subject, $recommend, 
                                                                                $difficulty, $voice, $target, $pattern, 
                                                                                $map['media.special_recommend'],
                                                                                $user_ID_cache
                                                                            ); //用户做过的题目id数组
            $map['question.id'] = array("not in", $user_question_ids);
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
            //
            //用户题目都做过，视作未做过一题
            if (empty($ret)) {
                unset($map['question.id']);
                /*
                  //用户当前类别题目都看过且都做过，则获取历史看过的题目的第一个
                  if (!empty($user_view_question_ids) && !empty($user_question_ids)) {
                  $map['question.id'] = $user_question_ids[0];
                  } */
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
                        $ret['tested'] = true;
                    }
                }
            }
            if (false === $ret) {
                return array();
            }
        }
        $ret['id'] = $ret['question_id'];
        $ret['target'] = $target;
        if ($viewType == 3) {
            $ret['recommend'] = $recommend;
        }
        $ret['record'] = $englishRecordModel->getQuestionUserRecord($ret['id']);
        $ret['record']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
        if (intval($ret['media_id']) > 0) {
            $mediaModel = D("EnglishMedia");
            $ret['media_info'] = $mediaModel->getMediaInfo($ret['media_id']); //媒体信息
            $ret['media_info']['sentences'] = D("EnglishQuestionSpeakSentence")->getSpeakQuestionSentenceList($ret['id']); //说力跟读句子信息
            $ret['media_info']['question_id'] = $ret['id'];
            $ret['media_info_json'] = $mediaModel->formatMdeiaInfoToFlash($ret['media_info']); //给flash的JSON数据封装
        }
        return $ret;
    }

}

?>
