<?php
/**
 * 英语角用户使用历史信息等逻辑函数封装
 * 
 * 供Action层调用，在Action中不出现直接调用M(),D()的语句，
 * 为后期DB收紧优化、加入缓存做准备
 * 
 * @author tellen 2013-09-11
 */
class EnglishUserLogic {
	
	public $errMsg;
	
	public $errCode;
	
	/** 英语角用户使用记录Model*/
	protected $mEnglishUserInfo = null;  
	
	/**题目Model*/
	protected $mEnglishUserCount = null;
	
	protected $mEnglishFeedback = null;
	
	protected $mEnglishViewRecord = null;
	
	public function __construct() {
		$this->mEnglishUserInfo = D("EnglishUserInfo");
		if (empty($this->mEnglishUserInfo)){
			$this->errMsg = 'EnglishUserInfo init failed';
			Log::write(__FUNCTION__ . ' EnglishUserInfo ' . __LINE__  .  ' init failed!', Log::ERR);
		}
				
		$this->mEnglishUserCount = D("EnglishUserCount");
		if (empty($this->mEnglishUserInfo)){
			$this->errMsg = 'EnglishUserCount init failed';
			Log::write(__FUNCTION__ . ' EnglishUserCount ' . __LINE__  .  ' init failed!', Log::ERR);
		}
		
		$this->mEnglishFeedback = D("EnglishFeedback");
		if (empty($this->$mEnglishFeedback)){
			$this->errMsg = 'EnglishFeedback init failed';
			Log::write(__FUNCTION__ . ' EnglishFeedback ' . __LINE__  .  ' init failed!', Log::ERR);
		}	

		$this->mEnglishViewRecord = D("EnglishViewRecord");
		if (empty($this->$mEnglishFeedback)){
			$this->errMsg = 'EnglishViewRecord init failed';
			Log::write(__FUNCTION__ . ' EnglishViewRecord ' . __LINE__  .  ' init failed!', Log::ERR);
		}
	}
	
	
 	/**
	 * @desc 获取用户英语角信息
	 * @todo  去除mEnglishUserInfo对session、cookie的依赖
	 * 通过action传递过去
	 * @param 
	 * @return 
	 */
	public function getEnglishUserInfo($sessionkey=0,$cookieInfo=array()) {

        $english_user_info = $this->mEnglishUserInfo->getEnglishUserInfo();
        if (false == $english_user_info){
        	$this->errMsg = 'EnglishUserInfo get failed';
			Log::write(__FUNCTION__ . ' EnglishUserInfo ' . __LINE__  .  ' get failed!', Log::ERR);
			return false;
        }
		
		return $english_user_info;
	}
	
	/**
	 * @desc 记录用户浏览题目
	 * @param $question = array('id'=>xx,'level'=>xx)
	 * @param int $viewType [视频显示来源] 
	 */
	public function saveUserViewedTopic($viewType,$question){
		
		$ret = false;
		switch (intval($viewType)){
			case 1:{
				$ret = $this->mEnglishViewRecord->addRecord($question['id'], $question['level'], $object, 0, 0, 0, $question['voice'], $question['target'], $question['pattern'], $viewType);
        		break;
			}
			case 2:{
			    $ret = $this->mEnglishViewRecord->addRecord($question['id'], 0, 0, $question['subject'], 0, $question['difficulty'], $question['voice'], $question['target'], $question['pattern'], $viewType);
       			break;
			}
			case 3:{
				 $ret = $this->mEnglishViewRecord->addRecord($question['id'], 0, 0, 0, $recommend, $question['difficulty'], $question['voice'], $question['target'], $question['pattern'], $viewType);
        		 break;
			}
			case 4:{
				 $ret = $this->mEnglishViewRecord->addRecord($question['id'], 0, 0, 0, 0, 0, $question['voice'], $question['target'], $question['pattern'], $viewType);
        		 break;
			}
		}
		if (false === $ret){
			Log::write('EnglishViewRecord add fail：' , Log::ERR);
			return false;
		}
		
		return true;
	}
	
