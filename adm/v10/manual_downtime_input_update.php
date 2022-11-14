<?php
$sub_menu = "960130";
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
    // print_r2($_POST['mst_idx'][$k]);
    // print_r2($_POST['dta_start_ymdhis'][$k]);
    // print_r2($_POST['dta_end_ymdhis'][$k]);
    // if(is_array($_POST['mst_idx'][$k])) {
    //     for ($j=0; $j<count($_POST['mst_idx'][$k]); $j++) {
    //         echo $_POST['dta_idx'][$k][$j].' dta_idx 번호<br>';
    //         echo $_POST['mst_idx'][$k][$j].' mst_idx 번호<br>';
    //         echo $_POST['dta_start_ymdhis'][$k][$j].' 시작시간<br>';
    //         echo $_POST['dta_end_ymdhis'][$k][$j].' 종료시간<br>';
    //         echo '<br>====================<br>';
    //     }
    // }

    // 설비정보
    $mms[$i] = get_table_meta('mms','mms_idx',$_POST['mms_idx'][$k]);

    if(is_array($_POST['mst_idx'][$k])) {
        for ($j=0; $j<count($_POST['mst_idx'][$k]); $j++) {
            // echo $i.' ..................................<br>';
            // echo $_POST['dta_idx'][$k][$j].' dta_idx 번호<br>';
            // echo $_POST['mst_idx'][$k][$j].' mst_idx 번호<br>';
            // echo $_POST['dta_start_ymdhis'][$k][$j].' 시작시간<br>';
            // echo $_POST['dta_end_ymdhis'][$k][$j].' 종료시간<br>';

            // 공통요소
            /*
            $sql_common = " com_idx = '".$mms[$i]['com_idx']."'
                            , imp_idx = '".$mms[$i]['imp_idx']."'
                            , mms_idx = '".$mms[$i]['mms_idx']."'
                            , mmg_idx = '".$mms[$i]['mmg_idx']."'
                            , mst_idx = '".$_POST['mst_idx'][$k][$j]."'
                            , dta_start_dt = '".strtotime($_POST['dta_start_ymdhis'][$k][$j])."'
                            , dta_end_dt = '".strtotime($_POST['dta_end_ymdhis'][$k][$j])."'
            ";
            */
            $sql_common = " com_idx = '".$mms[$i]['com_idx']."'
                            , imp_idx = '".$mms[$i]['imp_idx']."'
                            , mms_idx = '".$mms[$i]['mms_idx']."'
                            , mmg_idx = '".$mms[$i]['mmg_idx']."'
                            , mst_idx = '".$_POST['mst_idx'][$k][$j]."'
                            , dta_start_dt = '".strtotime($_POST['dta_start_ymd'][$k][$j].' '.$_POST['dta_start_h'][$k][$j].':'.$_POST['dta_start_m'][$k][$j].':'.$_POST['dta_start_s'][$k][$j])."'
                            , dta_end_dt = '".strtotime($_POST['dta_end_ymd'][$k][$j].' '.$_POST['dta_end_h'][$k][$j].':'.$_POST['dta_end_m'][$k][$j].':'.$_POST['dta_end_s'][$k][$j])."'
            ";
            //echo $sql_common;continue;
            // 정보 업데이트
            if($_POST['dta_idx'][$k][$j]) {
                
                $sql = "UPDATE {$g5['data_downtime_table']} SET 
                            {$sql_common}
                            , dta_update_dt = '".G5_TIME_YMDHIS."'
                        WHERE dta_idx = '".$_POST['dta_idx'][$k][$j]."'
                ";
                sql_query($sql,1);
                // echo $sql.'<br>';

            }
            // 정보입력
            else if($_POST['mst_idx'][$k][$j] && $_POST['dta_end_ymdhis'][$k][$j]) {
    
                $sql = "INSERT INTO {$g5['data_downtime_table']} SET 
                            {$sql_common}
                            , dta_reg_dt = '".G5_TIME_YMDHIS."'
                            , dta_update_dt = '".G5_TIME_YMDHIS."'
                ";
                sql_query($sql,1);
                // echo $sql.'<br>';
    
            }

        }
    }
    
}


//exit;
alert('비가동정보를 입력하였습니다.','./manual_downtime_input.php', false);
?>