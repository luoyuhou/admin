CREATE TABLE `fa_course` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员',
    `title` varchar(50) NOT NULL COMMENT '课程',
    `status` tinyint(1) DEFAULT '1' COMMENT '状态',
    `public` tinyint(1) DEFAULT '0' COMMENT '公开类型',
    `type` varchar(250) default null COMMENT '课程类型',
    `price` int(10) unsigned default '0' COMMENT '价格',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    primary key (`id`),
    unique key `course` (`admin_id`, `title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='课程表';

CREATE TABLE `fa_course_type` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员',
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员',
    `name` varchar(10) NOT NULL COMMENT '课程类型',
    primary key (`id`),
    unique key `course_type` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='课程类型';

CREATE TABLE `fa_course_list` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `course_id` int(10) unsigned NOT NULL COMMENT '课程',
    `title` varchar(50) NOT NULL COMMENT '课程标题',
    `preview` tinyint(1) default '1' COMMENT '免费预览',
    `url` varchar(250) default null COMMENT '课程地址',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    primary key (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='课程列表';

CREATE TABLE `fa_course_user` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `course_id` int(10) unsigned NOT NULL COMMENT '课程',
    `user_id` varchar(50) NOT NULL COMMENT '会员',
    `status` tinyint(1) default '1' COMMENT '状态',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    primary key (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户课程权限表';

CREATE TABLE `fa_course_user_process` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `course_list_id` int(10) unsigned NOT NULL COMMENT '课程标题',
    `user_id` varchar(50) NOT NULL COMMENT '会员',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    primary key (`id`),
    unique key `course_process` (`course_list_id`, `user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='课程列表';

ALTER TABLE `fastadmin`.`fa_course`
MODIFY COLUMN `status` varchar(10) NULL DEFAULT 'normal' COMMENT '状态' AFTER `title`;
ALTER TABLE `fastadmin`.`fa_course_type`
MODIFY COLUMN `admin_id` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '管理员' AFTER `id`,
MODIFY COLUMN `user_id` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '会员' AFTER `admin_id`;

CREATE TABLE `fa_upload` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL COMMENT '文件名称',
    `size` int(10) unsigned NOT NULL COMMENT '课程标题',
    `ip` varchar(50) NOT NULL COMMENT '会员',
    `agent` varchar(255) COMMENT '',
    `type` varchar(50) COMMENT '类型',
    `hash` varchar(50) COMMENT '哈希值',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    primary key (`id`),
    unique key `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='上传文件表';