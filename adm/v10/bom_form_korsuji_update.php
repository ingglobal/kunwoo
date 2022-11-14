<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
//완제품관련 작업일때만 조건 이하를 실행
if(${$pre."_type"} == 'product') {
    //완제품 새로 등록시 반제품 등록하고 bom_item 추가하기
    if($w == '') {
        //H_완제품P/NO 구성으로된 반제품 P/NO가 디비에 존재하는지 확인하고 없으면 생성한다.
        $bom = get_table_meta('bom', 'bom_idx', $bom_idx);
        $hbom = sql_fetch(" SELECT bom_idx FROM {$g5['bom_table']} WHERE bom_part_no = 'H_{$bom['bom_part_no']}' AND bom_type = 'half' AND bom_status NOT IN('delete','del','trash') ");
        //해당 반제품이 있으면 완제품의 bom_item에 귀속시킴
        if($hbom['bom_idx']){
            // $bit = sql_fetch(" SELECT bit_idx FROM {$g5['bom_item_table']} WHERE bom_idx = '{$bom_idx}' AND bom_idx_child = '{$hbom['bom_idx']}' ");
            $bit_sql = " INSERT INTO {$g5['bom_item_table']} SET
                       bom_idx = '{$bom_idx}'
                       , bom_idx_child = '{$hbom['bom_idx']}'
                       , bit_count = '1'
                       , bit_num = '-1'
                       , bit_reg_dt = '".G5_TIME_YMDHIS."'     
                       , bit_update_dt = '".G5_TIME_YMDHIS."'     
            ";
            sql_query($bit_sql,1);
        }
        //해당 반제품이 없으면 반제품을 생성하고 bom_item에 귀속시킴
        else{
            $bom_sql = " INSERT INTO {$g5['bom_table']} SET
                        com_idx = '{$_SESSION['ss_com_idx']}'
                        , com_idx_provider = '{$_SESSION['ss_com_idx']}'      
                        , com_idx_customer = '{$_SESSION['ss_com_idx']}'
                        , bct_id = '{$bom['bct_id']}'
                        , bom_name = '{$bom['bom_name']}_half'
                        , bom_part_no = 'H_{$bom['bom_part_no']}'
                        , bom_type = 'half'
                        , bom_moq = '1'
                        , bom_status = 'ok'
                        , bom_reg_dt = '".G5_TIME_YMDHIS."'     
                        , bom_update_dt = '".G5_TIME_YMDHIS."'     
            ";
            sql_query($bom_sql,1);
	        $bom_idx_h = sql_insert_id();

            $bit_sql = " INSERT INTO {$g5['bom_item_table']} SET
                       bom_idx = '{$bom_idx}'
                       , bom_idx_child = '{$bom_idx_h}'
                       , bit_count = '1'
                       , bit_num = '-1'
                       , bit_reg_dt = '".G5_TIME_YMDHIS."'     
                       , bit_update_dt = '".G5_TIME_YMDHIS."'     
            ";
            sql_query($bit_sql,1);
        }
    }
    //완제품 P/NO를 수정했을시 반제품도 연관된 P/NO로 수정해야 한다.
    else if($w == 'u') {
        $bit = sql_fetch(" SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = '{$bom_idx}' ORDER BY bit_idx LIMIT 1 ");
        $sql = " UPDATE {$g5['bom_table']} SET
                    bct_id = '{$bct_id}'
                    , com_idx_provider = '{$_SESSION['ss_com_idx']}'
                    , com_idx_customer = '{$_SESSION['ss_com_idx']}'
                    , bom_name = '{$bom_name}_half'
                    , bom_part_no = 'H_{$bom_part_no}'
                    , bom_update_dt = '".G5_TIME_YMDHIS."'
                WHERE bom_idx = '{$bit['bom_idx_child']}'    
        ";
        sql_query($sql,1);
    }
    //완제품 삭제시 해당 반제품도 삭제처리
    else if($w == 'd') {
        $bit = sql_fetch(" SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = '{$bom_idx}' ORDER BY bit_idx LIMIT 1 ");
        $sql = " UPDATE {$g5['bom_table']} SET
                    bom_status = 'trash'
                WHERE bom_idx = '{$bit['bom_idx_child']}'    
        ";
        sql_query($sql,1);
    }
}