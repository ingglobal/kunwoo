<?php
$sub_menu = "960130";
include_once("./_common.php");

auth_check($auth[$sub_menu],'w');

$mms = get_table_meta('mms', 'mms_idx', $mms_idx);
//print_r2($mms);
if (!$mms['mms_idx'])
    alert_close('존재하지 않는 자료입니다.');


$table_name = 'g5_1_data_offwork';
$arr['mms_idx'] = $mms_idx;
$arr['dta_dt'] = $time;
// 통계날짜, 2교대이면서 1교대 전인 경우는 하루전날을 통계 일자로 잡음, 함수 내부에서 처리되고 리턴됨
$stat_date = get_output_stat_date(array("mms_idx"=>$mms_idx,"time"=>$time));



// 품질 체크 & 입력
$sql_mst = "SELECT mst_idx, mst_name FROM {$g5['mms_status_table']}
            WHERE mst_name = '".$mst_name."'
";
//echo $sql_dta.'<br>';
$mst = sql_fetch($sql_mst,1);
// Insert if not exists.
if(!$mst['mst_idx']) {
    $sql = "INSERT INTO {$g5['mms_status_table']} SET 
                mms_idx = '".$arr['mms_idx']."'
                , mst_type = 'offwork'
                , mst_name = '".$mst_name."'
                , mst_status = 'ok'
                , mst_reg_dt = '".G5_TIME_YMDHIS."'
                , mst_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
    // echo $sql.'<br>';
    $mst['mst_idx'] = sql_insert_id();
}


// 공통요소
$sql_common = " com_idx = '".$mms['com_idx']."'
                , imp_idx = '".$mms['imp_idx']."'
                , mms_idx = '".$mms['mms_idx']."'
                , mmg_idx = '".$mms['mmg_idx']."'
                , dta_shf_no = '".$shift_no."'
                , mst_idx = '".$mst['mst_idx']."'
                , dta_date = '".$stat_date['stat_date']."'
                , dta_value = '".histosec($dta_value)."'
";

// 중복체크
$sql_dta = "   SELECT dta_idx FROM {$table_name}
                WHERE mms_idx = '".$mms['mms_idx']."'
                    AND dta_shf_no = '".$shift_no."'
                    AND mst_idx = '".$mst['mst_idx']."'
                    AND dta_date = '".$stat_date['stat_date']."'
";
//echo $sql_dta.'<br>';
$dta = sql_fetch($sql_dta,1);
// 정보 업데이트
if($dta['dta_idx']) {
    
    $sql = "UPDATE {$table_name} SET 
                {$sql_common}
                , dta_update_dt = '".G5_TIME_YMDHIS."'
            WHERE dta_idx = '".$dta['dta_idx']."'
    ";
    sql_query($sql,1);

}
// 정보 입력
else{

    $sql = "INSERT INTO {$table_name} SET 
                {$sql_common}
                , dta_reg_dt = '".G5_TIME_YMDHIS."'
                , dta_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
    // echo $sql.'<br>';
    $dta['dta_idx'] = sql_insert_id();

}
$result_arr[$i]['dta_idx'] = $dta['dta_idx'];   // 고유번호




$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '비가동정보 '.$html_title;
include_once('./_head.sub.php');


$itme_dom = $mms_idx."^".$shift_no;
?>
<script>
var item_each = '<div class="item_each"><?=$mst_name?>: <input name="dta_item[<?=$idx?>][<?=$mst['mst_idx']?>]" class="frm_input input_defect" value="<?=$dta_value?>"></div>';
$(item_each).insertBefore( $( '.item_each_add[item="<?=$itme_dom?>"]',opener.document) );
alert('비가동 정보를 입력하였습니다.');
window.close();
</script>

<?php
include_once('./_tail.sub.php');
?>
