<?php

/**
 * 英语角专题模型类
 *
 * @author Adam $date2013.08.27$
 */
class EnglishMediaSubjectModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("sort", "require", "排序必须")
    );
    protected $_auto = array(
        array("created", "time", 1, "function"),
        array("updated", "time", 3, "function")
    );

    public function getSubjectListToIndex($voice = 1, $target = 1, $pattern = 1) {
        $ret = $this->alias("subject")
                ->field("subject.*,(SELECT COUNT(question.id) from " .
                        C("DB_PREFIX") . "english_question question 
                        right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id 
                        where media.voice={$voice} and question.target={$target} and media.pattern={$pattern} and media.subject=subject.id and media.status=1 
                        and question.status=1) as question_num")
                ->where("subject.status=1")
                ->order("subject.sort asc")
                ->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

}

?>
