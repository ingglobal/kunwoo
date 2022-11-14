<?php
include_once('./_common.php');
include_once(G5_USER_ADMIN_PATH.'/lib/latest10.lib.php');

// com_idx 디폴트
$member['com_idx'] = $_SESSION['ss_com_idx'] ?: $member['com_idx'];
$com = get_table_meta('company','com_idx',$member['com_idx']);
$com_idx = $com['com_idx'];
// print_r2($com);

// print_r2($_REQUEST);


$g5['title'] = 'M-ERP 보고서';
include_once('./_head.sub.php');

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
    // 지시수량용 mms_idx 조건절
    $sql_mmses3 = " AND trm_idx_line IN (".implode(",",$mms_array).") ";
}
// 선택라인이 없으면 전체에서 추출한다.
else {

}

// 지시수량 목표 먼저 추출 (아래 부분 목표 추출하는 부분에서 활용합니다.)
$sql = "SELECT bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8, oop_9, oop_10
        FROM {$g5['order_out_practice_table']} AS oop
            LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
        WHERE oop_status IN ('confirm','done')
            AND orp_start_date >= '".$st_date."'
            AND orp_done_date <= '".$en_date."'
            AND orp_done_date != '0000-00-00'
            {$sql_mmses3}
        GROUP BY bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8, oop_9, oop_10
        ORDER BY bom_idx, trm_idx_line, orp_done_date
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++){
    // print_r2($row);
    $date1 = preg_replace("/[ :-]/","",substr($row['orp_done_date'],0,10));   // 날짜중에서 일자 추출하여 배열키값으로!
    $date2 = preg_replace("/[ :-]/","",date("Y-m",strtotime($date1)));     // 날짜중에서 월 추출하여 배열키값으로!
    $date3 = preg_replace("/[ :-]/","",date("Y",strtotime($date1)));       // 년도만
    $week1 = date("w",strtotime($date1)); // 0 (for Sunday) through 6 (for Saturday)
    // 주차값 (1년 중 몇 주, date('w')랑 기준이 달라서 일요일인 경우 다음차수로 넘김)
    $week2 = (!$week1) ? date("W",strtotime($date1))+1 : date("W",strtotime($date1));
    // echo $week1.'(0=sunsay..) : '.$week2.'주차 : ';
    // echo date("Y-m-d",$k).'(오늘날짜) : ';
    // echo date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days")).'(주첫날)<br>';
    $target['week_day'][$week2] = date('Y-m-d', strtotime(date("Y-m-d",strtotime($date1))." -".$week1."days"));  // 주차의 시작 일요일

    $target['bom'][$row['bom_idx']] += (int)$row['oop_count'];  // 제품별 목표
    $target['line'][$row['trm_idx_line']] += (int)$row['oop_count'];  // 제품별 목표
    $target['date_shift'][$date1]['1'] += (int)$row['oop_1'];  // 날짜별-1구간 목표
    $target['date_shift'][$date1]['2'] += (int)$row['oop_2'];  // 날짜별-2구간 목표
    $target['date_shift'][$date1]['3'] += (int)$row['oop_3'];  // 날짜별-3구간 목표
    $target['date_shift'][$date1]['4'] += (int)$row['oop_4'];  // 날짜별-4구간 목표
    $target['date_shift'][$date1]['5'] += (int)$row['oop_5'];  // 날짜별-5구간 목표
    $target['date_shift'][$date1]['6'] += (int)$row['oop_6'];  // 날짜별-6구간 목표
    $target['date_shift'][$date1]['7'] += (int)$row['oop_7'];  // 날짜별-7구간 목표
    $target['date_shift'][$date1]['8'] += (int)$row['oop_8'];  // 날짜별-8구간 목표
    $target['date_shift'][$date1]['9'] += (int)$row['oop_9'];  // 날짜별-9구간 목표
    $target['date_shift'][$date1]['10'] += (int)$row['oop_10'];  // 날짜별-10구간 목표
    $target['shift']['1'] += (int)$row['oop_1'];  // 1구간 목표
    $target['shift']['2'] += (int)$row['oop_2'];  // 2구간 목표
    $target['shift']['3'] += (int)$row['oop_3'];  // 3구간 목표
    $target['shift']['4'] += (int)$row['oop_4'];  // 4구간 목표
    $target['shift']['5'] += (int)$row['oop_5'];  // 5구간 목표
    $target['shift']['6'] += (int)$row['oop_6'];  // 6구간 목표
    $target['shift']['7'] += (int)$row['oop_7'];  // 7구간 목표
    $target['shift']['8'] += (int)$row['oop_8'];  // 8구간 목표
    $target['shift']['9'] += (int)$row['oop_9'];  // 9구간 목표
    $target['shift']['10'] += (int)$row['oop_10'];  // 날짜별-10구간 목표
    $target['date'][$date1] += (int)$row['oop_count'];  // 날짜별 목표
    $target['week'][$week2] += (int)$row['oop_count'];  // 주차별 목표
    $target['month'][$date2] += (int)$row['oop_count'];  // 월별 목표
    $target['year'][$date3] += (int)$row['oop_count'];  // 연도별 목표
    $target['total'] += (int)$row['oop_count'];  // 전체 목표
}
// print_r2($target);
// echo '----------<br>';


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
// echo $days['days'].'<br>';
// echo $days['days'].'<br>';
$run_rate = $runtime_avg / ($days['days']*86400) * 100;
// echo $run_rate.'<br>';



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
			   <span class="title">매출액</span>
				<span class="content" id="sum_sales">&nbsp;</span>
				<span class="unit"></span>
			</li>
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
				<span class="content" id="sum_runtime"><?=number_format($run_rate,1)?></span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">정비비용</span>
				<span class="content" id="sum_maintain">&nbsp;</span>
				<span class="unit">건</span>
			</li>
			<li>
				<span class="title">재고총액</span>
				<span class="content" id="sum_stock">&nbsp;</span>
				<span class="unit">건</span>
			</li>
		</ul>
	</div>
    <!-- the end of .div_stat  -->
    
	<!-- start of 생산보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 매출보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 라인별 매출</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col" style="width:200px;">구분</th>
                        <th scope="col" style="width:20%;">매출</th>
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
                                , output_sum_price
                                , output_defect_price
                            FROM (	
                                (
                            
                                SELECT 
                                    0 AS mmg_idx
                                    , 'total' AS mmg_name
                                    , 0 AS depth
                                    , 0 AS mmg_left
                                    , SUM( itm_weight ) AS output_sum
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                    , SUM( itm_weight * itm_price ) AS output_sum_price
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                FROM {$g5['item_sum_table']}
                                WHERE itm_date >= '".$st_date."'
                                    AND itm_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    AND itm_type = 'product'
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
                                    , SUM(output_sum_price) AS output_sum_price
                                    , SUM(output_defect_price) AS output_defect_price
                                FROM (	(
                                        SELECT mmg.mmg_idx
                                            , CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
                                            , (COUNT(parent.mmg_idx) - 1) AS depth
                                            , mmg.mmg_left
                                            , 0 output_sum
                                            , 0 output_defect
                                            , 0 output_sum_price
                                            , 0 output_defect_price
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
                                            , SUM(output_sum_price) AS output_sum_price
                                            , SUM(output_defect_price) AS output_defect_price
                                        FROM {$g5['mms_group_table']} AS mmg, 
                                            {$g5['mms_group_table']} AS parent,
                                            (
                                            SELECT 
                                                mmg_idx AS mmg_idx_group
                                                , SUM( itm_weight ) AS output_sum
                                                , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                                , SUM( itm_weight * itm_price ) AS output_sum_price
                                                , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                            FROM {$g5['item_sum_table']}
                                            WHERE itm_date >= '".$st_date."'
                                                AND itm_date <= '".$en_date."'
                                                AND com_idx='".$com_idx."'
                                                AND itm_type = 'product'
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
                            $amount_output_price = $row['output_sum_price'];
                            // echo $amount_output_price;
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
                                <td class="text_right pr_5">'.number_format($row['output_sum_price']).'</td><!-- 생산매출 -->
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
            <?php
            $amount_price = num_to_han($amount_output_price);
            // print_r2($amount_price);
            ?>
            <script>
            // 매출액, 목표달성율, 불량율 입력
            $('#sum_sales').text('<?=number_format($amount_price[0],1)?>');
            $('#sum_sales').closest('li').find('.unit').text('<?=$amount_price[1]?>');
            $('#sum_target').text('<?=number_format(($amount_output/$target['total']*100),1)?>');
            $('#sum_defect').text('<?=number_format(($amount_defect/$amount_output*100),2)?>');
            </script>

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 매출</i></div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:17%;">정상매출</th>
                        <th scope="col" style="width:12%;">불량매출</th>
                        <th scope="col" style="width:17%;">전체매출</th>
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
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                                SELECT 
                                    mms_idx
                                    , SUM(itm_weight) AS output_total
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                    , SUM(itm_weight * itm_price) AS output_total_price
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                FROM {$g5['item_sum_table']}
                                WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    AND itm_type = 'product'
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
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 파트넘버별 매출</i></div>
            <div class="div_info_body">
                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:16%;">정상매출</th>
                        <th scope="col" style="width:10%;">불량매출</th>
                        <th scope="col" style="width:16%;">전체매출</th>
                        <th scope="col" style="width:100px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',bom_part_no) ELSE 'total' END) AS item_name
                                , mms_idx
                                , bom_part_no
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , bom_part_no
                                    , SUM(itm_weight) AS output_total
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                    , SUM(itm_weight * itm_price) AS output_total_price
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                FROM {$g5['item_sum_table']}
                                WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    AND itm_type = 'product'
                                    {$sql_mmses}
                                GROUP BY mms_idx, bom_part_no
                                ORDER BY mms_idx, bom_part_no
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal), bom_part_no
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
                        $row['mmi_name'] = ($row['dta_mmi_no']) ? ' <span style="color:#3ab4d2;">'.$row['dta_mmi_no'].'</span>': '';

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['mms_title'].'>'.$row['mms_name'].' '.$row['bom_part_no'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 일자별 매출</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:15%;">정상매출</th>
                        <th scope="col" style="width:10%;">불량매출</th>
                        <th scope="col" style="width:15%">전체매출</th>
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
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                 
                                SELECT 
                                    ymd_date
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                    , SUM(output_total_price) AS output_total_price
                                    , SUM(output_good_price) AS output_good_price
                                    , SUM(output_defect_price) AS output_defect_price
                                FROM
                                (
                                    (
                                    SELECT 
                                        CAST(ymd_date AS CHAR) AS ymd_date
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                        , 0 AS output_total_price
                                        , 0 AS output_good_price
                                        , 0 AS output_defect_price
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        itm_date AS ymd_date
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                        , SUM(itm_weight * itm_price) AS output_total_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                    FROM {$g5['item_sum_table']}
                                    WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        AND itm_type = 'product'
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
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 주간별 매출</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:17%;">정상매출</th>
                        <th scope="col" style="width:17%;">불량매출</th>
                        <th scope="col" style="width:17%">전체매출</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_week ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                 
                                SELECT 
                                    ymd_week
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                    , SUM(output_total_price) AS output_total_price
                                    , SUM(output_good_price) AS output_good_price
                                    , SUM(output_defect_price) AS output_defect_price
                                FROM
                                (
                                    (
                                    SELECT 
                                        YEARWEEK(ymd_date,4) AS ymd_week
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                        , 0 AS output_total_price
                                        , 0 AS output_good_price
                                        , 0 AS output_defect_price
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        YEARWEEK(itm_date,4) AS ymd_week
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                        , SUM(itm_weight * itm_price) AS output_total_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                    FROM {$g5['item_sum_table']}
                                    WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        AND itm_type = 'product'
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
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 월별 매출</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:17%;">정상매출</th>
                        <th scope="col" style="width:17%;">불량매출</th>
                        <th scope="col" style="width:17%">전체매출</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_month ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                 
                                SELECT 
                                    ymd_month
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                    , SUM(output_total_price) AS output_total_price
                                    , SUM(output_good_price) AS output_good_price
                                    , SUM(output_defect_price) AS output_defect_price
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,7) AS ymd_month
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                        , 0 AS output_total_price
                                        , 0 AS output_good_price
                                        , 0 AS output_defect_price
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        substring( CAST(itm_date AS CHAR),1,7) AS ymd_month
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                        , SUM(itm_weight * itm_price) AS output_total_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                    FROM {$g5['item_sum_table']}
                                    WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        AND itm_type = 'product'
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
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 연도별 매출</i></div>
            <div class="div_info_body">

                <table class="table01">
                    <thead class="tbl_head">
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col" style="width:17%;">정상매출</th>
                        <th scope="col" style="width:17%;">불량매출</th>
                        <th scope="col" style="width:17%">전체매출</th>
                        <th scope="col" style="width:120px;">그래프</th>
                    </tr>
                    </thead>
                    <tbody class="tbl_body">
                    <?php
                    $sql = "SELECT (CASE WHEN n='1' THEN ymd_year ELSE 'total' END) AS item_name
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                                , SUM(output_total_price) AS output_total_price
                                , SUM(output_good_price) AS output_good_price
                                , SUM(output_defect_price) AS output_defect_price
                            FROM
                            (
                 
                                SELECT 
                                    ymd_year
                                    , SUM(output_total) AS output_total
                                    , SUM(output_good) AS output_good
                                    , SUM(output_defect) AS output_defect
                                    , SUM(output_total_price) AS output_total_price
                                    , SUM(output_good_price) AS output_good_price
                                    , SUM(output_defect_price) AS output_defect_price
                                FROM
                                (
                                    (
                                    SELECT 
                                        substring( CAST(ymd_date AS CHAR),1,4) AS ymd_year
                                        , 0 AS output_total
                                        , 0 AS output_good
                                        , 0 AS output_defect
                                        , 0 AS output_total_price
                                        , 0 AS output_good_price
                                        , 0 AS output_defect_price
                                    FROM {$g5['ymd_table']} AS ymd
                                    WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                    ORDER BY ymd_date
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        substring( CAST(itm_date AS CHAR),1,4) AS ymd_year
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                        , SUM(itm_weight * itm_price) AS output_total_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_good_price
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight * itm_price ELSE 0 END ) AS output_defect_price
                                    FROM {$g5['item_sum_table']}
                                    WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                        AND com_idx='".$com_idx."'
                                        AND itm_type = 'product'
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
                                <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                                <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
	<!-- start of 정비 및 재고  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 정비 및 재고</i></div>
	<div class="div_wrapper">
        <div class="div_left">

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
                        <th scope="col" style="width:20%">단가</th>
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
                    ";
                    // echo $sql.'<br>';
                    $rs = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($rs); $i++) {
                        // print_r2($row);
                        // wr_9 serialized 추출
                        $row['sried'] = get_serialized($row['wr_9']);
                        // print_r2($row['sried']);
                        // 재고총액
                        $wr_stock_price += $row['wr_3']*$row['wr_4'];

                        echo '
                        <tr class="'.$row['tr_class'].'">
                            <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                            <td class="">'.$row['wr_subject'].'</td><!-- 부품명 -->
                            <td class="text_center">'.number_format($row['wr_3']).'</td><!-- 단가 -->
                            <td class="text_center">'.$row['wr_4'].'</td><!-- 수량 -->
                        </tr>
                        ';
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
                <?php
                $stock_price = num_to_han($wr_stock_price);
                // print_r2($amount_price);
                ?>
                <script>
                // 재고총액
                $('#sum_stock').text('<?=number_format($stock_price[0],1)?>');
                $('#sum_stock').closest('li').find('.unit').text('<?=$stock_price[1]?>');
                </script>

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
