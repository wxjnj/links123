<?php

/*
 * 英语角首页控制类
 * @author adam 2013.5.28
 */

class IndexAction extends EnglishAction {

    public function index() {
        //视频解析库
        import("@.ORG.VideoHooks");

        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON', false);
        $objectModel = D("EnglishObject");
        $levelModel = D("EnglishLevel");
        $questionModel = D("EnglishQuestion");
        //记录用户上次选择的数组
        $user_last_select = array();
        $user_last_select = cookie('english_user_last_select');
        if (!is_array($user_last_select)) {
            $user_last_select = array();
        }
        //默认情况
        $user_last_select['voice'] = intval($user_last_select['voice']) > 0 ? intval($user_last_select['voice']) : 1;
        $user_last_select['target'] = intval($user_last_select['target']) > 0 ? intval($user_last_select['target']) : 1;
        $user_last_select['pattern'] = intval($user_last_select['pattern']) > 0 ? intval($user_last_select['pattern']) : 1;

        //默认的科目列表
        $object_list = $objectModel->getObjectListToIndex($user_last_select['voice'], $user_last_select['target'], $user_last_select['pattern']);

        //用户上次选择的科目
        $user_last_select['object'] = intval($user_last_select['object']);
        if ($user_last_select['object'] == 0) {
            $user_last_select['object_info'] = $objectModel->getDefaultObjectInfo($user_last_select['voice'], $user_last_select['target'], $user_last_select['pattern']);
        } else {
            $user_last_select['object_info'] = $objectModel->getInfoById($user_last_select['object']);
        }
        $user_last_select['object'] = intval($user_last_select['object_info']['id']) > 0 ? intval($user_last_select['object_info']['id']) : $user_last_select['object'];
        //用户上次选择的等级
        $user_last_select['level'] = intval($user_last_select['level']);
        if ($user_last_select['level'] == 0) {
            $user_last_select['level_info'] = $levelModel->getDefaultLevelInfo($user_last_select['level'], $user_last_select['voice'], $user_last_select['target'], $user_last_select['pattern']);
        } else {
            $user_last_select['level_info'] = $levelModel->getInfoById($user_last_select['level']);
        }

        //默认的等级列表
        $level_list = $levelModel->getLevelListToIndex($user_last_select['object'], $user_last_select['voice'], $user_last_select['target'], $user_last_select['pattern']);
        $ret = array();
        foreach ($level_list as $value) {
            $ret[$value['id']] = $value;
        }
        //确保上次选择的等级下拥有题目
        if ($ret[$user_last_select['level']]['question_num'] == 0) {
            foreach ($level_list as $value) {
                if ($value['question_num'] > 0) {
                    $user_last_select['level_info'] = $levelModel->getInfoById($value['id']);
                    break;
                }
            }
        }
        $user_last_select['level'] = $user_last_select['level_info']['id'];

        $this->assign("user_last_select", $user_last_select);

        //获取题目
        $question = $questionModel->getQuestionToIndex($user_last_select['object'], $user_last_select['level'], $user_last_select['voice'], $user_last_select['target'], $user_last_select['pattern']);
        //获取用户英语角信息
        $englishUserInfoModel = D("EnglishUserInfo");
        $english_user_info = $englishUserInfoModel->getEnglishUserInfo();
        $this->assign("english_user_info", $english_user_info);
        if ($user_last_select['object_info']['name'] == "综合") {
            D("EnglishViewRecord")->addRecord($question['id'], $user_last_select['object_info']['id']); //记录用户查看题目
        } else {
            D("EnglishViewRecord")->addRecord($question['id'], $question['object']); //记录用户查看题目
        }

        $englishUserCountModel = D("EnglishUserCount");
        $user_conut_info = $englishUserCountModel->getEnglishUserCountInfo($user_last_select['voice'], $user_last_select['target'], $user_last_select['object'], $user_last_select['level']);
        $this->assign("user_conut_info", $user_conut_info);

        //media_url为空，则进行视频解析
        if (!$question['media_url'] && !$question['media']) {

            $videoHooks = new VideoHooks();

            $question['media_text_url'] = trim(str_replace(' ', '', $question['media_text_url']));
            $videoInfo = $videoHooks->analyzer($question['media_text_url']);
			
            $media_url = $videoInfo['swf'];

            $media_img_url = $videoInfo['img'];

            //解析成功，保存视频解析地址
            if (!$videoHooks->getError() && $media_url) {

                $media_type = $videoInfo['media_type'];
                $saveData = array(
                    'media_img_url' => $media_img_url,
                    'media_type' => $media_type
                );

                if ($media_type) {

                    $saveData['media'] = $media_url;
                    $question['media'] = $media_url;
                } else {

                    $saveData['media_url'] = $media_url;
                }
                $questionModel->where('id=' . $question['id'])->save($saveData);
            }

            $question['media_url'] = $media_url;

            $question['media_img_url'] = $media_img_url;

            $question['media_type'] = $media_type;
        }

        //判断是否为about.com视频
        $isAboutVideo = 0;
        if (strpos($question['media_url'], 'http://c.brightcove.com') !== FALSE) {
            $isAboutVideo = 1;

            //about.com视频修改自动播放为false
            //$question['media_url'] = str_replace('&autoStart=true', '&autoStart=false', $question['media_url']);
        }
        if ($question['media']) {
            if (strpos($question['media_text_url'], 'britishcouncil.org') !== FALSE) {
                $question['media'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $question['media']);
                $question['media'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $question['media']);
            }
            $question['media'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $question['media']);
        }
        
        if ($question['media_type'] && !$question['media']) {
        	$question['media_url'] ? $question['media'] = $question['media_url'] : '';
        }

        //排行榜数据
        $ret = $englishUserInfoModel->getTopUserListByTypeName("object_综合");

        $this->assign("top_1", $ret[0]);
        $this->assign("top_2", $ret[1]);

        $this->assign("question", $question);
        $this->assign("level_list", $level_list);
        $this->assign("object_list", $object_list);

        $this->assign('isAboutVideo', $isAboutVideo);
        $this->display();
    }

