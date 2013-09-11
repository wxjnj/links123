<?php
/**
 * @desc 广告管理模型
 * @name AdvertModel.class.php
 * @package Admin
 * @author Frank UPDATE 2013-09-5
 * @version 1.0
 */
class AdvertModel extends CommonModel {
    public $_validate	=	array(
        array('name','require','标题必须'),
        );
}