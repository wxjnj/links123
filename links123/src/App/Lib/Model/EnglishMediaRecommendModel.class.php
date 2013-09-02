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

    public function getRecommendListToIndex() {
        $ret = $this->where("status=1")->order("`sort` asc")->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

}

?>
