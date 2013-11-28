CREATE TABLE IF NOT EXISTS `lnk_member_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'Foreign Key to lnk_member.id,用户id',
  `linknumber` varchar(50) DEFAULT NULL COMMENT '另客号',
  `number_set` int(11) DEFAULT 3 COMMENT '另客号摇号次数',
  `avatar` varchar(200) DEFAULT NULL COMMENT '头像文件名',
  `name` varchar(100) DEFAULT NULL COMMENT '真实姓名',
  `province_located` varchar(120) DEFAULT NULL COMMENT '所在地省',
  `city_located` varchar(120) DEFAULT NULL COMMENT '所在地市',
  `district_located` varchar(120) DEFAULT NULL COMMENT '所在地区',
  `province_based` varchar(120) NOT NULL COMMENT '户籍所在地',
  `city_based` varchar(120) NOT NULL COMMENT '户籍所在地',
  `provice_based` varchar(120) NOT NULL COMMENT '户籍所在地',
  `sex` tinyint(1) DEFAULT 0 COMMENT '性别',
  `sexual_orientation` tinyint(1) DEFAULT NULL COMMENT '性取向',
  `emotional_status` tinyint(1) DEFAULT NULL COMMENT '感情状况',
  `birth_day`   int(2) NOT NULL COMMENT '生日的日',
  `birth_month` int(2) NOT NULL COMMENT '生日的月',
  `birth_year`  int(2) NOT NULL COMMENT '生日的年',
  `blood_type` tinyint(1) DEFAULT NULL COMMENT '血型',
  `constellation` tinyint(1) DEFAULT NULL COMMENT '星座',
  `website` varchar(200) DEFAULT NULL COMMENT '个人网站',
  `alternate_email` varchar(50) DEFAULT NULL COMMENT '备用邮箱',
  `qq` varchar(20) DEFAULT NULL COMMENT '备用邮箱',
  `personal_introductions` text DEFAULT NULL COMMENT '个人介绍',
  `domain_name` varchar(200) DEFAULT NULL COMMENT '个性域名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='个人信息表';

CREATE TABLE IF NOT EXISTS `lnk_member_edu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'Foreign Key to lnk_member.id,用户id',
  `college` varchar(120) DEFAULT NULL COMMENT '大学',
  `college_department` varchar(100) DEFAULT NULL COMMENT '院系',
  `college_enroll_year` int(4) DEFAULT NULL COMMENT '入学年份',
  `senior_high_enroll_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `senior_high` varchar(120) DEFAULT NULL COMMENT '高中',
  `middle_high_enroll_year` int(4) DEFAULT NULL COMMENT '入学年份',
  `middle_high` varchar(120) DEFAULT NULL COMMENT '中专',
  `junior_high_enroll_year` int(4) DEFAULT NULL COMMENT '入学年份',
  `junior_high` varchar(120) DEFAULT NULL COMMENT '初中',
  `primary_enroll_year` int(4) DEFAULT NULL COMMENT '入学年份',
  `primary` varchar(120) DEFAULT NULL COMMENT '小学',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='教育信息表';

CREATE TABLE IF NOT EXISTS `lnk_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(225) DEFAULT NULL COMMENT '标签名',
  `count_member` int(11) DEFAULT NULL COMMENT '贴有此标签的用户数',
  `created_by` int(11) DEFAULT 0 COMMENT '0 表示基本，0<的表示设置此标签的用户id',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='标签表';

CREATE TABLE IF NOT EXISTS `lnk_tag_member_index` (
  `member_id` int(11) NOT NULL COMMENT 'Foreign Key to lnk_member.id,用户id',
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `given_by` smallint(6) DEFAULT NULL COMMENT '发起标签的好友的id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';

CREATE TABLE IF NOT EXISTS `lnk_password_guard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'Foreign Key to lnk_member.id,用户id',
  `question_1` varchar(225) DEFAULT NULL COMMENT '问题1',
  `answer_1` varchar(225) DEFAULT NULL COMMENT '答案1',
  `question_2` varchar(225) DEFAULT NULL COMMENT '问题2',
  `answer_2` varchar(225) DEFAULT NULL COMMENT '答案2',
  `question_3` varchar(225) DEFAULT NULL COMMENT '问题3',
  `answer_3` varchar(225) DEFAULT NULL COMMENT '答案3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='密码保护设置表';

ALTER TABLE `lnk_member` ADD `mobile` INT NULL COMMENT '手机号',
ADD `mobile_verify` INT NULL COMMENT '手机验证码';

--
-- Table structure for table `lnk_privacy`
--

CREATE TABLE IF NOT EXISTS  `lnk_privacy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name_cn` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '隐私名称',
  `tip_cn` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '注解说明',
  `value` int(8) NOT NULL DEFAULT '0' COMMENT '隐私条目数值',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT 'parent id.',
  `seq` int(8) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COMMENT='通用隐私';


INSERT INTO `lnk_privacy` VALUES (1,'评论权限','',0,0,1),(2,'添加好友','',0,0,2),(3,'成就','',0,0,3),(4,'个人信息','',0,0,4),(5,'另客银行','设置另客币财产状况隐私',0,0,5),(6,' 所有人','不包括你的黑名单用户',3,1,1),(7,'可信用户','包括我的好友、另客认证用户、手机绑定用户以及身份验证用户',5,1,2),(8,'仅好友可发表评论','',7,1,3),(9,'允许任何人添加为好友','',3,2,1),(10,'需要验证信息后','',5,2,2),(11,'不允许任何人添加为好友','',7,2,3),(12,'公开','允许他人查看我的成就',3,3,1),(13,'隐私','不允许他人查看我的成就',5,3,2),(14,'允许任何人查看','',3,4,1),(15,'允许可信用户查看','',5,4,2),(16,'仅好友可见','',7,4,3),(17,'我要炫富','所有人可见另客币持有数',3,5,1),(18,'仅好友可见','',5,5,2),(19,'保持低调','仅自己可见',7,5,3),(20,'搜索引擎收录','是否允许搜索引擎搜索到你在另客的信息',0,0,1),(21,'收录相册','',20,3,1),(22,'收录个人主页','',20,5,2),(23,'收录日志','',20,7,3),(24,'收录状态','',20,11,4);

CREATE table IF NOT EXISTS `lnk_privacy_member_index` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) unsigned NOT NULL COMMENT '对应的隐私项目的外键',
  `member_id` int(11) unsigned NOT NULL COMMENT '对应成员id',
  `value` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '计算出的值。',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_privacy` (`privacy_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='通用隐私设置表，关联到用户表和隐私表';

CREATE TABLE `lnk_member_chatlogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `query_password` varchar(32) NOT NULL COMMENT '查询密码',
  `salt` char(6) NOT NULL COMMENT '查询密码的salt.',
  `roaming_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '聊天记录漫游:1,开启; 0,关闭',
  `rows_to_keep` int(8) NOT NULL COMMENT '聊天记录条数',
  `created_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='聊天记录管理表';
