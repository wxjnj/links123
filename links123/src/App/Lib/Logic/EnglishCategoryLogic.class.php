<?php
/**
 * 英语角类目（科目、年级、难度等）等逻辑函数封装
 * 
 * 供Action层调用，在Action中不出现直接调用M(),D()的语句，
 * 为后期DB收紧优化、加入缓存做准备
 * 
 * @author tellen 2013-09-11
 */
class EnglishCategoryLogic {
	
	public $errMsg;
	
	public $errCode;
	
	/**英语视频推荐Model*/
	protected $mEnglishMediaRecommend = null;  
	
	/**题目Model*/
	protected $mQuestion = null;
		
	/**英语对象Model*/
	protected $mEnglishObject = null;
	
	/***/
	protected $mEnglishLevel = null;
	
	protected $mEnglishMedia = null;
	
	protected $mEnglishMediaSubject = null;

	
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
	}
	
	/**
	 * @desc 获取视频播放的课程或专题或推荐等相关参数
	 * @param $cookie = array()   []
	 */
	public function getMediaSetList(&$user_last_select){
		 
		if (!is_array($user_last_select)){
			$this->errMsg = 'param error ';
			Log::write(__FUNCTION__ . ' param error  ' . __LINE__  .  ' return false!', Log::ERR);
        	return false;
		}
		
		$viewType = in_array(intval($user_last_select['viewType']), array(1, 2, 3, 4)) ? intval($user_last_select['viewType']) : 1; //查看方式，1科目等级，2专题难度，3推荐
        $dwVoice = in_array(intval($user_last_select['voice']), array(1, 2)) ? intval($user_last_select['voice']) : 1;
        $dwTarget = in_array(intval($user_last_select['target']), array(1, 2)) ? intval($user_last_select['target']) : 1;
        $dwPattern = in_array(intval($user_last_select['pattern']), array(1, 2)) ? intval($user_last_select['pattern']) : 1;
    
        $recommedsQuestionNum = $this->mEnglishMedia->getRecommendQuestionNum($dwTarget, $dwVoice, $dwPattern);
        $subjectsQuestionNum = $this->mEnglishMedia->getSubjectQuestionNum($dwTarget, $dwVoice, $dwPattern);
        $tedsQuestionNum = $this->mEnglishMedia->getTedQuestionNum($target, $voice, $pattern);
		 switch ($viewType){
		 	case 5:{ //选择ted
		 		$ret = self::getTEDList($user_last_select,$dwVoice, $dwTarget, $dwPattern);
		 		break;
		 	}
        	case 4: { //特别推荐
           		$ret = self::getSpecRecommendList($dwVoice, $dwTarget, $dwPattern);
           		break;
        	}        	
        	case 3: { //选择推荐
           		$ret = self::getRecommendList($user_last_select,$dwVoice, $dwTarget, $dwPattern);
           		break;
        	}        	
        	case 2: { //选择专题
           		$ret = self::getTopicList($user_last_select, $dwVoice, $dwTarget, $dwPattern);
           		break;
        	}        	
        	case 1: { //选择课程
           		$ret = self::getCourseList($user_last_select, $dwVoice, $dwTarget, $dwPattern);
           		break;
        	}
		}
		if ($false === $ret) return $ret;
		
		$ret['recommedsQuestionNum'] = $recommedsQuestionNum;
		$ret['subjectsQuestionNum'] = $subjectsQuestionNum;
		$ret['tedsQuestionNum'] = $tedsQuestionNum;
	
		return $ret;
	}
	
	

	/**
	 * @desc 获取特别推荐列表  viewtype=4
 	 * @param int voice     
	 * @param int target     
	 * @param int pattern
	 * 
	 * @return  mixed | array() | boolean
	 */
	public function  getSpecRecommendList($dwVoice=1, $dwTarget=1, $dwPattern=1){
		
        $list = $this->mEnglishMediaRecommend->getRecommendListToIndex($dwVoice, $dwTarget, $dwPattern);
        if (false == $list){
        	$this->errMsg = 'mEnglishMediaRecommend getRecommendListToIndex failed';
			Log::write(__FUNCTION__ . ' getRecommendListToIndex ' . __LINE__  .  ' return false!', Log::ERR);
        	return false;
        }
        
        $dlist = $this->mQuestion->getDifficultyList(3, 0, 0, $dwVoice, $dwTarget, $dwPattern);
		
        return array('recommend_list' => $list,
                     'recommendDifficultyList' => $dlist,);
	}	
	
	/**
	 * @desc 获取TED列表  viewtype=5
 	 * @param int voice     
	 * @param int target     
	 * @param int pattern
	 * 
	 * @return  mixed | array() | boolean
	 */
	public function  getTEDList(&$user_last_select, $voice, $target, $pattern){
		
        $englishMediaTedModel = D("EnglishMediaTed");
            //TED列表
            $ted_list = $englishMediaTedModel->getTedListToIndex($voice, $target, $pattern);
            //TED
            $ted = intval($user_last_select['ted']);
            //
            $params = array("voice" => $voice, "target" => $target, "pattern" => $pattern, "ted" => $ted);
            //上次的ted为零，获取默认的ted的id
            if ($ted == 0) {
                $ted = $englishMediaTedModel->getDefaultTedId($voice, $target, $pattern);
            } else {
                //判断上次的ted下是否有题目，没有则获取默认ted
                $params['ted'] = $ted;
                $num = $questionModel->getQuestionNumber($params);
                if ($num == 0) {
                    $ted = $englishMediaTedModel->getDefaultTedId($voice, $target, $pattern);
                }
            }
            $params['ted'] = $ted; //更新查询数组
            //获取TED下的难度列表
            $tedDifficultyList = $questionModel->getDefaultDifficultyList($viewType, $params);
             //上次TED的难度
            if (in_array(intval($user_last_select['tedDifficulty']), array(1, 2, 3))) {
                $difficulty = intval($user_last_select['tedDifficulty']);
            } else {
                $difficulty = $questionModel->getDefaultDifficultyId($viewType, $params);
            }
            $params["difficulty"] = $difficulty;
            //防止上次的难度下没有题目
            $num = $questionModel->getQuestionNumber($params);
            if ($num == 0) {
                $difficulty = $questionModel->getDefaultDifficultyId($viewType, $params);
            }
            
          	$user_last_select['difficulty'] = $difficulty;
            $user_last_select['ted'] = $ted;
            
           	return array('ted_list' => $ted_list,
		                 'tedDifficultyList' => $tedDifficultyList,
		                 'difficulty' => $difficulty,
		                 'ted' => $ted);
	}
	
	/**
	 * @desc 获取推荐列表 viewtype=3
	 * @param int $dwRecommend  
 	 * @param int voice     
	 * @param int target    
	 * @param int pattern
	 * 
	 * @return  mixed | array() | boolean
	 */
	public function  getRecommendList(&$user_last_select, $dwVoice, $dwTarget, $dwPattern){
			
            //推荐列表
            $recommend_list = $this->mEnglishMediaRecommend->getRecommendListToIndex($dwVoice, $dwTarget, $dwPattern);
			if (false === $recommend_list){
				$this->errMsg = 'mEnglishMediaRecommend getRecommendListToIndex failed';
				Log::write(__FUNCTION__ . ' getRecommendListToIndex ' . __LINE__  .  ' return false!', Log::ERR);
        		return false;
			}
            //推荐
            $recommend = isset($user_last_select['recommend']) ? $user_last_select['recommend'] : 0;
   
            if ($recommend == 0) {
                $recommend = $this->mEnglishMediaRecommend->getDefaultRecommendId($dwVoice, $dwTarget, $dwPattern);
            } else {
                $num = $this->mQuestion->getQuestionNum(0, 0, 0, $recommend, 0, $dwVoice, $dwTarget, $dwPattern);
                if ($num == 0) {
                    $recommend = $this->mEnglishMediaRecommend->getDefaultRecommendId($dwVoice, $dwTarget, $dwPattern);
                }
            }
            if (in_array(intval($user_last_select['recommendDifficulty']), array(1, 2, 3))) {
                $difficulty = intval($user_last_select['recommendDifficulty']);
            } else {
                $difficulty = $this->mQuestion->getDefaultDifficulty(3, 0, $recommend, $dwVoice, $dwTarget, $dwPattern);
            }
            $num = $this->mQuestion->getQuestionNum(0, 0, 0, $recommend, $difficulty, $dwVoice, $dwTarget, $dwPattern);
            if ($num == 0) {
                $difficulty = $this->mQuestion->getDefaultDifficulty(3, 0, $recommend, $dwVoice, $dwTarget, $dwPattern);
            }
            
            $recommendDifficultyList = $this->mQuestion->getDifficultyList(3, 0, $recommend, $dwVoice, $dwTarget, $dwPattern);
           	$user_last_select['difficulty'] = $difficulty;
            $user_last_select['recommend'] = $recommend;
        
        	return array('recommend_list' => $recommend_list,
                     'recommendDifficultyList' => $recommendDifficultyList,
                     'difficulty' => $difficulty,
                     'recommend' => $recommend);
	}
	
	/**
	 * @desc 获取专题 viewtype=2
	 * @param array $user_last_select  
 	 * @param int recommendDifficulty     
	 * @param int target    
	 * @param int pattern
	 * 
	 * @return  mixed | array() | boolean
	 */
	public function  getTopicList(&$user_last_select, $dwVoice, $dwTarget, $dwPattern){
		
		$subject_list = $this->mEnglishMediaSubject->getSubjectListToIndex($dwVoice, $dwTarget, $dwPattern);

		//用户上次选择的专题
		$subject = intval($user_last_select['subject']);
		$subject_id = $subject;
		
		$num = $this->mQuestion->getQuestionNum(0, 0, $subject_id, 0, 0, $dwVoice, $dwTarget, $dwPattern);
		if (intval($subject_id) == 0 || intval($num) == 0) {
		    $subject = $this->mEnglishMediaSubject->getDefaultSubjectdId($dwVoice, $dwTarget, $dwPattern);
		}
		//难度列表
		$subjectDifficultyList = $this->mQuestion->getDifficultyList(2, $subject, 0, $dwVoice, $dwTarget, $dwPattern);
		
		//难度值
		if (in_array(intval($user_last_select['subjectDifficulty']), array(1, 2, 3))) {
		    $difficulty = intval($user_last_select['subjectDifficulty']);
		} else {
		    $difficulty = $this->mQuestion->getDefaultDifficulty(2, $subject, 0, $dwVoice, $dwTarget, $dwPattern);
		}
		$num = $this->mQuestion->getQuestionNum(0, 0, $subject, 0, $difficulty, $dwVoice, $dwTarget, $dwPattern);
		if ($num == 0) {
		    $difficulty = $this->mQuestion->getDefaultDifficulty(2, $subject, 0, $dwVoice, $dwTarget, $dwPattern);
		}
		$user_last_select['difficulty'] = $difficulty;
        $user_last_select['subject'] = $subject;
        
    	return array(
		         'subject_list' => $subject_list,
		         'subjectDifficultyList' => $subjectDifficultyList,
		         'difficulty' => $difficulty,
		         'subject' => $subject);
	}
	
	/**
	 * @desc 获取"选择课程"列表 viewtype=1  
	 * @param int recommend   
 	 * @param int recommendDifficulty     
	 * @param int target    
	 * @param int pattern
	 * 
	 * @return  mixed | array() | boolean
	 */
	public function getCourseList(&$user_last_select, $dwVoice, $dwTarget, $dwPattern){
		//科目列表
        $object_list = $this->mEnglishObject->getObjectListToIndex($dwVoice, $dwTarget, $dwPattern);
        //
        //用户上次选择的科目
        $object = intval($user_last_select['object']);
        $objectInfo = $this->mEnglishObject->getObjectInfo($object);
        //科目不存在或不可用
        if (false == $objectInfo && empty($objectInfo)) {
            $objectInfo = $this->mEnglishObject->getDefaultObjectInfo($dwVoice, $dwTarget, $dwPattern);
        }
        if (intval($objectInfo['id']) == 0) {
            $object = 1;
        } else {
            $object = $objectInfo['id'];
        }
        //
        //用户上次选择的等级
        $level = intval($user_last_select['level']);
        $levelInfo = $this->mEnglishLevel->getLevelInfo($level);
        //等级不存在或不可用
        if (false == $levelInfo && empty($levelInfo)) {
            $levelInfo = $this->mEnglishLevel->getDefaultLevelInfo($level, $dwVoice, $dwTarget, $dwPattern);
        }
        if (intval($levelInfo['id']) == 0) {
            $level = 0;
        }
        //默认的等级列表
        $level_list = $this->mEnglishLevel->getLevelListToIndex($object, $dwVoice, $dwTarget, $dwPattern);
        
        //确保上次选择的等级下拥有题目
        $ret = array();
        foreach ($level_list as $value) {
            $ret[$value['id']] = $value;
        }
        if ($ret[$level]['question_num'] == 0) {
            foreach ($level_list as $value) {
                if ($value['question_num'] > 0) {
                    $levelInfo = $this->mEnglishLevel->getInfoById($value['id']);
                    break;
                }
            }
            $level = $levelInfo['id'];
        }
        $user_last_select['level'] = $level;
        $user_last_select['object'] = $object;
        
    	return array(
		         'object_list' => $object_list,
		         'level_list' => $level_list,
		         'level' => $level,
		         'object' => $object,
		         'object_info' => $objectInfo,
		         'level_info' =>$levelInfo,
		         );
	}	
}

?>