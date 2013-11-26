<?php
class EnglishCatquestionModel extends CommonModel {
	public function getQuestionProperty($question_id, $type = 1) {
		$ret = $this->alias('catquestion')
					->field('catquestion.cat_id as id, catquestion.question_id, catquestion.type,  category.cat_attr_id, category.level_one, category.level_two, category.level_thr')
					->join('left join ' . C("DB_PREFIX") . 'english_category category on catquestion.cat_id = category.cat_id')
					->where(array("catquestion.question_id" => $question_id,"catquestion.type" => $type, "level_one" => array("neq", "-1")))
					->select();
        if(false === $ret){
            $ret = array();
        }
		return $ret;
	}

	public function forbidCatQuestionById($cat_id, $question_id, $status) {
		$ret = $this->where(array('cat_id' => $cat_id, "question_id" => $question_id))->setField('status',$status);
		return $ret;
	}

	public function foreverdelCatquestion($cat_id, $question_id) {
		$condition = array('cat_id' => $cat_id, "question_id" => $question_id);
		$del_catq_ret = $this->where($condition)->delete();
		$del_qnum_ret = D('EnglishCategory')->where($condition)->setDec('question_num');
		return ($del_catq_ret && $del_qnum_ret);
	}

	/**
	* @param [Integer] $question_id
	* @param [Integer] $spec_recommend_cat_id
	* @return [Boolean]
	*/
	public function setQuestionSpecRecommendBy($question_id, $spec_recommend_cat_id) {
		$catquestion_data = array(
                            "cat_id"      => $spec_recommend_cat_id, 
                            "question_id" => $question_id, 
                            "created"     => time(),
                            "type"        => 1, 
                            "status"      => 1);
        $catq_add_ret = D('EnglishCatquestion')->data($catquestion_data)->add();
        return $catq_add_ret;
	}

	/**
	* @param [Integer] $question_id
	* @param [Integer] $spec_recommend_cat_id
	* @return [Boolean]
	*/
	public function cancelQuestionSpecRecommendBy($question_id, $spec_recommend_cat_id) {
		$catquestion_data = array(
                            "cat_id"      => $spec_recommend_cat_id, 
                            "question_id" => $question_id);
		$catq_del_ret = D('EnglishCatquestion')->data($catquestion_data)->delete();
        
        return $catq_del_ret;
	}
}