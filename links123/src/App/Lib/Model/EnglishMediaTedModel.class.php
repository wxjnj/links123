<?php

/**
 * Description of EnglishMediaTedModel
 *
 * @author Adam
 */
class EnglishMediaTedModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("sort", "require", "排序必须")
    );
    protected $_auto = array(
        array("created", "time", 1, "function"),
        array("updated", "time", 3, "function")
    );

    /**
     * 
     * @param type $object
     * @param type $subject
     * @return boolean
     * @author Adam $date2013.09.16$
     */
    public function getTedIdByObjectOrSubject($object, $subject) {

        if (empty($object) && empty($subject)) {
            return true;
        }
        $ted_name = "";
        if ($subject > 0) {
            $ted_name = D("EnglishMediaSubject")->where(array("id" => $subject))->getField("name");
        } else {
            if ($object > 0) {
                $ted_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
            }
        }

        if ($ted_name) {
            $condition['name'] = $ted_name;
            $ted_id = $this->where($condition)->getField("id");
            if (intval($ted_id) == 0) {
                $max = $this->field("max(`sort`) as maxSort")->find();
                $max_sort = intval($max['maxSort']) + 1;
                $time = time();
                $data['sort'] = $max_sort;
                $data['name'] = $ted_name;
                $data['created'] = $time;
                $data['updated'] = $time;
                $ted_id = $this->add($data);
                if (false === $ted_id) {
                    return false;
                }
            }
        }
        return intval($ted_id);
    }

    public function getTedListToIndex($voice = 1, $target = 1, $pattern = 1) {
        $ret = $this->alias("ted")
                ->field("ted.*,(SELECT COUNT(question.id) from " .
                        C("DB_PREFIX") . "english_question question 
                        right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id 
                        where media.voice={$voice} and question.target={$target} and media.pattern={$pattern} and media.ted=ted.id and media.status=1 
                        and question.status=1) as question_num")
                ->where("ted.status=1")
                ->order("ted.sort asc")
                ->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

    public function getDefaultTedId($voice = 1, $target = 1, $pattern = 1) {
        $condition = "(select count(question.id) from " . C("DB_PREFIX") . "english_question question 
                    right join " . C("DB_PREFIX") . "english_media media on question.media_id=media.id where media.ted=ted.id 
                    and media.voice={$voice} and question.target={$target} and media.pattern={$pattern} and media.status=1 and question.status=1)>0";
        $default_id = $this->alias("ted")->where("{$condition}")->getField("id");
        return $default_id;
    }

}

?>
