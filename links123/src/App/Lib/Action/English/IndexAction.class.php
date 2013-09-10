<?php

/*
 * 英语角首页控制类
 * @author adam 2013.5.28
 */

class IndexAction extends EnglishAction {

    public function index() {
        //
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON', false);
        $objectModel = D("EnglishObject");
        $levelModel = D("EnglishLevel");
        $questionModel = D("EnglishQuestion");
        $englishMediaModel = D("EnglishMedia");
        $this->initUserHistorySelect();
        //记录用户上次选择的数组
        $user_last_select = cookie('english_user_last_select');
        if (!is_array($user_last_select)) {
            $user_last_select = array();
        }
        //
        //用户第一次进入的默认情况
        $viewType = in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4)) ? intval($user_last_select['viewType']) : 1; //查看方式，1科目等级，2专题难度，3推荐
        $voice = in_array(intval($user_last_select['voice']), array(1, 2)) ? intval($user_last_select['voice']) : 1;
        $target = in_array(intval($user_last_select['target']), array(1, 2)) ? intval($user_last_select['target']) : 1;
        $pattern = in_array(intval($user_last_select['pattern']), array(1, 2)) ? intval($user_last_select['pattern']) : 1;
        $recommedsQuestionNum = $englishMediaModel->getRecommendQuestionNum($target, $voice, $pattern);
        $subjectsQuestionNum = $englishMediaModel->getSubjectQuestionNum($target, $voice, $pattern);
        $this->assign("recommedsQuestionNum", $recommedsQuestionNum);
        $this->assign("subjectsQuestionNum", $subjectsQuestionNum);
        if ($viewType == 4) {
            $media_id = intval($user_last_select['media_id']);
            //推荐列表
            $recommend_list = D("EnglishMediaRecommend")->getRecommendListToIndex($voice, $target, $pattern);
            $this->assign("recommend_list", $recommend_list);
            //
            $recommendDifficultyList = $questionModel->getDifficultyList(3, 0, 0, $voice, $target, $pattern);
            $this->assign("recommendDifficultyList", $recommendDifficultyList);
        } else if ($viewType == 3) {
            $englishMediaRecommendModel = D("EnglishMediaRecommend");
            //推荐列表
            $recommend_list = $englishMediaRecommendModel->getRecommendListToIndex($voice, $target, $pattern);
            $this->assign("recommend_list", $recommend_list);
            //推荐
            $recommend = intval($user_last_select['recommend']);
            //
            if ($recommend == 0) {
                $recommend = $englishMediaRecommendModel->getDefaultRecommendId($voice, $target, $pattern);
            } else {
                $num = $questionModel->getQuestionNum(0, 0, 0, $recommend, 0, $voice, $target, $pattern);
                if ($num == 0) {
                    $recommend = $englishMediaRecommendModel->getDefaultRecommendId($voice, $target, $pattern);
                }
            }
            if (in_array(intval($user_last_select['recommendDifficulty']), array(1, 2, 3))) {
                $difficulty = intval($user_last_select['recommendDifficulty']);
            } else {
                $difficulty = $questionModel->getDefaultDifficulty($viewType, 0, $recommend, $voice, $target, $pattern);
            }
            $num = $questionModel->getQuestionNum(0, 0, 0, $recommend, $difficulty, $voice, $target, $pattern);
            if ($num == 0) {
                $difficulty = $questionModel->getDefaultDifficulty($viewType, 0, $recommend, $voice, $target, $pattern);
            }
            //
            $recommendDifficultyList = $questionModel->getDifficultyList(3, 0, $recommend, $voice, $target, $pattern);
            $this->assign("recommendDifficultyList", $recommendDifficultyList);
        } else if ($viewType == 2) {
            $englishMediaSubjectModel = D("EnglishMediaSubject");
            $subject_list = $englishMediaSubjectModel->getSubjectListToIndex($voice, $target, $pattern);
            $this->assign("subject_list", $subject_list);
            //
            //用户上次选择的专题
            $subject = intval($user_last_select['subject']);
            $subject_id = $englishMediaSubjectModel->where(array("id" => $subject, "status" => 1))->getField("id");
            if (intval($subject_id) == 0) {
                $subject = $englishMediaSubjectModel->getDefaultSubjectdId($voice, $target, $pattern);
            }
            //难度列表
            $subjectDifficultyList = $questionModel->getDifficultyList(2, $subject, 0, $voice, $target, $pattern);
            $this->assign("subjectDifficultyList", $subjectDifficultyList);
            //难度值
            if (in_array(intval($user_last_select['subjectDifficulty']), array(1, 2, 3))) {
                $difficulty = intval($user_last_select['subjectDifficulty']);
            } else {
                $difficulty = $questionModel->getDefaultDifficulty($viewType, $subject, 0, $voice, $target, $pattern);
            }
            $num = $questionModel->getQuestionNum(0, 0, $subject, 0, $difficulty, $voice, $target, $pattern);
            if ($num == 0) {
                $difficulty = $questionModel->getDefaultDifficulty($viewType, $subject, 0, $voice, $target, $pattern);
            }
        } else if ($viewType == 1) {
            //科目列表
            $object_list = $objectModel->getObjectListToIndex($voice, $target, $pattern);
            $this->assign("object_list", $object_list);
            //
            //用户上次选择的科目
            $object = intval($user_last_select['object']);
            $objectInfo = $objectModel->where(array("id" => $object, "status" => 1))->find();
            //科目不存在或不可用
            if (false == $objectInfo && empty($objectInfo)) {
                $objectInfo = $objectModel->getDefaultObjectInfo($voice, $target, $pattern);
            }
            if (intval($objectInfo['id']) == 0) {
                $object = 1;
            } else {
                $object = $objectInfo['id'];
            }
            //
            //用户上次选择的等级
            $level = intval($user_last_select['level']);
            $levelInfo = $levelModel->where(array("id" => $level, "status" => 1))->find();
            //等级不存在或不可用
            if (false == $levelInfo && empty($levelInfo)) {
                $levelInfo = $levelModel->getDefaultLevelInfo($level, $voice, $target, $pattern);
            }
            if (intval($levelInfo['id']) == 0) {
                $level = 0;
            }
            //默认的等级列表
            $level_list = $levelModel->getLevelListToIndex($object, $voice, $target, $pattern);
            $this->assign("level_list", $level_list);
            //
            //确保上次选择的等级下拥有题目
            $ret = array();
            foreach ($level_list as $value) {
                $ret[$value['id']] = $value;
            }
            if ($ret[$level]['question_num'] == 0) {
                foreach ($level_list as $value) {
                    if ($value['question_num'] > 0) {
                        $levelInfo = $levelModel->getInfoById($value['id']);
                        break;
                    }
                }
                $level = $levelInfo['id'];
            }
        }

        //获取题目
        $question = $questionModel->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $media_id);
        //
        //获取用户英语角信息
        $englishUserInfoModel = D("EnglishUserInfo");
        $english_user_info = $englishUserInfoModel->getEnglishUserInfo();
        $this->assign("english_user_info", $english_user_info);
        //
        //记录浏览题目
        $englishViewRecordModel = D("EnglishViewRecord");
        if ($viewType == 2) {
            $englishViewRecordModel->addRecord($question['id'], 0, 0, $question['subject'], 0, $question['difficulty'], $question['voice'], $question['target'], $question['pattern'], $viewType);
        } else if ($viewType == 3) {
            $englishViewRecordModel->addRecord($question['id'], 0, 0, 0, $recommend, $question['difficulty'], $question['voice'], $$question['target'], $question['pattern'], $viewType);
        } else if ($viewType == 1) {
            $englishViewRecordModel->addRecord($question['id'], $question['level'], $object, 0, 0, 0, $question['voice'], $question['target'], $question['pattern'], $viewType);
        } else if ($viewType == 4) {
            $englishViewRecordModel->addRecord($question['id'], 0, 0, 0, 0, 0, $question['voice'], $question['target'], $question['pattern'], $viewType);
        }
        //
        //获取用户统计信息
        $englishUserCountModel = D("EnglishUserCount");
        $user_count_info = array();
        if ($viewType == 3) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, $recommend, $difficulty, $voice, $targe);
        } else if ($viewType == 2) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, $subject, 0, $difficulty, $voice, $target);
        } else if ($viewType == 1) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, $object, $level, 0, 0, 0, $voice, $target);
        } else if ($viewType == 4) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, 0, 0, $voice, $target);
        }
        $this->assign("user_conut_info", $user_count_info);

        //play_code为空，则进行视频解析
        if (!$question['play_code']) {
            //视频解析库
            import("@.ORG.VideoHooks");
            $videoHooks = new VideoHooks();

            $question['media_source_url'] = trim(str_replace(' ', '', $question['media_source_url']));
            $videoInfo = $videoHooks->analyzer($question['media_source_url']);

            $play_code = $videoInfo['swf'];

            $media_thumb_img = $videoInfo['img'];

            //解析成功，保存视频解析地址
            if (!$videoHooks->getError() && $play_code) {

                $play_type = $videoInfo['media_type'];
                $saveData = array(
                    'id' => $question['media_id'],
                    'media_thumb_img' => $media_thumb_img,
                    'play_code' => $play_code,
                    'play_type' => $play_type
                );
                $englishMediaModel = $englishMediaModel ? $englishMediaModel : D("EnglishMedia");
                $englishMediaModel->save($saveData);
            }

            $question['play_code'] = $play_code;

            $question['media_thumb_img'] = $media_thumb_img;

            $question['play_type'] = $play_type;
        }

        //判断是否为about.com视频
        $isAboutVideo = 0;
        if (strpos($question['media_source_url'], 'http://video.about.com') !== FALSE) {
            $isAboutVideo = 1;

            //about.com视频修改自动播放为false
            //$question['media_url'] = str_replace('&autoStart=true', '&autoStart=false', $question['media_url']);
        }
        if ($question['play_code']) {
            if (strpos($question['media_source_url'], 'britishcouncil.org') !== FALSE) {
                $question['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $question['play_code']);
                $question['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $question['play_code']);
            }
            $question['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $question['play_code']);
        }
        //
        //保存历史记录
        $user_last_select['voice'] = $voice;
        $user_last_select['target'] = $target;
        $user_last_select['pattern'] = $pattern;
        $user_last_select['viewType'] = $viewType;
        if ($viewType == 4) {
            $user_last_select['media_id'] = intval($media_id);
        } else if ($viewType == 3) {
            //推荐
            if (false != strpos($question['recommend'], $recommend)) {
                $user_last_select['recommend'] = intval($recommend);
            } else {
                $recommend = current(explode(",", $question['recommend']));
            }
            $user_last_select['recommend'] = $recommend;
            $user_last_select['recommendDifficulty'] = intval($question['difficulty']);
        } else if ($viewType == 2) {
            $user_last_select['sbject'] = intval($question['subject']);
            $user_last_select['subjectDifficulty'] = intval($question['difficulty']);
        } else if ($viewType == 1) {
            $user_last_select['object'] = intval($object);
            $user_last_select['level'] = intval($question['level']);
            $user_last_select['object_info'] = serialize($objectInfo);
            $user_last_select['level_info'] = serialize($levelInfo);
        }
        $this->assign("user_last_select", $user_last_select);
        cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30);

        //排行榜数据
        $ret = $englishUserInfoModel->getTopUserListByTypeName("object_综合");
        $this->assign("top_1", $ret[0]);
        $this->assign("top_2", $ret[1]);
        //
        //特别推荐
        $englishMediaModel = $englishMediaModel ? $englishMediaModel : D("EnglishMedia");
        $special_media = $englishMediaModel->getSpecialRecommendMediaList();
        $this->assign("special_media", $special_media);
        $this->assign("special_media_num", count($special_media) - 1);

        $this->assign("question", $question);

        $this->assign('isAboutVideo', $isAboutVideo);
        $this->display();
    }

    //ajax获取题目
    /**
     * @author Adam $date2013.08.31$
     * @todo [请求的参数为空的时候的默认，暂时为指定的ID，需要获取默认]
     */
    public function ajax_get_question() {
        if ($this->isAjax()) {
            $ret = array();
            $levelModel = D("EnglishLevel");
            $objectModel = D("EnglishObject");
            $questionModel = D("EnglishQuestion");
            //
            //用户点击历史
            $user_last_select = cookie('english_user_last_select');
            if (!is_array($user_last_select)) {
                $user_last_select = array();
            }
            //
            //接收请求数据
            $viewType = intval($_REQUEST['viewType']); //查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐
            if ($viewType == 0) {
                if (in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4))) {
                    $viewType = intval($user_last_select['viewType']);
                } else {
                    $viewType = 1;
                }
            }
            $voice = intval($_REQUEST['voice']) == 0 ? 1 : intval($_REQUEST['voice']); //口语
            $target = intval($_REQUEST['target']) == 0 ? 1 : intval($_REQUEST['target']); //训练对象
            $pattern = intval($_REQUEST['pattern']) == 0 ? 1 : intval($_REQUEST['pattern']); //类型
            $type = empty($_REQUEST['type']) ? "category" : $_REQUEST['type']; //请求类型
            $now_question_id = intval($_REQUEST['now_question_id']); //当前的题目id
            //不同的浏览方式获取不同的数据
            //
            //特别推荐
            if ($viewType == 4) {
                $media_id = intval($_REQUEST['media_id']);
            } else if ($viewType == 3) {
                $englishMediaRecommendModel = D("EnglishMediaRecommend");
                //推荐id
                $recommend = intval($_REQUEST['recommend']);
                if ($recommend == 0) {
                    if (intval($user_last_select['recommend']) > 0) {
                        $recommend = intval($user_last_select['recommend']);
                    }
                }
                if ($recommend == 0) {
                    $recommend = $englishMediaRecommendModel->getDefaultRecommendId($voice, $target, $pattern);
                } else {
                    $questionNum = $questionModel->getQuestionNum(0, 0, 0, $recommend, 0, $voice, $target, $pattern);
                    if ($questionNum == 0) {
                        $recommend = $englishMediaRecommendModel->getDefaultRecommendId($voice, $target, $pattern);
                    }
                }
                //难度id
                $difficulty = intval($_REQUEST['difficulty']);
                if ($difficulty == 0) {
                    if (in_array(intval($user_last_select['recommendDifficulty']), array(1, 2, 3))) {
                        $difficulty = intval($user_last_select['recommendDifficulty']);
                    } else {
                        $difficulty = $questionModel->getDefaultDifficulty($viewType, 0, $recommend, $voice, $target, $pattern);
                    }
                }
                $questionNum = $questionModel->getQuestionNum(0, 0, 0, $recommend, $difficulty, $voice, $target, $pattern);
                if (intval($questionNum) == 0) {
                    $difficulty = $questionModel->getDefaultDifficulty($viewType, 0, $recommend, $voice, $target, $pattern);
                }
            } else if ($viewType == 2) {
                $subject = intval($_REQUEST['subject']);
                if ($subject == 0) {
                    if (intval($user_last_select['subject']) > 0) {
                        $subject = intval($user_last_select['subject']);
                    } else {
                        $englishMediaSubjectModel = D("EnglishMediaSubject");
                        $subject = D("EnglishMediaSubject")->getDefaultSubjectdId($voice, $target, $pattern);
                    }
                }
                //难度id
                $difficulty = intval($_REQUEST['difficulty']);
                if ($difficulty == 0) {
                    if (in_array(intval($user_last_select['subjectDifficulty']), array(1, 2, 3))) {
                        $difficulty = intval($user_last_select['subjectDifficulty']);
                    } else {
                        $difficulty = $questionModel->getDefaultDifficulty($viewType, $subject, 0, $voice, $target, $pattern);
                    }
                }
                $questionNum = $questionModel->getQuestionNum(0, 0, $subject, 0, $difficulty, $voice, $target, $pattern);
                if (intval($questionNum) == 0) {
                    $difficulty = $questionModel->getDefaultDifficulty($viewType, $subject, 0, $voice, $target, $pattern);
                }
            } else {
                $viewType = 1; //统一为空的浏览方式
                //科目
                $object = intval($_REQUEST['object']);
                if ($object == 0) {
                    if (intval($user_last_select['object']) > 0) {
                        $object = intval($user_last_select['object']);
                    } else {
                        $objectInfo = $objectModel->getDefaultObjectInfo($voice, $target, $pattern);
                        $object = intval($objectInfo['id']) > 0 ? intval($objectInfo['id']) : 1;
                    }
                    //$objectInfo = $objectModel->getInfoById($object);
                }
                //等级
                $level = intval($_REQUEST['level']);
                if ($level == 0) {
                    if (intval($user_last_select['level']) > 0) {
                        $level = intval($user_last_select['level']);
                    } else {
                        $levelInfo = $levelModel->getDefaultLevelInfo($object, $voice, $target, $pattern);
                        $level = intval($levelInfo['id']) > 0 ? intval($levelInfo['id']) : 1;
                    }
                }
                //防止等级下没有试题
                $levelNum = $questionModel->getQuestionNum($object, $level, 0, 0, 0, $voice, $target, $pattern);
                if ($levelNum == 0) {
                    $levelInfo = $levelModel->getDefaultLevelInfo($object, $voice, $target, $pattern);
                    $level = intval($levelInfo['id']) > 0 ? intval($levelInfo['id']) : 1;
                }
            }
            //上下题
            $user_last_question = array();
            $con = array();
            $con["question.status"] = 1;
            if ($type == "quick_select_prev") {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "prev", $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $viewType);
                $con["question.id"] = intval($last_question_info['question_id']);
                $user_last_question = $questionModel->getQuestionWithOption($con);
            } else if ($type == 'quick_select_next') {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "next", $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $viewType);
                $con["question.id"] = intval($last_question_info['question_id']);
                $user_last_question = $questionModel->getQuestionWithOption($con);
            } else if ($type == "category") {
                $viewType = 1; //大类，默认为科目进入
                $ret['object_info'] = $objectModel->getDefaultObjectInfo($voice, $target, $pattern);
                $object = $ret['object_info']['id'];
                $ret['level_info'] = $levelModel->getDefaultLevelInfo($object, $voice, $target, $pattern);
                $level = $ret['level_info']['id'];
                $ret['object_list'] = $objectModel->getObjectListToIndex($voice, $target, $pattern);
                $ret['level_list'] = $levelModel->getLevelListToIndex($object, $voice, $target, $pattern);
            }
            if (!empty($user_last_question)) {
                $user_last_question['object'] = $last_question_info['object'];
                $voice = $user_last_question['voice'];
                $target = $user_last_question['target'];
                $pattern = $user_last_question['pattern'];
                if ($viewType == 3) {
                    $user_last_question['recommend'] = $last_question_info['recommend'];
                    $recommend = $last_question_info['recommend'];
                    $difficulty = $user_last_question['difficulty'];
                } else if ($viewType == 2) {
                    $subject = $user_last_question['subject'];
                    $difficulty = $user_last_question['difficulty'];
                } else if ($viewType == 1) {
                    $object = $user_last_question['object'];
                    $level = $user_last_question['level'];
                }
            }
