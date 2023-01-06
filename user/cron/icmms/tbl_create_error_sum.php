<?php
$error_sum_sql = " CREATE TABLE `{$tbl_error_sum}` (
    `dta_idx` bigint(20) AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT '데이터idx',
    `com_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '업체번호',
    `imp_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'iMP번호',
    `mms_idx` int(11) NOT NULL COMMENT '설비코드',
    `mmg_idx` int(11) NOT NULL DEFAULT 0 COMMENT '그룹idx',
    `shf_idx` int(11) NOT NULL COMMENT '교대idx',
    `cod_idx` int(11) NOT NULL DEFAULT 0 COMMENT '코드idx',
    `trm_idx_category` int(11) NOT NULL DEFAULT 0 COMMENT '구분',
    `dta_shf_no` int(11) NOT NULL COMMENT '교대번호',
    `dta_group` varchar(10) NOT NULL COMMENT '데이터그룹',
    `dta_code` varchar(10) NOT NULL COMMENT '코드',
    `dta_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '날짜',
    `dta_value` int(11) NOT NULL DEFAULT 0 COMMENT '값'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
sql_query($error_sum_sql);
