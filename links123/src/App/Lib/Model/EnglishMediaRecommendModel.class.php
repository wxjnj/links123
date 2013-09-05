<?php

/**
 * 英语角媒体推荐分类模型类
 *
 * @author Adam $date2013.08.30$
 */
class EnglishMediaRecommendModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("sort", "require", "排序必须")
    );
    protected $_auto = array(
        array("created", "time", 1, "function"),
        array("updated", "time", 3, "function")
    );

    public function getRecommendListToIndex($voice = 1, $target = 1, $pattern = 1) {
        $ret = $this->alias("recommend")
                ->field("recommend.*")
                ->where("recommend.status=1 AND (SELECT COUNT(question.id) from " .
                        C("DB_PREFIX") . "english_question question 
                        RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id 
                        where media.voice={$voice} and question.target={$target} and media.pattern={$pattern} and FIND_IN_SET(recommend.id,media.recommend) and media.status=1 
                        and question.status=1)>0")
                ->order("recommend.sort asc")
                ->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

    public function getDefaultRecommendId($voice = 1, $target = 1, $pattern = 1) {
        $condition = "(select count(question.id) from " . C("DB_PREFIX") . "english_question question 
                    right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id where FIND_IN_SET(recommend.id,media.recommend) 
                    and media.voice={$voice} and question.target={$target} and media.pattern={$pattern} and media.status=1 and question.status=1)>0";
        $default_id = $this->alias("recommend")->where("{$condition}")->getField("id");
        return $default_id;
    }

}

?>