//            dump($recommend);exit;
            if ($viewType == 3) {
                if ($type == "quick_select_prev" || $type == "switch_view_type") {
                    $englishMediaRecommendModel = $englishMediaRecommendModel ? $englishMediaRecommendModel : D("EnglishMediaRecommend");
                    $ret['recommend_list'] = $englishMediaRecommendModel->getRecommendListToIndex($voice, $target, $pattern);
                    $ret['recommend_difficulty_list'] = $questionModel->getDifficultyList(3, 0, $recommend, $voice, $target, $pattern);
                } elseif ($type == "recommend") {
                    $ret['recommend_difficulty_list'] = $questionModel->getDifficultyList(3, 0, $recommend, $voice, $target, $pattern);
                }
            } else if ($viewType == 2) {
                if ($type == "quick_select_prev" || $type == "switch_view_type") {
                    $englishMediaSubjectModel = $englishMediaSubjectModel ? $englishMediaSubjectModel : D("EnglishMediaSubject");
                    $ret['subject_list'] = $englishMediaSubjectModel->getSubjectListToIndex($voice, $target, $pattern);
                    $ret['subject_difficulty_list'] = $questionModel->getDifficultyList(2, $subject, 0, $voice, $target, $pattern);
                } elseif ($type == "subject") {
                    $ret['subject_difficulty_list'] = $questionModel->getDifficultyList(2, $subject, 0, $voice, $target, $pattern);
                }
            } else if ($viewType == 1) {
                if ($type != "category") {
                    $ret['object_list'] = $objectModel->getObjectListToIndex($voice, $target, $pattern);
                    $ret['level_list'] = $levelModel->getLevelListToIndex($object, $voice, $target, $pattern);
                    $ret['object_info'] = $objectModel->getInfoById($object);
                    $ret['level_info'] = $levelModel->getInfoById($level);
                }
            }
            if (!empty($user_last_question)) {
                $ret['question'] = $user_last_question;
            } else {
                $ret['question'] = $questionModel->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $media_id);
            }
            $ret['viewType'] = $viewType;
            if ($viewType == 4) {
                $englishMediaRecommendModel = $englishMediaRecommendModel ? $englishMediaRecommendModel : D("EnglishMediaRecommend");
                $ret['recommend_list'] = $englishMediaRecommendModel->getRecommendListToIndex($ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern']);
                $ret['recommend_difficulty_list'] = $questionModel->getDifficultyList(3, 0, 0, $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], 0);
            }
            $englishMediaModel = D("EnglishMedia");
            $ret['recommedsQuestionNum'] = $englishMediaModel->getRecommendQuestionNum($target, $voice, $pattern);
            $ret['subjectsQuestionNum'] = $englishMediaModel->getSubjectQuestionNum($target, $voice, $pattern);
            //
            //记录浏览题目
            $englishViewRecordModel = D("EnglishViewRecord");
            if ($viewType == 2) {
                $englishViewRecordModel->addRecord($ret['question']['id'], 0, 0, $ret['question']['subject'], 0, $ret['question']['difficulty'], $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], $viewType);
            } else if ($viewType == 3) {
                $englishViewRecordModel->addRecord($ret['question']['id'], 0, 0, 0, $recommend, $ret['question']['difficulty'], $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], $viewType);
            } else if ($viewType == 1) {
                $englishViewRecordModel->addRecord($ret['question']['id'], $ret['question']['level'], $object, 0, 0, 0, $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], $viewType);
            } else if ($viewType == 4) {
                $englishViewRecordModel->addRecord($ret['question']['id'], 0, 0, 0, 0, 0, $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], $viewType);
            }
            $ret['english_user_info'] = D("EnglishUserInfo")->getEnglishUserInfo();
            //获取用户统计信息
            $englishUserCountModel = D("EnglishUserCount");
            $ret['user_count_info'] = array();
            if ($viewType == 4) {
                $ret['user_count_info'] = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, 0, 0, $voice, $target);
            } else if ($viewType == 3) {
                $ret['user_count_info'] = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, $recommend, $ret['question']['difficulty'], $voice, $target);
            } else if ($viewType == 2) {
                $ret['user_count_info'] = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, $subject, 0, $ret['question']['difficulty'], $voice, $target);
            } else if ($viewType == 1) {
                $ret['user_count_info'] = $englishUserCountModel->getEnglishUserCountInfo($viewType, $object, $level, 0, 0, 0, $voice, $target);
            }

            //
            //保存历史记录
            $user_last_select['voice'] = $voice;
            $user_last_select['target'] = $target;
            $user_last_select['pattern'] = $pattern;
            $user_last_select['viewType'] = $viewType;
            if ($viewType == 4) {
                $user_last_select['media_id'] = intval($media_id);
            } else if ($viewType == 3) {
                $user_last_select['recommend'] = intval($recommend);
                $user_last_select['recommendDifficulty'] = intval($ret['question']['difficulty']);
            } else if ($viewType == 2) {
                $user_last_select['subject'] = intval($subject);
                $user_last_select['subjectDifficulty'] = intval($ret['question']['difficulty']);
            } else if ($viewType == 1) {
                $user_last_select['object'] = $object;
                $user_last_select['level'] = $level;
            }
            cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30); // 存储用户点击历史
            //media_url为空，则进行视频解析
            if (!$ret['question']['play_code']) {
                //视频解析库
                import("@.ORG.VideoHooks");
                $videoHooks = new VideoHooks();

                $ret['question']['media_source_url'] = trim(str_replace(' ', '', $ret['question']['media_source_url']));
                $videoInfo = $videoHooks->analyzer($ret['question']['media_source_url']);

                $play_code = $videoInfo['swf'];

                $media_thumb_img = $videoInfo['img'];

                //解析成功，保存视频解析地址
                if (!$videoHooks->getError() && $play_code) {

                    $play_type = $videoInfo['media_type'];
                    $saveData = array(
                        'id' => $ret['question']['media_id'],
                        'play_code' => $play_code,
                        'media_thumb_url' => $media_thumb_img,
                        'play_type' => $play_type
                    );

                    $englishMediaModel->save($saveData);
//                    $questionModel->where('id=' . $ret['question']['id'])->save($saveData);
                }

                $ret['question']['media_thumb_url'] = $media_thumb_img;
                $ret['question']['play_code'] = $play_code;
                $ret['question']['play_type'] = $play_type;
            }

            //判断是否为about.com视频
            $isAboutVideo = 0;
            if (strpos($ret['question']['media_source_url'], 'http://vedio.about.com') !== FALSE) {
                $isAboutVideo = 1;

                //about.com视频修改自动播放为false
                //$ret['question']['media_url'] = str_replace('&autoStart=true', '&autoStart=false', $ret['question']['media_url']);
            }

            if ($ret['question']['play_code']) {
                if (strpos($ret['question']['media_source_url'], 'britishcouncil.org') !== FALSE) {
                    $ret['question']['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $ret['question']['play_code']);
                    $ret['question']['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $ret['question']['play_code']);
                }
                $ret['question']['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $ret['question']['play_code']);
            }

            /*
              if ($ret['question']['play_type'] && !$ret['question']['play_code']) {
              $ret['question']['play_code'] ? $ret['question']['play_code'] = $ret['question']['play_code'] : '';
              }
             * */

            $ret['question']['isAboutVideo'] = $isAboutVideo;

            $this->ajaxReturn($ret, "请求成功", true);
        }
    }

    //回答问题
    public function answer_question() {

        $question_id = intval($_REQUEST['question_id']);
        $select_option = intval($_REQUEST['select_option']);
        $viewType = in_array(intval($_REQUEST['viewType']), array(1, 2, 3, 4)) ? intval($_REQUEST['viewType']) : 1;
        $questionModel = D("EnglishQuestion");
        //更新题目被答次数
        $questionModel->addQuestionAnswerNum($question_id);
        //被答题信息
        $question_info = $questionModel->getInfoById($question_id);
        $question_info['real_object'] = $question_info['object']; //题目真实科目
        $question_info['now_object'] = intval($_REQUEST['object']); //当前请求的科目
        $question_info['real_recommend'] = $question_info['recommend']; //题目的全部推荐
        $question_info['now_recommend'] = intval($_REQUEST['recommend']); //当前请求的推荐
        $is_correct = false;
        if ($select_option === intval($question_info['answer'])) {
            $is_correct = true;
        }

        //初始化返回数组
        $data = array();
        $data['level_up'] = false; //是否升级
        $englishRecordModel = D("EnglishRecord");
        $record_info = $englishRecordModel->getQuestionUserRecord($question_id, $viewType);
        //用户统计信息
        $englishUserCountModel = D("EnglishUserCount");
        $user_count_info = array();
        if ($viewType == 4) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, 0, 0, $question_info ['voice'], $question_info['target']);
        } else if ($viewType == 3) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, 0, $question_info['now_recommend'], $question_info['difficulty'], $question_info ['voice'], $question_info['target']);
        } else if ($viewType == 2) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, 0, 0, $question_info['subject'], 0, $question_info['difficulty'], $question_info ['voice'], $question_info['target']);
        } else if ($viewType == 1) {
            $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($viewType, $question_info['now_object'], $question_info['level'], 0, 0, 0, $question_info ['voice'], $question_info['target']);
        }
        //用户英语角信息
        $englishUserInfoModel = D("EnglishUserInfo");
        $english_user_info = $englishUserInfoModel->getEnglishUserInfo();

        $english_user_info['test_num']++; //增加用户答题总数量

        if ($is_correct) {
            $english_user_info['correct_num']++; //增加用户答题总正确数量
            //
            //未做过的题目
            if ($record_info['test_num'] == 0) {
                $english_user_info['total_rice'] = $english_user_info['total_rice'] + 100; //更新用户总大米数
                $user_count_info['right_num']++; //统计信息表答题题目数增加
                $user_count_info['rice'] = $user_count_info ['rice'] + 100; //统计信息表大米数增加
                //判断是否答对10题，是则升级
                if ($viewType == 1) {
                    if ($user_count_info['right_num'] >= 10) {
                        $data['level_up'] = true;
                        $user_count_info['right_num'] = 10;
                    }
                }
            } else {
                //做的次数小于等于1
                if ($record_info['right_num'] <= 1) {
                    $english_user_info['total_rice'] = $english_user_info['total_rice'] + 100; //更新用户总大米数
                }
            }
            $user_count_info['continue_error_num'] = 0; //统计信息表连续错误数量置零
            $user_count_info['continue_right_num']++; //统计信息表连续正确数增加
        } else {
            $english_user_info['error_num']++; //增加答题错误数
            $user_count_info['continue_error_num']++; //统计信息表连续错误数增加
            $user_count_info['continue_right_num'] = 0; //统计信息表连续正确数置零
            //连错两题
            if ($user_count_info['continue_error_num'] == 2) {
                $english_user_info['total_rice'] = $english_user_info['total_rice'] - 100; //扣除100总大米
                $user_count_info['rice'] = $data ['rice'] - 100; //统计信息表扣除大米
                $user_count_info['right_num']--; //统计信息表等级做对题目扣1
                $user_count_info['continue_error_num'] = 0; //统计信息表连错两题，连错归零
            }
        }
        if ($english_user_info['total_rice'] < 0) {
            $english_user_info['total_rice'] = 0;
        }
        if ($user_count_info ['rice'] < 0) {
            $user_count_info['rice'] = 0;
        }
        if ($user_count_info['right_num'] < 0) {
            $user_count_info['right_num'] = 0;
        }
        $englishUserCountModel->saveEnglishUserCountInfo($user_count_info);

        if ($data['level_up']) {
            $best = $englishUserInfoModel->getEnglishUserBest();
            $english_user_info['best_object'] = $best['object_id'];
            $english_user_info['best_level'] = $best['level_id'];
            $english_user_info['best_object_name'] = $best['object_name'];
            $english_user_info['best_level_name'] = $best['level_name'];
        }

        $englishUserInfoModel->saveEnglishUserInfo($english_user_info);

        $data['user_count_info'] = $user_count_info;
        $data['english_user_info'] = $english_user_info;
        $data['question_info'] = $question_info;

        $data['english_user_record'] = $englishRecordModel->record($question_info, $select_option); //记录用户的答题记录
        if ($viewType == 4) {
            $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum(0, 0, 0, 0, 0, $question_info ['voice'], $question_info['target'], $question_info['pattern'], 1);
        } else if ($viewType == 3) {
            $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum(0, 0, 0, $question_info['now_recommend'], $question_info['difficulty'], $question_info ['voice'], $question_info['target'], $question_info['pattern']);
        } else if ($viewType == 2) {
            $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum(0, 0, $question_info['subject'], 0, $question_info['difficulty'], $question_info ['voice'], $question_info['target'], $question_info['pattern']);
        } else if ($viewType == 1) {
            $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($question_info['now_object'], $question_info['level'], 0, 0, 0, $question_info ['voice'], $question_info['target'], $question_info['pattern']);
        }
        $this->ajaxReturn($data, "请求成功", true);
    }

    //重新开始某个等级
    public function restart_level() {
        if ($this->isAjax()) {
            $voice = intval($_REQUEST['voice']); //口语
            $target = intval($_REQUEST['target']); //训练对象
            $object = intval($_REQUEST['object']); //科目
            $level = intval($_REQUEST['level']); //等级
            D("EnglishUserCount")->resetRightNum($voice, $target, $object, $level);
            $this->ajaxReturn("", "操作成功", true);
        }
    }

    public function get_top_user() {
        if ($this->isAjax()) {
            $type = $_REQUEST['type'];
            $ret = D("EnglishUserInfo")->getTopUserListByTypeName($type);
            if (empty($ret) || false === $ret) {
                $this->ajaxReturn('', "结果空", false);
            } else {
                $this->ajaxReturn($ret, "请求成功", true);
            }
        }
    }

    /**
     * 视频反馈报错
     * 
     * @param type: 反馈类型 0=>错误 1=>建议
     * @param question_id: 试题ID
     * @param media_html: 视频播放区域html($('.video_div').html())
     * 
     * @author slate date:2013-08-07
     */
    public function feedback() {

        if ($this->isAjax()) {

            $type = intval($_POST['type']);
            $question_id = intval($_POST['question_id']);
            $media_html = trim($_POST['media_html']);

            $data = array(
                'member_id' => intval($_SESSION[C('MEMBER_AUTH_KEY')]),
                'type' => $type,
                'question_id' => $question_id,
                'media_html' => $media_html,
                'create_time' => time()
            );

            D("EnglishFeedback")->add($data);

            $this->ajaxReturn('', true);
        }
    }

    public function match_media() {

        set_time_limit(0);

        import("@.ORG.VideoHooks");

        $englishQuestionModel = new EnglishQuestionModel();
        $questionList = $englishQuestionModel->where(" id > 5491")->getField('`id`, `media_text_url`');

        $videoHooks = new VideoHooks();

        $startTime = time();

        foreach ($questionList as $id => $url) {

            $videoInfo = array();

            $url = trim(str_replace(' ', '', $url));
            $videoInfo = $videoHooks->analyzer($url);

            $media_url = $videoInfo['swf'];

            $media_img_url = $videoInfo['img'];

            $media_type = $videoInfo['media_type'];

            //解析成功，保存视频解析地址
            if ($media_url) {

                $saveData = array(
                    'media_img_url' => $media_img_url,
                    'media_type' => $media_type
                );

                if ($media_type) {

                    $saveData['media'] = $media_url;
                } else {

                    $saveData['media_url'] = $media_url;
                }

                $englishQuestionModel->where("id=$id")->save($saveData);
            } else {
                var_dump($id);
            }
            sleep(1);
        }

        $endTime = time();

        echo 'start:' . date('Y-m-d H:i:s', $startTime) . '</br>';
        echo 'end:' . date('Y-m-d H:i:s', $endTime);
        exit();
    }

    /**
     * flash请求单词信息方法，测试方法
     * @todo 测试方法，请勿使用
     */
    public function requestionWordInfo() {
        $word = $this->_request("word"); //获取文字
        $word_info = array();
        if (!empty($word)) {
            $word_info['word'] = $word;
            $url = str_replace("###", $word, C("ENGLISH_DICT_SEARCH_URL")); //词典地址
            $html = file_get_contents($url); //获取html内容
            //
            //preg获取单词解释
            preg_match('/<div class="trans-container">\s+<ul>((\s*<li>.*<\/li>\s*)+)<\/ul>/i', $html, $match);
            $temp_arr = explode("<li>", preg_replace("/^\s+/", "", $match[1]));
            foreach ($temp_arr as $key => $value) {
                if (!empty($value)) {
                    $trans[] = current(explode("</li>", $value));
                }
            }
            $word_info['intro'] = implode(";", $trans);
            //
            //单词读音地址
            $word_info['speekurl'] = str_replace("###", $word, C("ENGLISH_DICT_SPEAK_URL"));
            $word_info['type'] = "名词"; //@todo 词性，但是一般词性多个无，如何处理
            //
            //单词的例句
            preg_match('/<div id=\"authority\".*\s+<ul.*\s+<li>\s+<p>\s?(.*)/i', $html, $match);
            $word_info['example'] = strip_tags($match[1]); //去除html标签
            //
            //音标
            preg_match('/<span class="phonetic">\[(.*)\]<\/span>/i', $html, $match);
            //音标的编码转换
            import("@.ORG.HtmlEncode");
            $obj = new HtmlEncode();
            $phonetic = $obj->encode($match[1]);
            //使用占位符确定音标的分隔个数
            $temp_str = preg_replace("/\&\#.*?;/", "#", $phonetic);
            preg_match_all("/\&\#.*?;/", $phonetic, $encode_match);
            $index = 0;
            $word_info['phsyclips'] = array();
            for ($i = 0; $i < strlen($temp_str); $i++) {
                $value = substr($temp_str, $i, 1);
                if ($value == "#") {
                    $value = html_entity_decode($encode_match[0][$index]);
                    $index++;
                }
                $word_info['phsyclips'][$i]['phsy'] = $value; //单个音标
                $word_info['phsyclips'][$i]['phsyurl'] = $value; //音标读音url
            }
            //
            echo json_encode($word_info);
            exit;
        }
    }

    /**
     * @todo 测试方法，请勿使用
     */
    public function speakScore() {
        $record = $this->_post("mp3data");
        $question_id = $this->_post("questionid");
        $senetence_id = $this->_post("clipid");
        //
        //
            $record = base64_decode($record);
        //
        //保存音频
        $recordFile = "./Public/Uploads/English/record/" . uniqid() . ".wav";
//        $recordFile = "./Public/test.wav";
        $fp = fopen($recordFile, w);
        fwrite($fp, $record);
        fclose($fp);
        //
        //请求google分数
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 Chromium/18.0.1025.168 Chrome/18.0.1025.168 Safari/535.19');
        curl_setopt($ch, CURLOPT_URL, C("ENGLISH_SPEECH_API_URL"));
        $header = array(
            "Content-Type:audio/L16;rate=22050"
        );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $record);
        $str = curl_exec($ch);
        curl_close($ch);
        $ret = objectToArray(json_decode($str)); //获取结果为数组
        $googleRet = array(); //获取需要的数据
        $senetenceInfo = D("EnglishQuestionSpeakSentence")->find($senetence_id);
        $senetenceInfo['content'] = preg_replace('/.$/', "", $senetenceInfo['content']);
        if (!empty($ret['hypotheses'])) {
            $googleRet['confidence'] = $ret['hypotheses'][0]['confidence'];
            $googleRet['utterance'] = $ret['hypotheses'][0]['utterance'];
            $googleRet['flag'] = strtolower(ftrim($googleRet['utterance'])) == strtolower(ftrim($senetenceInfo['content'])) ? 1 : 0;
        } else {
            $googleRet['confidence'] = 0;
            $googleRet['flag'] = 0;
        }
        //
        //生成标准音特征矢量
        $standard_audio_name = current(explode(".", $senetenceInfo['standard_audio']));
        $standard_audio_vec = "./Public/Uploads/Video" . $standard_audio_name . ".vec"; //标准音特征矢量文件
        //不存在特征矢量文件，则先生成
        if (!file_exists($standard_audio_vec)) {
            $standard_audio = "./Public/Uploads/Video" . $senetenceInfo['standard_audio']; //标准音
            ///mnt/oral/python
            //
            exec("python /mnt/oral/python/python/compute-vectors.py " . $standard_audio . " " . $standard_audio_vec); //生成特征矢量文件算法
            //python ./Extend/ryan/python/compute-vectors.py ./Public/Uploads/Video/1.wav ./Public/Uploads/Video/1.vec
        }
        //
        //评分
        exec("python /mnt/oral/python/oral-evaluation.py " . $standard_audio_vec . " " . $recordFile . ' "' . $senetenceInfo['content'] . '" "' . $googleRet['utterance'] . '" ' . $googleRet['confidence'], $score_ret);
        //echo "python ./Extend/python/oral-evaluation.py " . $standard_audio_vec . " " . $recordFile . ' "' . $senetenceInfo['content'] . '" "' . $googleRet['utterance'] . '" ' . $googleRet['confidence'];exit;
        $score = intval($score_ret[0]);
        if ($score == 4) {
            $score = 10;
        } else if ($score == 3) {
            $score = 50;
        } else if ($score == 2) {
            $score = 75;
        } else if ($score == 1) {
            $score = 95;
        }
        @unlink($recordFile);
        echo $score;
        exit;
    }

    /**
     * @author Adam $date2013-08-13$
     * @todo 测试方法，勿用
     */
    public function testPython() {
        header("Content-type: text/html; charset=utf-8");
//        $audio_path = './Public/Uploads/Video/1.wav';
        echo "测试Python算法生成特征矢量：";
        exec("python /home/ryan/python/compute-vectors.py ./Public/Uploads/Video/1.wav ./Public/Uploads/Video/1.vec");
//        echo "python /home/ryan/python/compute-vectors.py ./Public/Uploads/Video/1.wav ./Public/Uploads/Video/1.vec";
        $time = time();
        exec("python /home/ryan/python/oral-evaluation.py ./Public/Uploads/Video/1.vec ./Public/test.wav", $ret);

        echo "<br />time:" . ( time() - $time );
        dump($ret);
//        oral-evaluation.py 1jade.vec wav/Adam.wav
        exit;
    }

    /**
     * 获取媒体信息到flash
     */
    public function requestionMediaInfo() {
        header("Content-type: text/html; charset=utf-8");
        $media_id = $this->_request("mediaId");
        $question_id = $this->_request("questionId");
        $englishMediaModel = D("EnglishMedia");
        $meida_info = $englishMediaModel->getMediaInfo($media_id);
        $meida_info['sentences'] = D("EnglishQuestionSpeakSentence")->getSpeakQuestionSentenceList($question_id); //说力跟读句子信息
        $meida_info['question_id'] = $question_id;
        $ret = $englishMediaModel->formatMediaInfo($meida_info); //给flash的JSON数据封装
        if (intval($this->_request("playerMode")) == 3) {
            $ret['clips'] = $ret['sentences'];
        }
        die(json_encode($ret));
    }

    /**
     * 初始化用户的历史选择，防止用户上次选择的不存在题目
     * @autor Adam $date2013.09.10$
     */
    protected function initUserHistorySelect() {
        $user_last_select = cookie('english_user_last_select');
        $num = 0;
        $englishMediaModel = D("EnglishMedia");
        $viewType = in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4)) ? intval($user_last_select['viewType']) : 1; //查看方式，1科目等级，2专题难度，3推荐
        if ($viewType == 4) {
            $map = array();
            $map['media.status'] = 1;
            $map['question.status'] = 1;
            $map['media_id'] = intval($user_last_select['media_id']);
            $num = $englishMediaModel->alias("media")->join(C("DB_PREFIX") . "english_question question on question.media_id=media.id")->where($map)->count("questiong.id");
        } else {
            $voice = in_array(intval($user_last_select['voice']), array(1, 2)) ? intval($user_last_select['voice']) : 1;
            $target = in_array(intval($user_last_select['target']), array(1, 2)) ? intval($user_last_select['target']) : 1;
            $pattern = in_array(intval($user_last_select['pattern']), array(1, 2)) ? intval($user_last_select['pattern']) : 1;
            if ($viewType == 3) {
                $num = $englishMediaModel->getRecommendQuestionNum($target, $voice, $pattern);
            } else if ($viewType == 2) {
                $num = $englishMediaModel->getSubjectQuestionNum($target, $voice, $pattern);
            }
        }
        if (intval($num) == 0) {
            $user_last_select['viewType'] = 1;
            if ($viewType == 4) {
                $user_last_select['media_id'] = 0;
            }
        }
        cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30);
    }

}

?>
