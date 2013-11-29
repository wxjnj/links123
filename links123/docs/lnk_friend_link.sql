/*
Navicat MySQL Data Transfer

Source Server         : linkscn
Source Server Version : 50532
Source Host           : 223.4.56.81:3306
Source Database       : links

Target Server Type    : MYSQL
Target Server Version : 50532
File Encoding         : 65001

Date: 2013-11-29 18:19:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lnk_friend_link
-- ----------------------------
DROP TABLE IF EXISTS `lnk_friend_link`;
CREATE TABLE `lnk_friend_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `web_name` varchar(255) NOT NULL COMMENT '网站名称',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `created` int(10) NOT NULL DEFAULT '0',
  `updated` int(10) NOT NULL DEFAULT '0',
  `sort` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态，0正常，1，删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lnk_friend_link
-- ----------------------------
INSERT INTO `lnk_friend_link` VALUES ('15', '另客网', 'http://www.links123.cn', '1385719938', '1385719938', '1', '0');
INSERT INTO `lnk_friend_link` VALUES ('17', '英语角', 'http://en.links123.cn/', '1385719954', '1385719954', '2', '0');
INSERT INTO `lnk_friend_link` VALUES ('19', '另客导航', 'http://www.links123.cn/Home/Index/nav.html', '1385719988', '1385719988', '3', '0');
INSERT INTO `lnk_friend_link` VALUES ('21', '上海化妆学校', 'http://www.zhibeauty.com/', '1385720022', '1385720022', '4', '0');
