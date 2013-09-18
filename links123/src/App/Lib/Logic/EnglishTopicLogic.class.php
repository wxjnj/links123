<?php
/**
 * 英语角取题等逻辑函数封装
 * 
 * 供Action层调用，在Action中不出现直接调用M(),D()的语句，
 * 为后期DB收紧优化、加入缓存做准备
 * 
 * @author tellen 2013-09-11
 */
class EnglishTopicLogic {
	
	public $errMsg;
	
	public $errCode;
	
	/**英语视频推荐Model*/
	protected $mEnglishMediaRecommend = null;  
	
	/**题目Model*/
	protected $mQuestion = null;
		
	/**英语对象Model*/
	protected $mEnglishObject = null;
	
	protected $mEnglishLevel = null;
	
	protected $mEnglishMedia = null;
	
	protected $mEnglishMediaSubject = null;
	
	protected $mEnglishViewRecord = null;

	protected $cEnglishCategory = null;
	
	public function __construct() {
		$this->mEnglishMediaRecommend = D("EnglishMediaRecommend");
		if (empty($this->mEnglishMediaRecommend)){
			$this->errMsg = 'EnglishMediaRecommend init failed';
			Log::write(__FUNCTION__ . ' EnglishMediaRecommend ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mQuestion = D("EnglishQuestion");
		if (empty($this->mQuestion)){
			$this->errMsg = 'EnglishQuestion init failed';
			Log::write(__FUNCTION__ . ' EnglishQuestion ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mEnglishObject = D("EnglishObject");
		if (empty($this->mEnglishObject)){
			$this->errMsg = 'EnglishObject init failed';
			Log::write(__FUNCTION__ . ' EnglishObject ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mEnglishLevel = D("EnglishLevel");
		if (empty($this->mEnglishLevel)){
			$this->errMsg = 'EnglishLevel init failed';
			Log::write(__FUNCTION__ . ' EnglishLevel ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mEnglishMedia = D("EnglishMedia");
		if (empty($this->mEnglishMedia)){
			$this->errMsg = 'EnglishMedia init failed';
			Log::write(__FUNCTION__ . ' EnglishMedia ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mEnglishMediaSubject = D("EnglishMediaSubject");
		if (empty($this->mEnglishMediaSubject)){
			$this->errMsg = 'mEnglishMediaSubject init failed';
			Log::write(__FUNCTION__ . ' mEnglishMediaSubject ' . __LINE__  .  ' init failed!', Log::ERR);
		}		

		$this->mEnglishViewRecord = D("EnglishViewRecord");
		if (empty($this->mEnglishViewRecord)){
			$this->errMsg = 'EnglishViewRecord init failed';
			Log::write(__FUNCTION__ . ' EnglishViewRecord ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->cEnglishCategory = new EnglishCategoryLogic();
	}
	
 	/**
	 * @desc 获取视频相关题目信息
	 * 
	 * @param 
	 * @return 
	 */
	public function getQuestion($uls, $dwVoice, $dwTarget, $dwPattern, $media_id=0){
		
		$viewType = $uls['viewType'];
		$object = isset($uls['object'])?$uls['object']:0;
		$level =isset($uls['level'])?$uls['level']:0;
		$subject = isset($uls['subject'])?$uls['subject']:0;
		$recommend = isset($uls['recommend'])?$uls['recommend']:0;
		$difficulty = isset($uls['difficulty'])?$uls['difficulty']:0;
		$ted = isset($uls['ted'])?$uls['ted']:0;
		$now_question_id = isset($uls['now_question_id'])?$uls['now_question_id']:0;
		$voice = $dwVoice;
		$target = $dwTarget;
		$pattern = $dwPattern;
		
		if ($dwTarget == 2) {
            $question = $this->mQuestion->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $media_id);
        } elseif ($viewType == 2) {
            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId('', '', $subject, '', $difficulty, $voice, $target, $pattern);
            $question = $this->mQuestion->getSpecialSubjectQuestion($subject, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
        } elseif ($viewType == 3) {
            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId('', '', '', $recommend, $difficulty, $voice, $target, $pattern);
            $question = $this->mQuestion->getRecommendQuestion($recommend, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
        } elseif ($viewType == 4) {
            $question = $this->mQuestion->getSpecialRecommend($media_id, $type, $now_question_id);
        } elseif ($viewType == 5) {
            $question_id = $this->mEnglishViewRecord->getUserLastViewedTedQuestionId($ted, $difficulty, $voice, $target, $pattern);
            $question = $this->mQuestion->getTedQuestion($ted, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
        } else {
            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
            $question = $this->mQuestion->getSubjectQuestion($object, $level, $voice, $target, $pattern, $type, $question_id, $now_question_id);
        }
		
//         $question = $this->mQuestion->getQuestionToIndex(
//         		$uls['viewType'], 
//         		isset($uls['object'])?$uls['object']:0,
//         		isset($uls['level'])?$uls['level']:0,  
//         		isset($uls['subject'])?$uls['subject']:0, 
//         		isset($uls['recommend'])?$uls['recommend']:0,
//         		isset($uls['difficulty'])?$uls['difficulty']:0, 
//         		$dwVoice, $dwTarget, $dwPattern, $media_id);
		if (false === $question){
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' error!', Log::ERR);
			return false;
		}
		
		//解析视频url、判断是否为isAbout.com来源
		$isAboutVideo = 0;
		$this->getVedioUrl($question, $isAboutVideo);	
		
        return array('question'=>$question, 'isAboutVideo' => $isAboutVideo);
	}
	
	/**
	 * @desc 解析question play_code视频url获取 
	 * @param array $question
	 * @param bool $isAboutVideo
	 * @return  boolean
	 */
	public function getVedioUrl(&$question, &$isAboutVideo){
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
              
                $this->mEnglishMedia->save($saveData);
            }

            $question['play_code'] = $play_code;
            $question['media_thumb_img'] = $media_thumb_img;
            $question['play_type'] = $play_type;
        }

        //判断是否为about.com视频
        $isAboutVideo = 0;
        if (strpos($question['media_source_url'], 'http://video.about.com') !== FALSE) {
            $isAboutVideo = 1;
        }
        if ($question['play_code']) {
            if (strpos($question['media_source_url'], 'britishcouncil.org') !== FALSE) {
                $question['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $question['play_code']);
                $question['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $question['play_code']);
            }
            $question['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $question['play_code']);
        }
        
        return true;
	}
	
	/**
	 * @desc 听说力上下题目切换
	 * @param array $post_data = array('viewType' => $viewType,
            				'voice' => max(1, intval($_POST['voice'])),
            				'target' => max(1, intval($_POST['target'])),
            				'pattern' => max(1, intval($_POST['pattern'])),
            				'type' => empty($_POST['type']) ? "category" : $_POST['type'],
            				'now_question_id' => intval($_POST['now_question_id']),
            				'media_id' => isset($_POST['media_id']) ? $_POST['media_id'] :0,)
	 * @param array $user_last_select = array()
	 * 
	 * @return 
	 */
	public function getNextQuestion(&$user_last_select, $post_data){
		
		$viewType = $post_data['viewType'];
		
		/** 全量获取列表参数 @todo 根据不同场景可以降低db查询次数 */
		switch ($viewType){
			case 5: { //ted
           		$ret = $this->cEnglishCategory
           					->getTEDList($post_data['voice'], $post_data['target'], $post_data['pattern']);
           		break;
        	}   
        	case 4: { //特别推荐
           		$ret = $this->cEnglishCategory
           					->getSpecRecommendList($post_data['voice'], $post_data['target'], $post_data['pattern']);
           		break;
        	}        	
        	case 3: { //选择推荐
           		$retlist = $this->cEnglishCategory
           						->getRecommendList($user_last_select,$post_data['voice'],
           									 $post_data['target'], $post_data['pattern']);

           		break;
        	}        	
        	case 2: { //选择专题
           		 $retlist = $this->cEnglishCategory
           		 				 ->getTopicList($user_last_select,$post_data['voice'], 
           		 						$post_data['target'], $post_data['pattern']);
           		break;
        	}        	
        	case 1: { //选择课程
           		 $retlist = $this->cEnglishCategory
           		 				 ->getCourseList($user_last_select,$post_data['voice'], 
           		 				 			$post_data['target'], $post_data['pattern']);
           		break;
        	}
		}
		
        $type = $post_data['type'];
		if ($type == "category" && !in_array($viewType,array(1,2,3,4))) {
             $viewType = 1; //大类，默认为科目进入
 			 $retlist = self::_getCourseList($user_last_select,$post_data['voice'], $post_data['target'], $post_data['pattern']);
        }
        $ret = array();
        if (isset($retlist['object_info'])) $ret['object_info'] = $retlist['object_info'];
        if (isset($retlist['level_info'])) $ret['level_info'] = $retlist['level_info'];
        if (isset($retlist['object_list'])) $ret['object_list'] = $retlist['object_list'];
        if (isset($retlist['level_list'])) $ret['level_list'] = $retlist['level_list'];
        if (isset($retlist['ted_list'])) $ret['ted_list'] = $retlist['ted_list'];
        if (isset($retlist['tedDifficultyList'])) $ret['ted_difficulty_list'] = $retlist['tedDifficultyList'];
        if (isset($retlist['recommend_list'])) $ret['recommend_list'] = $retlist['recommend_list'];
        if (isset($retlist['recommendDifficultyList'])) $ret['recommend_difficulty_list'] = $retlist['recommendDifficultyList'];
        if (isset($retlist['subject_list'])) $ret['subject_list'] = $retlist['subject_list'];
        if (isset($retlist['subjectDifficultyList'])) $ret['subject_difficulty_list'] = $retlist['subjectDifficultyList'];
        

        $media_id = isset($post_data['media_id'])?$post_data['media_id']:0;
		$now_question_id = $post_data['now_question_id'];
		$voice = $post_data['voice'];
		$target = $post_data['target'];
		$pattern = $post_data['pattern'];
        $object = isset($retlist['object']) ? $retlist['object'] : 0;
        $level = isset($retlist['level']) ? $retlist['level'] : 0;
        $subject = isset($retlist['subject']) ? $retlist['subject'] : 0;
        $recommend = isset($retlist['recommend']) ? $retlist['recommend'] : 0;
        $difficulty = isset($retlist['difficulty']) ? $retlist['difficulty'] : 0;
        $recommend = isset($retlist['recommend']) ? $retlist['recommend'] : 0;

        /** 根据不同场景取题 */
        if ($target == 2){
        	$user_last_question = array();
            $con = array();
            $con["question.status"] = 1;
            if ($type == "quick_select_prev") {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "prev", $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $viewType);
                $con["question.id"] = intval($last_question_info['question_id']);
                $user_last_question = $this->mQuestion->getQuestionWithOption($con);
            } else if ($type == 'quick_select_next') {
                $last_question_info = D("EnglishViewRecord")->getViewedQuestionRecord($now_question_id, "next", $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $viewType);
                $con["question.id"] = intval($last_question_info['question_id']);
                $user_last_question = $this->mQuestion->getQuestionWithOption($con);
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
              if (!empty($user_last_question)) {
                $ret['question'] = $user_last_question;
            } else {
                $ret['question'] = $this->mQuestion->getQuestionToIndex($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $media_id);
            }
        }
        else {
	        $this->mEnglishViewRecord = D("EnglishViewRecord");
	        if ($viewType != 1 && $target == 2) {
	            $questionInfo = $this->mQuestion->getSpeak($viewType, $object, $level, $subject, $recommend, $difficulty, $voice, $pattern);
	        } elseif ($viewType == 2) {
	            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId( $subject,  $difficulty, $voice, $target, $pattern);
	            $questionInfo = $this->mQuestion->getSpecialSubjectQuestion($subject, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
	        } elseif ($viewType == 3) {
	            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId('', '', '', $recommend, $difficulty, $voice, $target, $pattern);
	            $questionInfo = $this->mQuestion->getRecommendQuestion($recommend, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
	        } elseif ($viewType == 4) {
	            $questionInfo = $this->mQuestion->getSpecialRecommend($media_id, $type, $now_question_id);
	        } elseif ($viewType == 5) {
                $questionInfo = $this->mQuestion->getTedQuestion($ted, $difficulty, $voice, $target, $pattern, $type, $question_id, $now_question_id);
            }else {
	            $question_id = $this->mEnglishViewRecord->getUserViewQuestionLastId($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern);
	            $questionInfo = $this->mQuestion->getSubjectQuestion($object, $level, $voice, $target, $pattern, $type, $question_id, $now_question_id);
	        }
	        $ret['question'] = $questionInfo;
        }
        
		$ret['viewType'] = $viewType;
		if ($viewType == 4) {
		    $ret['recommend_list'] = $this->mEnglishMediaRecommend->getRecommendListToIndex($ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern']);
		    $ret['recommend_difficulty_list'] = $this->mQuestion->getDifficultyList(3, 0, 0, $ret['question']['voice'], $ret['question']['target'], $ret['question']['pattern'], 0);
		}
		
		$ret['recommedsQuestionNum'] = $this->mEnglishMedia->getRecommendQuestionNum($target, $voice, $pattern);
		$ret['subjectsQuestionNum'] = $this->mEnglishMedia->getSubjectQuestionNum($target, $voice, $pattern);
            
        return $ret;
	}
}

?>