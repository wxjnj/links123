<?php
// 关健字模型
class IllegalKeywordsModel extends CommonModel {
	public $_validate =	array(
		array('keyword_name', 'require', '关健字名称必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
	
	//得到非法关健字
	public function getIllegalKeywordsArr(){
		$illegalKeywordArr = S("illegalKeywordArr");
		if (!empty($illegalKeywordArr)){
			return $illegalKeywordArr ;
		}
		$illegalKeywordArr=$this->where("status = 1")->select();
		S("illegalKeywordArr",$illegalKeywordArr);
	    return $illegalKeywordArr;
	}
	
}
?>
