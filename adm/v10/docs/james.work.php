https://www.highcharts.com/demo/stock/compare

CREATE TABLE `g5_1_order` (
  `ord_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '수주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `ord_price` int(11) NOT NULL DEFAULT '0' COMMENT '수주금액',
  `ord_pay_status` varchar(10) DEFAULT '' COMMENT '오더상태',
  `ord_ship_date` date DEFAULT '0000-00-00' COMMENT '출하예정일',
  `ord_memo` text COMMENT '메모',
  `ord_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `ord_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ord_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`ord_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `g5_1_data_output_40` (
  `dta_idx` bigint(20) NOT NULL COMMENT '데이터idx',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `g5_1_order_out` (
  `oro_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '수주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `com_idx_customer` bigint(20) NOT NULL DEFAULT '0' COMMENT '거래처번호',
  `ord_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '수주idx',
  `ori_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '수주제품idx',
  `oro_count` int(11) NOT NULL DEFAULT '0' COMMENT '출하수량',
  `oro_date_plan` date DEFAULT '0000-00-00' COMMENT '출하예정일',
  `oro_date` date DEFAULT '0000-00-00' COMMENT '실출하일',
  `oro_memo` text COMMENT '메모',
  `oro_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `oro_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `oro_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`oro_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_material_order` (
  `mto_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '발주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체idx',
  `com_idx_customer` bigint(20) NOT NULL DEFAULT '0' COMMENT '거래처idx',
  `mto_price` int(11) NOT NULL DEFAULT '0' COMMENT '발주금액',
  `mto_pay_status` varchar(10) DEFAULT '' COMMENT '발주결제상태',
  `mto_ship_date` date DEFAULT '0000-00-00' COMMENT '입고예정일',
  `mto_memo` text COMMENT '메모',
  `mto_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `mto_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `mto_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`mto_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_material_order_item` (
  `moi_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '발주제품idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체idx',
  `mto_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '발주idx',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `moi_count` int(11) NOT NULL DEFAULT '0' COMMENT '발주수량',
  `moi_price` int(11) NOT NULL DEFAULT '0' COMMENT '단가',
  `moi_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `moi_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `moi_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`moi_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `g5_1_order_item` (
  `ori_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '수주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `ord_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '수주idx',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `ori_count` int(11) NOT NULL DEFAULT '0' COMMENT '오더수량',
  `ori_price` int(11) NOT NULL DEFAULT '0' COMMENT '단가',
  `ori_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `ori_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ori_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`ori_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 생산실행
CREATE TABLE `g5_1_order_practice` (
  `orp_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '수주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `oro_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '출하idx',
  `orp_order_no` int(11) NOT NULL DEFAULT '0' COMMENT '지시번호',
  `trm_idx_operation` int(11) NOT NULL DEFAULT '0' COMMENT '공정',
  `trm_idx_line` int(11) NOT NULL DEFAULT '0' COMMENT '라인',
  `shf_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '작업구간idx',
  `mb_id` varchar(20) DEFAULT '' COMMENT '회원아이디',
  `orp_count` int(11) NOT NULL DEFAULT '0' COMMENT '생산수량',
  `orp_start_date` date DEFAULT '0000-00-00' COMMENT '생산시작일',
  `orp_done_date` date DEFAULT '0000-00-00' COMMENT '생산완료일',
  `orp_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `orp_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `orp_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`orp_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 작업시간
CREATE TABLE `g5_1_member_work` (
  `mbw_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '수주idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `mb_id` varchar(20) DEFAULT '' COMMENT '회원아이디',
  `shf_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '작업구간idx',
  `mbw_start_time` int(11) DEFAULT '0' COMMENT '시작시간',
  `mbw_done_time` int(11) DEFAULT '0' COMMENT '종료시간',
  `mbw_memo` text COMMENT '메모',
  `mbw_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `mbw_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `mbw_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`mbw_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 생산제품(완제품)
CREATE TABLE `g5_1_item` (
  `itm_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '생산제품idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `orp_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '생산실행idx',
  `shf_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '작업구간idx',
  `mb_id` varchar(20) DEFAULT '' COMMENT '회원아이디',
  `itm_name` varchar(100) DEFAULT '' COMMENT '품명',
  `itm_barcode` varchar(100) DEFAULT '' COMMENT '바코드',
  `itm_lot` varchar(100) DEFAULT '' COMMENT 'LOT번호',
  `itm_defect` int(11) NOT NULL DEFAULT '0' COMMENT '불량여부',
  `itm_defect_type` int(11) NOT NULL DEFAULT '0' COMMENT '불량타입',
  `trm_idx_where` int(11) NOT NULL DEFAULT '0' COMMENT '위치',
  `itm_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `itm_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `itm_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`itm_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 생산통계
CREATE TABLE `g5_1_item_sum` (
  `itm_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '생산제품idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `imp_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'IMPidx',
  `mms_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'MMSidx',
  `mmg_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '설비그룹idx',
  `shf_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '작업구간idx',
  `itm_shift` int(11) NOT NULL DEFAULT '0' COMMENT '구간번호',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `bom_part_no` varchar(100) DEFAULT '' COMMENT '파트넘버',
  `itm_price` int(11) NOT NULL DEFAULT '0' COMMENT '단가',
  `itm_count` int(11) NOT NULL DEFAULT '0' COMMENT '수량',
  `itm_defect` int(11) NOT NULL DEFAULT '0' COMMENT '불량여부',
  `itm_defect_type` int(11) NOT NULL DEFAULT '0' COMMENT '불량타입',
  `itm_date` date DEFAULT '0000-00-00' COMMENT '날짜',
  PRIMARY KEY (`itm_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 자재
CREATE TABLE `g5_1_material` (
  `mtr_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '생산제품idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `itm_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '제품idx',
  `mtr_name` varchar(100) DEFAULT '' COMMENT '품명',
  `mtr_barcode` varchar(100) DEFAULT '' COMMENT '바코드',
  `mtr_lot` varchar(100) DEFAULT '' COMMENT 'LOT번호',
  `mtr_defect` int(11) NOT NULL DEFAULT '0' COMMENT '불량여부',
  `mtr_defect_type` int(11) NOT NULL DEFAULT '0' COMMENT '불량타입',
  `trm_idx_where` int(11) NOT NULL DEFAULT '0' COMMENT '위치',
  `mtr_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `mtr_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `mtr_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`mtr_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// BOM
CREATE TABLE `g5_1_bom` (
  `bom_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'BOMidx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `com_idx_customer` bigint(20) NOT NULL DEFAULT '0' COMMENT '거래처번호',
  `bct_id` varchar(10) DEFAULT '' COMMENT '카테고리',
  `bom_num` int(11) NOT NULL DEFAULT '0' COMMENT '번호',
  `bom_reply` varchar(10) DEFAULT '' COMMENT 'Reply',
  `bom_parent` int(11) NOT NULL DEFAULT '0' COMMENT 'Parent',
  `bom_name` varchar(100) DEFAULT '' COMMENT '품명',
  `bom_no` varchar(100) DEFAULT '' COMMENT '고유번호',
  `bom_maker` varchar(100) DEFAULT '' COMMENT '메이커',
  `bom_type` varchar(100) DEFAULT '' COMMENT '타입',
  `bom_count` int(11) NOT NULL DEFAULT '0' COMMENT '구성품수',
  `bom_price` int(11) NOT NULL DEFAULT '0' COMMENT '단가',
  `bom_notax` int(11) NOT NULL DEFAULT '0' COMMENT '비과세',
  `bom_boq` int(11) NOT NULL DEFAULT '0' COMMENT '최소구매수량',
  `bom_lead_time` int(11) NOT NULL DEFAULT '0' COMMENT '평균리드타임',
  `bom_memo` text COMMENT '메모',
  `bom_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `bom_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `bom_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`bom_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// BOM 카테고리
CREATE TABLE `g5_1_bom_category` (
  `bct_id` varchar(10) NOT NULL COMMENT 'BOM카테고리id',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `bct_name` varchar(200) DEFAULT 'pending' COMMENT '카테고리명',
  `bct_order` bigint(20) NOT NULL DEFAULT '0' COMMENT '정렬순서',
  `bct_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `bct_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `bct_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`bct_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// BOM가격이력
CREATE TABLE `g5_1_bom_price` (
  `bop_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'BOM가격idx',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `bop_price` int(11) NOT NULL DEFAULT '0' COMMENT '가격',
  `bop_start_date` date DEFAULT '0000-00-00' COMMENT '적용시작일',
  `bop_memo` text COMMENT '메모',
  `bop_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `bop_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`bop_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// BOM대체자재
CREATE TABLE `g5_1_bom_backup` (
  `bob_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '대체자제idx',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `bom_idx_backup` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx대체',
  `bob_memo` text COMMENT '메모',
  `bob_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `bob_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`bob_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// BOM구성품
CREATE TABLE `g5_1_bom_item` (
  `bit_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'BOM구조idx',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `bom_idx_child` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx자식',
  `bit_num` int(11) NOT NULL DEFAULT '0' COMMENT '번호',
  `bit_reply` varchar(10) DEFAULT '' COMMENT 'Reply',
  `bit_parent` int(11) NOT NULL DEFAULT '0' COMMENT 'Parent',
  `bit_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `bit_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`bit_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

// 빠레트
CREATE TABLE `g5_1_pallet` (
  `plt_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '파레트idx',
  `plt_idx_parent` bigint(20) NOT NULL DEFAULT '0' COMMENT '파레트idx부모',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `bom_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'BOMidx',
  `bom_part_no` varchar(100) DEFAULT '' COMMENT '파트넘버',
  `plt_barcode` varchar(100) DEFAULT '' COMMENT '바코드',
  `plt_count` int(11) NOT NULL DEFAULT '0' COMMENT '갯수',
  `plt_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `plt_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `plt_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`plt_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



SELECT bop_price, bop_start_date 
FROM g5_1_bom_price
WHERE bom_idx = '1'
  AND bop_start_date <= '2021-07-17'
ORDER BY bop_start_date DESC
LIMIT 1


UPDATE g5_1_bom AS bom SET
    bom_price = (
      SELECT bop_price
      FROM g5_1_bom_price
      WHERE bom_idx = bom.bom_idx
        AND bop_start_date <= '2021-07-17'
      ORDER BY bop_start_date DESC
      LIMIT 1
    )
WHERE bom_status NOT IN ('delete','trash')


// 기존 구조
SELECT wr1.wr_id, wr1.wr_reply, wr1.wr_subject AS wr1_subject
	,GROUP_CONCAT(wr2.wr_subject ORDER BY wr2.wr_reply SEPARATOR '^') AS group_subject
	,GROUP_CONCAT(wr2.wr_content ORDER BY wr2.wr_reply SEPARATOR '^') AS group_content
	,GROUP_CONCAT(wr2.wr_link1 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_link1
	,GROUP_CONCAT(wr2.wr_1 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_1
	,GROUP_CONCAT(wr2.wr_2 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_2
	,COUNT(wr2.wr_id) AS group_count
FROM g5_write_navi AS wr1
	JOIN g5_write_navi AS wr2
WHERE wr1.wr_is_comment = 0 
	AND wr1.wr_num = wr2.wr_num
	AND wr2.wr_reply LIKE CONCAT(wr1.wr_reply,'%')
GROUP BY wr1.wr_num, wr1.wr_reply
ORDER BY wr1.wr_num DESC, wr1.wr_reply

SELECT wr1.wr_num, wr2.wr_num, wr1.wr_reply, wr2.wr_reply, wr1.wr_subject
FROM g5_write_navi AS wr1
	JOIN g5_write_navi AS wr2
WHERE wr1.wr_is_comment = 0 
	AND wr1.wr_num = wr2.wr_num
	AND wr2.wr_reply LIKE CONCAT(wr1.wr_reply,'%')
ORDER BY wr1.wr_num DESC, wr1.wr_reply


SELECT bit1.bit_idx, bit1.bit_reply, bit1.bom_idx_child AS bom_child
	,GROUP_CONCAT(bit2.bom_idx_child ORDER BY bit2.bit_reply SEPARATOR '^') AS group_child
	,COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
  AND bit1.bom_idx = 1
  AND bit2.bom_idx = 1
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply


SELECT bom.bom_name, bom.bom_part_no, bom.bom_price
	, GROUP_CONCAT(bit2.bom_idx_child ORDER BY bit2.bit_reply SEPARATOR '^') AS group_child
	, COUNT(bit2.bit_idx) AS group_count
  , bit1.bit_idx, bit1.bit_num, bit1.bit_reply, bit1.bom_idx_child AS bom_child
  , bit1.bit_count
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit1.bom_idx_child
WHERE bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
  AND bit1.bom_idx = 1 AND bit2.bom_idx = 1
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply

// group_count must be existed for nesting. I have to know the group_count
// that is the reason that I have to find out the group_count.
// Again.
SELECT bom_name, bom_part_no, bom_price
  , bit_idx, bit_num, bit_reply, bit.bom_idx, bom_idx_child
FROM g5_1_bom_item AS bit
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit.bom_idx_child
WHERE bit.bom_idx = 1
GROUP BY bit_num, bit_reply
ORDER BY bit_num DESC, bit_reply


SELECT bom.bom_name, bom.bom_part_no, bom.bom_price
	, GROUP_CONCAT(bit2.bom_idx_child ORDER BY bit2.bit_reply SEPARATOR '^') AS group_child
	, COUNT(bit2.bit_idx) AS group_count
  , bit1.bit_idx, bit1.bit_num, bit1.bit_reply, bit1.bom_idx_child AS bom_child
  , bit1.bit_count
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit1.bom_idx_child
WHERE bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
  AND bit1.bom_idx = 1 AND bit2.bom_idx = 1
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply


// 9개
SELECT bit1.*
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
ORDER BY bit1.bit_num DESC, bit1.bit_reply

SELECT bit1.*, bit2.bit_num, bit2.bit_reply
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
  AND bit1.bit_num = bit2.bit_num
ORDER BY bit1.bit_num DESC, bit1.bit_reply

SELECT bit1.*, bit2.bit_num, bit2.bit_reply
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
  AND bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
ORDER BY bit1.bit_num DESC, bit1.bit_reply


SELECT bit1.bit_idx, bit1.bit_num, bit1.bit_reply, bit2.bit_reply, bit1.bom_idx_child
  , bit1.bit_count
  , CONCAT(bit1.bit_reply,'%')
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bit_num = bit2.bit_num
  AND bit1.bom_idx = 1 AND bit2.bom_idx = 1
--	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
ORDER BY bit1.bit_num DESC, bit1.bit_reply


SELECT bit1.bit_idx, bit1.bit_num, bit1.bit_reply, bit1.bom_idx_child
  , bit1.bit_count
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bit_num = bit2.bit_num
  AND bit1.bom_idx = 1 AND bit2.bom_idx = 1
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
ORDER BY bit1.bit_num DESC, bit1.bit_reply


SELECT bit1.bit_idx, bit1.bit_num, bit1.bit_reply, bit1.bom_idx_child
  , bit1.bit_count
	, GROUP_CONCAT(bit2.bom_idx_child ORDER BY bit2.bit_reply SEPARATOR '^') AS group_child
	,COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item AS bit1
	JOIN g5_1_bom_item AS bit2
WHERE bit1.bit_num = bit2.bit_num
  AND bit1.bom_idx = 1 AND bit2.bom_idx = 1
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply

----------------------------------------
SELECT bit1.wr_id, bit1.wr_reply, bit1.wr_subject AS bit1_subject
	,GROUP_CONCAT(bit2.wr_subject ORDER BY bit2.wr_reply SEPARATOR '^') AS group_subject
	,COUNT(bit2.wr_id) AS group_count
FROM g5_1_bom_item2 AS bit1
	JOIN g5_1_bom_item2 AS bit2
WHERE bit1.wr_is_comment = 0 
	AND bit1.wr_num = bit2.wr_num
	AND bit2.wr_reply LIKE CONCAT(bit1.wr_reply,'%')
GROUP BY bit1.wr_num, bit1.wr_reply
ORDER BY bit1.wr_num DESC, bit1.wr_reply

SELECT bit1.bit_idx, bit1.bit_reply, bit1.bit_subject AS bit1_subject
	,GROUP_CONCAT(bit2.bit_subject ORDER BY bit2.bit_reply SEPARATOR '^') AS group_subject
	,COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item3 AS bit1
	JOIN g5_1_bom_item3 AS bit2
WHERE bit1.bit_is_comment = 0 
	AND bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply

SELECT bit1.bit_idx, bit1.bit_reply, bit1.bit_subject AS bit1_subject
	,GROUP_CONCAT(bit2.bit_subject ORDER BY bit2.bit_reply SEPARATOR '^') AS group_subject
	,COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item4 AS bit1
	JOIN g5_1_bom_item4 AS bit2
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
	AND bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply

SELECT bom.bom_name, bom.bom_part_no, bom.bom_price
  , bit1.bit_idx, bit1.bit_reply, bit1.bit_subject AS bit1_subject
	, GROUP_CONCAT(bom.bom_name ORDER BY bit2.bit_reply SEPARATOR '^') AS group_name
	, COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item4 AS bit1
	JOIN g5_1_bom_item4 AS bit2
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit2.bom_idx_child
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
	AND bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply

SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status
  , bit1.bit_idx, bit1.bit_reply
	, COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item4 AS bit1
	JOIN g5_1_bom_item4 AS bit2
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit1.bom_idx_child
WHERE bit1.bom_idx = 1 AND bit2.bom_idx = 1
	AND bit1.bit_num = bit2.bit_num
	AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply


TRUNCATE TABLE `g5_1_bom`;
TRUNCATE TABLE `g5_1_bom_item`;
TRUNCATE TABLE `g5_1_bom_price`;


// 수주목록
SELECT * 
FROM g5_1_order AS ord
  LEFT JOIN g5_1_company AS com ON ord.com_idx = com.com_idx 
WHERE ord_status NOT IN ('cancel','trash','delete')
  AND ord.com_idx = '8'
order by ord_reg_dt DESC

// 수주제품목록
SELECT *
FROM g5_1_order_item AS ori
  LEFT JOIN g5_1_order AS ord ON ord.ord_idx = ori.ord_idx 
WHERE ori_status IN ('ok')
  AND ori.com_idx = '8'
ORDER BY ori_reg_dt DESC

// 출하 디비 입력 (from 수주제품)
INSERT INTO g5_1_order_out
    (com_idx, com_idx_customer, ord_idx, ori_idx, oro_count, oro_date_plan, oro_status, oro_reg_dt, oro_update_dt)
SELECT ori.com_idx, ori.com_idx_customer, ori.ord_idx, ori_idx, ori_count, ord_ship_date, 'ok', now(), now()
FROM g5_1_order_item AS ori
  LEFT JOIN g5_1_order AS ord ON ord.ord_idx = ori.ord_idx 
WHERE ori_status IN ('ok')
  AND ori.com_idx = '8'
ORDER BY ori_reg_dt DESC


// 자재 디비 입력 (from BOM)
INSERT INTO g5_1_material
    (com_idx, bom_idx, mtr_name, mtr_price, mtr_status, mtr_reg_dt, mtr_update_dt)
SELECT com_idx, bom_idx, bom_name, bom_price, 'stock', now(), now()
FROM g5_1_bom
WHERE bom_status IN ('ok')
  AND com_idx = '8'
  AND bom_type = 'material'
ORDER BY bom_reg_dt DESC

UPDATE g5_1_material SET mtr_barcode = CONCAT((mtr_idx+2)*18,'-',(mtr_idx+2)*31), mtr_lot = (mtr_idx*18)-mtr_idx*12

// 생산실행계획 입력 (from 출하)
INSERT INTO g5_1_order_practice
    (com_idx, oro_idx, bom_idx, orp_count, orp_start_date, orp_status, orp_reg_dt, orp_update_dt)
SELECT oro.com_idx, oro_idx, bom_idx, oro_count, oro_date_plan, 'predict', now(), now()
FROM g5_1_order_out AS oro
  LEFT JOIN g5_1_order_item AS ori ON ori.ori_idx = oro.ori_idx 
WHERE oro_status IN ('ok')
  AND oro.com_idx = '8'
ORDER BY oro_reg_dt DESC


// Let's check weather this makes sense or not
UPDATE g5_1_material SET
    orp_idx = '27'
    , mtr_status = 'predict'
WHERE bom_idx = '540'
    AND mtr_defect = '0'
    AND mtr_status IN ('stock','repairstock')
    AND com_idx = '8'
ORDER BY mtr_idx
LIMIT 1

DELETE FROM g5_1_material WHERE bom_idx NOT IN (2,10,4,5,6)


// bom 구조
SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status, com_name
    , bit.bit_idx, bit.bom_idx_child, bit.bit_reply, bit.bit_count
FROM g5_1_bom_item AS bit
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit.bom_idx_child
  LEFT JOIN g5_1_company AS com ON com.com_idx = bom.com_idx_customer
WHERE bit.bom_idx = '9'
ORDER BY bit.bit_num DESC, bit.bit_reply



$sql = "SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status
            , bit1.bit_idx, bit1.bom_idx_child, bit1.bit_reply, bit1.bit_count
            , COUNT(bit2.bit_idx) AS group_count
        FROM {$g5['bom_item_table']} AS bit1
            JOIN {$g5['bom_item_table']} AS bit2
            LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = bit2.bom_idx_child
        WHERE bit1.bom_idx = '".$bom_idx."' AND bit2.bom_idx = '".$bom_idx."'
            AND bit1.bit_num = bit2.bit_num
            AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
        GROUP BY bit1.bit_num, bit1.bit_reply
        ORDER BY bit1.bit_num DESC, bit1.bit_reply
";
SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status, bom_min_cnt
      , bit1.bit_idx, bit1.bom_idx_child, bit1.bit_reply, bit1.bit_count 
      , COUNT(bit2.bit_idx) AS group_count
FROM g5_1_bom_item AS bit1 
  JOIN g5_1_bom_item AS bit2
  LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit2.bom_idx_child
WHERE bit1.bom_idx = '1083' AND bit2.bom_idx = '1083'
  AND bit1.bit_num = bit2.bit_num AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
GROUP BY bit1.bit_num, bit1.bit_reply
ORDER BY bit1.bit_num DESC, bit1.bit_reply


SELECT * FROM g5_1_item WHERE itm_barcode != '' AND itm_status = 'ing' ORDER BY RAND() LIMIT 1

SELECT * FROM g5_1_item
WHERE bom_part_no = '88700-J9110PUR' AND itm_status = 'finish' ORDER BY itm_idx LIMIT 100


SELECT bom_idx,com_idx,bct_id,bom_name,bom_part_no FROM g5_1_bom
WHERE com_idx = '11'
    AND bom_type = 'product'
    AND bom_status = 'ok'
ORDER BY RAND() LIMIT 1

// itme_sum
SELECT itm_date, itm_shift, itm_status
  , COUNT(itm_idx) AS itm_count_sum
FROM g5_1_item
WHERE itm_status NOT IN ('trash','delete')
      AND itm_date != '0000-00-00'
GROUP BY itm_date, itm_shift, itm_status
ORDER BY itm_date ASC, itm_shift, itm_status


SELECT itm.com_idx, itm_date, itm_shift, trm_idx_line, bom_idx, bom_part_no, itm_price
, COUNT(itm_idx) AS itm_count
FROM g5_1_item AS itm
    LEFT JOIN g5_1_order_practice AS orp USING(orp_idx)
WHERE itm_status NOT IN ('trash','delete')
    AND itm_date != '0000-00-00'
GROUP BY itm_date, trm_idx_line, itm_shift, itm_status
ORDER BY itm_date ASC, trm_idx_line, itm_shift, itm_status

// now, let's get the product count for defect and defect_type respectively
SELECT itm.com_idx, itm_date, itm_shift, trm_idx_line, bom_idx, bom_part_no, itm_price, itm_status
, COUNT(itm_idx) AS itm_count
FROM g5_1_item AS itm
    LEFT JOIN g5_1_order_practice AS orp USING(orp_idx)
WHERE itm_status NOT IN ('trash','delete')
    AND itm_date != '0000-00-00'
GROUP BY itm_date, trm_idx_line, itm_shift, bom_idx, itm_status
ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status


// itm_price update
UPDATE g5_1_item AS itm SET
itm_price = (SELECT bom_price FROM g5_1_bom WHERE bom_idx = itm.bom_idx)

delete FROM `g5_1_item` WHERE itm_reg_dt > '2021-12-14 00:00:00';
delete FROM `g5_1_item_sum` WHERE itm_date > '2021-12-13';


// Output production target query
SELECT bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8, oop_9, oop_10
FROM g5_1_order_out_practice AS oop
    LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
WHERE orp_done_date != '0000-00-00'
GROUP BY bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8, oop_9, oop_10
ORDER BY bom_idx, trm_idx_line, orp_done_date


// item statics by some amount of time unit.
SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
        , dta_shf_no, dta_mmi_no
        , SUM(dta_value) AS dta_value
        , (dta_dt DIV 3600) AS dta_divided
        , (dta_dt DIV 3600)*3600 AS dta_made_timestamp
        , FROM_UNIXTIME((dta_dt DIV 3600)*3600,'%Y-%m-%d %H:%i:%s') AS dta_made_dt
FROM g5_1_data_output_4
WHERE FROM_UNIXTIME(dta_dt,'%Y-%m-%d') = '2021-02-17'
GROUP BY dta_dt DIV 3600
ORDER BY dta_dt ASC

SELECT UNIX_TIMESTAMP(itm_reg_dt) AS dta_dt, itm_reg_dt
--        , COUNT( itm_idx ) AS itm_sum
--        , SUM( CASE WHEN itm_status IN ('ing','finish') THEN 1 ELSE 0 END ) AS itm_ok
--        , SUM( CASE WHEN itm_status IN ('error_stitch') THEN 1 ELSE 0 END ) AS itm_ng
        , (UNIX_TIMESTAMP(itm_reg_dt) DIV 3600) AS dta_divided
        , (UNIX_TIMESTAMP(itm_reg_dt) DIV 3600)*3600 AS dta_made_timestamp
        , FROM_UNIXTIME((UNIX_TIMESTAMP(itm_reg_dt) DIV 3600)*3600,'%Y-%m-%d %H:%i:%s') AS dta_made_dt
FROM g5_1_item
WHERE itm_reg_dt >= '2021-12-01 00:00:00' AND itm_reg_dt <= '2021-12-03 23:59:59'
-- GROUP BY UNIX_TIMESTAMP(itm_reg_dt) DIV 3600
ORDER BY UNIX_TIMESTAMP(itm_reg_dt) ASC


SELECT UNIX_TIMESTAMP(itm_reg_dt) AS dta_dt, itm_reg_dt
  , COUNT( itm_idx ) AS itm_sum
  , SUM( CASE WHEN itm_status IN ('ing','finish') THEN 1 ELSE 0 END ) AS itm_ok
  , SUM( CASE WHEN itm_status IN ('error_stitch') THEN 1 ELSE 0 END ) AS itm_ng
  , (UNIX_TIMESTAMP(itm_reg_dt) DIV 3600) AS dta_divided
  , (UNIX_TIMESTAMP(itm_reg_dt) DIV 3600)*3600 AS dta_made_timestamp
  , FROM_UNIXTIME((UNIX_TIMESTAMP(itm_reg_dt) DIV 3600)*3600,'%Y-%m-%d %H:%i:%s') AS dta_made_dt
FROM g5_1_item
WHERE itm_reg_dt >= '2021-12-01 00:00:00' AND itm_reg_dt <= '2021-12-03 23:59:59'
GROUP BY UNIX_TIMESTAMP(itm_reg_dt) DIV 3600
ORDER BY UNIX_TIMESTAMP(itm_reg_dt) ASC

// mms_idx value update from 46~49
SELECT ROUND((45.5 + RAND() * 4)) 
UPDATE `g5_1_item` SET mms_idx = ROUND((45.5 + RAND() * 4)) 



// statistics 통계 맞추는 쿼리 (최종)
// g5_1_item_sum 테이블 비우고 실행해 주시면 됩니다.
INSERT INTO g5_1_item_sum (com_idx, mms_idx, mmg_idx, itm_date, itm_shift, trm_idx_line, bom_idx, bom_part_no, itm_price, itm_status, itm_count)
SELECT itm.com_idx, itm.mms_idx, 14, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
, COUNT(itm_idx) AS itm_count
FROM g5_1_item AS itm
    LEFT JOIN g5_1_order_out_practice AS oop ON oop.oop_idx = itm.oop_idx
    LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
WHERE itm_status NOT IN ('trash','delete')
    AND itm_date != '0000-00-00'
GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status
ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status




// UPH basic query
SELECT SQL_CALC_FOUND_ROWS mms_idx, dta_mmi_no, dta_date
  , SUM(dta_value) AS output_sum
FROM g5_1_data_output_sum
WHERE mms_idx = '34' AND dta_date >= '2022-01-01' AND dta_date <= '2022-01-11'
GROUP BY dta_mmi_no, dta_date 
ORDER BY dta_mmi_no, dta_date



SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name , SUM(output_total) AS output_total , MAX(output_total) AS output_max , SUM(output_good) AS output_good , SUM(output_defect) AS output_defect FROM ( SELECT ymd_date , SUM(output_total) AS output_total , SUM(output_good) AS output_good , SUM(output_defect) AS output_defect FROM ( ( SELECT CAST(ymd_date AS CHAR) AS ymd_date , 0 AS output_total , 0 AS output_good , 0 AS output_defect FROM g5_5_ymd AS ymd WHERE ymd_date BETWEEN '2022-01-01' AND '2022-01-12' ORDER BY ymd_date ) UNION ALL ( 
  
SELECT itm_date AS ymd_date 
  , SUM(itm_count) AS output_total 
  , SUM( CASE WHEN itm_status IN ('ing','finish') THEN itm_count ELSE 0 END ) AS output_good 
  , SUM( CASE WHEN itm_status IN ('error_stitch','error_wrinkle','error_fabric','error_push','error_pollution','error_bottom','error_etc') THEN itm_count ELSE 0 END ) AS output_defect
FROM g5_1_item_sum
WHERE itm_date >= '2022-01-01' AND itm_date <= '2022-01-12' AND com_idx='11'
GROUP BY ymd_date
ORDER BY ymd_date


SELECT 
    itm_date AS ymd_date
    , SUM(itm_count) AS output_total
    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_count ELSE 0 END ) AS output_good
    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_count ELSE 0 END ) AS output_defect
    FROM {$g5['item_sum_table']}
WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
    AND com_idx='".$com_idx."'
    {$sql_mmses}
GROUP BY ymd_date
ORDER BY ymd_date