    //ajax获取题目
    public function ajax_get_question() {

        //视频解析库
        import("@.ORG.VideoHooks");

        if ($this->isAjax()) {
            $levelModel = D("EnglishLevel");
            $objectModel = D("EnglishObject");
            $questionModel = D("EnglishQuestion");
            $type = empty($_REQUEST['type']) ? "category" : $_REQUEST['type']; //请求类型
            $user_last_question = array(); //用户上一题
//        $id = intval($_REQUEST['id']);
            $now_question_id = intval($_REQUEST['now_question_id']);
            $con = array();
            $con["status"] = 1;
            if ($type == "quick_select_prev") {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "prev");
                $con["id"] = intval($last_question_info['question_id']);
                $user_last_question = $questionModel->getQuestionWithOption($con);
            } else if ($type == 'quick_select_next') {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "next");
                $con["id"] = intval($last_question_info['question_id']);
                $user_last_question = $questionModel->getQuestionWithOption($con);
            }
            if (!empty($user_last_question)) {
                $user_last_question['object'] = $last_question_info['object'];
                $voice = $user_last_question['voice'];
                $target = $user_last_question['target'];
                $pattern = $user_last_question['pattern'];
                $object = $user_last_question['object'];
                $level = $user_last_question['level'];
            } else {
                $voice = intval($_REQUEST['voice']) == 0 ? 1 : intval($_REQUEST['voice']); //口语
                $target = intval($_REQUEST['target']) == 0 ? 1 : intval($_REQUEST['target']); //训练对象
                $pattern = intval($_REQUEST['pattern']) == 0 ? 1 : intval($_REQUEST['pattern']); //类型
                $object = intval($_REQUEST['object']) == 0 ? $objectModel->getDefaultObjectInfo($voice, $target, $pattern) : intval($_REQUEST['object']); //科目
                $level = intval($_REQUEST['level']) == 0 ? $levelModel->getDefaultLevelInfo($object, $voice, $target, $pattern) : intval($_REQUEST['level']); //等级
            }
            /* 存储用户点击历史  开始 */
            $user_last_select = array();
            $user_last_select['object'] = $object;
            $user_last_select['level'] = $level;
            $user_last_select['voice'] = $voice;
            $user_last_select['target'] = $target;
            $user_last_select['pattern'] = $pattern;
            cookie('english_user_last_select', $user_last_select);
            /* 存储用户点击历史  结束 */

            $ret = array();
            if ($type == "category") {
                $ret['object_list'] = $objectModel->getObjectListToIndex($voice, $target, $pattern);
                $ret['level_list'] = $levelModel->getLevelListToIndex($object, $voice, $target, $pattern);
            } else if ($type == "object" || $type == "quick_select_prev" || $type == "quick_select_next") {
                $ret['level_list'] = $levelModel->getLevelListToIndex($object, $voice, $target, $pattern);
            }
            $ret['object_info'] = $objectModel->getInfoById($object);
            $ret['level_info'] = $levelModel->getInfoById($level);
            //记录上次选择等级，如等级下题目数为空则获取默认等级
            $levelNum = $questionModel->getQuestionNum($object, $level, $voice, $target, $pattern);
            if ($levelNum == 0) {
                $ret['level_info'] = $levelModel->getDefaultLevelInfo($object, $voice, $target, $pattern);
                $level = intval($ret['level_info']['id']);
            }

