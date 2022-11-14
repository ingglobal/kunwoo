<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

$demo = 1;  // 데모모드 = 1

$g5['title'] = '데이터분리 작업 측정';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once ('./_tail.php');
?>

<?php
// variables setting
$eidx = 0;  // 엑셀 카운터

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 5000000;  // 백만분의 몇초간 쉴지 설정
// $sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

// Count for mms_idx, dta_type, dta_no
$sql = "SELECT mms_idx, dta_type, dta_no
        FROM g5_1_data_measure 
        WHERE dta_group = 'mea' 
            AND dta_status = 0 
        GROUP BY mms_idx, dta_type, dta_no
";
$rs = sql_query($sql,1);

$arr = array();
for($i = 0 ; $row = sql_fetch_array($rs); $i++) {
    // mms_idx=1 is already donw.
    if($row['mms_idx'] == 1) {
        continue;
    }

    $cnt++;

    // variables setting
    // $dataA[$i] = ($dataA[$i]) ? $dataA[$i] : $item['매출기준일'][$i];

    // if tables is not exists, create tables
    $table_name = 'g5_1_data_measure_'.$row['mms_idx'].'_'.$row['dta_type'].'_'.$row['dta_no'];
    $sql = "SELECT EXISTS (
                SELECT 1 FROM Information_schema.tables
                WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
                AND TABLE_NAME = '".$table_name."'
            ) AS flag
    ";
    $tb1 = sql_fetch($sql,1);
    if(!$tb1['flag']) {
        $file = file('../../device/measure/sql_write.sql');
        $file = get_db_create_replace($file);
        $sql = implode("\n", $file);
        $source = array('/__TABLE_NAME__/', '/;/');
        $target = array($table_name, '');
        $sql = preg_replace($source, $target, $sql);
        sql_query($sql, FALSE);

        $sql_dta_dt = "";
    }
    // get the first dta_dt. it will be the date max for input reg date.
    else {
        $sql = "SELECT dta_dt
                FROM {$table_name}
                ORDER BY dta_idx
                LIMIT 1
        ";
        // print_r3($sql);
        $dta1 = sql_fetch($sql,1);
        if($dta1['dta_dt']) {
            $sql_dta_dt = " AND dta_dt < '".$dta1['dta_dt']."' ";
        }

    }


    // db input from original 34mil. measure.
    $sql = "INSERT INTO {$table_name} (dta_dt, dta_value, dta_reg_dt, dta_update_dt)
            SELECT dta_dt, dta_value, dta_reg_dt, dta_update_dt
            FROM g5_1_data_measure
            WHERE mms_idx = '".$row['mms_idx']."' 
                AND dta_type = '".$row['dta_type']."' 
                AND dta_no = '".$row['dta_no']."'
                AND dta_group = 'mea' 
                AND dta_status = 0 
                {$sql_dta_dt}
            ORDER BY dta_idx
    ";
    if(!$demo) {sql_query($sql,1);}
    else {print_r3($sql);}

    // 메시지 보임
    echo "<script> document.all.cont.innerHTML += '".$cnt
            .". ".$table_name." ----------->> 처리완료<br>'; </script>\n";
    

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    
    // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if ($i % $countgap == 0)
        echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
    
    // 화면 정리! 부하를 줄임 (화면 싹 지움)
    if ($i % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; </script>\n";

}



// 관리자 디버깅 메시지
if( is_array($g5['debug_msg']) ) {
    for($i=0;$i<sizeof($g5['debug_msg']);$i++) {
        echo '<div class="debug_msg">'.$g5['debug_msg'][$i].'</div>';
    }
?>
    <script>
    $(function(){
        $("#container").prepend( $('.debug_msg') );
    });
    </script>
<?php
}
?>



<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>