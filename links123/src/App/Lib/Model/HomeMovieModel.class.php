<?php
// 配置类型模型
class HomeMovieModel extends CommonModel {
    protected $_validate = array(
        array('title','require','名称必须'),
        );

    protected $_auto		=	array(
        array('status',0,self::MODEL_INSERT,'string'),
        array('create_time','time',self::MODEL_INSERT,'function'),
        );
    
}