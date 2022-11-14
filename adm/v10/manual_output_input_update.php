<?php
$sub_menu = "960120";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// print_r2($_REQUEST);
// exit;

$arr['dta_group'] = 'manual';
$arr['dta_defect'] = 1;


for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    // echo $_POST['mms_idx'][$k].' 설비번호<br>';
    // echo $_POST['shift_no'][$k].' 교대번호<br>';
    // echo $_POST['item_no'][$k].' 기종번호<br>';
    // echo $_POST['dta_time'][$k].' 입력시간<br>';
    // print_r2($_POST['dta_item'][$k]);
    // if(is_array($_POST['dta_item'][$k])) {
    //     foreach($_POST['dta_item'][$k] as $k1=>$v1) {
    //         if(isset($v1) && $v1 != '' && $v1 >= 0) {
    //             echo '<br>====================<br>';
    //             echo $k1.' mms_status 번호<br>';
    //             echo $v1.' 품질갯수<br>';
    //         }
    //     }
    // }

    if(is_array($_POST['dta_item'][$k])) {
        foreach($_POST['dta_item'][$k] as $k1=>$v1) {
            // echo $k1.' mms_status 번호<br>';
            // echo $v1.' 품질갯수<br>';
            // Only in case of k1 exists.
            if(isset($v1) && $v1 != '' && $v1 >= 0) {

                $table_name = 'g5_1_data_output_'.$_POST['mms_idx'][$k];
                $mst['mst_idx'] = $k1;
                $dta_value = $v1;
                $arr['dta_dt'] = $_POST['dta_time'][$k];
    
                // 통계날짜, 2교대이면서 1교대 전인 경우는 하루전날을 통계 일자로 잡음, 함수 내부에서 처리되고 리턴됨
                $stat_date = get_output_stat_date(array("mms_idx"=>$_POST['mms_idx'][$k],"time"=>$arr['dta_dt']));
                
                // 공통요소
                $sql_common = " dta_shf_no = '".$_POST['shift_no'][$k]."'
                                , dta_mmi_no = '".$_POST['item_no'][$k]."'
                                , dta_group = '".$arr['dta_group']."'
                                , dta_defect = '".$arr['dta_defect']."'
                                , dta_defect_type = '".$mst['mst_idx']."'
                                , dta_dt = '".$arr['dta_dt']."'
                                , dta_date = '".$stat_date['stat_date']."'
                                , dta_value = '".$dta_value."'
                ";
    
                // 중복체크
                $sql_dta = "   SELECT dta_idx FROM {$table_name}
                                WHERE dta_defect = '".$arr['dta_defect']."'
                                    AND dta_group = '".$arr['dta_group']."'
                                    AND dta_defect_type = '".$mst['mst_idx']."'
                                    AND dta_dt = '".$arr['dta_dt']."'
                ";
                //echo $sql_dta.'<br>';
                $dta = sql_fetch($sql_dta,1);
                // 정보 업데이트
                if($dta['dta_idx']) {
                    
                    $sql = "UPDATE {$table_name} SET 
                                {$sql_common}
                                , dta_update_dt = '".G5_SERVER_TIME."'
                            WHERE dta_idx = '".$dta['dta_idx']."'
                    ";
                    sql_query($sql,1);
    
                }
                // 정보 입력
                else{
    
                    $sql = "INSERT INTO {$table_name} SET 
                                {$sql_common}
                                , dta_reg_dt = '".G5_SERVER_TIME."'
                                , dta_update_dt = '".G5_SERVER_TIME."'
                    ";
                    sql_query($sql,1);
                    // echo $sql.'<br>';
                    $dta['dta_idx'] = sql_insert_id();
    
                }
                $result_arr[$i]['dta_idx'] = $dta['dta_idx'];   // 고유번호

                // 합계 정보 입력
                // mms_idx, shift_no, item_no, dta_group, dta_defect, dta_defect_type, stat_date
                $ar['mms_idx'] = $_POST['mms_idx'][$k];
                $ar['shift_no'] = $_POST['shift_no'][$k];
                $ar['item_no'] = $_POST['item_no'][$k];
                $ar['dta_group'] = $arr['dta_group'];
                $ar['dta_defect'] = $arr['dta_defect'];
                $ar['dta_defect_type'] = $k1;
                $ar['stat_date'] = $stat_date['stat_date'];
                update_output_sum($ar);
                unset($ar);
    

            }
        }
    }



    
}


// exit;
alert('품질정보를 입력하였습니다.','./manual_output_input.php', false);
?>