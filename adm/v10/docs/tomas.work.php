SELECT itm.com_idx, itm.mms_idx, 14, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
, COUNT(itm_idx) AS itm_count
FROM g5_1_item AS itm
    LEFT JOIN g5_1_order_out_practice AS oop ON oop.oop_idx = itm.oop_idx
    LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
WHERE itm_status NOT IN ('trash','delete')
    AND itm_date != '0000-00-00'
GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status
ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status




SELECT SQL_NO_CACHE itm.com_idx, itm.mms_idx, 14, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
, COUNT(itm_idx) AS itm_count
FROM g5_1_item AS itm
    LEFT JOIN g5_1_order_out_practice AS oop ON oop.oop_idx = itm.oop_idx
    LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
WHERE itm_status NOT IN ('trash','delete')
    AND trm_idx_line = '46'
    AND itm_date = '2022-01-24'
    AND itm.bom_idx = '1740'
GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status
ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status