	/**
	 * @desc 获取用户统计信息
	 * 
	 * @param 
	 * @return 
	 */
	public function getEnglishUserCount($uls) {
		$viewType = isset($uls['viewType']) ? $uls['viewType'] :1;
		$voice = isset($uls['voice']) ? $uls['voice'] :0;
		$target = isset($uls['target']) ? $uls['target'] :0;
		$recommendDifficulty = isset($uls['recommendDifficulty']) ? $uls['recommendDifficulty'] :0;
		$subject = isset($uls['subject']) ? $uls['subject'] :0;
		$subjectDifficulty = isset($uls['subjectDifficulty']) ? $uls['subjectDifficulty'] :0;
		$object = isset($uls['object']) ? $uls['object'] :0;
		$level = isset($uls['level']) ? $uls['level'] :0;
		$recommend = isset($uls['recommend']) ? $uls['recommend'] :0;
		
        $user_count_info = array();
        if ($viewType == 3) {
            $user_count_info = $this->mEnglishUserCount->getEnglishUserCountInfo($viewType, 0, 0, 0, $recommend, $recommendDifficulty, $voice, $target);
        } else if ($viewType == 2) {
            $user_count_info = $this->mEnglishUserCount->getEnglishUserCountInfo($viewType, 0, 0, $subject, 0, $subjectDifficulty, $voice, $target);
        } else if ($viewType == 1) {
            $user_count_info = $this->mEnglishUserCount->getEnglishUserCountInfo($viewType, $object, $level, 0, 0, 0, $voice, $target);
        } else if ($viewType == 4) {
            $user_count_info = $this->mEnglishUserCount->getEnglishUserCountInfo($viewType, 0, 0, 0, 0, 0, $voice, $target);
        }
        
		if (false === $ret){
			$this->errMsg = 'getEnglishUserCount  failed';
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' getEnglishUserCount failed!', Log::ERR);
			
			return false;
		}
		
		return $user_count_info;
	}

	/**
	 * @desc 获取排行榜数据 
	 * @param $type = 'object_英语'   //string
	 * 
	 * @return array('') | false;
	 */
	public function getTopUserList($type){
		
		$ret = $this->mEnglishUserInfo->getTopUserListByTypeName($type);
		if (false === $ret){
			$this->errMsg = 'getTopUserListByTypeName  failed';
			Log::write(__FUNCTION__ . ' EnglishUserInfo ' . __LINE__  .' getTopUserListByTypeName failed!', Log::ERR);
			
			return false;
		}
		if (empty($ret)){
			$this->errMsg = 'getTopUserListByTypeName  empty';
			Log::write(__FUNCTION__ . ' EnglishUserInfo ' . __LINE__  .' getTopUserListByTypeName empty!', Log::ERR);
			
			return false;
		}	
	
		return $ret;
	}
	
	/**
	 * @desc  重置做题正确数
	 * 
	 * @param $input = array('voice'=>'',
	 * 						 'target'=>xx,
	 * 						 'object'=>xx,
	 * 						 'level'=>xx)
	 * @param $sessionkey int
	 * @return true |fasle
	 */
	public function resetLevelRightNum($input, $sessionkey, $cookiekey){
		 
	   	 if (!is_array($input)){
	   	 	$this->errCode = -100;
	   	 	$this->errMsg = 'input param error';
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' input param error!', Log::ERR);
			
			return false;		
	   	 }
	   	 
	   	 if (empty($sessionkey) && empty($cookiekey)){
	   	 	$this->errCode = -101;
	   	 	$this->errMsg = 'input session or cookie error';
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' input session cookie param error!', Log::ERR);
			
			return false;	
	   	 }
	   	 
         return $this->mEnglishUserCount->resetRightNum($input, $sessionkey, $cookiekey);
	}
	
	/**
	 * @desc 
	 * @param $data=array('member_id' => xx,
	 *		'type' => xx,
	 *		'question_id' => xx,
     *			'media_html' => xx,)
	 */
	public function saveFeedBack($data){
		
		 if (!is_array($data)){
	   	 	$this->errCode = -100;
	   	 	$this->errMsg = 'input param error';
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' input param error!', Log::ERR);
			
			return false;		
	   	 }
	   	 
	   	 $data['create_time'] = time();
	   	 $ret = $this->mEnglishFeedback->add($data);
	   	 if (empty($ret)){
	   	 	$this->errCode = -200;
	   	 	$this->errMsg = 'insert error';
			Log::write(__FUNCTION__ . '  ' . __LINE__  .' insert error!', Log::ERR);
	   	 	return false ;
	   	 }
	   	 
		 return true;
	}
	
	/**
	 * @desc 获取用户特别推荐视频列表 
	 */
	public function getSpecialRecommendMediaList(){
		
	}

	/**
	 * 
	 */
	public function getUserViewQuestionLastId($subject, $difficulty, $voice, $target, $pattern){
		return $this->mEnglishViewRecord->getUserViewQuestionLastId('', '', $subject, '', $difficulty, $voice, $target, $pattern);
	}
}

?>