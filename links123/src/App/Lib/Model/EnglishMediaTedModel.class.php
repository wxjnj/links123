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

}

?>
