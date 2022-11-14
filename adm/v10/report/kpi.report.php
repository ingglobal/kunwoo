<?php
include_once('./_common.php');
include_once(G5_USER_ADMIN_PATH.'/lib/latest10.lib.php');

// com_idx 디폴트
$member['com_idx'] = $_SESSION['ss_com_idx'] ?: $member['com_idx'];
$com = get_table_meta('company','com_idx',$member['com_idx']);
$com_idx = $com['com_idx'];
// print_r2($com);

// print_r2($_REQUEST);
// exit;

$g5['title'] = '통합보고서';
include_once('./_head.sub.php');

$st_timestamp = strtotime($st_date.' 00:00:00');
$en_timestamp = strtotime($en_date.' 23:59:59');

// mmg_idx, if duplicated, mmg_idx is the last one.
while( list($key, $val) = each($_REQUEST) ) {
    if( preg_match("/mmg/",$key) && $_REQUEST[$key]!='' ) {
        // echo $_REQUEST[$key].'<br>';
        $mmg_idx = $_REQUEST[$key];
    }
}
// echo $mmg_idx.'<br>';

// In case of mms_idx. ex. 6-3, 4-5
// You should devide one eath other.
if( preg_match("/-/",$mmg_idx) ) {
    $mmg_arr = explode("-",$mmg_idx);
    $mmg_idx = $mmg_arr[0];
    $mms_idx = $mmg_arr[1];
    // echo $mmg_idx.'<br>';
    // echo $mms_idx.'<br>';
}
// exit;

// down_idxs를 뽑아두자. 라인별 합계를 위해서 미리 추출
$sql = "SELECT parent.mmg_idx
            , GROUP_CONCAT(cast(mmg.mmg_idx as char) ORDER BY mmg.mmg_left) AS down_idxs
        FROM {$g5['mms_group_table']} AS mmg, 
            {$g5['mms_group_table']} AS parent
        WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
            AND mmg.com_idx='".$com_idx."'
            AND parent.com_idx='".$com_idx."'
            AND mmg.mmg_status NOT IN ('trash','delete')
            AND parent.mmg_status NOT IN ('trash','delete')
        GROUP BY parent.mmg_idx
        ORDER BY parent.mmg_left
";
$result = sql_query($sql,1);
// echo $sql.'<br>';
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);
    $mmg_down_idxs[$row['mmg_idx']] = explode(",",$row['down_idxs']);
}
// print_r2($mmg_down_idxs);
// print_r2($mmg_down_idxs[$mmg_idx]); //mmg_idxs (그룹 번호들)


// mms_idxes 를 뽑아두어야 함 (이후 계산에서 해당 mms 관련 데이터들만 뽑아와야 함)
// 선택 라인이 있는 경우
if( is_array($mmg_down_idxs[$mmg_idx]) ) {
    // mmg_idxes 먼저 설정
    $mmg_array = $mmg_down_idxs[$mmg_idx];
    // print_r2($mmg_array);
    $sql_mmgs = " AND mmg_idx IN (".implode(',',$mmg_array).") ";
    // echo $sql_mmgs.'<br>';
    // mmg & parent 구조안에 있을 때 sql
    $sql_mmg_parent = " AND mmg.mmg_idx IN (".implode(',',$mmg_array).")
                        AND parent.mmg_idx IN (".implode(',',$mmg_array).")
    ";
    // echo $sql_mmg_parent;

    // mms_idxes 설정
    $sql = "SELECT GROUP_CONCAT(mms_idx) AS mmses
            FROM {$g5['mms_table']} AS mms
            WHERE mms_status NOT IN ('trash','delete') 
                AND mms.com_idx = '".$com_idx."'
                -- AND mms.mms_output_yn = 'Y'
                AND mmg_idx IN (".implode(',',$mmg_down_idxs[$mmg_idx]).")
            ORDER BY mms_idx
    ";
    // echo $sql.'<br>';
    // in case of mms_idx(설비)
    if($mms_idx) {
        $mms1['mmses'] = $mms_idx;
    }
    else {
        $mms1 = sql_fetch($sql,1);
    }
    // echo $mms1['mmses'];
    $mms_array = explode(",",$mms1['mmses']);
    // print_r2($mms_array);
    $sql_mmses = " AND mms_idx IN (".$mms1['mmses'].") ";
    // echo $sql_mmses.'<br>';

    // arm join인 경우는 mms_idx가 명확하지 않아서 재정의 필요 
    $sql_mmses1 = " AND arm.mms_idx IN (".implode(",",$mms_array).") ";
    // 게시판용 mms_idx 조건절
    $sql_mmses2 = " AND wr_2 IN (".implode(",",$mms_array).") ";
}
// 선택라인이 없으면 전체에서 추출한다.
else {

}



// 교대별 기종별 목표 먼저 추출 (아래 부분 목표 추출하는 부분에서 활용합니다.)
$sql = "SELECT shf_idx, sig_shf_no, mmi_no, sig_item_target
        FROM {$g5['shift_item_goal_table']} AS sig 
            LEFT JOIN {$g5['mms_item_table']} AS mmi ON mmi.mmi_idx = sig.mmi_idx
        WHERE (1)
            {$sql_mmses}
        ORDER BY shf_idx, sig_shf_no 
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++){
    // print_r2($row1);
    $target['shift_no_mmi'][$row['shf_idx']][$row['sig_shf_no']][$row['mmi_no']] += $row['sig_item_target'];    // 교대별 기종별 목표
}
// print_r2($target['shift_mmi']);
// echo '----------<br>';


// 목표추출 get target fetch
// 전체기간 설정이 있는 경우는 마지막 부분에서 돌면서 없는 날짜 목표를 채워줍니다.
$sql = "SELECT mms_idx, shf_idx, shf_period_type
        , (shf_target_1+shf_target_2+shf_target_3) AS shf_target_sum
        , shf_target_1
        , shf_target_2
        , shf_target_3
        , shf_start_dt AS db_shf_start_dt
        , shf_end_dt AS db_shf_end_dt
        , GREATEST('".$st_date." 00:00:00', shf_start_dt ) AS shf_start_dt
        , LEAST('".$en_date." 23:59:59', shf_end_dt ) AS shf_end_dt
        FROM {$g5['shift_table']}
        WHERE com_idx = '".$com['com_idx']."'
            AND shf_status NOT IN ('trash','delete')
            AND shf_end_dt >= '".$st_date."'
            AND shf_start_dt <= '".$en_date."'
            {$sql_mmses}
        ORDER BY mms_idx, shf_period_type, shf_start_dt
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
$byunit = 86400;
for($i=0;$row=sql_fetch_array($rs);$i++){
    $row['mmg_idx'] = $g5['mms'][$row['mms_idx']]['mmg_idx'];
    // print_r2($row);

    // 날짜범위를 for 돌면서 배열변수 생성
    $ts1 = strtotime(substr($row['shf_start_dt'],0,10));    // 시작 timestamp
    $ts2 = strtotime(substr($row['shf_end_dt'],0,10));    // 종료 timestamp
    // 종료일시가 오전인 경우는 전날로 바꾸어서 중복이 생기지 않도록 처리한다.
    if(substr($row['shf_end_dt'],11,2) < 12) {
        $ts2 = strtotime(substr($row['shf_end_dt'],0,10))-86400;
    }
    // echo date("Y-m-d",$ts1).'~'.date("Y-m-d",$ts2).'<br>';
    for($k=$ts1;$k<=$ts2;$k+=$byunit) {
        $date1 = preg_replace("/[ :-]/","",date("Y-m-d",$k));   // 날짜중에서 일자 추출하여 배열키값으로!

        // 전체기간 설정일 때는 동일설비, 같은 날짜값이 있으면 통과, 중복 계산하지 않도록 한다.
        if( $row['shf_period_type'] && $mms_date[$row['mms_idx']][$date1] ) {
            continue;
        }

        $date2 = preg_replace("/[ :-]/","",date("Y-m",$k));     // 날짜중에서 월 추출하여 배열키값으로!
        $date3 = preg_replace("/[ :-]/","",date("Y",$k));       // 년도만
        $week1 = date("w",$k); // 0 (for Sunday) through 6 (for Saturday)
        // 주차값 (1년 중 몇 주, date('w')랑 기준이 달라서 일요일인 경우 다음차수로 넘김)
        $week2 = (!$week1) ? date("W",$k)+1 : date("W",$k);
        // echo $week1.'(0=sunsay..) : '.$week2.'주차 : ';
        // echo date("Y-m-d",$k).'(오늘날짜) : ';
        // echo date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days")).'(주첫날)<br>';
        $target['week_day'][$week2] = date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days"));  // 주차의 시작 일요일

        $target['date'][$date1] += $row['shf_target_sum'];  // 날짜별 목표
        $target['week'][$week2] += $row['shf_target_sum'];  // 주차별 목표
        $target['month'][$date2] += $row['shf_target_sum'];  // 월별 목표
        $target['year'][$date3] += $row['shf_target_sum'];  // 연도별 목표
        $target['mms'][$row['mms_idx']] += $row['shf_target_sum'];  // 설비별 목표
        $target['mmg'][$row['mmg_idx']] += $row['shf_target_sum'];  // 그룹별 목표
        $target['total'] += $row['shf_target_sum'];  // 전체 목표
        // 날짜별 교대별 목표
        for($j=1;$j<4;$j++) {
            // echo $row['shf_target_'.$j].'<br>';
            $target['date_shift'][$date1][$j] += $row['shf_target_'.$j];    // 날짜별 교대별 목표
            $target['shift'][$j] += $row['shf_target_'.$j];    // 교대별 목표만
            $target['mms_shift'][$row['mms_idx']][$j] += $row['shf_target_'.$j];    // 설비별 교대별 목표만
            // 날짜별 교대별 목표. $target['shift_no_mmi'][shf_idx][shf_no][mmi_no] = 200 과 같은 구조로 되어 있음
            if( is_array($target['shift_no_mmi'][$row['shf_idx']]) ) {
                if(is_array($target['shift_no_mmi'][$row['shf_idx']][$j])) {
                    // $j=교대번호
                    foreach($target['shift_no_mmi'][$row['shf_idx']][$j] as $k1=>$v1) {
                        // k1=기종번호, $v1=목표값
                        $target['mms_mmi'][$row['mms_idx']][$k1] += $v1;    // 설비별 기종목표
                    }
                }
            }
        }
        $mms_date[$row['mms_idx']][$date1] = 1; // 중복 체크를 위해서 배열 생성해 둠
        // echo '------<br>';
    }
    // echo '<br>--------------<br>';
}
// print_r2($mms_date);
// print_r2($target);


