CREATE TABLE `__TABLE_NAME__` (
  `dta_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '데이터idx',
  `dta_shf_no` int(11) NOT NULL COMMENT '교대번호',
  `dta_mmi_no` int(11) NOT NULL COMMENT '기종번호',
  `dta_group` varchar(10) NOT NULL DEFAULT 'product' COMMENT '데이터그룹',
  `dta_defect` tinyint(2) NOT NULL COMMENT '불량',
  `dta_defect_type` tinyint(2) NOT NULL COMMENT '불량타입',
  `dta_dt` int(11) DEFAULT NULL COMMENT '일시',
  `dta_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '통계일',
  `dta_value` double DEFAULT 0 COMMENT '정수,음수,실수',
  `dta_reg_dt` int(11) DEFAULT NULL COMMENT '등록일시',
  `dta_update_dt` int(11) DEFAULT NULL COMMENT '수정일시',
  PRIMARY KEY (`dta_idx`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;