<?php
$sub_menu = "930100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');



$orp_end_date = $orp_start_date;
$orp_end_date = ($g5['setting']['set_orp_done_date_cnt'])?get_dayAddDate($orp_start_date,$g5['setting']['set_orp_done_date_cnt']):get_dayAddDate($orp_start_date,5);
$first_flag = ($orp_order_no && $trm_idx_line && $mb_id && $orp_start_date && $orp_end_date) ? true : false;
//$first_flag = ($orp_order_no && $trm_idx_line && $mb_id && $orp_start_date && $orp_end_date) ? true : false;


//history 저장내용
$history = 'mb_id='.$member['mb_id'].',orp_idx='.$orp_idx.',oop_count='.$oop_count.',oop_status='.$oop_status.',oop_update_dt='.G5_TIME_YMDHIS.'\n';



//완전 신규등록시
if($first_flag){
    //동일한 생산시작일에 같은 설비라인으로 등록하려는지 체크한다.
    $ord_chk_sql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_practice_table']} WHERE orp_start_date = '{$orp_start_date}' AND trm_idx_line = '{$trm_idx_line}' AND orp_status NOT IN('delete','del','trash') ");
    if($ord_chk_sql['cnt'])
        alert('동일한 생산시작일에 지정하신 설비 '.$g5['line_name'][$trm_idx_line].'의 생산계획이 이미 존재합니다.\n[생산계획ID(라인설비별)찾기]에서 해당 생산계획을 찾아서 추가 등록해 주세요.');


    $sql1 = " INSERT {$g5['order_practice_table']} SET
                com_idx = '".$_SESSION['ss_com_idx']."',
                orp_order_no = '".$orp_order_no."',
                trm_idx_operation = '',
                trm_idx_line = '".$trm_idx_line."',
                shf_idx = '',
                mb_id = '".$mb_id."',
                orp_start_date = '".$orp_start_date."',
                orp_done_date = '".$orp_end_date."',
                orp_memo = '',
                orp_status = 'confirm',
                orp_reg_dt = '".G5_TIME_YMDHIS."',
                orp_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql1,1);
    $orp_idx = sql_insert_id();

    //새로운 orp_idx의 등록이므로 bom_idx를 새롭게 등록해라.
    //oop테이블에 등록
    $sql2 = " INSERT {$g5['order_out_practice_table']} SET
        orp_idx = '".$orp_idx."',
        bom_idx = '".$bom_idx."',
        oop_count = '".$oop_count."',
        oop_memo = '".$oop_memo."',
        oop_history = '".$history."',
        oop_status = '".$oop_status."',
        oop_reg_dt = '".G5_TIME_YMDHIS."',
        oop_update_dt = '".G5_TIME_YMDHIS."',
        oop_1 = '".$oop_1."',
        oop_2 = '".$oop_2."',
        oop_3 = '".$oop_3."',
        oop_4 = '".$oop_4."',
        oop_5 = '".$oop_5."',
        oop_6 = '".$oop_6."',
        oop_7 = '".$oop_7."',
        oop_8 = '".$oop_8."',
        oop_9 = '".$oop_9."',
        oop_10 = '".$oop_10."'
    ";
    sql_query($sql2,1);
}
//완전히 새로운 등록이 아니면서, oop_idx없고, orp_idx와 bom_idx가 있을 경우, 
else if(!$first_flag && !$oop_idx && $orp_idx && $bom_idx){
    //기존 orp_idx 에 등록된 해당 bom_idx로 등록된 oop_idx가 존재하는지 확인하고 있으면 해당 oop_idx에서 수정하라고 튕긴다.
    $oop = sql_fetch(" SELECT COUNT(*) AS cnt, oop_idx FROM {$g5['order_out_practice_table']}
                    WHERE orp_idx = '{$orp_idx}' AND bom_idx = '{$bom_idx}' AND oop_status NOT IN('delelte','del','trash')
    ");
    // 동일제품이 있으면 튕겨내라
    if($oop['cnt']){
        alert("선택하신 설비라인에 이미 동일한 상품이 이미 존재합니다.",'./order_out_practice_list.php?'.$qstr);
    }
    // 동일제품이 없으면 등록해라
    else {
        $sql3 = " INSERT {$g5['order_out_practice_table']} SET
            orp_idx = '".$orp_idx."',
            bom_idx = '".$bom_idx."',
            oop_count = '".$oop_count."',
            oop_memo = '".$oop_memo."',
            oop_history = '".$history."',
            oop_status = '".$oop_status."',
            oop_reg_dt = '".G5_TIME_YMDHIS."',
            oop_update_dt = '".G5_TIME_YMDHIS."',
            oop_1 = '".$oop_1."',
            oop_2 = '".$oop_2."',
            oop_3 = '".$oop_3."',
            oop_4 = '".$oop_4."',
            oop_5 = '".$oop_5."',
            oop_6 = '".$oop_6."',
            oop_7 = '".$oop_7."',
            oop_8 = '".$oop_8."',
            oop_9 = '".$oop_9."',
            oop_10 = '".$oop_10."'
        ";
        //echo $sql3;exit;
        sql_query($sql3,1);
    }
}
//생산계획변경작업시 (!$first_flag && $oop_idx && $orp_idx)
else{
    //우선 기존 현재 oop_idx 가 속해 있는 orp_idx 가 $orp_idx와 같은지 다른지 확인한다.
    //라인설비 정보가 동일하니 기존 oop_idx의 수량/메모/상태 정보만 수정한다.
    if($orp_idx_org == $orp_idx){
        $oop_sql = " UPDATE {$g5['order_out_practice_table']} SET
                        oop_count = '".$oop_count."',
                        oop_memo = '".$oop_memo."',
                        oop_history = CONCAT('".$history."'),
                        oop_status = '".$oop_status."',
                        oop_update_dt = '".G5_TIME_YMDHIS."',
                        oop_1 = '".$oop_1."',
                        oop_2 = '".$oop_2."',
                        oop_3 = '".$oop_3."',
                        oop_4 = '".$oop_4."',
                        oop_5 = '".$oop_5."',
                        oop_6 = '".$oop_6."',
                        oop_7 = '".$oop_7."',
                        oop_8 = '".$oop_8."',
                        oop_9 = '".$oop_9."',
                        oop_10 = '".$oop_10."'
                    WHERE oop_idx = '{$oop_idx}'
        ";
        sql_query($oop_sql,1);
    }
    //라인변경이 발생
    else {
        //새로운 orp_idx에 해당 bom_idx가 존재하는지 확인
        $oop_chk_sql = " SELECT oop_idx,orp.orp_order_no FROM {$g5['order_out_practice_table']} AS oop
                            LEFT JOIN {$g5['bom_table']} AS bom ON oop.bom_idx = bom.bom_idx
                            LEFT JOIN {$g5['order_practice_table']} AS orp ON oop.orp_idx = orp.orp_idx
                            WHERE oop.orp_idx = '{$orp_idx}' AND oop.bom_idx = '{$bom_idx}' AND oop_status NOT IN('delete','del','trash') ";
        $oop_chk = sql_fetch($oop_chk_sql);
        //있으면 해당 orp_idx의 ori_idx의 정보를 수정하게 하고 팅겨내라
        if($oop_chk['oop_idx']){
            alert("선택하신 설비라인에 이미 동일한 생산계회상품ID:".$oop_chk['oop_idx']."이 존재합니다.");
        }
        //없으면 나의 oop_idx의 orp_idx를 새로운 orp_idx로 바꿔치기하고 (수량/메모/상태) 수정해라
        else {
            $oop_md_sql = " UPDATE {$g5['order_out_practice_table']} SET
                            orp_idx = '".$orp_idx."',
                            oop_count = '".$oop_count."',
                            oop_memo = '".$oop_memo."',
                            oop_history = CONCAT('".$history."'),
                            oop_status = '".$oop_status."',
                            oop_update_dt = '".G5_TIME_YMDHIS."',
                            oop_1 = '".$oop_1."',
                            oop_2 = '".$oop_2."',
                            oop_3 = '".$oop_3."',
                            oop_4 = '".$oop_4."',
                            oop_5 = '".$oop_5."',
                            oop_6 = '".$oop_6."',
                            oop_7 = '".$oop_7."',
                            oop_8 = '".$oop_8."',
                            oop_9 = '".$oop_9."',
                            oop_10 = '".$oop_10."'
                        WHERE oop_idx = '{$oop_idx}'
            ";
            sql_query($oop_md_sql,1);
        }

    }

}

// exit;
goto_url('./order_out_practice_list.php?'.$qstr, false);
//goto_url('./order_out_practice_form.php?'.$qstr.'&w=u&oop_idx='.$oop_idx, false);
?>