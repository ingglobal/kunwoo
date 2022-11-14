<?php
// bom 정보 입력
// 엑셀 한 줄에 두개 정보가 있을 수 있음 (일단 함수로 만들어 두고 for 문에서 두번 호출합니다.)
if(!function_exists('func_update_bom2')){
function func_update_bom2($arr) {
    global $g5,$demo;

    // 거래처 정보 =======================================================
    // 자재인 경우
    if($arr['bom_type']=='material') {

        // 거래처 검색
        $sql = "SELECT com_idx AS com_idx_customer FROM {$g5['company_table']} 
                WHERE com_status NOT IN ('trash','delete')
                    AND com_idx_par = '".$_SESSION['ss_com_idx']."'
                    AND com_name = '".$arr['com_name']."'
        ";
        // print_r3($sql);
        $com = sql_fetch($sql,1);
        // 거래처 정보 없으면 생성
        if(!$com['com_idx_provider']) {
            $sql = "INSERT INTO {$g5['company_table']} SET
                        com_idx_par = '".$_SESSION['ss_com_idx']."'
                        , com_name = '".addslashes($arr['com_name'])."'
                        , com_names = ', ".$arr['com_name']."(".G5_TIME_YMD."~)'
                        , com_level = '2'
                        , com_send_type = 'email'
                        , com_status = 'ok'
                        , com_reg_dt = '".G5_TIME_YMDHIS."'
                        , com_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql,1);
            $com['com_idx_provider'] = sql_insert_id();
        }

    }
    // 완제품인 경우, 거래처 com_idx = MY com_idx
    else if($arr['bom_type']=='product') {
        $com['com_idx_provider'] = $_SESSION['ss_com_idx'];
    }
    // 거래처 정보 =======================================================


    $sql_common = " com_idx = '".$_SESSION['ss_com_idx']."',
                    com_idx_provider = '".$com['com_idx_provider']."',
                    bct_id = '".$arr['bct_id']."',
                    bom_name = '".addslashes($arr['bom_name'])."',
                    bom_type = '".$arr['bom_type']."',
                    bom_price = '".$arr['bom_price']."',
                    bom_update_dt = '".G5_TIME_YMDHIS."'
    ";
    
    $sql = "SELECT *
                FROM {$g5['bom_table']}
            WHERE bom_part_no = '".$arr['bom_part_no']."'
    ";
    $bom = sql_fetch($sql,1);
    if(!$bom['bom_idx']) {
        $sql = " INSERT INTO {$g5['bom_table']} SET
                    {$sql_common} 
                    , bom_part_no = '".$arr['bom_part_no']."'
                    , bom_status = 'ok'
                    , bom_reg_dt = '".G5_TIME_YMDHIS."'
        ";
        if(!$demo) {sql_query($sql,1);}
        $bom['bom_idx'] = sql_insert_id();
    }
    else {
        $sql = "UPDATE {$g5['bom_table']} SET
                    {$sql_common}
                WHERE bom_idx = '".$bom['bom_idx']."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    if($demo) {print_r3($sql);}
    // print_r3($sql);


    // 가격 정보 =======================================================
    $sql = "SELECT *
                FROM {$g5['bom_price_table']}
            WHERE bom_idx = '".$bom['bom_idx']."'
                AND bop_start_date = '2021-01-01'
    ";
    $bop = sql_fetch($sql,1);
    if(!$bop['bop_idx']) {
        $sql = " INSERT INTO {$g5['bom_price_table']} SET
                    bom_idx = '".$bom['bom_idx']."'
                    , bop_price = '".$arr['bom_price']."'
                    , bop_start_date = '2021-01-01'
                    , bop_reg_dt = '".G5_TIME_YMDHIS."'
                    , bop_update_dt = '".G5_TIME_YMDHIS."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    else {
        $sql = "UPDATE {$g5['bom_price_table']} SET
                    bop_price = '".$arr['bom_price']."'
                    , bop_update_dt = '".G5_TIME_YMDHIS."'
                WHERE bop_idx = '".$bop['bop_idx']."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    if($demo) {print_r3($sql);}
    // 가격 정보 =======================================================
    
    return $bom['bom_idx'];
}
}



// 엑셀 자재 삭제 처리 함수
if(!function_exists('func_delete_bom_item2')){
function func_delete_bom_item2($arr) {
    global $g5,$demo;

    if(!$arr['bom_idx'] || !$arr['bom_child_arr']) return false;

    $sql = "DELETE FROM {$g5['bom_item_table']}
            WHERE bom_idx = '".$arr['bom_idx']."'
                AND bom_idx_child NOT IN (".implode(',',$arr['bom_child_arr']).")
    ";
    sql_query($sql,1);

    return $arr['bom_idx'];
}
}