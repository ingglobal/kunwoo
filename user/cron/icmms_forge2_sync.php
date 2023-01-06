<?php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1
// return;
$g5['title'] = 'ICMMS측정DB동기화';
include_once('./_head.sub.php');
include_once('./_head.icmms.php');
//http://myadmin.icmms.co.kr/db_sql.php?db=icmms_www
//http://kunwoo.epcs.co.kr/user/cron/icmms_forge2_sync.230107.php
// icmms / icmms@ingglobal - icmms_www
//건우금속의 단조프레스2번(mms_idx=67)는 "시장대응형"프로젝트로 icmms로 데이터가 올라간다. 
//그러니 kunwoo DB에도 싱크가 되도록 해주자 
$mms_idx = 67; //건우금속의 단조프레스2번(mms_idx=67)
//g5_1_data_output_67 (epcs에서는 필요없음)
$tbl_measures = 'g5_1_data_measure_'.$mms_idx.'_%';
//단조프레스2번의 해당하는 측정테이블들이 전부 조회
$icsql = " SHOW TABLES LIKE '{$tbl_measures}' ";
// echo $icsql."<br>";
$res1 = $icmms_connect_db_pdo->query($icsql);
$tbl_measure_list = array();
$tbl_last_idx = array();
/* 
// 각 테이블의 dta_idx의 마지막 숫자를 meta테이블의 다시 저장
while($row1 = $res1->fetch(PDO::FETCH_NUM)){
    $tbl_measure = $row1[0];
    array_push($tbl_measure_list,$tbl_measure);
    //개별 테이블유무를 확인하고 없으면 생성
    $measure_exist_sql = " DESC `{$tbl_measure}` ";
    $measure_chk = @sql_query($measure_exist_sql);

    $sql = " UPDATE `g5_5_meta` mta SET 
    mta_value = ( SELECT dta_idx FROM {$tbl_measure} ORDER BY dta_idx DESC LIMIT 1 )
    WHERE mta_db_table = 'icmms_measure'
        AND mta_db_id = '{$mms_idx}'
        AND mta_key = '{$tbl_measure}';
    ";
    sql_query($sql);
}
return;
*/
while($row1 = $res1->fetch(PDO::FETCH_NUM)){
    $tbl_measure = $row1[0];
    array_push($tbl_measure_list,$tbl_measure);
    //개별 테이블유무를 확인하고 없으면 생성
    $measure_exist_sql = " DESC `{$tbl_measure}` ";
    $measure_chk = @sql_query($measure_exist_sql);
    if(!$measure_chk){
        include(G5_USER_CRON_PATH.'/icmms/tbl_create_measure.php');
    }
    $meta_val = sql_fetch(" SELECT mta_value FROM {$g5['meta_table']} 
                    WHERE mta_db_table = 'icmms_measure' 
                    AND mta_db_id = '{$mms_idx}'
                    AND mta_key = '{$tbl_measure}'");
    $tbl_last_idx[$tbl_measure] = $meta_val['mta_value'];
}

// print_r2($tbl_last_idx);
// return;

$mms_sql = " SELECT com_idx, imp_idx, mmg_idx FROM {$g5['mms_table']} WHERE mms_idx = '{$mms_idx}' ";
$mms = sql_fetch($mms_sql);
if(!$mms) return;
$com_idx = $mms['com_idx'];
$imp_idx = $mms['imp_idx'];
$mmg_idx = $mex['mmg_idx'];


$countgap = ($demo||$db_id) ? 10 : 20;    // 몇건씩 보낼지 설정
$maxscreen = ($demo||$db_id) ? 30 : 100;  // 몇건씩 화면에 보여줄건지?/
$sleepsec = 200;     // 천분의 몇초간 쉴지 설정 (1sec=1000)
// echo 'ok';return;
if(count($tbl_measure_list)){
?>
<span style='font-size:9pt;'>
    <p>INSERT시작 ...<p><font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전에는 중간에 중지하지 마세요.<p>
</span>
<span id="cont"></span>
<?php
include_once('./_tail.sub.php');

flush();
ob_flush();
ob_end_flush();

$tbl_cnt = 0;
$total_cnt = 0;
for($i=0;$i<count($tbl_measure_list);$i++){   
    $sql = " SELECT * FROM {$tbl_measure_list[$i]} WHERE dta_idx > {$tbl_last_idx[$tbl_measure_list[$i]]}
    ";
    $res = $icmms_connect_db_pdo->query($sql, PDO::FETCH_ASSOC);
    //테이블에 데이터가 없으면 다음 테이블로 skip
    if(!$res) continue;

    $tbl_cnt++;
    $row_cnt = 0;
    $last_idx = $tbl_last_idx[$tbl_measure_list[$i]];
    foreach($res as $row){
        $row_cnt++;
        $total_cnt = $tbl_cnt * $row_cnt;
        // print_r2($row);

        $ins_sql = " INSERT INTO {$tbl_measure_list[$i]} SET
                dta_dt = '{$row['dta_dt']}'
                , dta_value = '{$row['dta_value']}'
                , dta_reg_dt = '{$row['dta_reg_dt']}'
                , dta_update_dt = '{$row['dta_update_dt']}'
        ";
        sql_query($ins_sql);
        $last_idx = $row['dta_idx'];

        echo "<script> document.all.cont.innerHTML += '".$tbl_cnt."번TBL의. ".$row_cnt."번row (".($tbl_cnt*$row_cnt).")개 완료<br>'; </script>\n";
        flush();
        @ob_flush();
        @ob_end_flush();
        usleep($sleepsec);

        // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if ($total_cnt % $countgap == 0)
            echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
        // 화면 정리! 부하를 줄임 (화면 싹 지움)
        if ($total_cnt % $maxscreen == 0)
            echo "<script> document.all.cont.innerHTML = ''; </script>\n";
    }
    meta_update2(array("mta_country"=>"ko_KR","mta_db_table"=>"icmms_measure","mta_db_id"=>$mms_idx,"mta_key"=>$tbl_measure_list[$i],"mta_value"=>$last_idx,"mta_reg_dt"=>G5_TIME_YMDHIS));
} //for($i=0;$i<count($tbl_measure_list);$i++)
} //if(count($tbl_measure_list))
//meta환경변수 셋팅유무를 확인하고 없으면 저장


//g5_1_data_measure_67_(dta_idx:데이터idx,dta_dt:일시,dta_value:값,dta_reg_dt:등록일시,dta_update_dt:수정일시)
?>
<script>
    document.all.cont.innerHTML += "<br><br><?=$total_cnt?>개 완료<br><font color=crimson><b>[끝]</b></font>";
</script>
<?php
include_once ('./_tail.icmms.php');
?>
