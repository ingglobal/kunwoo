CREATE TABLE `__TABLE_NAME__` (
  `dta_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '데이터idx',
  `dta_dt` int(11) DEFAULT NULL COMMENT '일시',
  `dta_value` double DEFAULT 0 COMMENT '정수,음수,실수',
  `dta_reg_dt` int(11) DEFAULT NULL COMMENT '등록일시',
  `dta_update_dt` int(11) DEFAULT NULL COMMENT '수정일시',
  PRIMARY KEY (`dta_idx`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;