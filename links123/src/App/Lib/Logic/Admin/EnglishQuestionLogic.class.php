<?php
/**
* 英语角后台试题管理logic层
* author reasono
*/
class EnglishQuestionLogic {

	private $error_msg = '';

	/**
	* @param [Integer] $question_id 题目ID
	* @param [Integer] $question_type 题目类型 0=说力 1=听力
	* @return [Array] question and property
	*/
	public function getQuestionAndProperty($question_id, $question_type = 1) {
		$dic = array();
        $default_list = array();
		if ($question_type == 0) {
			$question = D('EnglishQuestionSpeak')->find($question_id);	
		} else {
			$question = D('EnglishQuestion')->find($question_id);
		}
		$ret = D('EnglishLevelname')->getCategoryLevelListBy();
		foreach($ret as $each_lvname) {
			$dic[$each_lvname["id"]] = $each_lvname["name"];
            if($each_lvname['level'] == 2 && $each_lvname['default'] == 1){
                $default_list[$each_lvname["id"]] = 1;
            }
		}
		$ret   = D('EnglishCatquestion')->getQuestionProperty($question_id, $question_type);
        foreach($ret as $key=>$value){
            if($default_list[$value["level_two"]] == 1){
                unset($ret[$key]);
                continue;
            }
			$cat_attr_id = sprintf("%03d", decbin($value["cat_attr_id"]));
			$ret[$key]["voice"]   = substr($cat_attr_id, 0, 1);
			$ret[$key]["target"]  = substr($cat_attr_id, 1, 1);
			$ret[$key]["pattern"] = substr($cat_attr_id, 2, 1);

			$ret[$key]["voice_name"]   = ($ret[$key]["voice"]   == 1) ? "美音" : "英音";
			$ret[$key]["target_name"]  = ($ret[$key]["target"]  == 1) ? "听力" : "说力";
			$ret[$key]["pattern_name"] = ($ret[$key]["pattern"] == 1) ? "视频" : "音频";

			$ret[$key]["level_one_name"] = $dic[$value["level_one"]];
			$ret[$key]["level_two_name"] = $dic[$value["level_two"]];
			$ret[$key]["level_thr_name"] = $dic[$value["level_thr"]];
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
       
        $englishCategoryModel = D('EnglishCategory');
        $englishCatquestionModel = D("EnglishCatquestion");
        
        //有效试题信息，用于判断是否需要增加试题数量
        $questionModel =D("EnglishQuestion");
        if($type == 0){
            $questionModel = D("EnglishQuestionSpeak");
        }
        $question_info = $questionModel->alias("question")
                        ->join(C("DB_PREFIX")."english_media media on question.media_id = media.id")
                        ->where(array("question.id"=>$question_id,"question.status"=>1,"media.status"=>1))
                        ->find();
        $time = time();
        //需要添加到的分类是否存在
        $cate_ret = $englishCategoryModel->where($data)->find();
        //添加到的分类不存在
        if (!isset($cate_ret["cat_id"])) {
            //准备字典
            $levelnames = D('EnglishLevelname')->order("sort asc")->select();
            $difficulty_list = array();//难度列表
            $grad_list = array();//年级列表
            $object_level_one_id = 7;//使用年级的一级分类id
            //找到难度列表、年级列表和使用年级的一级分类id
            foreach($levelnames as $each_lv) {
                
                if($each_lv['id'] == $level_one){
                    $level_one_info = $each_lv;
                }
                if($each_lv['id'] == $level_two){
                    $level_two_info = $each_lv;
                }
                
                if($each_lv['level'] == 1){
                    if($each_lv['default'] == 1){
                        $object_level_one_id = $each_lv['id'];
                    }
                }elseif($each_lv['level'] == 3){
                    if($each_lv['name'] == "初级"){
                        $difficulty_list["初级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "中级"){
                        $difficulty_list["中级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "高级"){
                        $difficulty_list["高级"] = $each_lv['id'];
                    }else{
                        $grad_list[$each_lv['name']] = $each_lv['id'];
                    }
                }
            }
            $level_sort = $englishCategoryModel->where(array("cat_attr_id"=>$data['cat_attr_id'],"level_one"=>$level_one,"level_two"=>$level_two,"level_one_sort"=>array("gt",0),"level_two_sort"=>array("gt",0)))->find();
            $level_one_sort = intval($level_sort['level_one_sort']) > 0 ? intval($level_sort['level_one_sort']) : $level_one_info['sort'];
            $level_two_sort = intval($level_sort['level_two_sort']) > 0 ? intval($level_sort['level_two_sort']) : $level_two_info['sort'];
            //获取对应的三级分类列表
            if($cate_ret['level_one'] == $object_level_one_id){
                $level_thr_list = $grad_list;
            }else{
                $level_thr_list = $difficulty_list;
            }
            //循环查找三级分类，没有则添加
            $k = 1;
            foreach ($level_thr_list as $value) {
                $level_thr_map = array();
                $level_thr_map['cat_attr_id'] = $data["cat_attr_id"];
                $level_thr_map['level_one'] = $level_one;
                $level_thr_map['level_two'] = $level_two;
                $level_thr_map['level_thr'] = $value;
                $now_cat_id = $englishCategoryModel->where($level_thr_map)->find();
                if(intval($now_cat_id) == 0){
                    if($value == $level_thr && !empty($question_info)){
                        $level_thr_map['question_num'] = 1;
                    }
                    $level_thr_map['updated'] = $level_thr_map['created'] = $time;
                    $level_thr_map['status'] = $status;
                    $level_thr_map['level_one_sort'] = $level_one_sort;
                    $level_thr_map['level_two_sort'] = $level_two_sort;
                    $level_thr_map['level_thr_sort'] = $k;
                    $new_cat_id = $englishCategoryModel->add($level_thr_map);
                    if(false === $new_cat_id){
                        $this->error_msg = '(#100)添加类目失败';
                        return false;
                    }
                    if($value == $level_thr){
                        $cat_id = $new_cat_id;
                    }
                    $k++;
                }
            }

            $catquestion_data = array(
                                    "cat_id"      => $cat_id, 
                                    "question_id" => $question_id, 
                                    "created"     => $time,
                                    "updated"     => $time,
                                    "type"        => $type, 
                                    "status"      => $status);
            $catq_add_ret = $englishCatquestionModel->add($catquestion_data);
            if ($catq_add_ret === false) {
                $this->error_msg = '(#101)添加类目失败！';
                return false;
            }
        } else {
            $catquestion_data = array(
                                    "cat_id"      => $cate_ret["cat_id"], 
                                    "question_id" => $question_id);
            $catquestion_ret  = $englishCatquestionModel->where($catquestion_data)->find();
            if (!isset($catquestion_ret["cat_id"])) {
                $catquestion_data = array(
                                    "cat_id"      => $cate_ret["cat_id"], 
                                    "question_id" => $question_id, 
                                    "created"     => $time,
                                    "updated"     => $time,
                                    "type"        => $type, 
                                    "status"      => $status);
                $catq_add_ret = $englishCatquestionModel->data($catquestion_data)->add();
                if ($catq_add_ret === false) {
                    $this->error_msg = '(#102)添加类目失败';
                    return false;
                }
                
                if(!empty($question_info)){
                    //@ 需要更新EnglishCatquestion 题目数＋1
                    $englishCategoryModel->where(array('cat_id' => $cate_ret["cat_id"]))->setInc('question_num');
                }
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