<?php
$sub_menu = "960100";
include_once("./_common.php");

if ($member['mb_level']<10)
    alert('해당 메뉴에 접근 권한이 없습니다.');

if (!$dta_start_dt || !$dta_end_dt) {
    alert('날짜 범위를 입력하세요.');
}

// print_r2($_REQUEST);
// exit;

// 날짜 조정
if($set_dta_dt) {
    $dta_time_text = ($dta_time<0) ? $dta_time : '+'.$dta_time; // 음수일 경우도 수용
    $dta_dt_text = ($set_dta_dt_case=='batch') ? "dta_dt".$dta_time_text : "UNIX_TIMESTAMP('".$dta_dt."')";

    $sql = "UPDATE g5_1_data_output_".$mms_idx." SET
                dta_dt = ".$dta_dt_text."
            WHERE dta_mmi_no = '".$dta_mmi_no."'
                AND dta_dt >= UNIX_TIMESTAMP('".$dta_start_dt."')
                AND dta_dt <= UNIX_TIMESTAMP('".$dta_end_dt."')
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);
}

// 값 변경
if($set_count) {
    $dta_updown_text = ($dta_updown<0) ? $dta_updown : '+'.$dta_updown; // 음수일 경우도 수용
    $dta_value_text = ($set_count_case=='batch') ? "dta_value".$dta_updown_text : "'".$dta_value."'";

    $sql = "UPDATE g5_1_data_output_".$mms_idx." SET
                dta_value = ".$dta_value_text."
            WHERE dta_mmi_no = '".$dta_mmi_no."'
                AND dta_dt >= UNIX_TIMESTAMP('".$dta_start_dt."')
                AND dta_dt <= UNIX_TIMESTAMP('".$dta_end_dt."')
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);
}

// 통계일자 조정
if($set_dta_date) {

    $sql = "UPDATE g5_1_data_output_".$mms_idx." SET
                dta_date = '".$dta_day."'
            WHERE dta_mmi_no = '".$dta_mmi_no."'
                AND dta_dt >= UNIX_TIMESTAMP('".$dta_start_dt."')
                AND dta_dt <= UNIX_TIMESTAMP('".$dta_end_dt."')
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);
}

// 교대번호 조정
if($set_shift_no) {

    $sql = "UPDATE g5_1_data_output_".$mms_idx." SET
                dta_shf_no = '".$dta_shift_no."'
            WHERE dta_mmi_no = '".$dta_mmi_no."'
                AND dta_dt >= UNIX_TIMESTAMP('".$dta_start_dt."')
                AND dta_dt <= UNIX_TIMESTAMP('".$dta_end_dt."')
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);
}



// exit;
$qstr = "&ser_mms_idx=$mms_idx&sfl=$sfl&stx=$dta_mmi_no&dta_start_dt=$dta_start_dt&dta_end_dt=$dta_end_dt";
$qstr .= "&set_dta_dt=$set_dta_dt&set_dta_dt_case=$set_dta_dt_case&set_count=$set_count&set_count_case=$set_count_case&set_dta_date=$set_dta_date&set_shift_no=$set_shift_no";

alert('일괄 수정 성공!','./data_output_batch.php?'.$qstr, false);
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>