<?php
// Direct URL for DEMO check
// URL: G5_URL/theme/v10/skin/board/schedule11/ajax.calendar.php
include_once("./_common.php");

//$a['set_time_unit'] = 60;
//$a['set_max_apply'] = 20;
//$a['set_default_status'] = 'pending';
//$a['set_stat_show'] = 1;
//$b = addslashes(serialize($a));
//echo $b.'<br>';
//$a['holiday_name'] = '삼일절';
//$a['holiday_description'] = '삼일절은 공휴일입니다.';
//$b = addslashes(serialize($a));
//echo $b.'<br>';

//print_r2($board);


// 초기 입력값 추출 ($month 변수)
$month = ($month)? $month : date('Ym', G5_SERVER_TIME);
$year = substr($month,0,4);
$_month = $year.'-'.substr($month,-2);	// 2018-02와 같이 -가 있는 월변수
$last_month = $month - 1;
$next_month = $month + 1;
// 달력 시작일 (이전달의 마지막주 포함, 1월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".$_month."-01') ORDER BY ymd LIMIT 1 ");
$sql_date_start = $w1['ymd'];
// 달력 종료일 (다음달의 첫주 포함, 12월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = (SELECT LAST_DAY('".$_month."-01')) ) ORDER BY ymd DESC LIMIT 1 ");
$sql_date_end = $w1['ymd'];

// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
$sql_week = " WEEK(ymd,0) AS date_week ";
$sql_group_by = " GROUP BY WEEK(ymd,0) ";
// 01월인 경우
if( substr($month,-2) == '01' ) {
	$last_month = ($year-1).'12';

	// 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".$year."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_start = $year.'0101';
	}
	// 1월1일이 일요일 아닌 경우는 작년 마지막 주간의 시작일이 달력 시작일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".(($year-1))."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year-1)."-12-31') ORDER BY ymd LIMIT 1 ");
		$sql_date_start = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	$sql_week = " if(WEEK(ymd,0) > 50, ".$w2['week_num'].", WEEK(ymd,0)) AS date_week ";
    
    // 작년 마지막 주의 주차수값=52,51 등과 같으므로
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)<30, WEEK(ymd,0), 0) ";
    
} 
// 12인 경우
if( substr($month,-2) == '12' ) {
	$next_month = ($year+1).'01';

	// 내년 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".($year+1)."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_end = $year.'1231';
	}
	// 내년 1월1일이 일요일 아닌 경우는 내년 첫주의 마지막날이 달력 종료일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".($year+1)."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year+1)."-01-01') ORDER BY ymd DESC LIMIT 1 ");
		$sql_date_end = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	// 12월31일의 주차 (보통은 52주차가 마지막, 1월1일이 딱 일요일이면 마지막 주차가 53)
	$w3 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd LIKE '".$year."1231' ");
	$sql_week = " if(WEEK(ymd,0) < 2, ".$w3['week_num'].", WEEK(ymd,0)) AS date_week ";

    // 내년 첫주의 주차수값=0 이므로 보정해 줘야 함
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)>30, WEEK(ymd,0), ".$w3['week_num'].") ";
}


// 일정 내용 { ======================================================
if( !$bo_config ) { // 환경설정이 아닐 때만 일정 추출

    // 만료 인증건 추출 [ ======================================================
    $sql = "SELECT * FROM (
                SELECT crt_idx, crt.com_idx, crt_code, crt_certify_date, crt_status, com_name
                    , date_add(crt_certify_date, interval +10 month) AS plus10m
                    , date_add(crt_certify_date, interval +11 month) AS plus11m
                    , date_add(crt_certify_date, interval +358 day) AS plus358d
                FROM {$g5['certify_table']} AS crt
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = crt.com_idx
                WHERE crt_status NOT IN ('trash','delete')
            ) AS db1
            WHERE plus10m BETWEEN '{$_month}-01' AND '{$_month}-31'
                OR plus11m BETWEEN '{$_month}-01' AND '{$_month}-31'
                OR plus358d BETWEEN '{$_month}-01' AND '{$_month}-31'
            ORDER BY com_name
    ";
