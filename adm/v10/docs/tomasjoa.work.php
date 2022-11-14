SELECT 
		SQL_CALC_FOUND_ROWS * , 
		com.com_idx AS com_idx , 
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' AND prj_idx IN (171,167) ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT 
		prj.prj_idx, 
		prj.prj_name,
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' 
		AND prj_idx IN (171,167) 
		AND
			(SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
				  AS misu FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0
		
	ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT 
		prj.prj_idx, 
		prj.prj_name,
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' 
		AND prj_idx IN (194,193) 
		AND
			((SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
				  FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0)
		
	ORDER BY prj_idx DESC LIMIT 0, 25

================================================================
bom, bom_item, bom_category, company
================================================================
SELECT bom.bom_idx
    , bom.bom_name
    , bom.bom_part_no
    , bom.bom_std
    , bom.bom_price
    , bct.bct_desc
    , boi.bom_idx_child AS bom_idx_c
    , boc.bom_name AS bom_name_c
    , boc.bom_part_no AS bom_part_no_c
    , boc.bom_std AS bom_std_c
    , com.com_name
FROM g5_1_bom AS bom
    LEFT JOIN g5_1_bom_item AS boi ON bom.bom_idx = boi.bom_idx
    LEFT JOIN g5_1_bom AS boc ON boi.bom_idx_child = boc.bom_idx
    LEFT JOIN g5_1_bom_category AS bct ON bct.bct_id = bom.bct_id
        AND bct.com_idx = '14'
    LEFT JOIN g5_1_company AS com ON bom.com_idx_customer = com.com_idx
WHERE bom.bom_type = 'product'
    AND bom.bom_status NOT IN ('del','delete','trash','cancel')
    AND bom.com_idx = '14'
    AND boc.bom_idx != ''
ORDER BY bom.bom_idx DESC