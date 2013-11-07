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
    /**
     * 根据试题id更新分类的有效试题数量
     * @param int/array $question_id [试题id，string或者数组]
     * @param int $isDec [是否减少，true为减少，flase为增加]
     * @param int $type [试题类型，1听力，0说力]
     */
    public function updateCategoryQuestionNumByQuestion($question_id, $isDec, $type){
        $question_ids = array();
        if(!is_array($question_id)){
            $question_ids[] = $question_id;
        }else{
            $question_ids = $question_id;
        }
        $catquestionModel = D("EnglishCatquestion");
        $categoryModel = D("EnglishCategory");
        $time = time();
        foreach ($question_ids as $value){
            $cat_question_map = array(
                "a.question_id"=>$value,
                "a.type"=>$type,
                "a.status"=>1
            );
            $cat_list = $catquestionModel
                    ->alias("a")
                    ->field("a.cat_id,b.question_num")
                    ->join(C("DB_PREFIX")."english_category b on b.cat_id=a.cat_id")
                    ->where($cat_question_map)->select();
            Log::write("查询试题分类：".$catquestionModel->getLastSql(), LOG::INFO);
            foreach($cat_list as $v){
                $data=array();
                if($isDec){
                    if($v['question_num'] <= 0){
                        $data['question_num'] = 0;
                    }else{
                        $data['question_num'] = array('exp','question_num-1');
                    }
                }else{
                    if($v['question_num'] <= 0){
                        $data['question_num'] = 1;
                    }else{
                        $data['question_num'] = array('exp','question_num+1');
                    }
                }
                $data['updated'] = $time;
                $map = array(
                    "cat_id"=>$v['cat_id']
                );
                $ret = $categoryModel->where($map)->save($data);
                Log::write("更新试题数量：".$categoryModel->getLastSql(), LOG::INFO);
                if(false === $ret){
                    return false;
                }
            }
        }
        return true;
    }
}