// 비가동추출 get offwork time
// 전체기간 설정이 있는 경우는 마지막 부분에서 돌면서 없는 날짜 목표를 채워줍니다.
// 설비별 가동율도 계산해야 하지만 여기에서는 제외하는 걸로 합니다. (나중에 추가하자.)
$sql = "SELECT mms_idx, off_idx, off_period_type
        , off_start_time AS db_off_start_time
        , off_end_time AS db_off_end_time
        , FROM_UNIXTIME(off_start_time,'%Y-%m-%d %H:%i:%s') AS db_off_start_ymdhis
        , FROM_UNIXTIME(off_end_time,'%Y-%m-%d %H:%i:%s') AS db_off_end_ymdhis
        , GREATEST('".$st_timestamp."', off_start_time ) AS off_start_time
        , LEAST('".$en_timestamp."', off_end_time ) AS off_end_time
        , FROM_UNIXTIME( GREATEST('".$st_timestamp."', off_start_time ) ,'%Y-%m-%d %H:%i:%s') AS off_start_ymdhis
        , FROM_UNIXTIME( LEAST('".$en_timestamp."', off_end_time ) ,'%Y-%m-%d %H:%i:%s') AS off_end_ymdhis
        FROM {$g5['offwork_table']}
        WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            AND off_status IN ('ok')
            AND off_end_time >= '".$st_timestamp."'
            AND off_start_time <= '".$en_timestamp."'
            AND mms_idx IN (0)
        ORDER BY mms_idx DESC, off_period_type, off_start_time
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
$byunit = 86400;
for($i=0;$row=sql_fetch_array($rs);$i++){
    // print_r2($row);
    $offwork[$i]['mms_idx'] = $row['mms_idx'];
    $offwork[$i]['start'] = date("His",$row['db_off_start_time']);
    $offwork[$i]['end'] = date("His",$row['db_off_end_time']);
    // print_r2($offwork[$i]);
    // echo '<br>----<br>';
    // echo $i.'번째  <br>';
    // 앞에서 정의한 겹치는 시간이 있으면 빼야 함, 중복 계산하지 않도록 한다.
    if( is_array($offwork) ) {
        $offworkold = $offwork;
        for($j=0;$j<sizeof($offworkold);$j++){
            // print_r2($offworkold[$j]);
            // 완전 내부 포함인 경우는 중복 제외
            if( $offwork[$i]['start'] > $offworkold[$j]['start'] && $offwork[$i]['end'] < $offworkold[$j]['end'] ) {
                unset($offwork[$i]);
            }
            // 걸쳐 있는 경우
            else if( $offwork[$i]['start'] < $offworkold[$j]['end'] && $offwork[$i]['end'] > $offworkold[$j]['start'] ) {
                if( $offwork[$i]['start'] < $offworkold[$j]['start'] ) {
                    $offwork[$i]['end'] = $offworkold[$j]['start'];
                }
                if( $offwork[$i]['end'] > $offworkold[$j]['end'] ) {
                    $offwork[$i]['start'] = $offworkold[$j]['end'];
                }
            }
        }
    }
    // echo '<br>정리<br>';
    // print_r2($offwork[$i]);
    // echo '<br>----------------------------------------<br>';

    
}
// print_r2($mms_date);
// print_r2($offwork);

// 하루의 전체 비가동 시간 계산
$off_total = 0;
for($j=0;$j<sizeof($offwork);$j++){
    // print_r2($offwork[$j]);
    $off_total += strtotime($offwork[$j]['end']) - strtotime($offwork[$j]['start']);
}
// echo $off_total.'<br>';


// 설비가동율 추출
$sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
            , mms_idx
            , SUM(dta_value_sum) AS dta_value_sum
            , COUNT(mms_idx) AS mms_count
        FROM
        (
            SELECT
                mms_idx
                , SUM(dta_value) AS dta_value_sum
            FROM g5_1_data_run_sum
            WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                AND com_idx='".$com_idx."'
                {$sql_mmses}
            GROUP BY mms_idx
            ORDER BY mms_idx

        ) AS db1, g5_5_tally AS db_no
        WHERE n <= 2
        GROUP BY item_name
        ORDER BY n DESC, item_name
";
// echo $sql.'<br>';
$result = sql_query($sql,1);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);
    // total 부분만 가지고 와서 사용
    if($row['item_name'] == 'total') {
        $runtime_avg = $row['dta_value_sum']/$row['mms_count'];
    }
}
// echo $runtime_avg.'<br>';

// 날짜 차이 (+1을 해 줘야 함)
$sql = " SELECT TIMESTAMPDIFF(day,'".$st_date."','".$en_date."')+1 AS days ";
$days = sql_fetch($sql,1);
$days['seconds'] = $days['days']*86400;
// echo $days['days'].'<br>';
// echo $days['days'].'<br>';
$run_rate = $runtime_avg / $days['seconds'] * 100;
// echo $run_rate.'<br>';

// 비가동 시간 다시 계산합니다. (가동율이 너무 낮다는 요청으로!!)
$run_rate2 = ($days['seconds']-$off_total) / $days['seconds'] * 100;





add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi1.css">', 0);
?>
<style>
.td_graph {line-height:14px;}
</style>

