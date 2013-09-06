<?php

/**
 * 英语角媒体表模型类
 *
 * @author Adam $date2013.7.25$
 */
class EnglishMediaModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("object", "require", "科目必须"),
        array("level", "require", "等级必须"),
        array("media_source_url", "require", "来源地址必须"),
        array("media_source_url", "unique", "来源地址已存在", 1, "unique", 1),
    );
    protected $_auto = array(
        array("updated", "time", 3, "function"),
        array("created", "time", 3, "function")
    );

    /**
     * 设置媒体的推荐
     * @param string $id [将要设置的媒体id,多个逗号分隔]
     * @param int $target_recommend [指定是否推荐]
     * @param int $target_subject [指定的专题id]
     * @return boolean|string
     * @author Adam $date2013.09.01$
     */
    public function setRecommend($id, $target_recommend, $target_subject = 0) {
        $ids = explode(",", $id);
        if (empty($ids)) {
            return false;
        }
        $recommendModel = D("EnglishMediaRecommend");
        $recommendList = $recommendModel->field("id,name,`sort`")->where("status=1")->order("`sort` desc")->select();
        foreach ($recommendList as $value) {
            $recommendNameList[$value['name']] = intval($value['id']);
        }
        $recommendSort = intval($recommendList[0]['sort']) + 1;
        $ret = $this->alias("media")
                ->field("media.id,media.recommend,object.name as object_name,subject.name as subject_name")
                ->join(C("DB_PREFIX") . "english_object object on media.object=object.id")
                ->join(C("DB_PREFIX") . "english_media_subject subject on media.subject=subject.id")
                ->where(array("media.id" => array('in', $ids)))
                ->select();
        $time = time();
        if (intval($target_subject) > 0) {
            $target_subject_name = D("EnglishMediaSubject")->where(array("id" => $target_subject))->getField("name");
        }
        $data['updated'] = $time;
        foreach ($ret as $media) {
            $data['id'] = intval($media['id']);
            $recommend = intval($media['recommend']);
            if (isset($target_recommend)) {
                if ($target_recommend == 0 && $recommend == 0) {
                    continue;
                }
            }
            $object_name = $media['object_name'];
            if ($target_subject_name) {
                $subject_name = $target_subject_name;
            } else {
                $subject_name = $media['subject_name'];
            }
            if ($recommend == 0 || $target_recommend == 1) {
                $recommend_ids = array();
                //科目存在
                if ($object_name) {
                    $recommend_id_a = $recommendNameList[$object_name];
                    //推荐类存在科目名
                    if (intval($recommend_id_a) == 0) {
                        $recommend_data['sort'] = $recommendSort;
                        $recommend_data['name'] = $object_name;
                        $recommend_data['created'] = $time;
                        $recommend_data['updated'] = $time;
                        $recommend_id_a = $recommendModel->add($recommend_data);
                        if (false === $recommend_id_a) {
                            $this->rollback();
                            return false;
                        }
                        $recommendNameList[$object_name] = $recommend_id_a;
                    }
                    $recommendSort++;
                    array_push($recommend_ids, $recommend_id_a);
                }
                //专题存在
                if ($subject_name) {
                    $recommend_id_b = $recommendNameList[$subject_name];
                    //推荐类存在专题名
                    if (intval($recommend_id_b) == 0) {
                        $recommend_data['sort'] = $recommendSort;
                        $recommend_data['name'] = $subject_name;
                        $recommend_data['created'] = $time;
                        $recommend_data['updated'] = $time;
                        $recommend_id_b = $recommendModel->add($recommend_data);
                        if (false === $recommend_id_b) {
                            $this->rollback();
                            return false;
                        }
                        $recommendNameList[$subject_name] = $recommend_id_b;
                    }
                    $recommendSort++;
                    array_push($recommend_ids, $recommend_id_b);
                }
                if (empty($recommend_ids)) {
                    $this->rollback();
                    return false;
                }
                sort($recommend_ids);
                $recommend = implode(",", $recommend_ids);
            } else {
                $recommend = 0;
                $data['special_recommend'] = 0;
            }
            $data['recommend'] = $recommend;
            if (false === $this->save($data)) {
                $this->rollback();
                return false;
            }
        }
        if (count($ids) == 1) {
            return $recommend;
        } else {
            return true;
        }
    }

    /**
     * 获取特别推荐的视频列表
     * @return max
     * @author Adam $date2013.09.01$
     */
    public function getSpecialRecommendMediaList($limit = 20) {
        $ret = $this->field("id,name,media_thumb_img")
                ->where("special_recommend=1 and media_thumb_img!=''")
//                ->limit($limit)
                ->order("difficulty desc")
                ->select();
        return $ret;
    }

}

?>
