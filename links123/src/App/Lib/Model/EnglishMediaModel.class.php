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

}

?>