//    echo $sql.'<br>';
    $result = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 어떤 날짜가 해당 항목인지 추출
        if(is_array($crt_items)) {
            foreach($crt_items as $key=>$val) {
//                echo $row[$key].': '.$val.'<br>';
                // 이번 달인 경우만 표현
                if( substr($row[$key],0,7) == $_month ) {
//                    echo $row[$key].': '.$val.' ---- <br>';
                    $row['href'] = G5_USER_ADMIN_URL.'/certify_list.php?sfl=crt_idx&stx='.$row['crt_idx'];
                    $day_content[$row[$key]] .= '<div class="certify_item certify_'.$row['crt_status'].'" crt_idx="'.$row['crt_idx'].'">'
                                                    .'<span class="certify_ymd">'.$row['crt_certify_date'].'</span>'
                                                    .'<span class="certify_text"><i class="fa fa-circle"></i> '.$val.'</span>'
                                                    .'<span class="certify_code_name">'.$g5['set_crt_codes_value'][$row['crt_code']].'</span>'
                                                    .'<span class="certify_com_name"><a href="'.$row['href'].'">'.$row['com_name'].'</a></span>'
                                                  .'</div>';
                }
            }
        }
    }
    //print_r2($day_content);
    // ] 만료 인증건 추출 ======================================================

    // 심사 추출 
    $sql1 = "   SELECT *
                FROM {$g5['certify_judge_table']} AS crj
                    LEFT JOIN {$g5['certify_table']} AS crt ON crt.crt_idx = crj.crt_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = crt.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = crj.mb_id
                WHERE crj_date BETWEEN '".$_month."-01' AND '".$_month."-31'
                    AND crj_status NOT IN ('trash','delete')
                ORDER BY crj_date
    ";
//    echo $sql1;
    $rs1 = sql_query($sql1,1);
    for ($i=0; $row=sql_fetch_array($rs1); $i++) {
        //echo $row['crj_date'].'<br>';	// 2018-02-03
        $day_judge[$row['crj_date']] .= $row['com_name'].' '.$g5['set_crj_codes_value'][$row['crj_code']].'<br>';

        $row['href'] = G5_USER_ADMIN_URL.'/certify_list.php?sfl=crt_idx&stx='.$row['crt_idx'];
        $day_content[$row['crj_date']] .= '<div class="certify_item certify_'.$row['crj_status'].'" crj_idx="'.$row['crj_idx'].'">'
                                        .'<span class="certify_judge_date">'.$row['crj_date'].'</span>'
                                        .'<span class="certify_judge_name"><i class="fa fa-circle"></i> '.$g5['set_crj_type_value'][$row['crj_type']].'</span>'
                                        .'<span class="certify_mb_name_rank">'.$row['mb_name'].' '.$g5['set_mb_ranks_value'][$row['mb_3']].'</span>'
                                        .'<span class="certify_com_name"><a href="'.$row['href'].'">'.$row['com_name'].'</a><span class="certify_code_name">'.$g5['set_crt_codes_value'][$row['crt_code']].'</span></span>'
                                      .'</div>';
    }
    
    
    $where = array();
    // 디폴트 검색조건
    $where[] = ($set_notin_status_array) ? " wr_9 NOT IN ('".implode("','",$set_notin_status_array)."') " : " (1) ";
    // 기간 설정