<div id="report_wrapper">

    <!-- the start of .div_stat  -->
	<div class="div_stat">
		<ul>
			<li>
			   <span class="title">목표달성율</span>
               <span class="content" id="sum_target">&nbsp;</span>
				<span class="unit">%</span>
			</li>
			<li>
			   <span class="title">불량율</span>
				<span class="content" id="sum_defect">&nbsp;</span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">설비가동율</span>
				<span class="content" id="sum_runtime"><?=number_format($run_rate2,1)?></span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">알람발생</span>
				<span class="content" id="sum_alarm">&nbsp;</span>
				<span class="unit">건</span>
			</li>
			<li>
				<span class="title">예지발생</span>
				<span class="content" id="sum_predict">&nbsp;</span>
				<span class="unit">건</span>
			</li>
			<li>
				<span class="title">계획정비<d style="font-size:0.8em;">(D-10)</d></span>
				<span class="content" id="sum_plan">&nbsp;</span>
				<span class="unit">건</span>
			</li>
		</ul>
	</div>
    <!-- the end of .div_stat  -->
    
	<!-- start of 생산보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 생산보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 라인별 생산</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:200px;">구분</th>
                        <th scope="col" style="width:15%;">목표</th>
                        <th scope="col" style="width:15%;">생산</th>
                        <th scope="col" style="width:15%;">달성율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT 
                                mmg_idx
                                , mmg_name AS mmg_name
                                , depth
                                , mmg_left
                                , output_sum
                                , output_defect
                            FROM (	
                                (
                            
                                SELECT 
                                    0 AS mmg_idx
                                    , 'total' AS mmg_name
                                    , 0 AS depth
                                    , 0 AS mmg_left
                                    , SUM( dta_value ) AS output_sum
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                FROM {$g5['data_output_sum_table']}
                                WHERE dta_date >= '".$st_date."'
                                    AND dta_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                            
                                )
                            UNION ALL
                                (
                            
                                SELECT 
                                    mmg_idx
                                    , GROUP_CONCAT(name) AS mmg_name
                                    , GROUP_CONCAT(cast(depth as char)) AS depth
                                    , mmg_left
                                    , SUM(output_sum) AS output_sum
                                    , SUM(output_defect) AS output_defect
                                FROM (	(
                                        SELECT mmg.mmg_idx
                                            , CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
                                            , (COUNT(parent.mmg_idx) - 1) AS depth
                                            , mmg.mmg_left
                                            , 0 output_sum
                                            , 0 output_defect
                                        FROM {$g5['mms_group_table']} AS mmg,
                                                {$g5['mms_group_table']} AS parent
                                        WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
                                            AND mmg.com_idx='".$com_idx."'
                                            AND parent.com_idx='".$com_idx."'
                                            AND mmg.mmg_status NOT IN ('trash','delete')
                                            AND parent.mmg_status NOT IN ('trash','delete')
                                            {$sql_mmg_parent}
                                        GROUP BY mmg.mmg_idx
                                        ORDER BY mmg.mmg_left
                                        )
                                    UNION ALL
                                        (
                                        SELECT parent.mmg_idx
                                            , NULL name
                                            , NULL depth
                                            , parent.mmg_left
                                            , SUM(output_sum) AS output_sum
                                            , SUM(output_defect) AS output_defect
                                        FROM {$g5['mms_group_table']} AS mmg, 
                                            {$g5['mms_group_table']} AS parent,
                                            (
                                            SELECT 
                                                mmg_idx AS mmg_idx_group
                                                , SUM( dta_value ) AS output_sum
                                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                            FROM {$g5['data_output_sum_table']}
                                            WHERE dta_date >= '".$st_date."'
                                                AND dta_date <= '".$en_date."'
                                                AND com_idx='".$com_idx."'
                                                {$sql_mmses}
                                            GROUP BY mmg_idx
                                            ) AS db1
                                        WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
                                            AND mmg.com_idx='".$com_idx."'
                                            AND parent.com_idx='".$com_idx."'
                                            AND mmg.mmg_status NOT IN ('trash','delete')
                                            AND parent.mmg_status NOT IN ('trash','delete')
                                            AND mmg.mmg_idx = mmg_idx_group
                                            {$sql_mmg_parent}
                                        GROUP BY parent.mmg_idx
                                        ORDER BY parent.mmg_left
                            
                                        ) 
                                    ) db2
                                GROUP BY mmg_idx
                                ORDER BY mmg_left
                            
                                ) 
                            ) db3
                            ORDER BY mmg_left
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // $row['output_sum'] = 78586;
                        // $row['output_sum'] += 1300;

                        //-- 들여쓰기
                        $row['indent'] = $row['depth']*20;
                        
                        // 합계인 경우
                        if($row['mmg_name'] == 'total') {
                            $row['mmg_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = $target['total'];
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            $amount_max = ($row['target'] && $row['output_sum']>$row['target']) ? $row['output_sum'] : $row['target'];
                            $amount_output = $row['output_sum'];
                            $amount_defect = $row['output_defect'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            // echo $row['mmg_idx'].'=======';
                            // print_r2($mmg_down_idxs[$row['mmg_idx']]);
                            // echo '<br>---------<br>';
                            for($j=0;$j<sizeof($mmg_down_idxs[$row['mmg_idx']]);$j++) {
                                // echo $mmg_down_idxs[$row['mmg_idx']][$j].' > '.$target['mmg'][$mmg_down_idxs[$row['mmg_idx']][$j]].'<br>';
                                $row['target'] += $target['mmg'][$mmg_down_idxs[$row['mmg_idx']][$j]];
                            }
                            // echo '<br>---------<br>';
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate'] = ($row['target']) ? $row['output_sum'] / $row['target'] * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($amount_max && $row['output_sum']) {
                            $row['rate_output'] = ($amount_max && $row['target']) ? $row['output_sum'] / $amount_max * 100 : 0 ;
                            $row['graph_output'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_output'].'%;background:'.$row['rate_color'].';" height="8px" title="달성율: '.number_format($row['rate'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block;" height="2px" title="목표">';
                        }

                        echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" style="padding-left:'.(15+$row['indent']).'px;">'.$row['mmg_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_sum']).'</td><!-- 생산 -->
                                <td class="text_right" style="color:'.$row['rate_color'].';">'.number_format($row['rate'], 1).'%</td><!-- 달성율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_output'].$row['graph_target'].'</td>
                            </tr>
                            ';
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
            // 목표달성율, 불량율 입력
            $('#sum_target').text('<?=number_format(($amount_output/$target['total']*100),1)?>');
            $('#sum_defect').text('<?=number_format(($amount_defect/$amount_output*100),2)?>');
            </script>

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 생산</i></div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:12%;">목표</th>
                        <th scope="col" style="width:12%;">생산</th>
                        <th scope="col" style="width:12%;">정상</th>
                        <th scope="col" style="width:10%;">불량</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN mms_idx ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                                SELECT 
                                    mms_idx
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                FROM {$g5['data_output_sum_table']}
                                WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                                GROUP BY mms_idx
                                ORDER BY mms_idx
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal)
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['mms'][$row['item_name']];
                        }
                    }
                    // print_r2($target['mms']);
                    // print_r2($item_target);
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['mms'][$row['item_name']];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$g5['mms'][$row['item_name']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5"><a href="javascript:" mms_idx="'.$row['item_name'].'" class="link_mmd_product" st_date="'.$st_date.'" en_date="'.$en_date.'">'
                                    .number_format($row['output_total']).'</a></td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
                $(document).on('click','.link_mmd_product',function(e){
                    var this_mms_idx = $(this).attr('mms_idx');
                    var this_st_date = $(this).attr('st_date');
                    var this_en_date = $(this).attr('en_date');
                    var href = './kpi.hourly.php?mms_idx='+this_mms_idx+'&st_date='+this_st_date+'&en_date='+this_en_date;
                    winStatHour = window.open(href, "winStatHour", "left=100, top=100, width=460, height=600");
                    winStatHour.focus();
                });
            </script>
                

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">교대별 생산</i></div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:12%;">목표</th>
                        <th scope="col" style="width:12%;">생산</th>
                        <th scope="col" style="width:12%;">정상</th>
                        <th scope="col" style="width:10%;">불량</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',dta_shf_no) ELSE 'total' END) AS item_name
                                , mms_idx
                                , dta_shf_no
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , dta_shf_no
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                FROM {$g5['data_output_sum_table']}
                                WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                                GROUP BY mms_idx, dta_shf_no
                                ORDER BY mms_idx, dta_shf_no
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal), dta_shf_no
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['mms'][$row['item_name']];
                        }
                    }
                    // print_r2($target['mms']);
                    // print_r2($item_target);
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['mms_shift'][$row['mms_idx']][$row['dta_shf_no']];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // 교대명 (길이가 길면 잘라서 표현하고 popover나타남)
                        // $cur_len = 17;
                        // $row['mms_name'] = cut_str($g5['mms'][$row['mms_idx']]['mms_name'],$cur_len,'..');
                        $row['mms_name'] = $g5['mms'][$row['mms_idx']]['mms_name'];
                        $row['arr_str'] = preg_split("//u", $g5['mms'][$row['mms_idx']]['mms_name'], -1, PREG_SPLIT_NO_EMPTY);
                        $row['str_len'] = count($row['arr_str']);
                        // $row['mms_title'] = ($row['str_len'] >= $cur_len) ? ' title="'.$g5['mms'][$row['mms_idx']]['mms_name'].'"' : "";
                        $row['shf_name'] = ($row['dta_shf_no']) ? ' '.$row['dta_shf_no'].'교대': '';

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left"'.$row['mms_title'].'>'.$row['mms_name'].'<span style="color:#3ab4d2;">'.$row['shf_name'].'</span></td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 기종별 생산</i></div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:12%;">목표</th>
                        <th scope="col" style="width:12%;">생산</th>
                        <th scope="col" style="width:12%;">정상</th>
                        <th scope="col" style="width:10%;">불량</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    // Get the mmi_nos for each mms
                    $sql = "SELECT mms_idx, mmi_no, mmi_name
                            FROM g5_1_mms_item
                            WHERE mmi_status = 'ok'
                            GROUP BY mms_idx, mmi_no
                            ORDER BY mms_idx, mmi_no
                    ";
                    // echo $sql.'<br>';
                    $rs = sql_query($sql,1);
                    for($i=0;$row=sql_fetch_array($rs);$i++) {
                        // print_r3('설비: '.$row['mms_idx'].' - '.$row['mmi_no'].'--------------------------');
                        $mms_mmi[$row['mms_idx']][] = $row['mmi_no'];
                        $mmi_name[$row['mms_idx']][$row['mmi_no']] = $row['mmi_name'];
                    }
                    // print_r3($mms_mmi);
                    // print_r2($mmi_name);
                    
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',dta_mmi_no) ELSE 'total' END) AS item_name
                                , mms_idx
                                , dta_mmi_no
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , dta_mmi_no
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                FROM {$g5['data_output_sum_table']}
                                WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                                GROUP BY mms_idx, dta_mmi_no
                                ORDER BY mms_idx, dta_mmi_no
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal), dta_mmi_no
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['mms'][$row['item_name']];
                        }
                    }
                    // print_r2($target['mms']);
                    // print_r2($item_target);
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['mms_mmi'][$row['mms_idx']][$row['dta_mmi_no']];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // 기종명
                        // $cur_len = 17;
                        // $row['mms_name'] = cut_str($g5['mms'][$row['mms_idx']]['mms_name'],$cur_len,'..');
                        $row['mms_name'] = $g5['mms'][$row['mms_idx']]['mms_name'];
                        $row['arr_str'] = preg_split("//u", $g5['mms'][$row['mms_idx']]['mms_name'], -1, PREG_SPLIT_NO_EMPTY);
                        $row['str_len'] = count($row['arr_str']);
                        // $row['mms_title'] = ($row['str_len'] >= $cur_len) ? ' title="'.$g5['mms'][$row['mms_idx']]['mms_name'].'"' : "";
                        $row['mmi_no'] = ($row['dta_mmi_no']) ? ' <span style="color:#3ab4d2;">'.$row['dta_mmi_no'].'</span>': '';
                        $row['mmi_name'] = ($row['dta_mmi_no']) ? ' <div style="color:#818181;font-size:0.7em;margin-top:-7px;">'.$mmi_name[$row['mms_idx']][$row['dta_mmi_no']].'</div>': '';

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['mms_title'].'>'.$row['mms_name'].$row['mmi_no'].$row['mmi_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 생산</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:100px;">구분</th>
                        <th scope="col" style="width:15%">목표</th>
                        <th scope="col" style="width:15%">생산</th>
                        <th scope="col" style="width:15%;">정상</th>
                        <th scope="col" style="width:15%;">불량</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                 
                                SELECT 
                                    ymd_date
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        dta_date AS ymd_date
                                        , SUM(dta_value) AS output_total
                                        , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    FROM {$g5['data_output_sum_table']}
                                    WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_date
                                    ORDER BY ymd_date
                                    )
                                ) AS db_table
                                GROUP BY ymd_date

                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['date'][preg_replace("/-/","",$row['item_name'])];
                        }
                    }
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['date'][preg_replace("/-/","",$row['item_name'])];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 생산</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:100px;">구분</th>
                        <th scope="col" style="width:15%">목표</th>
                        <th scope="col" style="width:15%">생산</th>
                        <th scope="col" style="width:15%;">정상</th>
                        <th scope="col" style="width:15%;">불량</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                 
                                SELECT 
                                    ymd_week
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(dta_date,4) AS ymd_week
                                        , SUM(dta_value) AS output_total
                                        , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    FROM {$g5['data_output_sum_table']}
                                    WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_week
                                    ORDER BY ymd_week
                                    )
                                ) AS db_table
                                GROUP BY ymd_week

                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $week = substr($row['item_name'],-2);
                            $item_target[] = $target['week'][$week];
                        }
                    }
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['year'] = substr($row['item_name'],0,4);
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['week'][$row['week']];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            // 주차 첫날자
                            // $row['week_start_date'] = $target['week_day'][$row['week']];
                            // echo $row['year'].'/'.$row['week'].'<br>'; // 2020, 49주차
                            // 월요일이므로 -86400, 하루를 뺴야 됩니다.
                            // echo date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400).'<br>';
                            $row['week_start_date'] = date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400);

                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['week_start_date'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 생산</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:100px;">구분</th>
                        <th scope="col" style="width:15%">목표</th>
                        <th scope="col" style="width:15%">생산</th>
                        <th scope="col" style="width:15%;">정상</th>
                        <th scope="col" style="width:15%;">불량</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                 
                                SELECT 
                                    ymd_month
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        substring( CAST(dta_date AS CHAR),1,7) AS ymd_month
                                        , SUM(dta_value) AS output_total
                                        , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    FROM {$g5['data_output_sum_table']}
                                    WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_month
                                    ORDER BY ymd_month
                                    )
                                ) AS db_table
                                GROUP BY ymd_month

                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['month'][preg_replace("/-/","",$row['item_name'])];
                        }
                    }
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['month'][preg_replace("/-/","",$row['item_name'])];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 연도별 생산</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:100px;">구분</th>
                        <th scope="col" style="width:15%">목표</th>
                        <th scope="col" style="width:15%">생산</th>
                        <th scope="col" style="width:15%;">정상</th>
                        <th scope="col" style="width:15%;">불량</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_year ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                 
                                SELECT 
                                    ymd_year
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,4) AS ymd_year
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        substring( CAST(dta_date AS CHAR),1,4) AS ymd_year
                                        , SUM(dta_value) AS output_total
                                        , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    FROM {$g5['data_output_sum_table']}
                                    WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_year
                                    ORDER BY ymd_year
                                    )
                                ) AS db_table
                                GROUP BY ymd_year

                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 전체 목표가 아니고 날짜별 목표중에서 최고값 추출
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        // 합계인 경우는 건너뛰고
                        if($row['item_name'] != 'total') {
                            $item_target[] = $target['year'][$row['item_name']];
                        }
                    }
                    // echo max($item_target).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_target); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $amount_max = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $amount_max = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                            $row['target'] = $target['year'][preg_replace("/-/","",$row['item_name'])];
                        }
                        // echo $amount_max.'<br>';

                        // 목표 대비 달성율
                        $row['rate_total'] = ($row['target']) ? $row['output_total'] / $row['target'] * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_good'] = ($row['target']) ? $row['output_good'] / $row['target'] * 100 : 0 ;
                        $row['rate_good_color'] = '#d1c594';
                        $row['rate_good_color'] = ($row['rate_good']>=80) ? '#72ddf5' : $row['rate_good_color'];
                        $row['rate_good_color'] = ($row['rate_good']>=100) ? '#ff9f64' : $row['rate_good_color'];
                        $row['rate_defect'] = ($row['target']) ? $row['output_defect'] / $row['target'] * 100 : 0 ;
                        $row['rate_defect_color'] = '#ff7029';
                        $row['rate_defect_color'] = ($row['rate_defect']>=80) ? '#ff2929' : $row['rate_defect_color'];
                        $row['rate_defect_color'] = ($row['rate_defect']>=100) ? '#ff0d0d' : $row['rate_defect_color'];

                        // 그래프
                        if($amount_max && $row['output_total']) {
                            $row['rate_total_percent'] = ($amount_max && $row['target']) ? $row['output_total'] / $amount_max * 100 : 0 ;
                            $row['graph_total'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="./img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="./img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="./img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_right -->
    </div><!-- .div_wrapper -->

	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 알람보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 알람보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 구분타입별 알람</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:9%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:140px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT
                                trm_idx
                                , GROUP_CONCAT(name) AS item_name
                                , GROUP_CONCAT(cast(depth as char)) AS depth
                                , trm_left
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM (	(
                            
                                    SELECT term.trm_idx AS trm_idx
                                        , CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
                                        , (COUNT(parent.trm_idx) - 1) AS depth
                                        , term.trm_left
                                        , 0 arm_count_sum
                                        , 0 arm_alarm_sum
                                        , 0 arm_predict_sum
                                    FROM g5_5_term AS term,
                                            g5_5_term AS parent
                                    WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                        AND term.trm_taxonomy = 'category'
                                        AND parent.trm_taxonomy = 'category'
                                        AND term.trm_status = 'ok'
                                        AND parent.trm_status = 'ok'
                                    GROUP BY term.trm_idx
                                    ORDER BY term.trm_left
                            
                                    )
                                UNION ALL
                                    (
                            
                                    SELECT
                                        trm_idx_category AS trm_idx
                                        , NULL AS name
                                        , NULL AS depth
                                        , NULL AS trm_left
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        {$sql_mmses1}
                                    GROUP BY trm_idx_category
                                    ORDER BY trm_idx_category
                            
                                    ) 
                                ) AS db1
                            GROUP BY trm_idx
                            ORDER BY arm_alarm_sum DESC, trm_left
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        $item_max[] = $row['arm_alarm_sum'];
                        $item_sum += $row['arm_alarm_sum'];
                    }
                    // echo max($item_max).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_max); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $item_sum = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $item_sum = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_alarm_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_alarm_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_alarm_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        $row['item_name'] = $row['item_name'] ?: '구분없음';

                        // First line total skip, start from second line.
                        if($i>=0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_alarm_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 알람</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
                                , mms_idx
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                    , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                FROM g5_1_alarm
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                                GROUP BY mms_idx
                                ORDER BY mms_idx
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal)
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_alarm_sum'];
                            $item_sum += $row['arm_alarm_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $arm_count_sum = $row['arm_count_sum'];
                            $arm_alarm_sum = $row['arm_alarm_sum'];
                            $arm_predict_sum = $row['arm_predict_sum'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }

                        // 비율
                        $row['rate_total'] = ($item_sum) ? $row['arm_alarm_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_alarm'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_predict'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;

                        // 그래프
                        if($item_sum && $row['arm_alarm_sum']) {
                            // $row['rate_percent'] = $row['arm_alarm_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_alarm_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="비율:'.number_format($row['rate_total'],1).'%">';
                        }

                        // item_name
                        $row['item_name'] = ($row['item_name']!='합계') ? $g5['mms'][$row['item_name']]['mms_name'] : $row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_alarm_sum']).'</td><!-- 발생수 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
            // 알람발생, 예지발생
            $('#sum_alarm').text('<?=number_format($arm_alarm_sum)?>');
            $('#sum_predict').text('<?=number_format($arm_predict_sum)?>');
            </script>


            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 알람 발생횟수</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">설비명</th>
                        <th scope="col">알람내용</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:15%;">비율</th>
                        <th scope="col" style="width:100px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(arm_cod_code) ELSE 'total' END) AS item_name
                                , mms_idx
                                , arm_cod_code
                                , SUM(arm_count_sum) AS arm_count_sum
                                , cod_name
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , arm_cod_code
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , cod_name
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_code = arm.arm_cod_code AND cod.mms_idx = arm.mms_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND arm_cod_type IN ('a')
                                        {$sql_mmses1}
                                GROUP BY arm.mms_idx, arm_cod_code
                                ORDER BY arm_count_sum DESC
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, arm_count_sum DESC
                            LIMIT 30
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // code 
                        $row['code_title'] = ' title="'.$row['item_name'].'"';

                        // href
                        $row['href'] = './alarm_data_list.php?st_date='.$st_date.'&st_time=00:00:00&en_date='.$en_date.'&en_time=23:59:59&ser_mms_idx='.$row['mms_idx'].'&sfl=cod_code&stx='.$row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['code_title'].'>'.$g5['mms'][$row['mms_idx']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_left"><a href="'.$row['href'].'" target="_blank">'.$row['cod_name'].'</a></td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 에러수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 알람</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_date
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,10) AS ymd_date
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                            {$sql_mmses}
                                    GROUP BY ymd_date
                                    ORDER BY ymd_date
                                    )
                                ) AS db_table
                                GROUP BY ymd_date
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_alarm_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 알람</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">전체</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_week
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_week
                                    )
                                UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(arm_reg_dt,4) AS ymd_week
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                            {$sql_mmses}
                                    GROUP BY ymd_week
                                    ORDER BY ymd_week
                                    )
                                ) AS db_table
                                GROUP BY ymd_week
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name                    
                            
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['year'] = substr($row['item_name'],0,4);
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        
                        // First line total skip, start from second line.
                        if($i>0) {
                            // item_name
                            $row['week_start_date'] = date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400);
                            $row['item_name'] = $row['week_start_date'];

                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_alarm_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 알람</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_month
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,7) AS ymd_month
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_month
                                    ORDER BY ymd_month
                                    )
                                ) AS db_table
                                GROUP BY ymd_month
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                                    
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['week_start_date'] = $target['week_day'][$row['week']];    // 주차 첫날자
                        // $row['item_name'] = $row['week_start_date'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_alarm_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_right -->
    </div><!-- .div_wrapper -->


	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 설비이상보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 설비이상보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 구분타입별 비가동</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:9%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:140px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT
                                trm_idx
                                , GROUP_CONCAT(name) AS item_name
                                , GROUP_CONCAT(cast(depth as char)) AS depth
                                , trm_left
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM (	(
                            
                                    SELECT term.trm_idx AS trm_idx
                                        , CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
                                        , (COUNT(parent.trm_idx) - 1) AS depth
                                        , term.trm_left
                                        , 0 arm_count_sum
                                        , 0 arm_alarm_sum
                                        , 0 arm_predict_sum
                                    FROM g5_5_term AS term,
                                            g5_5_term AS parent
                                    WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                        AND term.trm_taxonomy = 'category'
                                        AND parent.trm_taxonomy = 'category'
                                        AND term.trm_status = 'ok'
                                        AND parent.trm_status = 'ok'
                                    GROUP BY term.trm_idx
                                    ORDER BY term.trm_left
                            
                                    )
                                UNION ALL
                                    (
                            
                                    SELECT
                                        trm_idx_category AS trm_idx
                                        , NULL AS name
                                        , NULL AS depth
                                        , NULL AS trm_left
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_offline_yn='1'
                                        {$sql_mmses1}
                                    GROUP BY trm_idx_category
                                    ORDER BY trm_idx_category
                            
                                    ) 
                                ) AS db1
                            GROUP BY trm_idx
                            ORDER BY arm_count_sum DESC, trm_left
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        $item_max[] = $row['arm_count_sum'];
                        $item_sum += $row['arm_count_sum'];
                    }
                    // echo max($item_max).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_max); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $item_sum = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $item_sum = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        $row['item_name'] = $row['item_name'] ?: '구분없음';

                        // First line total skip, start from second line.
                        if($i>=0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 비가동</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
                                , mms_idx
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                    , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND cod_offline_yn = '1'
                                    {$sql_mmses1}
                                GROUP BY mms_idx
                                ORDER BY mms_idx
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal)
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $arm_count_sum = $row['arm_count_sum'];
                            $arm_alarm_sum = $row['arm_alarm_sum'];
                            $arm_predict_sum = $row['arm_predict_sum'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }

                        // 비율
                        $row['rate_total'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_alarm'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_predict'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="비율:'.number_format($row['rate_total'],1).'%">';
                        }

                        // item_name
                        $row['item_name'] = ($row['item_name']!='합계') ? $g5['mms'][$row['item_name']]['mms_name'] : $row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate_total'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
            // 알람발생, 예지발생
            $('#sum_alarm').text('<?=number_format($arm_alarm_sum)?>');
            $('#sum_predict').text('<?=number_format($arm_predict_sum)?>');
            </script>


            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 비가동 발생횟수</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">설비명</th>
                        <th scope="col">내용</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:80px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(arm_cod_code) ELSE 'total' END) AS item_name
                                , mms_idx
                                , arm_cod_code
                                , SUM(arm_count_sum) AS arm_count_sum
                                , cod_name
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , arm_cod_code
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , cod_name
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    -- LEFT JOIN g5_1_code AS cod ON cod.cod_code = arm.arm_cod_code AND cod.mms_idx = arm.mms_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND arm_cod_type IN ('p','p2')
                                    AND cod_offline_yn = '1'
                                        {$sql_mmses1}
                                GROUP BY arm.mms_idx, arm_cod_code
                                ORDER BY arm_count_sum DESC
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, arm_count_sum DESC
                            LIMIT 30
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // code 
                        $row['code_title'] = ' title="'.$row['item_name'].'"';

                        // href
                        $row['href'] = './pre_data_list.php?st_date='.$st_date.'&st_time=00:00:00&en_date='.$en_date.'&en_time=23:59:59&ser_mms_idx='.$row['mms_idx'].'&sfl=cod_code&stx='.$row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['code_title'].'>'.$g5['mms'][$row['mms_idx']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_left"><a href="'.$row['href'].'" target="_blank">'.$row['cod_name'].'</a></td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 에러수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 비가동</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_date
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,10) AS ymd_date
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_offline_yn ='1'
                                            {$sql_mmses1}
                                    GROUP BY ymd_date
                                    ORDER BY ymd_date
                                    )
                                ) AS db_table
                                GROUP BY ymd_date
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 비가동</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_week
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_week
                                    )
                                UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(arm_reg_dt,4) AS ymd_week
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_offline_yn = '1'
                                            {$sql_mmses1}
                                    GROUP BY ymd_week
                                    ORDER BY ymd_week
                                    )
                                ) AS db_table
                                GROUP BY ymd_week
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name                    
                            
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['year'] = substr($row['item_name'],0,4);
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        
                        // First line total skip, start from second line.
                        if($i>0) {
                            // item_name
                            $row['week_start_date'] = date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400);
                            $row['item_name'] = $row['week_start_date'];

                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 비가동</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_month
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,7) AS ymd_month
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_offline_yn = '1'
                                        {$sql_mmses1}
                                    GROUP BY ymd_month
                                    ORDER BY ymd_month
                                    )
                                ) AS db_table
                                GROUP BY ymd_month
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                                    
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['week_start_date'] = $target['week_day'][$row['week']];    // 주차 첫날자
                        // $row['item_name'] = $row['week_start_date'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_right -->
    </div><!-- .div_wrapper -->    

	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 예지  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 예지보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 구분타입별 예지</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:9%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:140px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT
                                trm_idx
                                , GROUP_CONCAT(name) AS item_name
                                , GROUP_CONCAT(cast(depth as char)) AS depth
                                , trm_left
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM (	(
                            
                                    SELECT term.trm_idx AS trm_idx
                                        , CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
                                        , (COUNT(parent.trm_idx) - 1) AS depth
                                        , term.trm_left
                                        , 0 arm_count_sum
                                        , 0 arm_alarm_sum
                                        , 0 arm_predict_sum
                                    FROM g5_5_term AS term,
                                            g5_5_term AS parent
                                    WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                        AND term.trm_taxonomy = 'category'
                                        AND parent.trm_taxonomy = 'category'
                                        AND term.trm_status = 'ok'
                                        AND parent.trm_status = 'ok'
                                    GROUP BY term.trm_idx
                                    ORDER BY term.trm_left
                            
                                    )
                                UNION ALL
                                    (
                            
                                    SELECT
                                        trm_idx_category AS trm_idx
                                        , NULL AS name
                                        , NULL AS depth
                                        , NULL AS trm_left
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        {$sql_mmses1}
                                    GROUP BY trm_idx_category
                                    ORDER BY trm_idx_category
                            
                                    ) 
                                ) AS db1
                            GROUP BY trm_idx
                            ORDER BY arm_predict_sum DESC, trm_left
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        $item_max[] = $row['arm_predict_sum'];
                        $item_sum += $row['arm_predict_sum'];
                    }
                    // echo max($item_max).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_max); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $item_sum = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $item_sum = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_predict_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_predict_sum']) {
                            // $row['rate_percent'] = $row['arm_predict_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_predict_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        $row['item_name'] = $row['item_name'] ?: '구분없음';

                        // First line total skip, start from second line.
                        if($i>=0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 예지</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
                                , mms_idx
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                    , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                FROM g5_1_alarm
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND com_idx='".$com_idx."'
                                    {$sql_mmses}
                                GROUP BY mms_idx
                                ORDER BY mms_idx
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal)
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_predict_sum'];
                            $item_sum += $row['arm_predict_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $arm_count_sum = $row['arm_count_sum'];
                            $arm_alarm_sum = $row['arm_alarm_sum'];
                            $arm_predict_sum = $row['arm_predict_sum'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }

                        // 비율
                        $row['rate_total'] = ($item_sum) ? $row['arm_predict_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_alarm'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_predict'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;

                        // 그래프
                        if($item_sum && $row['arm_predict_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_predict_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="비율:'.number_format($row['rate_total'],1).'%">';
                        }

                        // item_name
                        $row['item_name'] = ($row['item_name']!='합계') ? $g5['mms'][$row['item_name']]['mms_name'] : $row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
            // 알람발생, 예지발생
            $('#sum_alarm').text('<?=number_format($arm_alarm_sum)?>');
            $('#sum_predict').text('<?=number_format($arm_predict_sum)?>');
            </script>


            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 예지 발생횟수</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">설비명</th>
                        <th scope="col">예지내용</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:15%;">비율</th>
                        <th scope="col" style="width:140px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(arm_cod_code) ELSE 'total' END) AS item_name
                                , mms_idx
                                , arm_cod_code
                                , SUM(arm_count_sum) AS arm_count_sum
                                , cod_name
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , arm_cod_code
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , cod_name
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_code = arm.arm_cod_code AND cod.mms_idx = arm.mms_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND arm_cod_type IN ('p','p2')
                                        {$sql_mmses1}
                                GROUP BY arm.mms_idx, arm_cod_code
                                ORDER BY arm_count_sum DESC
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, arm_count_sum DESC
                            LIMIT 30
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // code 
                        $row['code_title'] = ' title="'.$row['item_name'].'"';

                        // href
                        $row['href'] = './pre_data_list.php?st_date='.$st_date.'&st_time=00:00:00&en_date='.$en_date.'&en_time=23:59:59&ser_mms_idx='.$row['mms_idx'].'&sfl=cod_code&stx='.$row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['code_title'].'>'.$g5['mms'][$row['mms_idx']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_left"><a href="'.$row['href'].'" target="_blank">'.$row['cod_name'].'</a></td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 에러수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 예지</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_date
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,10) AS ymd_date
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                            {$sql_mmses}
                                    GROUP BY ymd_date
                                    ORDER BY ymd_date
                                    )
                                ) AS db_table
                                GROUP BY ymd_date
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 예지</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_week
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_week
                                    )
                                UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(arm_reg_dt,4) AS ymd_week
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                            {$sql_mmses}
                                    GROUP BY ymd_week
                                    ORDER BY ymd_week
                                    )
                                ) AS db_table
                                GROUP BY ymd_week
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name                    
                            
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['year'] = substr($row['item_name'],0,4);
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        
                        // First line total skip, start from second line.
                        if($i>0) {
                            // item_name
                            $row['week_start_date'] = date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400);
                            $row['item_name'] = $row['week_start_date'];

                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 예지</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_month
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,7) AS ymd_month
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND com_idx='".$com_idx."'
                                        {$sql_mmses}
                                    GROUP BY ymd_month
                                    ORDER BY ymd_month
                                    )
                                ) AS db_table
                                GROUP BY ymd_month
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                                    
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['week_start_date'] = $target['week_day'][$row['week']];    // 주차 첫날자
                        // $row['item_name'] = $row['week_start_date'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_right -->
    </div><!-- .div_wrapper -->

	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 품질보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 품질(불량)보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 구분타입별 품질</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:9%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:140px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT
                                trm_idx
                                , GROUP_CONCAT(name) AS item_name
                                , GROUP_CONCAT(cast(depth as char)) AS depth
                                , trm_left
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM (	(
                            
                                    SELECT term.trm_idx AS trm_idx
                                        , CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
                                        , (COUNT(parent.trm_idx) - 1) AS depth
                                        , term.trm_left
                                        , 0 arm_count_sum
                                        , 0 arm_alarm_sum
                                        , 0 arm_predict_sum
                                    FROM g5_5_term AS term,
                                            g5_5_term AS parent
                                    WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                        AND term.trm_taxonomy = 'category'
                                        AND parent.trm_taxonomy = 'category'
                                        AND term.trm_status = 'ok'
                                        AND parent.trm_status = 'ok'
                                    GROUP BY term.trm_idx
                                    ORDER BY term.trm_left
                            
                                    )
                                UNION ALL
                                    (
                            
                                    SELECT
                                        trm_idx_category AS trm_idx
                                        , NULL AS name
                                        , NULL AS depth
                                        , NULL AS trm_left
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_quality_yn='1'
                                        {$sql_mmses1}
                                    GROUP BY trm_idx_category
                                    ORDER BY trm_idx_category
                            
                                    ) 
                                ) AS db1
                            GROUP BY trm_idx
                            ORDER BY arm_count_sum DESC, trm_left
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        $item_max[] = $row['arm_count_sum'];
                        $item_sum += $row['arm_count_sum'];
                    }
                    // echo max($item_max).'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $row['target'] = max($item_max); // 그래프 표현을 위해서 목표값 임시 변경
                            // max 추출 (목표 or 생산), 맨 처음 시작될 때 추출해야 함
                            // $item_sum = ($row['target'] && $row['output_total']>$row['target']) ? $row['output_total'] : $row['target'];
                            $item_sum = ($row['target'] && $row['output_max']>$row['target']) ? $row['output_max'] : $row['target'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        $row['item_name'] = $row['item_name'] ?: '구분없음';

                        // First line total skip, start from second line.
                        if($i>=0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 품질</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:12%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
                                , mms_idx
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                    , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND cod_quality_yn = '1'
                                    {$sql_mmses1}
                                GROUP BY mms_idx
                                ORDER BY mms_idx
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal)
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $arm_count_sum = $row['arm_count_sum'];
                            $arm_alarm_sum = $row['arm_alarm_sum'];
                            $arm_predict_sum = $row['arm_predict_sum'];
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }

                        // 비율
                        $row['rate_total'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_total_color'] = '#d1c594';
                        $row['rate_total_color'] = ($row['rate_total']>=80) ? '#72ddf5' : $row['rate_total_color'];
                        $row['rate_total_color'] = ($row['rate_total']>=100) ? '#ff9f64' : $row['rate_total_color'];
                        $row['rate_alarm'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_predict'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="비율:'.number_format($row['rate_total'],1).'%">';
                        }

                        // item_name
                        $row['item_name'] = ($row['item_name']!='합계') ? $g5['mms'][$row['item_name']]['mms_name'] : $row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate_total'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <script>
            // 알람발생, 예지발생
            $('#sum_alarm').text('<?=number_format($arm_alarm_sum)?>');
            $('#sum_predict').text('<?=number_format($arm_predict_sum)?>');
            </script>


            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 품질 발생횟수</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">설비명</th>
                        <th scope="col">내용</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:80px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(arm_cod_code) ELSE 'total' END) AS item_name
                                , mms_idx
                                , arm_cod_code
                                , SUM(arm_count_sum) AS arm_count_sum
                                , cod_name
                            FROM
                            (
                                SELECT
                                    arm.mms_idx AS mms_idx
                                    , arm_cod_code
                                    , COUNT(arm_idx) AS arm_count_sum
                                    , cod_name
                                FROM g5_1_alarm AS arm
                                    LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    -- LEFT JOIN g5_1_code AS cod ON cod.cod_code = arm.arm_cod_code AND cod.mms_idx = arm.mms_idx
                                WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                    AND arm.com_idx='".$com_idx."'
                                    AND arm_cod_type IN ('p','p2')
                                    AND cod_quality_yn = '1'
                                        {$sql_mmses1}
                                GROUP BY arm.mms_idx, arm_cod_code
                                ORDER BY arm_count_sum DESC
                            
                            ) AS db3, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, arm_count_sum DESC
                            LIMIT 30
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // code 
                        $row['code_title'] = ' title="'.$row['item_name'].'"';

                        // href
                        $row['href'] = './pre_data_list.php?st_date='.$st_date.'&st_time=00:00:00&en_date='.$en_date.'&en_time=23:59:59&ser_mms_idx='.$row['mms_idx'].'&sfl=cod_code&stx='.$row['item_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['code_title'].'>'.$g5['mms'][$row['mms_idx']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_left"><a href="'.$row['href'].'" target="_blank">'.$row['cod_name'].'</a></td>
                                <td class="text_right pr_5">'.number_format($row['arm_count_sum']).'</td><!-- 에러수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
        
        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 품질</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_date
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,10) AS ymd_date
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_quality_yn ='1'
                                            {$sql_mmses1}
                                    GROUP BY ymd_date
                                    ORDER BY ymd_date
                                    )
                                ) AS db_table
                                GROUP BY ymd_date
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['item_name'] = $row['mms_name'].$row['shf_name'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 품질</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_week
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_week
                                    )
                                UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(arm_reg_dt,4) AS ymd_week
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_quality_yn = '1'
                                            {$sql_mmses1}
                                    GROUP BY ymd_week
                                    ORDER BY ymd_week
                                    )
                                ) AS db_table
                                GROUP BY ymd_week
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name                    
                            
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['year'] = substr($row['item_name'],0,4);
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        
                        // First line total skip, start from second line.
                        if($i>0) {
                            // item_name
                            $row['week_start_date'] = date('Y-m-d', strtotime($row['year'].'W'.$row['week'])-86400);
                            $row['item_name'] = $row['week_start_date'];

                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 품질</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:10%">발생수</th>
                        <th scope="col" style="width:10%;">비율</th>
                        <th scope="col" style="width:150px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(arm_count_sum) AS arm_count_sum
                                , SUM(arm_alarm_sum) AS arm_alarm_sum
                                , SUM(arm_predict_sum) AS arm_predict_sum
                            FROM
                            (
                                SELECT 
                                    ymd_month
                                    , SUM(arm_count_sum) AS arm_count_sum
                                    , SUM(arm_alarm_sum) AS arm_alarm_sum
                                    , SUM(arm_predict_sum) AS arm_predict_sum
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS arm_count_sum
                                        , 0 AS arm_alarm_sum
                                        , 0 AS arm_predict_sum
                                    FROM g5_5_ymd AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT
                                        substring( CAST(arm_reg_dt AS CHAR),1,7) AS ymd_month
                                        , COUNT(arm_idx) AS arm_count_sum
                                        , SUM( IF(arm_cod_type='a',1,0) ) AS arm_alarm_sum
                                        , SUM( IF(arm_cod_type IN ('p','p2'),1,0) ) AS arm_predict_sum
                                    FROM g5_1_alarm AS arm
                                        LEFT JOIN g5_1_code AS cod ON cod.cod_idx = arm.cod_idx
                                    WHERE arm_reg_dt >= '".$st_date." 00:00:00' AND arm_reg_dt <= '".$en_date." 23:59:59'
                                        AND arm.com_idx='".$com_idx."'
                                        AND cod_quality_yn = '1'
                                        {$sql_mmses1}
                                    GROUP BY ymd_month
                                    ORDER BY ymd_month
                                    )
                                ) AS db_table
                                GROUP BY ymd_month
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, item_name
                                    
                    ";
                    // echo $sql;
                    $result = sql_query($sql,1);

                    // 최고값 추출
                    $item_max = array();
                    $item_sum = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);
                        if($row['item_name'] != 'total') {
                            $item_max[] = $row['arm_count_sum'];
                            $item_sum += $row['arm_count_sum'];
                        }
                    }
                    // echo max($item_max).'<br>';
                    // echo $item_sum.'<br>';

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        // print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                        }
                        else {
                            $row['week'] = substr($row['item_name'],-2);
                            $row['tr_class'] = 'tr_stat_normal';
                        }
                        // echo $item_sum.'<br>';

                        // 비율
                        $row['rate'] = ($item_sum) ? $row['arm_count_sum'] / $item_sum * 100 : 0 ;
                        $row['rate_color'] = '#d1c594';
                        $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                        $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                        // 그래프
                        if($item_sum && $row['arm_count_sum']) {
                            // $row['rate_percent'] = $row['arm_count_sum'] / max($item_max) * 100;
                            $row['rate_percent'] = $row['arm_count_sum'] / $item_sum * 100;
                            $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                        }

                        // item_name
                        // $row['week_start_date'] = $target['week_day'][$row['week']];    // 주차 첫날자
                        // $row['item_name'] = $row['week_start_date'];

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['arm_predict_sum']).'</td><!-- 발생수 -->
                                <td class="text_right pr_5">'.number_format($row['rate'],1).'%</td><!-- 비율 -->
                                <td class="td_graph text_left pl_0">'.$row['graph'].'</td>
                            </tr>
                            ';
                        }
                    
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->

        </div><!-- .div_right -->
    </div><!-- .div_wrapper -->


    
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 정비 및 재고  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 정비 및 재고</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">계획(예방) 정비</i>
                <a href="<?=G5_BBS_URL?>/board.php?bo_table=plan" target="_parent" class="more">더보기</a>
            </div>
            <div class="div_info_body">
                <?php
                // 점검기한을 D-10 형태로 표현해야 해서 변경
                // echo latest10('theme/kpi10', 'plan', 10, 23);
                ?>
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:30%">구분</th>
                        <th scope="col">제목</th>
                        <th scope="col" style="width:20%;">정비일</th>
                        <th scope="col" style="width:20%">점검기한</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT *
                            FROM g5_write_plan
                            WHERE wr_is_comment = 0
                                AND wr_1 = '".$com['com_idx']."'
                                {$sql_mmses2}
                            ORDER BY wr_num
                            LIMIT 5
                    ";
                    // echo $sql.'<br>';
                    $rs = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($rs); $i++) {
                        // print_r2($row);
                        // wr_9 serialized 추출
                        $row['sried'] = get_serialized($row['wr_9']);
                        // print_r2($row['sried']);

                        // 점검기한 계산
                        $row['date_diff'] = date_diff(new DateTime($row['wr_3']), new DateTime(date("Y-m-d",G5_SERVER_TIME)));
                        $row['wr_date_diff'] = $row['date_diff']->days;
                        $row['wr_date_diff_prefix'] = $row['date_diff']->invert ? 'D-' : 'D+';
                        // Color awarness from one month before. 
                        $row['wr_date_diff_color'] = ($row['date_diff']->invert && $row['date_diff']->days<30) ? ' style="color:darkorange;"' : '';
                        // $row['wr_date_diff'] = (($row['date_diff']->invert)*-1)*$row['date_diff']->days;
                        // print_r2($row['date_diff']);

                        echo '
                        <tr class="'.$row['tr_class'].'">
                            <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                            <td class="">'.$row['wr_subject'].'</td><!-- 제목 -->
                            <td class="text_center">'.$row['wr_3'].'</td><!-- 정비일 -->
                            <td class="text_center" '.$row['wr_date_diff_color'].'>'.$row['wr_date_diff_prefix'].$row['wr_date_diff'].'</td><!-- 점검기한 -->
                        </tr>
                        ';
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>

                <?php
                // 오늘 ~ +10일
                $tmp_write_table = $g5['write_prefix'].'plan'; // 게시판 테이블 전체이름
                $sql = "SELECT COUNT(wr_id) AS plan_cnt
                        FROM {$tmp_write_table}
                        WHERE wr_is_comment = 0
                            AND wr_1 = '".$com['com_idx']."'
                            {$sql_mmses2}
                            AND wr_3 != ''
                            AND wr_3 >= '".G5_TIME_YMD."'
                            AND wr_3 < DATE_ADD('".G5_TIME_YMDHIS."' , INTERVAL +10 DAY)
                        ORDER BY wr_num
                ";
                // echo $sql.'<br>';
                $plan = sql_fetch($sql,1);
                // echo $plan['plan_cnt'].'<br>';
                ?>
            </div><!-- .div_info_body -->
            <script>
            // 게획정비
            $('#sum_plan').text('<?=number_format($plan['plan_cnt'])?>');
            </script>

        
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">정비 이력</i>
                <a href="<?=G5_BBS_URL?>/board.php?bo_table=maintain" target="_parent" class="more">더보기</a>
            </div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:30%">구분</th>
                        <th scope="col">제목</th>
                        <th scope="col" style="width:20%;">정비일</th>
                        <th scope="col" style="width:20%">비용</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT *
                            FROM g5_write_maintain
                            WHERE wr_is_comment = 0
                                AND wr_1 = '".$com['com_idx']."'
                                {$sql_mmses2}
                                AND wr_3 != ''
                                AND wr_3 >= '".$st_date."'
                                AND wr_3 <= '".$en_date."'
                            ORDER BY wr_num
                    ";
                    // echo $sql.'<br>';
                    $rs = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($rs); $i++) {
                        // print_r2($row);
                        // wr_9 serialized 추출
                        $row['sried'] = get_serialized($row['wr_9']);
                        // print_r2($row['sried']);

                        // 비용
                        $wr_maintain_price += $row['wr_6'];

                        echo '
                        <tr class="'.$row['tr_class'].'">
                            <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                            <td class="">'.$row['wr_subject'].'</td><!-- 제목 -->
                            <td class="text_center">'.$row['wr_3'].'</td><!-- 정비일 -->
                            <td class="text_center text_right pr_10">'.number_format($row['wr_6']).'</td><!-- 비용 -->
                        </tr>
                        ';
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
                <?php
                $maintain_price = num_to_han($wr_maintain_price);
                // print_r2($maintain_price);
                ?>
                <script>
                // 정비비용
                $('#sum_maintain').text('<?=number_format($maintain_price[0],1)?>');
                $('#sum_maintain').closest('li').find('.unit').text('<?=$maintain_price[1]?>');
                </script>
            </div><!-- .div_info_body -->


        </div><!-- .div_left -->
        <div class="div_right">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 재고</i>
                <a href="<?=G5_BBS_URL?>/board.php?bo_table=parts" target="_parent" class="more">더보기</a>
            </div>
            <div class="div_info_body">
                <?php 
                // latest에서 불러오면 cache때문에 시차가 생김
                // echo latest10('theme/kpi20', 'parts', 10, 23,0);
                ?>
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:30%">구분</th>
                        <th scope="col">부품명</th>
                        <th scope="col" style="width:20%">수량</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT *
                            FROM g5_write_parts
                            WHERE wr_is_comment = 0
                                AND wr_1 = '".$com['com_idx']."'
                                {$sql_mmses2}
                            ORDER BY wr_num
                            LIMIT 5
                    ";
                    // echo $sql.'<br>';
                    $rs = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($rs); $i++) {
                        // print_r2($row);
                        // wr_9 serialized 추출
                        $row['sried'] = get_serialized($row['wr_9']);
                        // print_r2($row['sried']);
                        echo '
                        <tr class="'.$row['tr_class'].'">
                            <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                            <td class="">'.$row['wr_subject'].'</td><!-- 부품명 -->
                            <td class="text_center">'.$row['wr_4'].'</td><!-- 수량 -->
                        </tr>
                        ';
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>

            </div><!-- .div_info_body -->

        </div><!-- .div_r -->
    </div><!-- .div_wrapper -->



</div><!-- #repot_wrapper -->

<script>
$(function(e) {
    $(document).tooltip({
        track: true
    });
});

// 부모창에 나의 높이를 전달
parent.postMessage(document.body.scrollHeight+100,"<?=G5_URL?>"); // 부모창의 URL 주소

// parent loading image remove
parent.display_loading('hide');
</script>

<?php
include_once('./_tail.sub.php');
?>
