<?php
$sub_menu = "930100";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'w');

// echo $oop_idx."<br>";
// echo $ooc_date."<br>";
// echo $ooc_day_night."<br>";
// exit;
//동일한 날짜의 자식복제본이 존재하는 지 확인한다.($g5['order_oop_child_table'])

$chk_res = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_oop_child_table']}
                WHERE oop_idx = '{$oop_idx}'
                    AND ooc_date = '{$ooc_date}'
                    AND ooc_status NOT IN ('delete','del','trash') ");
if($chk_res['cnt']){
    if($ooc_day_night != 'T'){ //삭제(Trash)가 아니면
        $sql = " UPDATE {$g5['order_oop_child_table']} SET
                        ooc_day_night = '{$ooc_day_night}'
                        , ooc_status = 'ok'
                        , ooc_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE oop_idx = '{$oop_idx}'
                        AND ooc_date = '{$ooc_date}'
                        AND ooc_status NOT IN ('delete','del','trash') ";
    }
    else{ //삭제(Trash)라면
        $sql = " DELETE FROM {$g5['order_oop_child_table']}
                WHERE oop_idx = '{$oop_idx}'
                    AND ooc_date = '{$ooc_date}'
                    AND ooc_status NOT IN ('delete','del','trash') ";
    }
}
else{
    $chk = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_oop_child_table']} ");
    if(!$chk['cnt'])
        sql_query(" ALTER TABLE {$g5['order_oop_child_table']} auto_increment=1 ");

    $sql = " INSERT INTO {$g5['order_oop_child_table']} SET
                oop_idx = '{$oop_idx}'
                , ooc_date = '{$ooc_date}'
                , ooc_day_night = '{$ooc_day_night}'
                , ooc_status = 'ok'
                , ooc_reg_dt = '".G5_TIME_YMDHIS."'
                , ooc_update_dt = '".G5_TIME_YMDHIS."'
    ";
}

sql_query($sql,1);


$qstr .= '&start_date='.$start_date.'&end_date='.$end_date;
$order_out_practice_calendar_url = './order_out_practice_calendar_list.php?'.$qstr;

goto_url($order_out_practice_calendar_url, false);