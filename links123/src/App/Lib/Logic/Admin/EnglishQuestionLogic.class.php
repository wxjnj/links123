<?php
/**
* 英语角后台试题管理logic层
* author reasono
*/
class EnglishQuestionLogic {

	private $error_msg = '';

	/**
	* @param [Integer] $question_id 题目ID
	* @param [Integer] $question_type 题目类型 1=说力 0=听力
	* @return [Array] question and property
	*/
	public function getQuestionAndProperty($question_id, $question_type = 1) {
		$dic = array();
		if ($question_type == 1) {
			$question = D('EnglishQuestionSpeak')->find($question_id);	
		} else {
			$question = D('EnglishQuestion')->find($question_id);
		}
		$ret = D('EnglishLevelname')->getCategoryLevelListBy();
		foreach($ret as $each_lvname) {
			$dic[$each_lvname["id"]] = $each_lvname["name"];
		}
		$ret   = D('EnglishCatquestion')->getQuestionProperty($question_id);
        
		for($i = 0; $i < count($ret); $i++) {
			$cat_attr_id = decbin($ret[$i]["cat_attr_id"]);
			$ret[$i]["voice"]   = substr($cat_attr_id, 0, 1);
			$ret[$i]["target"]  = substr($cat_attr_id, 1, 1);
			$ret[$i]["pattern"] = substr($cat_attr_id, 2, 1);

			$ret[$i]["voice_name"]   = ($ret[$i]["voice"]   == 1) ? "美音" : "英音";
			$ret[$i]["target_name"]  = ($ret[$i]["target"]  == 1) ? "听力" : "说力";
			$ret[$i]["pattern_name"] = ($ret[$i]["pattern"] == 1) ? "视频" : "音频";

			$ret[$i]["level_one_name"] = $dic[$ret[$i]["level_one"]];
			$ret[$i]["level_two_name"] = $dic[$ret[$i]["level_two"]];
			$ret[$i]["level_thr_name"] = $dic[$ret[$i]["level_thr"]];
		}
		return array("question" => $question, "property" => $ret);
	}

	/**
	* 保存题目所属的分类
	* @param [Integer] $question_id
	* @param [Integer] $voice
	* @param [Integer] $target
	* @param [Integer] $pattern
	* @param [Integer] $level_one
	* @param [Integer] $level_two
	* @param [Integer] $level_thr
	* @param [Integer] $status
	* @param [Integer] $type
	*
	* @return [Boolean] 
	*/
	public function saveProperty($question_id, $voice, $target, $pattern, $level_one, $level_two, $level_thr, $status, $type) {
		if ($question_id == 0) {
            $this->error_msg = '缺少题目ID';
            return false;
        }

        if ($level_one == 0 || $level_two == 0 || $level_thr == 0) {
            $this->error_msg = '缺少类目等级参数';
            return false;
        }

        $data["cat_attr_id"] = bindec($voice . $target . $pattern);
        $data["level_one"]   = $level_one;
        $data["level_two"]   = $level_two;
        $data["level_thr"]   = $level_thr;
        
        $cate_ret = D('EnglishCategory')->where($data)->select();
        if (!isset($cate_ret[0]["cat_id"])) {
            $data["status"]  = $status;
            $data["question_num"] = 1;
            $data["created"] = time();
            $add_ret = D('EnglishCategory')->data($data)->add();
            if ($add_ret === false) {
                $this->error_msg = '(#100)添加类目失败';
                return false;
            }
            $cat_id = D('EnglishCategory')->getLastInsID();

            $catquestion_data = array(
                                    "cat_id"      => $cat_id, 
                                    "question_id" => $question_id, 
                                    "created"     => time(),
                                    "type"        => $type, 
                                    "status"      => $status);
            $catq_add_ret = D('EnglishCatquestion')->data($catquestion_data)->add();
            if ($catq_add_ret === false) {
                $this->error_msg = '(#101)添加类目失败！';
                return false;
            }
        } else {
            $catquestion_data = array(
                                    "cat_id"      => $cate_ret[0]["cat_id"], 
                                    "question_id" => $question_id);
            $catquestion_ret  = D('EnglishCatquestion')->where($catquestion_data)->select();
            if (!isset($catquestion_ret[0]["cat_id"])) {
                $catquestion_data = array(
                                    "cat_id"      => $cate_ret[0]["cat_id"], 
                                    "question_id" => $question_id, 
                                    "created"     => time(),
                                    "type"        => $type, 
                                    "status"      => $status);
                $catq_add_ret = D('EnglishCatquestion')->data($catquestion_data)->add();
                if ($catq_add_ret === false) {
                    $this->error_msg = '(#102)添加类目失败';
                    return false;
                }
                //@ 需要更新EnglishCatquestion 题目数＋1
            	D('EnglishCategory')->where(array('cat_id' => $cate_ret[0]["cat_id"]))->setInc('question_num');
            } else {
                $this->error_msg = '已经添加过此类目和题目的对应属性';
                return false;
            }
        }
        return true;
	}

	public function forbidCatetoryOfQuestion($cat_id, $question_id) {
		$ret = D('EnglishCatquestion')->forbidCatQuestionById($cat_id, $question_id, 0);
		return $ret;
	}

	public function resumeCatetoryOfQuestion($cat_id, $question_id) {
		$ret = D('EnglishCatquestion')->forbidCatQuestionById($cat_id, $question_id, 1);
		return $ret;
	}

	public function foreverdelCatquestion($cat_id, $question_id) {
		$ret = D('EnglishCatquestion')->foreverdelCatquestion($cat_id, $question_id);
		return $ret;
	}

	public function isQuestionSpecRecommend($question_id) {
		$ret = D('EnglishCategory')->isQuestionSpecRecommend($question_id);
		return $ret;
	}

	public function setQuestionSpecRecommendBy($question_id) {
		$spec_recommend_id = D('EnglishCategory')->getQuestionSpecRecommend();
		$ret = D('EnglishCatquestion')->setQuestionSpecRecommendBy($question_id, $spec_recommend_id);
		return $ret;
	}

	public function cancelQuestionSpecRecommendBy($question_id) {
		$spec_recommend_id = D('EnglishCategory')->getQuestionSpecRecommend();
		$ret = D('EnglishCatquestion')->cancelQuestionSpecRecommendBy($question_id, $spec_recommend_id);
		return $ret;
	}

	public function getErrorMessage() {
		return $this->error_msg;
	}	
}