CREATE TABLE `g5_0_material` (
  `mtr_idx` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `mtr_cd` varchar(255) NULL COMMENT '품목코드',
  `mtr_name` varchar(255) NULL COMMENT '품명',
  `mtr_no_std` varchar(255) NULL COMMENT '품명/규격',
  `mtr_cd2` varchar(255) NULL COMMENT '코드'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_0_item20` (
  `itm_idx` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `itm_cd` varchar(255) NULL COMMENT '품목코드',
  `itm_name_std` varchar(255) NULL COMMENT '품명/규격',
  `itm_std` varchar(255) NULL COMMENT '규격',
  `itm_level` varchar(255) NULL COMMENT 'Level',
  `itm_low_cd` varchar(255) NULL COMMENT '하위품목코드',
  `itm_low_name` varchar(255) NULL COMMENT '하위품명',
  `itm_low_std` varchar(255) NULL COMMENT '하위규격',
  `itm_low_kg` float(4,4) NULL COMMENT '단위수량',
  `itm_low_proc` varchar(255) NULL COMMENT '공정',
  `itm_low_proc_name` varchar(255) NULL COMMENT '공정명'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_0_item30` (
  `itm_idx` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `itm_cd` varchar(255) NULL COMMENT '품목코드',
  `itm_name_std` varchar(255) NULL COMMENT '품명/규격',
  `itm_std` varchar(255) NULL COMMENT '규격',
  `itm_level` varchar(255) NULL COMMENT 'Level',
  `itm_low_cd` varchar(255) NULL COMMENT '하위품목코드',
  `itm_low_name` varchar(255) NULL COMMENT '하위품명',
  `itm_low_std` varchar(255) NULL COMMENT '하위규격',
  `itm_low_kg` float(4,4) NULL COMMENT '단위수량',
  `itm_low_proc` varchar(255) NULL COMMENT '공정',
  `itm_low_proc_name` varchar(255) NULL COMMENT '공정명'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

SELECT * FROM `g5_0_item20` WHERE `itm_low_cd` REGEXP '\\s'; 329
SELECT * FROM `g5_0_item20` WHERE `itm_low_proc_name` REGEXP '\\s'; 329

SELECT wut_place_cd, wut_place_name FROM `g5_0_workingplace_uphtime` GROUP BY wut_place_cd;