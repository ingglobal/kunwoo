<?php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1

$g5['title'] = '동기화';
include_once('./_head.sub.php');
include_once('./_head.icmms.php');

//-- 화면 표시
$countgap = ($demo||$db_id) ? 10 : 20;    // 몇건씩 보낼지 설정
$maxscreen = ($demo||$db_id) ? 30 : 100;  // 몇건씩 화면에 보여줄건지?/
$sleepsec = 200;     // 천분의 몇초간 쉴지 설정 (1sec=1000)

$table1 = 'MES_CAST_SHOT_PRESSURE';

$table2 = 'g5_1_cast_shot_pressure';
$fields2 = sql_field_names($table2);

// NEXT YMD Default
if($ym) {
    // 다음달
    $sql = " SELECT DATE_ADD('".$ym."-01' , INTERVAL +1 MONTH) AS ym FROM dual ";
    $dat = sql_fetch($sql,1);
    $ym_next = substr($dat['ym'],0,7);
    // echo $ym.'<br>';
    // echo $ym_next.'<br>';
    // exit;
}
else if($ymd) {
    // 다음일
    $sql = " SELECT DATE_ADD('".$ymd."' , INTERVAL +1 DAY) AS ymd FROM dual ";
    $dat = sql_fetch($sql,1);
    $ymd_next = substr($dat['ymd'],0,10);
    // echo $ymd.'<br>';
    // echo $ymd_next.'<br>';
    // exit;
}

// if db_id exists.
if($db_id) {
    $search1 = " WHERE SHOT_ID = '".$db_id."' ";
}
// 한달씩
else if($ym) {
    // $search1 = " WHERE EVENT_TIME LIKE '".$ym."' ";
    $search1 = " WHERE EVENT_TIME >= '".$ym."-01 00:00:00' AND EVENT_TIME <= '".$ym."-31 23:59:59' ";
}
// 하루씩
else if($ymd) {
    $st_time = ($ymd=='2020-11-07') ? '12:39:08':'00:00:00';
    // $search1 = " WHERE EVENT_TIME LIKE '".$ymd."%' ";
    $search1 = " WHERE EVENT_TIME >= '".$ymd." ".$st_time."' AND EVENT_TIME <= '".$ymd." 23:59:59' ";
    // $search1 = " WHERE CAMP_NO IN ('C0175987','C0175987') ";    // 특정레코드
}
else {
    // 데이터의 마지막 일 ------
    $sql = " SELECT event_time FROM {$table2} ORDER BY csp_idx DESC LIMIT 1 ";
    $dat = sql_fetch($sql,1);
    $ymdhis = $dat['event_time'];

    $search1 = " WHERE EVENT_TIME > '".$ymdhis."' ";
    $latest = 1;
}

$sql = "SELECT *
        FROM {$table1}
        {$search1}
        ORDER BY EVENT_TIME
";
// echo $sql.'<br>';
// exit;
$result = $icmms_connect_db_pdo->query($sql);
?>

<span style='font-size:9pt;'>
    <p><?=($ym)?$ym:$ymd?> 입력시작 ...<p><font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전에는 중간에 중지하지 마세요.<p>
</span>
<span id="cont"></span>


<?php
include_once ('./_tail.sub.php');


$status_array = array("00"=>"pending"
    ,"01"=>"pending"
    ,"02"=>"ok"
    ,"10"=>"ok"
    ,"20"=>"pending"
);


flush();
ob_flush();
ob_end_flush();

