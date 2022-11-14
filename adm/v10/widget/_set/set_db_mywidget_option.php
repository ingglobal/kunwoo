<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$mywidget_option_sql = " CREATE TABLE `".$g5['mywidget_option_table']."` (
    `mwo_idx`     		BIGINT(20)  NOT NULL,
    `wig_idx`     	    INT(11)  NOT NULL COMMENT '위젯idx : wig_idx번호',
    `mwo_name`          VARCHAR(20) NOT NULL COMMENT '추가옵션설정에 해당하는 컬럼 slick의 autoplay, arrow, dots등등',
	`mwo_value` 	    TEXT    NULL     COMMENT '추가옵션설정에 대한 값'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
sql_query($mywidget_option_sql);
sql_query(" ALTER TABLE `".$g5['mywidget_option_table']."` ADD PRIMARY KEY (`mwo_idx`) ");
sql_query(" ALTER TABLE `".$g5['mywidget_option_table']."` MODIFY `mwo_idx` bigint(20) NOT NULL AUTO_INCREMENT ");