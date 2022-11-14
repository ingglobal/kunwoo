<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$dashboard_sql = " CREATE TABLE `".$g5['dashboard_table']."` (
    `dsh_idx`           INT(11) NOT NULL,
    `mb_id`             VARCHAR(20) NOT NULL COMMENT '회원아이디',
    `dsh_name`          VARCHAR(30) NOT NULL COMMENT '대시보드이름',
    `dsh_order`   		INT(10)     NOT NULL DEFAULT 0 COMMENT '대시보드정렬 순서',
	`dsh_status`      	VARCHAR(20)  NOT NULL DEFAULT 'ok' COMMENT 'pending=대기,ok=정상,hide=숨김,trash=삭제',
	`dsh_reg_dt`      	DATETIME     NULL     DEFAULT '0000-00-00 00:00:00' COMMENT '대시보드의 등록일시'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
sql_query($dashboard_sql);
sql_query(" ALTER TABLE `".$g5['dashboard_table']."` ADD PRIMARY KEY (`dsh_idx`) ");
sql_query(" ALTER TABLE `".$g5['dashboard_table']."` MODIFY `dsh_idx` int(11) NOT NULL AUTO_INCREMENT ");