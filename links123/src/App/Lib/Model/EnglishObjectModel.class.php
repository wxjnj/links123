<?php

/**
 * 英语角科目模型类
 * 该模型类，继承CommonModel基础模型类 
 * @author Adam $date2013.5$
 */
class EnglishObjectModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "科目名称必须"),
        array("sort", "require", "科目排序必须")
    );

    /**
     * 获取科目列表到首页
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [表现类型]
     * @return max [科目列表包括当前科目下的题目数量]
     * @author Adam $date2013.6$
     */
    public function getObjectListToIndex($voice = 1, $target = 1, $pattern = 1) {
        $question_table_name = "english_question";
        if ($target == 2) {
            $question_table_name = "english_question_speak";
        }
        $ret = $this->alias("object")
                ->field("object.*,
                    (SELECT COUNT(question.id) from " .
                        C("DB_PREFIX") . $question_table_name . " question right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id 
                        where media.voice={$voice} and media.pattern={$pattern} and media.object=object.id and media.status=1 
                        and question.status=1)as question_num")
                ->where("object.status=1")
                ->order("object.sort asc")
                ->select();
        $all_questiom_num = 0;
        foreach ($ret as $value) {
            $all_questiom_num +=$value['question_num'];
        }
        foreach ($ret as $key => $value) {
            if ($value['name'] == "综合") {
                $ret[$key]['question_num'] = $all_questiom_num;
            }
        }
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

    /**
     * 获取默认的科目信息
     * 优先获取系统默认，如果默认下不存在题目。则获取该类下拥有题目的第一个
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [表现类型]
     * @return max [默认的科目信息]
     * @author Adam $date2013.6$
     */
    public function getDefaultObjectInfo($voice = 1, $target = 1, $pattern = 1) {
        $question_table_name = "english_question";
        if ($target == 2) {
            $question_table_name = "english_question_speak";
        }
        $object_info = $this->where("`default`=1")->find();
        if ($object_info['name'] == "综合") {
            return $object_info;
        }
        $condition = "(select count(question.id) from " . C("DB_PREFIX") . $question_table_name . " question 
                    right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id where media.object=object.id and media.voice={$voice} 
                    and media.pattern={$pattern} and media.status=1 and question.status=1)>0";
        $default_ret = $this->alias("object")->where("`default`=1 and {$condition}")->find();
        if (false === $default_ret || empty($default_ret)) {
            $default_ret = $this->alias("object")->where($condition)->find();
        }
        return $default_ret;
    }
    
    public function getObjectInfo($object){
    	
    	$ret = $this->alias("object")->where(array("id" => $object, "status" => 1))->find();
    	
    	return $ret;
    }

}

?>
