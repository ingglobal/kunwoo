<?php
$sub_menu = "945118";
include_once('./_common.php');


check_demo();

auth_check($auth[$sub_menu], 'r');

check_admin_token();

if(!$item_add){
    if (!count($_POST['chk'])) {
        alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
    }
    
    if ($_POST['act_button'] == "선택수정") {
        foreach($_POST['chk'] as $gst_idx_v) {
            $sql = "UPDATE {$g5['guest_stock_table']} SET
                        gst_count = '".sql_real_escape_string($_POST['gst_count'][$gst_idx_v])."'
                    WHERE gst_idx = '".$gst_idx_v."'
            ";
    
            sql_query($sql,1);
        }
    }
    else if($_POST['act_button'] == "선택삭제") {
        foreach($_POST['chk'] as $gst_idx_v) {
            $sql = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_idx = '".$gst_idx_v."' ";
    
            sql_query($sql,1);
        }
    }
    
    
}
//개별제품 추가하기
else{
    //print_r2($_POST);exit;
    if(!$bom_idx)
        alert('제품을 선택해 주세요.');
    
    if(!$gst_date)
        alert('재고확인날짜를 입력해 주세요.');
    
    if(!$gst_counts)
        alert('해당 제품의 고객처 재고량을 입력해 주세요.');

    $gst = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['guest_stock_table']} WHERE bom_idx = '{$bom_idx}' AND gst_date = '{$gst_date}' AND gst_status NOT IN('delete','del','trash') ");
    if($gst['cnt']){
        alert('등록하려는 제품이 해당날짜의 재고데이터로 이미 존재합니다.\n등록된 제품을 검색해서 수량을 수정해 주세요.');
    }

    $sql = " INSERT INTO {$g5['guest_stock_table']} SET
                com_idx = '{$_SESSION['ss_com_idx']}'
                ,com_idx_customer = '{$com_idx_customer}'
                ,bom_idx = '{$bom_idx}'
                ,gst_count = '{$gst_counts}'
                ,gst_date = '{$gst_date}'
                ,gst_status = 'ok'
                ,gst_reg_dt = '".G5_TIME_YMDHIS."'
                ,gst_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
}

//값이 없는 레코드는 전부 삭제한다.
$sql_del = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_count = '0' ";
sql_query($sql_del,1);

//exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
if($schrows)
    $qstr .= '&schrows='.$schrows;
goto_url('./guest_item_list.php?'.$qstr);