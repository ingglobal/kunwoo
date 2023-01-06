<?php
$error_sql = " CREATE TABLE `{$tbl_error}` (
    `dta_idx` bigint(20) AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT '데이터idx',
    `com_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '업체번호',
    `imp_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'iMP번호',
    `mms_idx` int(11) NOT NULL COMMENT '설비코드',
    `shf_idx` int(11) NOT NULL COMMENT '교대idx',
    `cod_idx` int(11) NOT NULL DEFAULT 0 COMMENT '업체idx',
    `trm_idx_category` int(11) NOT NULL DEFAULT 0 COMMENT '구분',
    `dta_shf_no` int(11) NOT NULL COMMENT '교대번호',
    `dta_shf_max` int(11) NOT NULL COMMENT '총교대수',
    `dta_group` varchar(10) NOT NULL COMMENT '데이터그룹',
    `dta_code` varchar(10) NOT NULL COMMENT '코드',
    `dta_dt` int(11) DEFAULT NULL COMMENT '일시',
    `dta_message` varchar(100) DEFAULT NULL COMMENT '메시지',
    `dta_status` tinyint(4) DEFAULT NULL COMMENT '상태',
    `dta_reg_dt` int(11) DEFAULT NULL COMMENT '등록일시',
    `dta_update_dt` int(11) DEFAULT NULL COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
sql_query($error_sql);
