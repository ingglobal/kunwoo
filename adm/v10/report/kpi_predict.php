<?php
$sub_menu = "955400";
include_once('./_common.php');

$g5['title'] = '예지보고서';
include_once('./_top_menu_kpi.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// print_r2($_REQUEST);
// exit;


add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi1.css">', 1);

add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/function.date.js"></script>', 0);


// 날짜 선택 영역 (선택 및 각종 기본 변수 설정)
include_once('./_top.kpi.php');
?>
<style>
.td_graph {line-height:14px;}
</style>

<div id="report_wrapper">

	<?php
	// 공통통계 영역
	include_once('./_top.statistics.php');
	?>

	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- ========================================================================================= -->
	<!-- start of 예지  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 예지보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02f"><i class="fa fa-check" aria-hidden="true"> 구분타입별 예지</i></div>
            <div id="chart_type"></div>
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
                    $pre_type_cat = array();
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
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
                        if($i >= 0){//($row['item_name'] != 'total'){
                            array_push($pre_type_cat,array($row['item_name'].'('.number_format($row['rate'],1).'%)',(int)$row['arm_predict_sum']));
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
            <div id="chart_facility"></div>
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
                    $pre_faci_cat = array();
                    $pre_faci_tot = 0;
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);

                        // 합계인 경우
                        if($row['item_name'] == 'total') {
                            $row['item_name'] = '합계';
                            $row['tr_class'] = 'tr_stat_total';
                            $arm_count_sum = $row['arm_count_sum'];
                            $arm_alarm_sum = $row['arm_alarm_sum'];
                            $arm_predict_sum = $row['arm_predict_sum'];
                            $pre_faci_tot = (int)$row['arm_predict_sum'];
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="비율:'.number_format($row['rate_total'],1).'%">';
                        }

                        // item_name
                        $row['item_name'] = ($row['item_name']!='합계') ? $g5['mms'][$row['item_name']]['mms_name'] : $row['item_name'];
                        $row['rate'] = (float)($row['arm_predict_sum'] / $pre_faci_tot) * 100; 
                        $row['rate'] = sprintf("%2.2f",$row['rate']); 
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
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($pre_faci_cat,array($row['item_name'].'('.(int)$row['arm_predict_sum'].'회)',$row['rate']));
                        }
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
                <?php //echo $pre_faci_tot; ?>
            </div><!-- .div_info_body -->


            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 예지 발생횟수</i></div>
            <div id="chart_occur"></div>
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
                    $pre_occur_cat = array();
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
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
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($pre_occur_cat,array($g5['mms'][$row['mms_idx']]['mms_name'].'('.number_format($row['rate'],1).'%)',$row['arm_count_sum']));
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
            <div class="div_title_02f"><i class="fa fa-check" aria-hidden="true"> 일자별 예지</i></div>
            <div id="chart_day"></div>
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
                    $pre_day_cat = array();
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
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
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($pre_day_cat,array($row['item_name'].'('.number_format($row['rate'],1).'%)',(int)$row['arm_predict_sum']));
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
            <div id="chart_weekly"></div>
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
                    $pre_weekly_cat = array();
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
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
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($pre_weekly_cat,array($row['item_name'].'('.number_format($row['rate'],1).'%)',(int)$row['arm_predict_sum']));
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
                            $row['graph'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
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



</div><!-- #report_wrapper -->


<script>
$(function(e) {
    $(document).tooltip({
        track: true
    });
});
</script>


<?php
include_once ('./_tail.php');
?>

