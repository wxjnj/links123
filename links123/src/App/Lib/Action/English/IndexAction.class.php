<?php

/*
 * @desc 英语角首页控制类
 * @author adam 2013.5.28
 * @modify tellen 2013.9.15 action（web层面）限制只能调用logic层面的接口
 */

class IndexAction extends EnglishAction {
	
	//获取本级大米数、累计大米数
	public function get_price(){

		if (isset($_SESSION[C('MEMBER_AUTH_KEY')]))
			$sessionkey = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
		else {
			$sessionkey = 0;
			$cookieInfo = unserialize(cookie("english_user_info"));
		}
		
		if ($this->isAjax()) {
			$user_info = $this->englishUserLogic->getEnglishUserInfo($sessionkey , $cookieInfo);
		
			$user_count = $this->englishUserLogic->getEnglishUserCount(cookie('english_user_last_select'));
			
			$data = array('user_count_info'=>$user_count,'english_user_info' => $user_info);
		    $this->ajaxReturn($data, "操作成功", true);
		}
	}
	
	/**
	 * @desc 获取分类信息  @todo切换
	 */
	public function get_category(){

		 //用户点击历史
        $user_last_select = cookie('english_user_last_select');
        if (!is_array($user_last_select)) {
            $user_last_select = array();
        }
        
        $viewType = intval($_POST['viewType']); //查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐
        ($viewType == 0) ? $viewType = intval($user_last_select['viewType']) : $viewType=1;    
        $post_data = array(
        				'viewType' => $viewType,
        				'voice' => max(1, intval($_POST['voice'])),
        				'target' => max(1, intval($_POST['target'])),
        				'pattern' => max(1, intval($_POST['pattern'])),
        				'type' => empty($_POST['type']) ? "category" : $_POST['type'],
        				'now_question_id' => intval($_POST['now_question_id']),
        				'media_id' => isset($_POST['media_id']) ? $_POST['media_id'] :0,
            		 );
		if ($this->isAjax()) {
			
		}
	}
	
