CREATE TABLE `typecho_domaintheme` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'domaintheme表主键',
  `name` varchar(200) default NULL COMMENT 'domaintheme名称',
  `domain` varchar(200) default NULL COMMENT 'domaintheme域名',
  `theme` varchar(200) default NULL COMMENT 'domaintheme主题',
  `user` varchar(200) default NULL COMMENT '自定义',
  PRIMARY KEY  (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=%charset%;
