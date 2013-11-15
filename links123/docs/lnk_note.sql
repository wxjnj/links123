/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : links123_en

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2013-11-15 20:21:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lnk_note
-- ----------------------------
DROP TABLE IF EXISTS `lnk_note`;
CREATE TABLE `lnk_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `content` varchar(5000) NOT NULL,
  `background` varchar(255) NOT NULL,
  `pageX` int(3) NOT NULL DEFAULT '0',
  `pageY` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `created` int(10) NOT NULL,
  `updated` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
