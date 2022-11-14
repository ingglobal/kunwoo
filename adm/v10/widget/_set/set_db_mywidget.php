<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$mywidget_sql = " CREATE TABLE `".$g5['mywidget_table']."` (
    `myw_idx`           INT(11) NOT NULL,
    `myw_name`          VARCHAR(30) NOT NULL COMMENT '마이위젯이름',
    `dsh_idx`           INT(11) NOT NULL COMMENT '대시보드idx',
    `mb_id`             VARCHAR(20) NOT NULL COMMENT '회원아이디',
    `wig_idx`     	    INT(11)  NOT NULL COMMENT '위젯idx : wig_idx번호',
    `myw_order`   		INT(10)     NOT NULL DEFAULT 0 COMMENT '마이위젯 순서',
	`myw_status`      	VARCHAR(20)  NOT NULL DEFAULT 'ok' COMMENT 'pending=대기,ok=정상,hide=숨김,trash=삭제'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
sql_query($mywidget_sql);
sql_query(" ALTER TABLE `".$g5['mywidget_table']."` ADD PRIMARY KEY (`myw_idx`) ");
sql_query(" ALTER TABLE `".$g5['mywidget_table']."` MODIFY `myw_idx` int(11) NOT NULL AUTO_INCREMENT ");