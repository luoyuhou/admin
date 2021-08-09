DROP TABLE IF EXISTS `fa_charges_config`;
CREATE TABLE `fa_charges_config` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `type` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '游戏模式:1=单人模式,2=生涯模式,3=店内联机',
             `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
             `level` tinyint(2) NOT NULL COMMENT '等级',
             `value` int(11) DEFAULT NULL COMMENT '值',
             `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
             `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
             PRIMARY KEY (`id`),
             UNIQUE KEY `charges_config_index` (`type`,`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收费配置';

DROP TABLE IF EXISTS `fa_game_info`;
CREATE TABLE `fa_game_info` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `game_type` int NOT NULL COMMENT "游戏模式",
    `game_name` varchar(16) NOT NULL COMMENT '游戏名称',
    `people` tinyint(4) unsigned NOT NULL COMMENT '参数人数',
    `game_time` tinyint(4) unsigned NOT NULl COMMENT '游戏时间',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游戏信息表';

DROP TABLE IF EXISTS `fa_game_info_detail`;
CREATE TABLE `fa_game_info_detail` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `game_info_id` int NOT NULL COMMENT "游戏记录ID",
    `user_id` int NOT NULL COMMENT '用户ID',
    `rank` tinyint(4) unsigned NOT NULL COMMENT '排名',
    `time` tinyint(4) unsigned NOT NULl COMMENT '耗时',
    `note` text COMMENT '备注',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游戏信息详情表';
