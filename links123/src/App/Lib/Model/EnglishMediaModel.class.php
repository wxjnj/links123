<?php

/**
 * 英语角媒体表模型类
 *
 * @author Adam $date2013.7.25$
 */
class EnglishMediaModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("media_source_url", "require", "来源地址必须"),
        array("media_source_url", "unique", "来源地址已存在", 1, "unique", 1),
    );
    protected $_auto = array(
        array("updated", "time", 3, "function"),
        array("created", "time", 3, "function")
    );

    /**
     * 设置媒体的推荐
     * @param int $id [将要设置的媒体id]
     * @param int $object [推荐到的科目id]
     * @param int $subject [推荐到的专题id]
     * @return boolean|string
     * @author Adam $date2013.09.01$
     */
    public function setRecommend($id, $object, $subject) {
        if(intval($id)==0){
            return false;
        }
        $data['id'] = $id;
        $recommend = intval($this->where(array("id" => $id))->getField("recommend"));
        if ($recommend == 0) {
            $time = time();
            $recommend_ids = array();
            $this->startTrans();
            //科目存在
            if ($object > 0) {
                $recommendModel = D("EnglishMediaRecommend");
                $maxSort = $recommendModel->field("max(`sort`) as max_sort")->find();
                $recommendSort = intval($maxSort['max_sort']) + 1;
                $object_name = D("EnglishObject")->where(array("id" => $object))->getField("name");
                $recommend_id_a = $recommendModel->where(array("name" => $object_name))->getField("id");
                //推荐类存在科目名
                if (intval($recommend_id_a == 0) && $object_name) {
                    $recommend_data['sort'] = $recommendSort;
                    $recommend_data['name'] = $object_name;
                    $recommend_data['created'] = $time;
                    $recommend_data['updated'] = $time;
                    $recommend_id_a = $recommendModel->add($recommend_data);
                    if (false === $recommend_id_a) {
                        $this->rollback();
                        return false;
                    }
                }
                $recommendSort++;
                array_push($recommend_ids, $recommend_id_a);
            }
            //专题存在
            if ($subject > 0) {
                $subject_name = D("EnglishMediaSubject")->where(array("id" => $subject))->getField("name");
                $recommend_id_b = $recommendModel->where(array("name" => $subject_name))->getField("id");
                //推荐类存在专题名
                if (intval($recommend_id_b == 0) && $subject_name) {
                    $recommend_data['sort'] = $recommendSort;
                    $recommend_data['name'] = $subject_name;
                    $recommend_data['created'] = $time;
                    $recommend_data['updated'] = $time;
                    $recommend_id_b = $recommendModel->add($recommend_data);
                    if (false === $recommend_id_b) {
                        $this->rollback();
                        return false;
                    }
                }
                array_push($recommend_ids, $recommend_id_b);
            }
            $recommend = implode(",", $recommend_ids);
        } else {
            $recommend = 0;
        }
        $data['recommend'] = $recommend;
        if (false === $this->save($data)) {
            $this->rollback();
            return false;
        } else {
            $this->commit();
            return $recommend;
        }
    }

}

?>
