<?php
$downtime_sql = " CREATE TABLE `{$tbl_downtime}` (
    `dta_idx` bigint(20) AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT '데이터',
    `com_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '업체번호',
    `imp_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'iMP번호',
    `mms_idx` int(11) NOT NULL COMMENT '설비코드',
    `mmg_idx` int(11) NOT NULL DEFAULT 0 COMMENT '그룹',
    `mst_idx` int(11) NOT NULL COMMENT '비가동타입',
    `dta_start_dt` int(11) NOT NULL DEFAULT 0 COMMENT '비가동시작일시',
    `dta_end_dt` int(11) NOT NULL DEFAULT 0 COMMENT '비가동종료일시',
    `dta_memo` text DEFAULT '' COMMENT '메모',
    `dta_reg_dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
    `dta_update_dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
sql_query($downtime_sql);