//    $where[] = " wr_2 BETWEEN '{$_month}-01 00:00:00' AND '{$_month}-31 23:59:59' ";
    $where[] = " wr_2 BETWEEN '{$_month}-01' AND '{$_month}-31' ";
    // WHERE 절 생성
    $sql_search = ($where) ? ' WHERE '.implode(' AND ', $where) : '';


    // 날짜별 상태별 통계 { -------
    $sql  = "SELECT SUBSTRING(wr_2,1,10) AS wr_ymd, wr_9, SUM(wr_3) AS wr_sum
                FROM ".$g5['write_prefix'].$bo_table."
                {$sql_search}
                GROUP BY wr_ymd, wr_9
    ";
    //echo $sql;
    $result = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 날짜별 상태별 합계
        $day_status_sum[$row['wr_ymd']][$row['wr_9']] = $row['wr_sum'];
        // 날짜별 합계
        $day_total[$row['wr_ymd']] += $day_status_sum[$row['wr_ymd']][$row['wr_9']];
    }
    //print_r2($day_sum);
    //print_r2($day_total);
    // 날짜별 통계 HTML
    if( is_array($day_total) ) {
        foreach($day_total as $key=>$value) {
            foreach($day_status_sum[$key] as $key1=>$value1) {
                $day_status_sum_stat[$key][] .= '<div class="schedule_stat_'.$key1.'">'.$g5['set_reservation_status_value'][$key1].': '.$value1.'</div>';
            }
            // 해당일 상태별
            $day_status_stat[$key] = '<div class="schedule_status_stat" wr_2="'.$key.'">'.implode("",$day_status_sum_stat[$key]).'</div>';
            // 해당일 전체
            $day_total_stat[$key] = '<div class="schedule_total_stat">'.$value.'</div>';
        }
    }
    //print_r2($day_status_stat);
    //print_r2($day_total_stat);
    // } 날짜별 상태별 통계 -------


    // 일정리스트 { -------
    $sql  = "SELECT *
                FROM ".$g5['write_prefix'].$bo_table."
                {$sql_search}
                ORDER BY STR_TO_DATE(wr_2, '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(wr_3, '%H:%i:%s')
    ";
//    echo $sql.'<br>';
    $result = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 날짜 분리
        $row['wr_2_arr'] = date_parse($row['wr_2']);
        $row['wr_ymd'] = sprintf("%04d",$row['wr_2_arr']['year']).'-'.sprintf("%02d",$row['wr_2_arr']['month']).'-'.sprintf("%02d",$row['wr_2_arr']['day']);
        $row['wr_hi'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']);
        $row['wr_his'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']).':'.sprintf("%02d",$row['wr_2_arr']['second']);
        $row['wr_ampm'] = date("A h:i ", strtotime($row['wr_3']));
        $row['wr_ampm2'] = date("A h:i ", strtotime($row['wr_4']));
        $row['wr_range'] = date("A h:i",strtotime($row['wr_3']));
        $row['wr_range'] .= ($row['wr_4'])?'~'.date("A h:i",strtotime($row['wr_4'])):'';
        $row['href'] = get_pretty_url($bo_table, $row['wr_id']);

        
        // 일정표현
        $day_content[substr($row['wr_2'],0,10)] .= '<div class="schedule_item schedule_'.$row['wr_9'].'" wr_id="'.$row['wr_id'].'">'
                                                                    .'<span class="schedule_ymd">'.$row['wr_ymd'].'</span>'
                                                                    .'<span class="schedule_ampm">'.$row['wr_ampm'].'</span>'
                                                                    .'<span class="schedule_hi">'.$row['wr_hi'].'</span>'
                                                                    .'<span class="schedule_his">'.$row['wr_his'].'</span>'
                                                                    .'<span class="schedule_range"><i class="fa fa-circle"></i>'.$row['wr_range'].'</span>'
                                                                    .'<span class="schedule_subject"><a href="'.$row['href'].'">'.$row['wr_subject'].'</a></span>'
                                                                  .'</div>';
    }
    //print_r2($day_content);
    // } 일정리스트 -------
}
// } 일정 내용 ======================================================


// 달력 추출 ======================================================
$sql1 = "SELECT max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_date, '') ) as day0,
		       max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_more, '') ) as day0more,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_date, '') ) as day1,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_more, '') ) as day1more,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_date, '') ) as day2,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_more, '') ) as day2more,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_date, '') ) as day3,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_more, '') ) as day3more,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_date, '') ) as day4,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_more, '') ) as day4more,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_date, '') ) as day5,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_more, '') ) as day5more,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_date, '') ) as day6,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_more, '') ) as day6more
		FROM g5_5_ymd
		WHERE ymd BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
		{$sql_group_by}
";
//echo $sql1;
$rs1 = sql_query($sql1,1);
//while($row = sql_fetch_array($rs1)) { $response->rows[] = $row; }