$cnt=0;
// 정보 입력
for ($i=0; $row=$result->fetch(PDO::FETCH_ASSOC); $i++) {
	$cnt++;
    // print_r2($row);
    if($demo) {
        if($i >= 2) {break;}
    }

    // table2 변수 추출 $arr
    for($j=0;$j<sizeof($fields2);$j++) {
        // 공백제거 & 따옴표 처리
        $arr[$fields2[$j]] = addslashes(trim($row[strtoupper($fields2[$j])]));
        // 천단위 제거
        if(preg_match("/_price$/",$fields2[$j]))
            $arr[$fields2[$j]] = preg_replace("/,/","",$arr[$fields2[$j]]);
    }

    // table2 입력을 위한 table1 변수 치환
    // $row['EVENT_TIME'] = substr($arr['EVENT_TIME'],0,19);
    // print_r2($arr);

    // table2 입력을 위한 변수배열 일괄 생성 ---------
    // 건너뛸 변수들 설정
    $skips = array('csp_idx','machine_id','shot_no');
    for($j=0;$j<sizeof($fields2);$j++) {
        if(in_array($fields2[$j],$skips)) {continue;}
        $arr[$fields2[$j]] = ($fields21[$fields2[$j]]) ? $arr[$fields21[$fields2[$j]]] : $arr[$fields2[$j]];
        $sql_commons[$i][] = " ".strtolower($fields2[$j])." = '".$arr[$fields2[$j]]."' ";
        $sql_field_arr[$i][] = " ".strtolower($fields2[$j])." ";            // for timescaleDB
        $sql_value_arr[$i][] = " '".$arr[$fields2[$j]]."' ";    // for timescaleDB
    }

    // table2 입력을 위한 변수 재선언 (or 생성)
    // $sql_commons[$i][] = " trm_idx_department = '".$mb2['mb_2']."' ";

    // machine_id 추출
    $sql2 = " SELECT machine_id, shot_no FROM g5_1_cast_shot WHERE shot_id = '".$arr['shot_id']."' ";
    // echo $sql2.'<br>';
    $csh = sql_fetch($sql2,1);
    // 주조공정 shot_it 가 없으면 건너뜀
    if(!$csh['machine_id']) {continue;}
    $sql_commons[$i][] = " machine_id = '".$csh['machine_id']."' ";
    $sql_commons[$i][] = " shot_no = '".$csh['shot_no']."' ";
    $sql_field_arr[$i][] = " machine_id ";  // for timescaleDB
    $sql_value_arr[$i][] = " '".$csh['machine_id']."' ";    // for timescaleDB
    $sql_field_arr[$i][] = " shot_no ";  // for timescaleDB
    $sql_value_arr[$i][] = " '".$csh['shot_no']."' ";    // for timescaleDB
    // print_r2($sql_field_arr[$i]);
    // print_r2($sql_value_arr[$i]);
    // exit;

    // 최종 변수 생성
    $sql_text[$i] = (is_array($sql_commons[$i])) ? implode(",",$sql_commons[$i]) : '';


    // Record update
    $sql3 = "   SELECT csp_idx FROM {$table2}
                WHERE shot_id = '".$arr['shot_id']."' AND event_time = '".$arr['event_time']."'
    ";
    // echo $sql3.'<br>';
    $row3 = sql_fetch($sql3,1);
    // 정보 업데이트
    if($row3['csp_idx']) {
		$sql = "UPDATE {$table2} SET
					$sql_text[$i]
				WHERE csp_idx = '".$row3['csp_idx']."'
		";
		if(!$demo) {sql_query($sql,1);}
	    else {echo $sql.'<br><br>';}
    }
    // 정보 입력
    else{
		$sql = "INSERT INTO {$table2} SET
					$sql_text[$i]
		";
		if(!$demo) {sql_query($sql,1);}
	    else {echo $sql.'<br><br>';}
    }


    // 공통쿼리 생성
    $sql_fields[$i] = (is_array($sql_field_arr[$i])) ? "(".implode(",",$sql_field_arr[$i]).")" : '';
    $sql_values[$i] = (is_array($sql_value_arr[$i])) ? "(".implode(",",$sql_value_arr[$i]).")" : '';
    // PgSQL insert record.
    $sql3 = "INSERT INTO {$table2}
                {$sql_fields[$i]} VALUES {$sql_values[$i]} 
            RETURNING csp_idx 
	";
    if(!$demo) {sql_query_pg($sql3,1);}
    else {echo $sql3.'<br><br>';}


    // g5_1_data_measure_58 디비 구조에 추가로 입력
    // echo $csh['machine_id'].'<br>';
    // echo $g5['mms_idx2'][$csh['machine_id']].'<br>';
    $mms_idx = $g5['mms_idx2'][$csh['machine_id']];
    $pg_table = 'g5_1_data_measure_'.$mms_idx;

    // $sql = "SELECT EXISTS (
    //         SELECT 1 FROM pg_tables 
    //         WHERE tableowner='".G5_PGSQL_USER."' AND tablename='".$pg_table."'
    //     ) AS flag
    // ";
    // $tb1 = sql_fetch_pg($sql,1);
    // // if table exists.
    // if($tb1['flag']) {
        // print_r2($g5['set_data_pressure_no_value']);
        if(is_array($g5['set_data_pressure_no_value'])) {
            $j = 0;
            foreach($g5['set_data_pressure_no_value'] as $k1 => $v1) {
                if($arr[$k1]>0) {
                    // echo $k1.'='.$arr[$k1].'<br>';
                    // $field_arr[$j][] = " ".strtolower($k1)." "; // for PgSQL
                    // $value_arr[$j][] = " '".$arr[$k1]."' ";     // for PgSQL
                    $sql3 = "INSERT INTO {$pg_table}
                                (dta_type,dta_no,dta_value,dta_1,dta_2,dta_dt) VALUES
                                ('8','".$v1."','".$arr[$k1]."','".$arr['shot_id']."','".$csh['shot_no']."','".$arr['event_time']."')
                            RETURNING dta_idx
                    ";
                    if(!$demo) {sql_query_pg($sql3,false);}
                    else {echo $sql3.'<br><br>';}
                    // echo $sql3.'<br><br>';
                }
                $j++;
            }
        }
    // }




    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$arr['shot_id']." (".$arr['event_time'].") 완료<br>'; </script>\n";

    flush();
    @ob_flush();
    @ob_end_flush();
    usleep($sleepsec);

	// 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
	if ($cnt % $countgap == 0)
		echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";

	// 화면 정리! 부하를 줄임 (화면 싹 지움)
	if ($cnt % $maxscreen == 0)
		echo "<script> document.all.cont.innerHTML = ''; </script>\n";

}

// Terminate in case of db_id found.
if($db_id) {
?>
    <script>
    	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><font color=crimson><b>[끝]</b></font>";
    </script>
    <?php
}
// 월간 처리
else {
    if($ym_next > date("Y-m") || $ymd_next > date("Y-m-d") || $demo || $latest) {
    ?>
    <script>
        document.all.cont.innerHTML += "<br><br><?=($ymd)?$ymd:$ym?> 완료<br><font color=crimson><b>[끝]</b></font>";
    </script>
    <?php
    }
    // 다음 페이지가 있는 경우는 3초 후 이동
    else {
    ?>
    <script>
        document.all.cont.innerHTML += "<br><br><?=($ymd)?$ymd:$ym?> 완료 <br><font color=crimson><b>2초후</b></font> 다음 페이지로 이동합니다.";
        setTimeout(function(){
            self.location='?ym=<?=$ym_next?>&ymd=<?=$ymd_next?>';
        },2000);
    </script>
    <?php
    }
}

include_once ('./_tail.icmms.php');
?>
