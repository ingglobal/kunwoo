<?php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1

$g5['title'] = 'ICMMS에러합산DB동기화';
include_once('./_head.sub.php');
include_once('./_head.icmms.php');
//http://myadmin.icmms.co.kr/db_sql.php?db=icmms_www
//http://kunwoo.epcs.co.kr/user/cron/icmms_forge2_sync.230107.php
// icmms / icmms@ingglobal - icmms_www
//건우금속의 단조프레스2번(mms_idx=67)는 "시장대응형"프로젝트로 icmms로 데이터가 올라간다. 
//그러니 kunwoo DB에도 싱크가 되도록 해주자 
$mms_idx = 67; //건우금속의 단조프레스2번(mms_idx=67)
//디비테이블이 존재하지 않으면 생성하고 환경변수도 초기셋팅
$error_sum_exist_sql = " DESC `{$tbl_error_sum}` ";
$error_sum_chk = @sql_query($error_sum_exist_sql);
if(!$error_sum_chk){
    include(G5_USER_CRON_PATH.'/icmms/tbl_create_error_sum.php');
}
$mta_chk = sql_fetch(" SELECT mta_key FROM {$g5['meta_table']} 
                    WHERE mta_db_table = 'icmms_error_sum'
                        AND mta_db_id = '{$mms_idx}'
                        AND mta_key = '{$tbl_error_sum}' ");
if(!$mta_chk['mta_key']){
    meta_update(array("mta_country"=>"ko_KR","mta_db_table"=>"icmms_error_sum","mta_db_id"=>$mms_idx,"mta_key"=>$tbl_error_sum,"mta_value"=>'0',"mta_reg_dt"=>G5_TIME_YMDHIS));
}

$meta_val = sql_fetch(" SELECT mta_value FROM {$g5['meta_table']} 
                WHERE mta_db_table = 'icmms_error_sum' 
                AND mta_db_id = '{$mms_idx}'
                AND mta_key = '{$tbl_error_sum}'");
$tbl_last_idx = $meta_val['mta_value'];

$mms_sql = " SELECT com_idx, imp_idx, mmg_idx FROM {$g5['mms_table']} WHERE mms_idx = '{$mms_idx}' ";
$mms = sql_fetch($mms_sql);
if(!$mms) return;
$com_idx = $mms['com_idx'];
$imp_idx = $mms['imp_idx'];
$mmg_idx = $mms['mmg_idx'];


$countgap = ($demo||$db_id) ? 10 : 20;    // 몇건씩 보낼지 설정
$maxscreen = ($demo||$db_id) ? 30 : 100;  // 몇건씩 화면에 보여줄건지?/
$sleepsec = 200;     // 천분의 몇초간 쉴지 설정 (1sec=1000)
// echo 'ok';return;
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
   
$sql = " SELECT * FROM {$tbl_error_sum} 
    WHERE dta_idx > {$tbl_last_idx}
        AND com_idx = '{$com_idx}'
        AND mms_idx = '{$mms_idx}'
        AND imp_idx = '{$imp_idx}'
";
// print_r2($sql);return;
$res = $icmms_connect_db_pdo->query($sql, PDO::FETCH_ASSOC);
//테이블에 데이터가 없으면 작업중지
if(!$res) return;

$row_cnt = 0;
$last_idx = $tbl_last_idx;
foreach($res as $row){
    $row_cnt++;
    
    $ins_sql = " INSERT INTO {$tbl_error_sum} SET
            com_idx = '{$row['com_idx']}'
            , imp_idx = '{$row['imp_idx']}'
            , mms_idx = '{$row['mms_idx']}'
            , mmg_idx = '{$row['mmg_idx']}'
            , shf_idx = '{$row['shf_idx']}'
            , cod_idx = '{$row['cod_idx']}'
            , trm_idx_category = '{$row['trm_idx_category']}'
            , dta_shf_no = '{$row['dta_shf_no']}'
            , dta_group = '{$row['dta_group']}'
            , dta_code = '{$row['dta_code']}'
            , dta_date = '{$row['dta_date']}'
            , dta_value = '{$row['dta_value']}'
    ";
    sql_query($ins_sql);
    $last_idx = $row['dta_idx'];

    echo "<script> document.all.cont.innerHTML += '".$row_cnt."개 완료<br>'; </script>\n";
    flush();
    @ob_flush();
    @ob_end_flush();
    usleep($sleepsec);

    // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if ($row_cnt % $countgap == 0)
        echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
    // 화면 정리! 부하를 줄임 (화면 싹 지움)
    if ($row_cnt % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; </script>\n";
} //foreach($res as $row)

meta_update2(array("mta_country"=>"ko_KR","mta_db_table"=>"icmms_error_sum","mta_db_id"=>$mms_idx,"mta_key"=>$tbl_error_sum,"mta_value"=>$last_idx,"mta_reg_dt"=>G5_TIME_YMDHIS));

?>
<script>
    document.all.cont.innerHTML += "<br><br><?=$row_cnt?>개 완료<br><font color=crimson><b>[끝]</b></font>";
</script>
<?php
include_once ('./_tail.icmms.php');
?>