// --------------------- for Debuging
//echo '<table id="table01_list">
//<caption></caption>
//';
//echo '
//	<thead>
//	<tr>
//	    <th scope="col" style="width:14%">일</th>
//	    <th scope="col" style="width:14%">월</th>
//	    <th scope="col" style="width:14%">화</th>
//	    <th scope="col" style="width:14%">수</th>
//	    <th scope="col" style="width:14%">목</th>
//	    <th scope="col" style="width:14%">금</th>
//	    <th scope="col" style="width:14%">토</th>
//	</tr>
//	</thead>
//	';
//echo '<tbody>';
// --------------------- for Debuging


	
// 날짜 표현
for($i=0;$row=sql_fetch_array($rs1);$i++) {
	echo '<tr class=" ">';
	// 일 ~ 토 한 주간 표현
	for($j=0;$j<7;$j++) {
        //echo $row['day'.$j].'<br>';	// 2018-02-03
        $row[$i][$j]['dates'] = explode("-",$row['day'.$j]);    // 날짜값 분리 배열
        $row[$i][$j]['day_no'] = number_format($row[$i][$j]['dates'][2]);    // 날짜만 숫자로

		// 해당 날짜의 개별 설정 unserialize 추출
        if($row["day".$j."more"]) {
            $unser = unserialize(stripslashes($row["day".$j."more"]));
            if( is_array($unser) && substr($row['day'.$j],0,7) == $_month ) {
                foreach ($unser as $key=>$value) {
                    $row[$i][$j][$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                }    
            }
        }
        //print_r2($row[$i][$j]);
		
		// 요일별 클래스
		if( $j==0 )
			$day_style_week[$i][$j] = " day_sunday";
		else if( $j==6 )
			$day_style_week[$i][$j] = " day_saturday";
		else
			$day_style_week[$i][$j] = " day_weekday";

		// 오늘
        $day_today[$i][$j] = ( $row['day'.$j] == G5_TIME_YMD )? " day_today" : "";
		// 오늘 이전
		$day_oldday[$i][$j] = ( $row['day'.$j] < G5_TIME_YMD )? " day_oldday":"";
		// 공휴일
		$day_holiday[$i][$j] = ( $row[$i][$j]['holiday_name'] )? " day_holiday":"";
		// 이전달
		$day_prev_month[$i][$j] = ( substr($row['day'.$j],0,7) < $_month )? " day_prev_month":"";
		// 다음달
		$day_next_month[$i][$j] = ( substr($row['day'.$j],0,7) > $_month )? " day_next_month":"";
		// 날짜값이 없는 경우
		$day_null[$i][$j] = ( !$row['day'.$j] )? " day_null":"";

		echo '<td td_date="'.$row['day'.$j].'" class="td_day '
                .$day_style_week[$i][$j].$day_today[$i][$j].$day_holiday[$i][$j].$day_oldday[$i][$j].$day_prev_month[$i][$j].$day_next_month[$i][$j].$day_null[$i][$j].'"';
        echo '>';	// end of <td
        // 날짜 & 공휴일명
		if($row["day".$j]) {
            echo '<div class="day_no_holiday">';
            echo '<div class="day_no">'.number_format($row[$i][$j]['dates'][2]).'</div>';
            echo ($row[$i][$j]['holiday_name']) ? '<div class="day_holiday_text" title="'.$row[$i][$j]['holiday_description'].'">'.$row[$i][$j]['holiday_name'].'</div>' : '' ;   // 공휴일 내용이 있으면 표현
            echo '</div>';
        }

        // 관리자 설정 버튼 (이달인 경우만 설정하게 함)
        if($bo_config && substr($row['day'.$j],0,7) == $_month) {
            echo '<div class="btn_set_holiday">[공휴일설정]</div>';
        }

        // 일정내용
        echo ($day_content[$row['day'.$j]]) ? $day_content[$row['day'.$j]] : '' ;

        // 관리자인 경우 통계 보임
        if($is_admin && $board['set_stat_show']) {
            echo ($day_status_stat[$row['day'.$j]]) ? $day_status_stat[$row['day'.$j]] : '' ;
        }
		echo '</td>';
	}
	echo '</tr>';
}


// --------------------- for Debuging
//echo '</tbody>';
//echo '</table>';
// --------------------- for Debuging

exit;
?>