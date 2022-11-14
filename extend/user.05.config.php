<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 사용자 테이블 정의
define('PROJ_TABLE_PREFIX', G5_TABLE_PREFIX.'1_');  

$g5['company_table']            = PROJ_TABLE_PREFIX.'company';
$g5['company_member_table']     = PROJ_TABLE_PREFIX.'company_member';
$g5['company_saler_table']      = PROJ_TABLE_PREFIX.'company_saler';
$g5['code_table']               = PROJ_TABLE_PREFIX.'code';
$g5['imp_table']                = PROJ_TABLE_PREFIX.'imp';
$g5['mms_table']                = PROJ_TABLE_PREFIX.'mms';
$g5['mms_group_table']          = PROJ_TABLE_PREFIX.'mms_group';
$g5['mms_parts_table']          = PROJ_TABLE_PREFIX.'mms_parts';
$g5['mms_checks_table']         = PROJ_TABLE_PREFIX.'mms_checks';
$g5['mms_status_table']         = PROJ_TABLE_PREFIX.'mms_status';
$g5['mms_item_table']           = PROJ_TABLE_PREFIX.'mms_item';
$g5['mms_item_price_table']     = PROJ_TABLE_PREFIX.'mms_item_price';
$g5['maintain_table']           = PROJ_TABLE_PREFIX.'maintain';
$g5['maintain_parts_table']     = PROJ_TABLE_PREFIX.'maintain_parts';
$g5['shift_table']              = PROJ_TABLE_PREFIX.'shift';
$g5['shift_item_goal_table']    = PROJ_TABLE_PREFIX.'shift_item_goal';
$g5['data_table']               = PROJ_TABLE_PREFIX.'data';
$g5['data_measure_table']       = PROJ_TABLE_PREFIX.'data_measure';
$g5['data_measure_sum_table']   = PROJ_TABLE_PREFIX.'data_measure_sum';
$g5['data_error_table']         = PROJ_TABLE_PREFIX.'data_error';
$g5['data_error_sum_table']     = PROJ_TABLE_PREFIX.'data_error_sum';
$g5['data_run_table']           = PROJ_TABLE_PREFIX.'data_run';
$g5['data_run_sum_table']       = PROJ_TABLE_PREFIX.'data_run_sum';
$g5['data_run_real_table']      = PROJ_TABLE_PREFIX.'data_run_real';
$g5['data_output_table']        = PROJ_TABLE_PREFIX.'data_output';
$g5['data_output_sum_table']    = PROJ_TABLE_PREFIX.'data_output_sum';
$g5['data_downtime_table']      = PROJ_TABLE_PREFIX.'data_downtime';
$g5['member_dash_table']        = PROJ_TABLE_PREFIX.'member_dash';
$g5['alarm_table']              = PROJ_TABLE_PREFIX.'alarm';
$g5['offwork_table']            = PROJ_TABLE_PREFIX.'offwork';
$g5['alarm_send_table']         = PROJ_TABLE_PREFIX.'alarm_send';
$g5['cost_config_table']        = PROJ_TABLE_PREFIX.'cost_config';
$g5['bom_table']                = PROJ_TABLE_PREFIX.'bom';
$g5['bom_category_table']       = PROJ_TABLE_PREFIX.'bom_category';
$g5['bom_price_table']          = PROJ_TABLE_PREFIX.'bom_price';
$g5['bom_backup_table']         = PROJ_TABLE_PREFIX.'bom_backup';
$g5['bom_item_table']           = PROJ_TABLE_PREFIX.'bom_item';
$g5['guest_stock_table']        = PROJ_TABLE_PREFIX.'guest_stock';
$g5['order_table']              = PROJ_TABLE_PREFIX.'order';
$g5['order_item_table']         = PROJ_TABLE_PREFIX.'order_item';
$g5['order_out_table']          = PROJ_TABLE_PREFIX.'order_out';
$g5['order_out_practice_table'] = PROJ_TABLE_PREFIX.'order_out_practice';
$g5['order_practice_table']     = PROJ_TABLE_PREFIX.'order_practice';
$g5['member_work_table']        = PROJ_TABLE_PREFIX.'member_work';
$g5['item_table']               = PROJ_TABLE_PREFIX.'item';
$g5['item_sum_table']           = PROJ_TABLE_PREFIX.'item_sum';
$g5['material_table']           = PROJ_TABLE_PREFIX.'material';
$g5['material_order_table']     = PROJ_TABLE_PREFIX.'material_order';
$g5['material_order_item_table']= PROJ_TABLE_PREFIX.'material_order_item';
$g5['pallet_table']             = PROJ_TABLE_PREFIX.'pallet';

?>