<?php

class EnglishLevelModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "等级名称必须"),
        array("sort", "require", "等级排序必须")
    );

    /**
     * 获取默认的等级信息
     * 优先获取系统默认，如果默认下不存在题目。则获取该类下拥有题目的第一个
     * @param int $object [科目id]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [表现类型]
     * @return max [默认等级信息]
     * @author Adam $date2013.5$
     */
    public function getDefaultLevelInfo($object, $voice = 1, $target = 1, $pattern = 1) {
        $question_table_name = "english_question";
        if ($target == 2) {
            $question_table_name = "english_question_speak";
        }
        $object_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
        if ($object_name == "综合") {
            $condition = "(select count(question.id) from " . C("DB_PREFIX") . $question_table_name . " question 
                    right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id where media.level=level.id and media.voice={$voice} 
                    and media.pattern={$pattern} and media.status=1 and question.status=1)>0";
        } else {
            $condition = "(select count(question.id) from " . C("DB_PREFIX") . $question_table_name . " question 
                    right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id where media.level=level.id  and media.voice={$voice} 
                    and media.object={$object} and media.pattern={$pattern} and media.status=1 and question.status=1)>0";
        }
        $default_ret = $this->alias("level")->where("level.default=1 and {$condition}")->find();
        if (false === $default_ret || empty($default_ret)) {
            $default_ret = $this->alias("level")->where($condition)->find();
        }
        return $default_ret;
    }

    /**
     * 根据科目的id获取登记列表，包含科目等级下的题目数量
     * @param int $object_id [科目id]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [表现类型]
     * @return max [等级列表]
     * @author Adam $date2013.5$
     */
    public function getLevelListToIndex($object_id, $voice = 1, $target = 1, $pattern = 1) {
        $question_table_name = "english_question";
        if ($target == 2) {
            $question_table_name = "english_question_speak";
        }
        $object_name = D("EnglishObject")->where("id={$object_id}")->getField("name");
        if ($object_name == "综合") {
            $condition = "media.voice={$voice} and media.pattern={$pattern}
                and media.level=level.id and question.status=1 and media.status=1";
        } else {
            $condition = "media.voice={$voice} and media.pattern={$pattern} 
                and media.level=level.id and question.status=1 and media.status=1  and media.object=" . intval($object_id);
        }
        $ret = $this->alias("level")
                ->field("level.*,(SELECT COUNT(question.id) from " .
                        C("DB_PREFIX") . $question_table_name . " question 
                        right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id 
                        where " . $condition . ") as question_num")
                ->where("level.status=1")
                ->order("level.sort asc")
                ->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }
    
    public function getLevelInfo($level){
    	
    	$ret = $this->alias("level")->where(array("id" => $level, "status" => 1))->find();
    	
    	return $ret;
    }

}

?>
