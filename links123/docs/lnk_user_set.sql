CREATE TABLE IF NOT EXISTS `lnk_user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_number` varchar(50) DEFAULT NULL COMMENT '另客号',
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
  `birthday` varchar(30) NOT NULL COMMENT '生日',
  `blood_type` tinyint(1) DEFAULT NULL COMMENT '血型',
  `constellation` tinyint(1) DEFAULT NULL COMMENT '星座',
  `website` varchar(200) DEFAULT NULL COMMENT '个人网站',
  `alternate_email` varchar(50) DEFAULT NULL COMMENT '备用邮箱',
  `qq` varchar(20) DEFAULT NULL COMMENT '备用邮箱',
  `personal_introductions` text DEFAULT NULL COMMENT '个人介绍',
  `domain_name` varchar(200) DEFAULT NULL COMMENT '个性域名',
  `user_id` smallint(6) NOT NULL COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='个人信息表';

CREATE TABLE IF NOT EXISTS `lnk_user_edu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `college_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `college` varchar(120) DEFAULT NULL COMMENT '大学',
  `college_department` varchar(100) DEFAULT NULL COMMENT '院系',
  `senior_high_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `senior_high` varchar(120) DEFAULT NULL COMMENT '高中',
  `middle_high_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `middle_high` varchar(120) DEFAULT NULL COMMENT '中专',
  `junior_high_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `junior_high` varchar(120) DEFAULT NULL COMMENT '初中',
  `primary_enroll_year` varchar(4) DEFAULT NULL COMMENT '入学年份',
  `primary` varchar(120) DEFAULT NULL COMMENT '小学',
  `user_id` smallint(6) NOT NULL COMMENT '用户名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='教育信息表';

CREATE TABLE IF NOT EXISTS `lnk_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(225) DEFAULT NULL COMMENT '标签名',
  `count_user` int(11) DEFAULT NULL COMMENT '标签用户数',
  `created_by` smallint(6) DEFAULT NULL COMMENT '0 表示基本，0<的表示有用户设置的',
  `uptime` timestamp NULL DEFAULT NULL COMMENT '入学年份',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='标签表';

CREATE TABLE IF NOT EXISTS `lnk_tag_user_index` (
  `user_id` smallint(6) NOT NULL COMMENT '关联用户',
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `given_user_id` smallint(6) DEFAULT NULL COMMENT '发起标签的好友的id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';

CREATE TABLE IF NOT EXISTS `lnk_password_guard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_1` varchar(225) DEFAULT NULL COMMENT '问题1',
  `answer_1` varchar(225) DEFAULT NULL COMMENT '答案1',
  `question_2` varchar(225) DEFAULT NULL COMMENT '问题2',
  `answer_2` varchar(225) DEFAULT NULL COMMENT '答案2',
  `question_3` varchar(225) DEFAULT NULL COMMENT '问题3',
  `answer_3` varchar(225) DEFAULT NULL COMMENT '标签名',
  `user_id` smallint(6) NOT NULL COMMENT '答案3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='密码保护设置表';

ALTER TABLE `lnk_user` ADD `mobile` INT NULL COMMENT '手机号',
ADD `mobile_verify` INT NULL COMMENT '手机验证码';