    public function index() {
       	//保证输出不受静态缓存影响
        C('HTML_CACHE_ON', false);
		$this->initUserHistorySelect();
        //记录用户上次选择的数组
        $user_last_select = cookie('english_user_last_select');

        if (!is_array($user_last_select)){
            $user_last_select = array();
        }  
        $user_last_select['viewType'] = in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4)) ? intval($user_last_select['viewType']) : 1; //查看方式，1科目等级，2专题难度，3推荐
        $user_last_select['voice'] = in_array(intval($user_last_select['voice']), array(1, 2)) ? intval($user_last_select['voice']) : 1;
        $user_last_select['target'] = in_array(intval($user_last_select['target']), array(1, 2)) ? intval($user_last_select['target']) : 1;
        $user_last_select['pattern'] = in_array(intval($user_last_select['pattern']), array(1, 2)) ? intval($user_last_select['pattern']) : 1;
        
        //根据用户浏览器cookie信息判断调用不同的logic接口返回可选课程等
		$ret = $this->englishCategoryLogic->getMediaSetList($user_last_select);
        if (is_array($ret)){
        	foreach ($ret AS $key => $val){
        		$this->assign($key, $val);
        	}
        }
        		
        //获取题目&解析视频url
       $retQues = $this->englishTopicLogic->getQuestion($user_last_select,$user_last_select['voice'],$user_last_select['target'],$user_last_select['pattern']);
        if (is_array($retQues)){
        	foreach ($retQues AS $key => $val){
        		$this->assign($key, $val);
        	}
        }
        $question = isset($retQues['question'])? $retQues['question'] : array();
        
        //记录用户浏览信息  @TODO:session、cookie信息不需要透传到model层
        $this->englishUserLogic->saveUserViewedTopic($user_last_select['viewType'],$question);

        //保存历史记录
         if ($viewType == 5) {
            $user_last_select['ted'] = intval($question['ted']);
            $user_last_select['tedDifficulty'] = intval($question['difficulty']);
         } else if ($viewType == 4) {
            $user_last_select['media_id'] = 0 ;//intval($media_id);
        } else if ($viewType == 3) {
			$recommend = $user_last_select['recommend'];
            if (false != strpos($question['recommend'], $recommend)) {
                $user_last_select['recommend'] = intval($recommend);
            } else {
                $recommend = current(explode(",", $question['recommend']));
            }
            $user_last_select['recommend'] = $recommend;
            $user_last_select['recommendDifficulty'] = intval($question['difficulty']);
        } else if ($viewType == 2) {
            $user_last_select['subject'] = intval($question['subject']);
            $user_last_select['subjectDifficulty'] = intval($question['difficulty']);
        } else if ($viewType == 1) {
            $user_last_select['level'] = intval($question['level']);
            $user_last_select['object_info'] = serialize($user_last_select['object_info']);
            $user_last_select['level_info'] = serialize($user_last_select['level_info']);
        }
        $this->assign("user_last_select", $user_last_select);
        cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30);

        $this->display();
    }

    //ajax获取题目
    /**
     * @author Adam $date2013.08.31$
     * @todo [请求的参数为空的时候的默认，暂时为指定的ID，需要获取默认]
     */
    public function ajax_get_question() {
        $this->get_question();
    }

    //回答问题
    /**
     * @todo 讲逻辑放到 EnglishiTopicLogic(最好不要$_REQUEST,明确指定（$_POST、$_GET $_COOKIE）)
     */
    public function answer_question() {

        $question_id = intval($_REQUEST['question_id']);
        $select_option = intval($_REQUEST['select_option']);
        $viewType = intval($_REQUEST['viewType']);
        if (!in_array($viewType, array(1, 2, 3, 4, 5))) {
            $viewType = 1;
        }
        $questionModel = D("EnglishQuestion");
        //更新题目被答次数
        $questionModel->addQuestionAnswerNum($question_id);
        //被答题信息
        $question_info = $questionModel->getInfoById($question_id);
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
        $record_info = $englishRecordModel->getQuestionUserRecord($question_id, $viewType);
        //用户统计信息
        $englishUserCountModel = D("EnglishUserCount");
        $user_count_info = $englishUserCountModel->getEnglishUserCount($viewType, $question_info);

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
        $user_count_info['rice'] = $user_count_info['right_num'] * 100;
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
        //$data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNum();

        $data['question_info']['untested_num'] = $englishRecordModel->getUserUntestedQuestionNumber($viewType, $question_info);
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
    	$type = isset($_PSOT['type']) ? $_PSOT['type'] : 'object_综合';
        if ($this->isAjax()) {
            $ret = $this->englishUserLogic->getTopUserList($type);
            if (false === $ret) {
                $this->ajaxReturn('', $englishUserLogic->errMsg, false);
            } 
            else {
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

   		$data = array(
			'member_id' => empty($_SESSION[C('MEMBER_AUTH_KEY')])?cookie('english_tourist_id'):intval($_SESSION[C('MEMBER_AUTH_KEY')]),
			'type' => empty($_POST['type']) ? 1 : intval($_POST['type']),
			'question_id' => empty($_POST['question_id']) ? 1 : intval($_POST['question_id']),
			'media_html' => trim($_POST['media_html']),
		);
		
        if ($this->isAjax()) {
            $ret = self::$englishUserLogic->saveFeedBack($data);

            $this->ajaxReturn(self::$englishUserLogic->errMsg, $ret);
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
            exec("python /mnt/oral/python/compute-vectors.py " . $standard_audio . " " . $standard_audio_vec); //生成特征矢量文件算法
            //python ./Extend/ryan/python/compute-vectors.py ./Public/Uploads/Video/1.wav ./Public/Uploads/Video/1.vec
        }
        //
        //评分
        exec("python /mnt/oral/python/oral-evaluation.py " . $standard_audio_vec . " " . $recordFile . ' "' . $senetenceInfo['content'] . '" "' . $googleRet['utterance'] . '" ' . $googleRet['confidence'], $score_ret);
        //echo "python /mnt/oral/python/oral-evaluation.py " . $standard_audio_vec . " " . $recordFile . ' "' . $senetenceInfo['content'] . '" "' . $googleRet['utterance'] . '" ' . $googleRet['confidence'];exit;
        $score = intval($score_ret[0]);
        if ($score == 4) {
            $score = 20;
        } else if ($score == 3) {
            $score = 70;
        } else if ($score == 2) {
            $score = 85;
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
        //查看方式，1科目等级，2专题难度，3推荐难度，4特别推荐，5TED
        if (in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4, 5))) {
            $viewType = intval($user_last_select['viewType']);
        } else {
            $viewType = 1;
        }
        $voice = in_array(intval($user_last_select['voice']), array(1, 2)) ? intval($user_last_select['voice']) : 1;
        $target = in_array(intval($user_last_select['target']), array(1, 2)) ? intval($user_last_select['target']) : 1;
        $pattern = in_array(intval($user_last_select['pattern']), array(1, 2)) ? intval($user_last_select['pattern']) : 1;

        if ($viewType == 4) {
            $map = array();
            $map['media.status'] = 1;
            $map['question.status'] = 1;
            $map['media.id'] = intval($user_last_select['media_id']);
            $num = $englishMediaModel->alias("media")->join(C("DB_PREFIX") . "english_question question on question.media_id=media.id")->where($map)->count("question.id");
        } else {
            if ($viewType == 3) {
                $num = $englishMediaModel->getRecommendQuestionNum($target, $voice, $pattern);
            } else if ($viewType == 2) {
                $num = $englishMediaModel->getSubjectQuestionNum($target, $voice, $pattern);
            } else if ($viewType == 5) {
                $num = $englishMediaModel->getTedQuestionNum($target, $voice, $pattern);
            }
        }
        if (intval($num) == 0) {
            $user_last_select['viewType'] = 1;
            if ($viewType == 4) {
                $user_last_select['media_id'] = 0;
            }
        }
        $user_last_select['voice'] = $voice;
        $user_last_select['target'] = $target;
        $user_last_select['pattern'] = $pattern;
        cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30);
    }

    /**
     * 请求题目
     * 
     * @param viewType: 查看方式，1,选择课程 2,选择专题 3,选择推荐 4,选择特别推荐
     * @param voice: 美音/英音
     * @param target: 听力/说力
     * @param pattern: 视频/音频
     * @param type: category:美音/英音/听力/说力/视频/音频;level:级别;object:课程-类别;subject:专题-类别;recommend:推荐-类别;difficulty:初级,中级,高级 ;quick_select_next:下一题;quick_select_prev:上一题
     * @param now_question_id: 当前题目ID
     * 
     * @author slate date:2013-09-11
     */
    public function get_question() {
       if ($this->isAjax()) {
            $user_id = intval($_SESSION[C("MEMBER_AUTH_KEY")]);
            if (!$user_id) {
                $user_id = intval(cookie('english_tourist_id')); //从cookie获取游客id
                if (!$user_id) {
                    $user_id = -$user_id;
                }
            }
            //用户点击历史
            $user_last_select = cookie('english_user_last_select');
            if (!is_array($user_last_select)) {
                $user_last_select = array();
            }
            //接收请求数据
            $viewType = intval($_POST['viewType']); //查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐  
            if($viewType == 0)  $viewType = intval($user_last_select['viewType']);    
            $post_data = array(
            				'viewType' => $viewType,
            				'voice' => max(1, intval($_POST['voice'])),
            				'target' => max(1, intval($_POST['target'])),
            				'pattern' => max(1, intval($_POST['pattern'])),
            				'type' => empty($_POST['type']) ? "category" : $_POST['type'],
            				'now_question_id' => intval($_POST['now_question_id']),
            				'media_id' => isset($_POST['media_id']) ? $_POST['media_id'] :0,
            				'object' => isset($_POST['object']) ? $_POST['object'] :0,
            				'level' => isset($_POST['level']) ? $_POST['level'] :0,
            				'difficulty'=> isset($_POST['difficulty']) ? $_POST['difficulty'] :0,
            				'recommend'=> isset($_POST['recommend']) ? $_POST['recommend'] :0,
            				'subject'=> isset($_POST['subject']) ? $_POST['subject'] :0,
            				'ted'=> isset($_POST['ted']) ? $_POST['ted'] :0,
            			);
            $user_last_select['type'] = $post_data['type'];         
            $user_last_select['now_question_id'] = $post_data['now_question_id'];         
            $user_last_select['media_id'] = $post_data['media_id'];         
            $user_last_select['object'] = $post_data['object'];         
            $user_last_select['level'] = $post_data['level'];         
            $user_last_select['recommendDifficulty'] = $post_data['difficulty'];         
            $user_last_select['subjectDifficulty'] = $post_data['difficulty'];         
            $user_last_select['recommend'] = $post_data['recommend'];         
            $user_last_select['subject'] = $post_data['subject'];      
            $user_last_select['target'] = $post_data['target'];      
            $user_last_select['ted'] = $post_data['ted'];      
            
    
            //不同的浏览方式获取不同的数据
			$ret = $this->englishTopicLogic->getNextQuestion($user_last_select, $post_data);
			
     		//记录用户浏览信息  @TODO:session、cookie信息不需要透传到model层
      		$this->englishUserLogic->saveUserViewedTopic($viewType,$ret['question']);

            //保存历史记录
            if ($viewType == 5) {
                $user_last_select['ted'] = intval($ted);
                $user_last_select['tedDifficulty'] = intval($ret['question']['difficulty']);
            } 
            else if ($viewType == 4) {
              //  $user_last_select['media_id'] = intval($media_id);
            } else if ($viewType == 3) {
                $user_last_select['recommendDifficulty'] = intval($ret['question']['difficulty']);
            } else if ($viewType == 2) {
                $user_last_select['subjectDifficulty'] = intval($ret['question']['difficulty']);
            } 
            $user_last_select['voice'] = $post_data['voice'];  
            $user_last_select['target'] = $post_data['target'];  
            $user_last_select['pattern'] = $post_data['pattern'];  
            $user_last_select['viewType'] = $post_data['viewType'];  
            cookie('english_user_last_select', $user_last_select, 60 * 60 * 24 * 30); // 存储用户点击历史
            
            //play_code为空，则进行视频解析
            $isAboutVideo = 0;
            $this->englishTopicLogic->getVedioUrl($ret['question'], $isAboutVideo);
            $ret['question']['isAboutVideo'] = $isAboutVideo;

            
            $ret['english_user_info'] = $this->englishUserLogic->getEnglishUserInfo($user_id ,  unserialize(cookie("english_user_info")));		   
            //获取用户统计信息
            $ret['user_count_info'] = $this->englishUserLogic->getEnglishUserCount($user_last_select);

            $this->ajaxReturn($ret, "请求成功", true);
        }
    }

    public function getAudio() {
        $word = $_REQUEST['word'];
        $type = $_REQUEST['type'];
        $html = file_get_contents("http://dictionary.cambridge.org/dictionary/british/" . $word); //获取html内容
        /*
          echo "<pre>";
          echo htmlentities($html);
          echo "</pre>";
         * 
         */
        //
        //preg获取单词解释
        if ($type == 1) {
            $preg = "/<audio\sid=\"audio_pron-uk_0\".*<source .*src=\"(.*\.mp3)\"/i";
        } else {
            $preg = "/<audio id=\"audio_pron-us_1\".*<source .*src=\"(.*\.mp3)\"/i";
        }
        preg_match($preg, $html, $match);
        //<audio id="audio_pron-uk_0" onerror="playSoundException('play_sound_button')"><source class="audio_file_source" type="audio/mpeg" src="http://dictionary.cambridge.org/media/british/uk_pron/u/ukm/ukmut/ukmutto011.mp3"></source>
        //audio_pron-us_1
//        header('Content-Type:"audio/mpeg"');
        if ($match[1]) {
            redirect($match[1]);
        } else {
            if ($type == 1) {
                $req_type = 2;
            } else {
                $req_type = 1;
            }
            redirect("http://dict.youdao.com/dictvoice?audio=" . $word . "type=" . $req_type);
        }
    }

}

?>