            if (!empty($user_last_question)) {
                $ret['question'] = $user_last_question;
            } else {
                $ret['question'] = $questionModel->getQuestionToIndex($object, $level, $voice, $target, $pattern);
            }
            D("EnglishViewRecord")->addRecord($ret['question']['id'], $object); //记录用户查看题目
            $ret['english_user_info'] = D("EnglishUserInfo")->getEnglishUserInfo();
            $ret['user_count_info'] = D("EnglishUserCount")->getEnglishUserCountInfo($voice, $target, $object, $level);

            //media_url为空，则进行视频解析
            if (!$ret['question']['media_url'] && !$ret['question']['media']) {

                $videoHooks = new VideoHooks();

                $ret['question']['media_text_url'] = trim(str_replace(' ', '', $ret['question']['media_text_url']));
                $videoInfo = $videoHooks->analyzer($ret['question']['media_text_url']);

                $media_url = $videoInfo['swf'];

                $media_img_url = $videoInfo['img'];

                //解析成功，保存视频解析地址
                if (!$videoHooks->getError() && $media_url) {

                    $media_type = $videoInfo['media_type'];
                    $saveData = array(
                        'media_img_url' => $media_img_url,
                        'media_type' => $media_type
                    );

                    if ($media_type) {

                        $saveData['media'] = $media_url;
                        $ret['question']['media'] = $media_url;
                    } else {

                        $saveData['media_url'] = $media_url;
                    }
                    $questionModel->where('id=' . $ret['question']['id'])->save($saveData);

                }

                $ret['question']['media_img_url'] = $media_img_url;

                $ret['question']['media_url'] = $media_url;
                $ret['question']['media_type'] = $media_type;
            }

            //判断是否为about.com视频
            $isAboutVideo = 0;
            if (strpos($ret['question']['media_url'], 'http://c.brightcove.com') !== FALSE) {
                $isAboutVideo = 1;

                //about.com视频修改自动播放为false
                //$ret['question']['media_url'] = str_replace('&autoStart=true', '&autoStart=false', $ret['question']['media_url']);
            }

            if ($ret['question']['media']) {
                if (strpos($ret['question']['media_text_url'], 'britishcouncil.org') !== FALSE) {
                    $ret['question']['media'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $ret['question']['media']);
                    $ret['question']['media'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $ret['question']['media']);
                }
                $ret['question']['media'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $ret['question']['media']);
            }
            
            if ($ret['question']['media_type'] && !$ret['question']['media']) {
            	$ret['question']['media_url'] ? $ret['question']['media'] = $ret['question']['media_url'] : '';
            }
            
            $ret['question']['isAboutVideo'] = $isAboutVideo;

            $this->ajaxReturn($ret, "请求成功", true);
        }
    }

    //回答问题
    public function answer_question() {

        $question_id = intval($_REQUEST['question_id']);
        $select_option = intval($_REQUEST['select_option']);
        $questionModel = D("EnglishQuestion");
        $questionModel->addQuestionAnswerNum($question_id); //更新题目被答次数
        $question_info = $questionModel->getInfoById($question_id); //被答题信息
        $question_info['real_object'] = $question_info['object']; //题目真实科目
        $question_info['object'] = intval($_REQUEST['object']); //当前请求的科目
        $is_correct = false;
        if ($select_option === intval($question_info['answer'])) {
            $is_correct = true;
        }

        //初始化返回数组
        $data = array();
        $data['level_up'] = false; //是否升级
        $englishRecordModel = D("EnglishRecord");
        $record_info = $englishRecordModel->getQuestionUserRecord($question_id);
        //用户统计信息
        $englishUserCountModel = D("EnglishUserCount");
        $user_count_info = $englishUserCountModel->getEnglishUserCountInfo($question_info['voice'], $question_info['target'], $question_info['object'], $question_info['level']);
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
                $user_count_info['rice'] = $user_count_info['rice'] + 100; //统计信息表大米数增加
                //判断是否答对10题，是则升级
                if ($user_count_info['right_num'] >= 10) {
                    $data['level_up'] = true;
                    $user_count_info['right_num'] = 10;
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

                $user_count_info['rice'] = $data['rice'] - 100; //统计信息表扣除大米
                $user_count_info['right_num']--; //统计信息表等级做对题目扣1
                $user_count_info['continue_error_num'] = 0; //统计信息表连错两题，连错归零
            }
        }
        if ($english_user_info['total_rice'] < 0) {
            $english_user_info['total_rice'] = 0;
        }
        if ($user_count_info['rice'] < 0) {
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
        $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum($question_info['object'], $question_info['level'], $question_info['voice'], $question_info['target'], $question_info['pattern']);
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
}

?>
