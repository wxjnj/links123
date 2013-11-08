CREATE TABLE `lnk_member_guest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL COMMENT '游客会员ID，采用ID负数',
  `create_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '1:有效,-1:已删',
  `myarea_sort` text NOT NULL,
  `theme` int(11) NOT NULL DEFAULT '0',
  `app_sort` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员游客表';