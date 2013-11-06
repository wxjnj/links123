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
	* @param [int] $question_id
	* @param [int] $voice
	* @param [int] $target
	* @param [int] $pattern
	* @param [int] $level_one
	* @param [int] $level_two
	* @param [int] $level_thr
	* @param [int] $status
	* @param [int] $type
     * @param [int] $is_add Description
	*
	* @return [Boolean] 
	*/
	public function saveProperty($question_id, $voice, $target, $pattern, $level_one, $level_two, $level_thr, $status, $type, $is_add = true) {
		if ($question_id == 0) {
            $this->error_msg = '缺少题目ID';
            return false;
        }

        if ($level_one == 0 || $level_two == 0 || $level_thr == 0) {
            $this->error_msg = '缺少类目等级参数';
            return false;
        }
        $catQuestionModel = D("EnglishCatquestion");
        if(is_null($voice) && is_null($target) && is_null($pattern)){
            $cat_question_map = array(
                "a.type" => $type,
                "a.question_id" => $question_id
            );
            $question_cat_attr_id = $catQuestionModel->alias("a")
                ->join(C("DB_PREFIX")."english_category b on a.cat_id=b.cat_id")
                ->where($cat_question_map)
                ->order("a.status desc,a.created desc")
                ->getField("b.cat_attr_id");
            $data["cat_attr_id"] = intval($question_cat_attr_id);
        }else{
            $data["cat_attr_id"] = bindec($voice . $target . $pattern);
        }
        
        if($is_add){
            //判断当前一级分类下是否已有分类，不允许一级分类试题对应多个二级
            $new_map = array(
                    "b.level_one" => $level_one,
                    "b.cat_attr_id" => $data["cat_attr_id"],
                    'a.question_id' => $question_id,
                    'a.type' => $type
                );
                $level_one_cat_id = $catQuestionModel->alias("a")
                        ->join(C("DB_PREFIX")."english_category b on a.cat_id=b.cat_id")
                        ->where($new_map)
                        ->getField("a.cat_id");
            if(intval($level_one_cat_id) > 0){
                $this->error_msg = '当前一级分类已存在，不允许试题对应多个一级分类';
                return false;
            }
        }
        
        //准备字典
        $levelnames = D('EnglishLevelname')->order("sort asc")->select();
        $difficulty_list = array();//难度列表
        $grad_list = array();//年级列表
        $grade_name_list = array();
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
                    $grade_name_list[$each_lv['name']] = $each_lv;
                }
            }
        }
        $data["level_one"]   = $level_one;
        $data["level_two"]   = $level_two;
        //查找这个试题的年级，自动生成三级目录
        if($level_one != $object_level_one_id){
            $new_map = array(
                "b.level_one" => $object_level_one_id,
                'a.question_id' => $question_id,
                'a.type' => $type
            );
            $grade_level_thr_sort = $catQuestionModel->alias("a")
                    ->join(C("DB_PREFIX")."english_category b on a.cat_id=b.cat_id")
                    ->where($new_map)
                    ->getField("level_thr_sort");
            if(intval($grade_level_thr_sort) > 0){
                if ($grade_level_thr_sort <= $grade_name_list['小六']['sort']) {
                    $level_thr = $difficulty_list["初级"];
                } else if ($grade_level_thr_sort >= $grade_name_list['大一']['sort']) {
                    $level_thr = $difficulty_list["高级"];
                } else {
                    $level_thr = $difficulty_list["中级"];
                }
            }
        }
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
            
            $level_sort = $englishCategoryModel->where(array("cat_attr_id"=>$data['cat_attr_id'],"level_one"=>$level_one,"level_two"=>$level_two,"level_one_sort"=>array("gt",0),"level_two_sort"=>array("gt",0)))->find();
            $level_one_sort = intval($level_sort['level_one_sort']) > 0 ? intval($level_sort['level_one_sort']) : $level_one_info['sort'];
            $level_two_sort = intval($level_sort['level_two_sort']) > 0 ? intval($level_sort['level_two_sort']) : $level_two_info['sort'];
            //获取对应的三级分类列表
            if($level_one == $object_level_one_id){
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
    public function setQuestionCatAttrId($id, $type = "voice",$question_type = 1){
        if($id == 0){
            $this->error_msg = "请指定操作对象";
            return false;
        }
        $time = time();
        $catQuestionModel = D("EnglishCatquestion");
        $categoryModel = D("EnglishCategory");
        $map = array();
        $map['a.question_id'] = $id;
        $map['a.type'] = $question_type;
        //查询试题的所有分类列表
        $cat_list = $catQuestionModel->alias("a")
                ->join(C("DB_PREFIX")."english_category b on a.cat_id=b.cat_id")
                ->where($map)
                ->order("a.status desc,a.created asc")
                ->select();
        Log::write("查询：".$catQuestionModel->getLastSql(), log::SQL);
        //查询试题是否可用
        if($question_type == 1){
            $questionModel = D("EnglishQuestion");
        }else{
            $questionModel = D("EnglishQuestionSpeak");
        }
        $question_info = $questionModel->alias("a")
                ->join(C("DB_PREFIX")."english_media b on a.media_id = b.id")
                ->where(array("a.id"=>$id,"a.status"=>1,"b.status"=>1))
                ->find();
        $new_cat_attr_id = 7;
        if(false !== $cat_list && !empty($cat_list)){
            //计算新的cat_attr_id
            $question_cat_attr_id = $cat_list[0]["cat_attr_id"];
            $bin_cat = sprintf("%03d",decbin($question_cat_attr_id));
            $voice = substr($bin_cat, 0, 1);
            $target = substr($bin_cat, 1, 1);
            $pattern = substr($bin_cat, 2, 1);
            if($$type == 1){
                $$type = 0;
            }else{
                $$type = 1;
            }
            $new_cat_attr_id = bindec($voice."".$target."".$pattern);
            if($new_cat_attr_id >= 0 && $new_cat_attr_id <= 7){
                //准备字典
                $levelnames = D('EnglishLevelname')->order("sort asc")->select();
                $difficulty_list = array();//难度列表
                $grad_list = array();//年级列表
                $object_level_one_id = 7;//使用年级的一级分类id
                //找到难度列表、年级列表和使用年级的一级分类id
                foreach($levelnames as $each_lv) {

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
                //删除原有的试题分类关联
                if(false === $catQuestionModel->where(array("question_id"=>$id))->delete()){
                    $this->error_msg = "删除原有分类失败[#000]";
                    return false;
                }
                //循环查找新的分类，并关联试题
                foreach($cat_list as $value){
                    //原来分类的有效试题数更新
                    if(!empty($question_info)){
                        $categoryModel->where(array("cat_id"=>$value['cat_id']))->setDec("question_num");
                        Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
                    }
                    $new_cat_map = array();
                    $new_cat_map['cat_attr_id'] = $new_cat_attr_id;
                    $new_cat_map['level_one'] = $value['level_one'];
                    $new_cat_map['level_two'] = $value['level_two'];
                    $new_cat_map['level_thr'] = $value['level_thr'];
                    $new_cat_id = $categoryModel->where($new_cat_map)->getField("cat_id");
                    Log::write("查询分类：".$categoryModel->getLastSql(), log::SQL);
                    if(false === $new_cat_id){
                        $this->error_msg = "查找新分类失败[#001]";
                        return false;
                    }
                    //没有找到，则增加
                    if(intval($new_cat_id) == 0){
                        if($value['level_one'] == $object_level_one_id){
                            $level_thr_list = $grad_list;
                        }else{
                            $level_thr_list = $difficulty_list;
                        }
                        $k = 0;
                        foreach($level_thr_list as $level_thr){
                            $new_cat_map['level_thr'] = $level_thr;
                            if(intval($categoryModel->where($new_cat_map)->getField("cat_id")) > 0){
                                continue;
                            }
                            $new_cat_map['created'] = $new_cat_map['updated'] = $time;
                            $new_cat_map['level_one_sort'] = $value['level_one_sort'];
                            $new_cat_map['level_two_sort'] = $value['level_two_sort'];
                            $new_cat_map['level_thr_sort'] = ++$k;
                            if($level_thr == $value['level_thr'] && !empty($question_info)){
                                $new_cat_map['question_num'] = 1;
                            }else{
                                $new_cat_map['question_num'] = 0;
                            }
                            $new_id = $categoryModel->add($new_cat_map);
                            Log::write("增加分类：".$categoryModel->getLastSql(), log::SQL);
                            if(false === $new_id){
                                $this->error_msg = "增加新分类失败[#002]";
                                return false;
                            }
                            if($level_thr == $value['level_thr']){
                                $new_cat_id = $new_id;
                            }
                        }
                    }else{
                        if(!empty($question_info)){
                            if(false === $categoryModel->where(array("cat_id"=>$new_cat_id))->setInc("question_num")){
                                $this->error_msg = "更新分类的试题数量[#005]";
                                return false;
                            }
                            Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
                        }
                    }
                    //关联分类和试题
                    $cat_question_data = array(
                        "cat_id" => $new_cat_id,
                        'type' => $question_type,
                        "question_id" => $id
                    );
                    if(false === $catQuestionModel->add($cat_question_data)){
                        $this->error_msg = "增加试题分类关联失败[#003]";
                        Log::write("更新分类试题关联：".$catQuestionModel->getLastSql(), log::SQL);
                        return false;
                    }
                    Log::write("更新分类试题关联：".$catQuestionModel->getLastSql(), log::SQL);
                }
                return $$type;
            }else{
                $this->error_msg = "指定的大分类属性错误[#004]";
                return false;
            }
        }
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