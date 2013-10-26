<?php
/**
* 英语角后台类目串的model层
* author reasono
*/
class EnglishCategoryModel extends CommonModel {

	/**
	* @param [Integer] $question_id
	* @return [Boolean]
	*/
	public function getQuestionSpecRecommend() {
		$ret = $this->alias('category')
				    ->field('cat_id')
				    ->where(array("level_one" => "-1", "level_two" => "-1", "level_thr" => "-1"))
				    ->select();
		return $ret[0]['cat_id'];
	}

	/**
	* @param [Integer] $question_id
	* @return [Boolean]
	*/
	public function isQuestionSpecRecommend($question_id) {
		$ret = $this->alias('category')
				    ->field('english_catquestion.question_id')
				    ->join('left join ' . C("DB_PREFIX") . 'english_catquestion catquestion on catquestion.cat_id = category.cat_id')
				    ->where(array("level_one" => "-1", "level_two" => "-1", "level_thr" => "-1", "question_id" => $question_id))
				    ->select();
		if (isset($ret[0]['question_id'])) {
			return true;
		}
		return false;
	}
}