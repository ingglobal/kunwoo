<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$user_log_sql = " CREATE TABLE
`".$g5['user_log_table']."` (
    `usl_idx`     BIGINT(20) NOT NULL,
    `mb_id`       VARCHAR(50)  NOT NULL COMMENT '접속자 id',
    `usl_menu_cd` VARCHAR(50)  NOT NULL COMMENT '접속메뉴코드',
    `usl_type`    VARCHAR(50)  NOT NULL DEFAULT 'login' COMMENT 'login=접속,logout=종료,register=등록,modify=수정,
    delete=삭제,search=검색',
    `usl_reg_dt`  DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ";

sql_query($user_log_sql);
sql_query(" ALTER TABLE `".$g5['user_log_table']."` ADD PRIMARY KEY (`usl_idx`) ");
sql_query(" ALTER TABLE `".$g5['user_log_table']."` MODIFY `usl_idx` bigint(20) NOT NULL AUTO_INCREMENT ");
