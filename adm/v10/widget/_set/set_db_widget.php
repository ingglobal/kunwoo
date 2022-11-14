<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$widget_sql = " CREATE TABLE `".$g5['widget_table']."` (
    `wig_idx`           INT(11) NOT NULL,
    `wig_cd`            VARCHAR(20) NOT NULL COMMENT '중복되지 않는 식별(위치)코드',
    `wig_name`          VARCHAR(30) NOT NULL COMMENT '위젯이름',
    `wig_content`       TEXT NULL COMMENT '위젯내용',
    `wig_db_category` 	VARCHAR(50)  NULL     COMMENT '내용=content, 게시판=board, 쇼핑몰=shop, 없음=일반위젯',
	`wig_db_table`    	VARCHAR(50)  NULL     COMMENT 'content=콘텐츠명, board=게시판명, shop=item',
	`wig_db_id`      	VARCHAR(255) NULL     COMMENT '해당 db테이블 레코드의 id값을 저장',
	`wig_device`      	VARCHAR(10)  NOT NULL COMMENT 'pc / mobile',
	`wig_skin`        	VARCHAR(50)  NOT NULL COMMENT '해당스킨에 필요한 설정값들을 정의하고 지정',
	`wig_manual_url`    VARCHAR(255) NULL     COMMENT '해당 위젯의 메뉴얼 URL을 저장',
    `wig_level`   		TINYINT(4)   NULL DEFAULT 0 COMMENT '위젯공개등급',
	`wig_status`      	VARCHAR(20)  NOT NULL DEFAULT 'ok' COMMENT 'pending=대기,ok=정상,hide=숨김,trash=삭제',
	`wig_reg_dt`      	DATETIME     NULL     DEFAULT '0000-00-00 00:00:00' COMMENT '이 위젯의 등록일시',
	`wig_update_dt`   	DATETIME     NULL     DEFAULT '0000-00-00 00:00:00' COMMENT '이 위젯의 수정일시'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
sql_query($widget_sql);
sql_query(" ALTER TABLE `".$g5['widget_table']."` ADD PRIMARY KEY (`wig_idx`) ");
sql_query(" ALTER TABLE `".$g5['widget_table']."` MODIFY `wig_idx` int(11) NOT NULL AUTO_INCREMENT ");