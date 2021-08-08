DROP TABLE IF EXISTS `fa_charges_config`;
CREATE TABLE `fa_charges_config` (
    `id`      int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`    varchar(30)  NOT NULL COMMENT '名称',
    `level`   tinyint(2)  NOT NULL COMMENT '等级',
    `value`   int COMMENT '值',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    index KEY `charges_config_index` (`name`, `level`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT='收费配置';