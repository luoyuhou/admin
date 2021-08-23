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
    `game_type` int NOT NULL COMMENT '游戏模式',
    `game_name` varchar(16) NOT NULL COMMENT '游戏名称',
    `people` tinyint(4) unsigned NOT NULL COMMENT '参数人数',
    `game_time` tinyint(4) unsigned NOT NULl COMMENT '游戏时间',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游戏信息表';

DROP TABLE IF EXISTS `fa_game_info_detail`;
CREATE TABLE `fa_game_info_detail` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `game_info_id` int NOT NULL COMMENT '游戏记录ID',
    `user_id` int NOT NULL COMMENT '用户ID',
    `rank` tinyint(4) unsigned NOT NULL COMMENT '排名',
    `time` tinyint(4) unsigned NOT NULl COMMENT '耗时',
    `note` text COMMENT '备注',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游戏信息详情表';

DROP TABLE IF EXISTS `fa_order`;
CREATE TABLE `fa_order` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '用户',
    `order_id` varchar(32) NOT NULL COMMENT '订单号',
    `state` tinyint(2) NOT NULL DEFAULT 0 COMMENT '订单状态',
    `amount` int unsigned NOT NULL COMMENT '所得金额',
    `price` int unsigned NOT NULL COMMENT '标价',
    `money` int unsigned NOT NULL COMMENT '充值金额',
    `discount` tinyint(2) unsigned NOT NULL DEFAULT 0 COMMENT '折扣',
    `is_delete` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `finishtime` int(10) DEFAULT NULL COMMENT '完成时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_index`(`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值订单表';

DROP TABLE IF EXISTS `fa_order_detail`;
CREATE TABLE `fa_order_detail` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `o_id` int unsigned NOT NULL COMMENT '订单ID',
    `event` varchar(255) DEFAULT NULL COMMENT '活动',
    `coupon` varchar(255) DEFAULT NULL COMMENT '使用卡券',
    `additional` varchar(255) DEFAULT NULL COMMENT '额外赠送',
    `description` varchar(255) DEFAULT NULL COMMENT '说明',
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_detail_index`(`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单详情表';

DROP TABLE IF EXISTS `fa_order_recharege`;
CREATE TABLE `fa_order_recharge` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `o_id` int unsigned NOT NULL COMMENT '订单ID',
    `recharge_money` int unsigned NOT NULL DEFAULT 0 COMMENT '支付金额',
    `recharge_type` tinyint unsigned NOT NULL COMMENT '支付方式',
    `createtime` int unsigned NOT NULL COMMENT '支付时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单支付表';

DROP TABLE IF EXISTS `fa_refund`;
CREATE TABLE `fa_refund` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '用户',
    `order_id` varchar(32) NOT NULL COMMENT '订单号',
    `state` tinyint(2) NOT NULL DEFAULT 0 COMMENT '订单状态',
    `money` int unsigned NOT NULL COMMENT '退款金额',
    `receiver` varchar(128) NOT NULL COMMENT '收款账号',
    `note` text DEFAULT NULL COMMENT '备注',
    `createtime` int(10) DEFAULT NULL COMMENT '申请时间',
    `finishtime` int(10) DEFAULT NULL COMMENT '完成时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `refund_index`(`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='退款表';

DROP TABLE IF EXISTS `fa_terminal`;
CREATE TABLE `fa_terminal` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `number` varchar(64) NOT NULL COMMENT '设备编号',
    `name` varchar(64) NOT NULL COMMENT '设备名称',
    `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '设备状态',
    `group` tinyint(4) unsigned NOT NULL COMMENT '设备分组',
    `level` tinyint(2) unsigned NOT NULL COMMENT '设备级别',
    `address` varchar(64) DEFAULT NULL COMMENT '设备位置',
    `usetime` int unsigned NOT NULL DEFAULT 0 COMMENT '设备使用时间',
    `available` tinyint(1) NOT NULL DEFAULT 1 COMMENT '设备可用',
    `createtime` int(10) DEFAULT NULL COMMENT '投用时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `terminal_index`(`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='设备终端表';

DROP TABLE IF EXISTS `fa_terminal_config`;
CREATE TABLE `fa_terminal_config` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `t_id` int unsigned NOT NULL COMMENT '设备id',
    `video_url` varchar(255) COMMENT '视频地址',
    `text` text COMMENT '描述',
    `btn_config_package` text COMMENT '按键配置',
    `extend` text COMMENT '其他',
    PRIMARY KEY (`id`),
    UNIQUE KEY `terminal_config_index`(`t_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='设备终端配置表';

DROP TABLE IF EXISTS `fa_event`;
CREATE TABLE `fa_event` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '操作者',
    `title` varchar(128) NOT NULL COMMENT '活动名称',
    `description` text NOT NULL COMMENT '活动描述',
    `prop_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '奖励类型',
    `prop_amount` int unsigned NOT NULL COMMENT '奖励数量',
    `repeat` tinyint(1) NOT NULL DEFAULT 1 COMMENT '活动是否重复参加',
    `cycle` int unsigned NOT NULL DEFAULT 0 COMMENT '周期，单位/天',
    `status` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '活动状态',
    `createtime` int unsigned NOT NULL COMMENT '活动开始时间',
    `updatetime` int unsigned COMMENT '活动更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='活动表';
