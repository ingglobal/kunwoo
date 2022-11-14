<?php
$sub_menu = "920100";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST['ord_idx']);
// print_r2($_POST['chk']);
// exit;
if($w == 'd') {
    foreach($_POST['chk'] as $ord_idx_v) {
        //1. 생산실행에 ord_idx가 있으면 삭제할 수 없다.
        $ori_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_out_practice_table']} WHERE ord_idx = '{$ord_idx_v}' AND oop_status NOT IN('del','delete','cancel','trash') ";
        //echo $ori_sql;exit;
        $ori = sql_fetch($ori_sql);
        if($ori['cnt']){
            alert('생산실행에 등록된 수주데이터는 삭제할 수 없습니다.');
            exit;
        }
        //2. 출하계획에 ord_idx가 있으면 삭제할 수 없다.
        $oro_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_out_table']} WHERE ord_idx = '{$ord_idx_v}' AND oro_status NOT IN('del','delete','cancel','trash') ";
        $oro = sql_fetch($oro_sql);
        if($oro['cnt']){
            alert('출하계획에 등록된 수주데이터는 삭제할 수 없습니다.');
            exit;
        }
        //해당 수주데이터 삭제처리
        $sql = " UPDATE {$g5['order_table']} SET
                ord_status = 'trash'
            WHERE ord_idx = '{$ord_idx_v}'
        ";
        sql_query($sql,1);
        //해당 수주제품 삭제처리
        $sql_ori = " UPDATE {$g5['order_item_table']} SET
                        ori_status = 'trash'
                    WHERE ord_idx = '{$ord_idx_v}'  
        ";
        sql_query($sql_ori,1);
    }
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';

goto_url('./order_list.php?'.$qstr, false);