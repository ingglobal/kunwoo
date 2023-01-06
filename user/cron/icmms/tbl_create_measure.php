<?php
$measure_sql = " CREATE TABLE `{$tbl_measure}` (
    `dta_idx` bigint(20) AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT '데이터idx',
    `dta_dt` int(11) DEFAULT NULL COMMENT '일시',
    `dta_value` double DEFAULT 0 COMMENT '값',
    `dta_reg_dt` int(11) DEFAULT NULL COMMENT '등록일시',
    `dta_update_dt` int(11) DEFAULT NULL COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
sql_query($measure_sql);
$mta_chk = sql_fetch(" SELECT mta_key FROM {$g5['meta_table']} 
                    WHERE mta_db_table = 'icmms_measure'
                        AND mta_db_id = '{$mms_idx}'
                        AND mta_key = '{$tbl_measure}' ");
if(!$mta_chk['mta_key']){
    meta_update(array("mta_country"=>"ko_KR","mta_db_table"=>"icmms_measure","mta_db_id"=>$mms_idx,"mta_key"=>$tbl_measure,"mta_value"=>'0',"mta_reg_dt"=>G5_TIME_YMDHIS));
}
