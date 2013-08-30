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

}

?>
