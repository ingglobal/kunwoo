<?php
include_once('./_head.sub.php');

$f_tblname = 'g5_0_companies';
$t_tblname = $g5['company_table'];
//필드명 추출
$col_sql = " DESC {$f_tblname} ";
$col_res = sql_query($col_sql);
$col_arr = array();

for($i=0; $nrow=sql_fetch_array($col_res);$i++){
    array_push($col_arr,$nrow['Field']);
}

//업체정보추출
$rec_sql = " SELECT * FROM {$f_tblname} ";
$rec_res = sql_query($rec_sql);
$con_arr = array();
for($i=0; $rrow=sql_fetch_array($rec_res); $i++){
    $rec_arr = array();
    for($j=0;$j<count($col_arr);$j++){
        $rec_arr[$col_arr[$j]] = $rrow[$col_arr[$j]];
    }
    array_push($con_arr,$rec_arr);
}
// print_r2($con_arr);
?>
<div class="btn_box">
    <a href="<?=G5_USER_ADMIN_SQL_URL?>" class="btn btn_04 btn_start">SQL홈</a><br><br>
    <a href="<?=G5_USER_ADMIN_SQL_URL?>/reg_company.php?start=1" class="btn btn_02 btn_start">[시작]</a>
</div>
<div id="cont"></div>
<?php
if($start == 1 && count($con_arr)){
    $countgap = 10; //몇건씩 보낼지 설정
    $sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
    $maxscreen = 30; // 몇건씩 화면에 보여줄건지 설정

    //초기화
    /*
    $truncate_sql = " TRUNCATE {$t_tblname} ";
    sql_query($truncate_sql,1);
    */
    $del_sql = " DELETE FROM {$t_tblname} WHERE com_idx > 14 ";
    sql_query($del_sql,1);
    $seq_sql = " ALTER TABLE {$t_tblname} AUTO_INCREMENT=15 ";
    sql_query($seq_sql,1);

    flush();
    ob_flush();

    $tcnt = 0;
    for($i=0;$i<count($con_arr);$i++){
        $sql = ' INSERT INTO '.$t_tblname.' SET
                com_idx_par = "14"
                ,com_names = "'.$con_arr[$i]['cos_name'].'(22-09-22~)"
                ,com_level = "4"
                ,com_type = "carparts"
                ,com_president = "'.$con_arr[$i]['cos_name'].'"
                ,com_code = "'.$con_arr[$i]['cos_cd'].'"
                ,com_name = "'.$con_arr[$i]['cos_name'].'"
                ,com_addr1 = "'.$con_arr[$i]['cos_addr'].'"
                ,com_sale_yn = "'.($con_arr[$i]['cos_yn_sale'] == 'Y'?1:0).'"
                ,com_out_yn = "'.($con_arr[$i]['cos_yn_out'] == 'Y'?1:0).'"
                ,com_buy_yn = "'.($con_arr[$i]['cos_yn_buy'] == 'Y'?1:0).'"
                ,com_tel = "'.$con_arr[$i]['cos_tel'].'"
                ,com_fax = "'.$con_arr[$i]['cos_fax'].'"
                ,com_status = "ok"
                ,com_reg_dt = "'.G5_TIME_YMDHIS.'"
                ,com_update_dt = "'.G5_TIME_YMDHIS.'"
        ';
        sql_query($sql,1);

        $tcnt++;
        echo "<script>document.getElementById('cont').innerHTML += '".$tcnt."개 - ".$con_arr[$i]['cos_name']." - 처리됨<br>';</script>\n";

        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);
    
        //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if($tcnt % $countgap == 0){
            echo "<script>document.getElementById('cont').innerHTML += '<br>';</script>\n";
        }
    
        //화면 정리! 부하를 줄임 (화면을 싹 지움)
        if($tcnt % $maxscreen == 0){
            echo "<script>document.getElementById('cont').innerHTML = '';</script>\n";
        }
    }
//if($start == 1 && count($allData)) //############################ 작업종료 ##############
?>
<script>
document.getElementById('cont').innerHTML += "<br><br>총 <?php echo number_format($tcnt); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>
<?php
}
else{
    echo '<div class="display_empty">[시작]버튼을 누르면 작업이 실행됩니다.</div>';
}

include_once('./_tail.sub.php');