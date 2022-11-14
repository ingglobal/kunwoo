<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'item_sum';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


if (!count($_POST['chk']) && $w != 'm') {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if($w == 'u') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        ${$pre} = get_table_meta($table_name, $pre.'_idx', $_POST[$pre.'_idx'][$k]);
        if (!${$pre}[$pre.'_idx'])
            $msg .= ${$pre}[$pre.'_idx'].': 자료가 존재하지 않습니다.\\n';
        else {
            $sql = "	UPDATE {$g5_table_name} SET 
                            ".$pre."_status = '".$_POST[$pre.'_status'][$k]."'
                        WHERE ".$pre."_idx = '".$_POST[$pre.'_idx'][$k]."' 
            ";
			sql_query($sql,1);
        }
    }

}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        ${$pre} = get_table_meta($table_name, $pre.'_idx', $_POST[$pre.'_idx'][$k]);
        if (!${$pre}[$pre.'_idx'])
            $msg .= ${$pre}[$pre.'_idx'].': 자료가 존재하지 않습니다.\\n';
        else {
            // $sql = "	UPDATE {$g5_table_name} SET 
            //                 ".$pre."_status = '1'
            //             WHERE ".$pre."_idx = '".$_POST[$pre.'_idx'][$k]."' 
            // ";
            $sql = " DELETE FROM {$g5_table_name} WHERE ".$pre."_idx = '".$_POST[$pre.'_idx'][$k]."' ";
			sql_query($sql,1);
        }
    }
}
// 전체 데이터 item_sum 테이블 보정
else if($w == 'm') {
    //테이블 초기화
    $truncate_sql = " TRUNCATE {$g5['item_sum_table']} ";
    sql_query($truncate_sql,1);

    $sql = " INSERT INTO {$g5['item_sum_table']} (com_idx, mms_idx, mmg_idx, itm_date, itm_shift, trm_idx_line, bom_idx, bom_part_no, itm_price, itm_status, itm_count, itm_weight)
            SELECT itm.com_idx, itm.mms_idx, 14, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
            , COUNT(itm_idx) AS itm_cnt
            , SUM(itm_weight) AS itm_weight
            FROM {$g5['item_table']} AS itm
                LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oop_idx = itm.oop_idx
                LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
            WHERE itm_status NOT IN ('trash','delete')
                AND itm_date != '0000-00-00'
            GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status
            ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status 
    ";
    sql_query($sql,1);
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';
	
goto_url('./'.$fname.'.php?'.$qstr, false);
?>