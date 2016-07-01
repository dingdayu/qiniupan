/*
Navicat MariaDB Data Transfer

Source Server         : doc.dingxiaoyu.com
Source Server Version : 100109
Source Host           : doc.dingxiaoyu.com:3306
Source Database       : qiniupan

Target Server Type    : MariaDB
Target Server Version : 100109
File Encoding         : 65001

Date: 2016-07-01 11:13:27
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_directory
-- ----------------------------
DROP TABLE IF EXISTS `tp_directory`;
CREATE TABLE `tp_directory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '目录名称',
  `pid` int(10) unsigned DEFAULT NULL COMMENT '上级ID',
  `last_time` int(11) unsigned DEFAULT '0' COMMENT '最后修改时间',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '记录创建',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '记录更新时间',
  `status` tinyint(3) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for tp_files
-- ----------------------------
DROP TABLE IF EXISTS `tp_files`;
CREATE TABLE `tp_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '文件名',
  `dir` varchar(255) DEFAULT '/' COMMENT '文件目录',
  `hash` varchar(255) DEFAULT NULL COMMENT 'hash',
  `fsize` int(10) unsigned DEFAULT '0',
  `mimeType` varchar(255) DEFAULT '' COMMENT '文件类型',
  `putTime` int(11) unsigned DEFAULT NULL COMMENT '文件上传时间',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '记录创建',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '记录更新时间',
  `status` tinyint(3) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for tp_user
-- ----------------------------
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) DEFAULT '' COMMENT '用户密码',
  `email` varchar(255) DEFAULT '' COMMENT '用户邮箱',
  `last_login_time` int(10) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(255) DEFAULT '' COMMENT '最后登录IP',
  `reg_time` int(10) unsigned DEFAULT '0' COMMENT '注册时间',
  `reg_ip` varchar(255) DEFAULT '' COMMENT '用户注册IP',
  `status` varchar(255) DEFAULT 'init' COMMENT '用户状态(默认为:init;正常:normal;锁定:locked;删除:released)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
