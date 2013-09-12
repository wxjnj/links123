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

    public function getRecommendIdByObjectOrSubject($object, $subject) {
        if (empty($object) && empty($subject)) {
            return true;
        }
        $recommend_name = "";
        if ($subject > 0) {
            $recommend_name = D("EnglishMediaSubject")->where(array("id" => $subject))->getField("name");
        } else {
            if ($object > 0) {
                $recommend_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
            }
        }

        if ($recommend_name) {
            $condition['name'] = $recommend_name;
            $recommend_id = $this->where($condition)->getField("id");
            if (intval($recommend_id) == 0) {
                $max = $this->field("max(`sort`) as maxSort")->find();
                $max_sort = intval($max['maxSort']) + 1;
                $time = time();
                $data['sort'] = $max_sort;
                $data['name'] = $recommend_name;
                $data['created'] = $time;
                $data['updated'] = $time;
                $recommend_id = $this->add($data);
                if (false === $recommend_id) {
                    return false;
                }
            }
        }
        return intval($recommend_id);
    }

}

?>
