<?php
$sub_menu = "955400";
include_once('./_common.php');

$g5['title'] = '생산보고서';
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
    
	<!-- start of 생산보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 생산보고서</i></div>
	<div class="div_wrapper">
        <div class="div_left">

            <!-- ========================================================================================= -->
            <div class="div_title_02f" style="display:none;"><i class="fa fa-check" aria-hidden="true"> 라인별 생산</i></div>
            <div class="div_info_body" style="display:none;">

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
                                    , SUM( itm_weight ) AS output_sum
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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
                                                , SUM( itm_weight ) AS output_sum
                                                , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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
                            $row['graph_output'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_output'].'%;background:'.$row['rate_color'].';" height="8px" title="달성율: '.number_format($row['rate'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block;" height="2px" title="목표">';
                        }

                        echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" style="padding-left:'.(15+$row['indent']).'px;">'.$row['mmg_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_sum']).'</td><!-- 생산 -->
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

            <!-- ========================================================================================= -->
            <div class="div_title_02f"><i class="fa fa-check" aria-hidden="true">설비별 생산</i></div>
            <div id="chart_facility"></div>
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
                                    , SUM(itm_weight) AS output_total
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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
                    $prd_fac_cnt = 0;
                    $prd_fac_name = array();
                    $prd_fac_cat = [
                        /*
                        array(
                            'name' => '목표남은수량'
                            ,'data' => array()
                        ),*/
                        array(
                            'name' => '생산량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array()
                        )
                    ];
                    /*
                    $prd_fac_cat2 = [
                        array(
                            'name' => '생산량'
                            ,'data' => array(4912,2456,819,4912,2456,840)
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array(0,0,0,0,0,0)
                        )
                    ];
                    */
                    
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$g5['mms'][$row['item_name']]['mms_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5"><a href="javascript:" mms_idx="'.$row['item_name'].'" class="link_mmd_product" st_date="'.$st_date.'" en_date="'.$en_date.'">'
                                    .number_format($row['output_total']).'</a></td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }

                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($prd_fac_cat[0]['data'],(int)$row['output_good']);//생산수량
                            array_push($prd_fac_cat[1]['data'],(int)$row['output_defect']);//불량수량
                            array_push($prd_fac_name,$g5['mms'][$row['item_name']]['mms_name']);
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
                    var href = '../kpi.hourly.php?mms_idx='+this_mms_idx+'&st_date='+this_st_date+'&en_date='+this_en_date;
                    winStatHour = window.open(href, "winStatHour", "left=100, top=100, width=460, height=600");
                    winStatHour.focus();
                });
            </script>
                

            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">교대별 생산</i></div>
            <div id="chart_alternating"></div>
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
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',itm_shift) ELSE 'total' END) AS item_name
                                , mms_idx
                                , itm_shift
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , itm_shift
                                    , SUM(itm_weight) AS output_total
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
                                FROM {$g5['item_sum_table']}
                                WHERE itm_date >= '".$st_date."' AND itm_date <= '".$en_date."'
                                    AND com_idx='".$com_idx."'
                                    AND itm_type = 'product'
                                    {$sql_mmses}
                                GROUP BY mms_idx, itm_shift
                                ORDER BY mms_idx, itm_shift
                            ) AS db2, g5_5_tally AS db_no
                            WHERE n <= 2
                            GROUP BY item_name
                            ORDER BY n DESC, convert(item_name, decimal), itm_shift
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
                    $prd_alt_cnt = 0;
                    $prd_alt_mms_idx = 0;
                    $prd_alt_name = array();
                    $prd_alt_cat = [
                        array(
                            'name' => '목표량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '생산량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '정상'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array()
                        )
                    ];
                    /*
                    $prd_alt_name2 = array(
                        '4000톤 프레스',
                        '용접기',
                        'CNC',
                        '4000톤 트랜스퍼',
                        '로봇',
                        '범퍼밴딩(전용기)',
                        '범퍼밴딩(전용기)1교대',
                        '범퍼밴딩(전용기)2교대'
                    );
                    $prd_alt_cat2 = [
                        array(
                            'name' => '목표량'
                            ,'data' => array(0,0,0,0,0,0,7000,7000)
                        ),
                        array(
                            'name' => '생산량'
                            ,'data' => array(4912,2456,819,4912,2456,542,798,0)
                        ),
                        array(
                            'name' => '정상'
                            ,'data' => array(4912,2456,819,4912,2456,542,498,0)
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array(0,0,0,0,0,0,300,0)
                        )
                    ];
                    */
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['target'] = $target['mms_shift'][$row['mms_idx']][$row['itm_shift']];
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // 교대명 (길이가 길면 잘라서 표현하고 popover나타남)
                        // $cur_len = 17;
                        // $row['mms_name'] = cut_str($g5['mms'][$row['mms_idx']]['mms_name'],$cur_len,'..');
                        $row['mms_name'] = $g5['mms'][$row['mms_idx']]['mms_name'];
                        $row['arr_str'] = preg_split("//u", $g5['mms'][$row['mms_idx']]['mms_name'], -1, PREG_SPLIT_NO_EMPTY);
                        $row['str_len'] = count($row['arr_str']);
                        // $row['mms_title'] = ($row['str_len'] >= $cur_len) ? ' title="'.$g5['mms'][$row['mms_idx']]['mms_name'].'"' : "";
                        $row['shf_name'] = ($row['itm_shift']) ? ' '.$row['itm_shift'].'교대': '';

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
                        
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($prd_alt_cat[0]['data'],(int)$row['target']);//목표량
                            array_push($prd_alt_cat[1]['data'],(int)$row['output_total']);//생산량
                            array_push($prd_alt_cat[2]['data'],(int)$row['output_good']);//정상량
                            array_push($prd_alt_cat[3]['data'],(int)$row['output_defect']);//불량
                            array_push($prd_alt_name,$row['mms_name'].(($row['shf_name']) ? '<span style="color:blue;">('.$row['shf_name'].')</span>':''));
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
            <div id="chart_mode"></div>
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
                    $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',bom_part_no) ELSE 'total' END) AS item_name
                                , mms_idx
                                , bom_part_no
                                , SUM(output_total) AS output_total
                                , MAX(output_total) AS output_max
                                , SUM(output_good) AS output_good
                                , SUM(output_defect) AS output_defect
                            FROM
                            (
                                SELECT
                                    mms_idx
                                    , bom_part_no
                                    , SUM(itm_weight) AS output_total
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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

                    $prd_mod_cnt = 0;
                    $prd_mod_name = array();
                    $prd_mod_cat = [
                        /*
                        array(
                            'name' => '목표남은수량'
                            ,'data' => array()
                        ),*/
                        array(
                            'name' => '생산량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array()
                        )
                    ];

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['target'] = $target['mms_mmi'][$row['mms_idx']][$row['bom_part_no']];
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // 기종명
                        // $cur_len = 17;
                        // $row['mms_name'] = cut_str($g5['mms'][$row['mms_idx']]['mms_name'],$cur_len,'..');
                        $row['mms_name'] = $g5['mms'][$row['mms_idx']]['mms_name'];
                        $row['arr_str'] = preg_split("//u", $g5['mms'][$row['mms_idx']]['mms_name'], -1, PREG_SPLIT_NO_EMPTY);
                        $row['str_len'] = count($row['arr_str']);
                        // $row['mms_title'] = ($row['str_len'] >= $cur_len) ? ' title="'.$g5['mms'][$row['mms_idx']]['mms_name'].'"' : "";
                        $row['mmi_no'] = ($row['bom_part_no']) ? ' <span style="color:#3ab4d2;">'.$row['bom_part_no'].'</span>': '';
                        $row['mmi_name'] = ($row['bom_part_no']) ? ' <div style="color:#818181;font-size:0.7em;margin-top:-7px;">'.$mmi_name[$row['mms_idx']][$row['bom_part_no']].'</div>': '';

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left" '.$row['mms_title'].'>'.$row['mms_name'].$row['mmi_no'].$row['mmi_name'].'</td><!-- cache/mms-setting.php -->
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }
                        
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($prd_mod_cat[0]['data'],(int)$row['output_good']);//생산수량
                            array_push($prd_mod_cat[1]['data'],(int)$row['output_defect']);//불량수량
                            array_push($prd_mod_name,$row['mms_name'].$row['mmi_no'].'번');
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
            <div class="div_title_02f"><i class="fa fa-check" aria-hidden="true"> 일자별 생산</i></div>
            <div id="chart_day"></div>
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
                                        itm_date AS ymd_date
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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
                    
                    $prd_day_cnt = 0;
                    $prd_day_name = array();
                    $prd_day_cat = [
                        /*
                        array(
                            'name' => '목표남은수량'
                            ,'data' => array()
                        ),*/
                        array(
                            'name' => '생산량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array()
                        )
                    ];

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
                        }

                        // First line total skip, start from second line.
                        if($i>0) {
                            echo '
                            <tr class="'.$row['tr_class'].'">
                                <td class="text_left">'.$row['item_name'].'</td>
                                <td class="text_right pr_5">'.number_format($row['target']).'</td><!-- 목표 -->
                                <td class="text_right pr_5">'.number_format($row['output_total']).'</td><!-- 생산 -->
                                <td class="text_right pr_5">'.number_format($row['output_good']).'</td><!-- 양호 -->
                                <td class="text_right pr_5">'.number_format($row['output_defect']).'</td><!-- 불량 -->
                                <td class="td_graph text_left pl_0">'.$row['graph_good'].$row['graph_defect'].$row['graph_target'].'</td>
                            </tr>
                            ';
                        }

                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($prd_day_cat[0]['data'],(int)$row['output_good']);//생산수량
                            array_push($prd_day_cat[1]['data'],(int)$row['output_defect']);//불량수량
                            array_push($prd_day_name,$row['item_name']);
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
            <div id="chart_weekly"></div>
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
                                        YEARWEEK(itm_date,4) AS ymd_week
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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

                    $prd_weekly_cnt = 0;
                    $prd_weekly_name = array();
                    $prd_weekly_cat = [
                        /*
                        array(
                            'name' => '목표남은수량'
                            ,'data' => array()
                        ),*/
                        array(
                            'name' => '생산량'
                            ,'data' => array()
                        ),
                        array(
                            'name' => '불량'
                            ,'data' => array()
                        )
                    ];

                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
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
                        
                        if($i > 0){//($row['item_name'] != 'total'){
                            array_push($prd_weekly_cat[0]['data'],(int)$row['output_good']);//생산수량
                            array_push($prd_weekly_cat[1]['data'],(int)$row['output_defect']);//불량수량
                            array_push($prd_weekly_name,$row['week_start_date']);
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
            <div id="chart_monthly"></div>
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
                                        substring( CAST(itm_date AS CHAR),1,7) AS ymd_month
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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

                    $prd_monthly_cat = array();
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
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
                        
                        if($i > 0){//($row['item_name'] != 'total'){
                            $per_good = @(float)($row['output_good'] / $row['output_total'])*100;
                            $per_good = sprintf("%2.2f",$per_good);
                            $per_defect = @(float)($row['output_defect'] / $row['output_total'])*100;
                            $per_defect = sprintf("%2.2f",$per_defect);
                            array_push($prd_monthly_cat,array('name'=>'정상('.$row['output_good'].')', 'y'=>(float)$per_good));
                            array_push($prd_monthly_cat,array('name'=>'불량('.$row['output_defect'].')', 'y'=>(float)$per_defect));
                        }
                    }
                    if ($i == 0)
                        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                    ?>
                </tbody>
                </table>
            </div><!-- .div_info_body -->
            <?php
            //print_r2($prd_monthly_cat);
            ?>
            <!-- ========================================================================================= -->
            <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 연도별 생산</i></div>
            <div id="chart_annual"></div>
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
                                        substring( CAST(itm_date AS CHAR),1,4) AS ymd_year
                                        , SUM(itm_weight) AS output_total
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_weight ELSE 0 END ) AS output_good
                                        , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_weight ELSE 0 END ) AS output_defect
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
                    
                    $prd_annual_cat = array();
                    $result = sql_query($sql,1);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                        //print_r2($row);
                        $tmp_arr = array();
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
                            $row['graph_total'] = '<img class="graph_output" src="../img/dot.gif" style="width:'.$row['rate_total_percent'].'%;background:'.$row['rate_total_color'].';" height="8px" title="생산: '.number_format($row['rate_total'],1).'%">';
                            $row['rate_good_percent'] = ($amount_max && $row['target']) ? $row['output_good'] / $amount_max * 100 : 0 ;
                            $row['graph_good'] = '<img class="graph_good" src="../img/dot.gif" style="width:'.$row['rate_good_percent'].'%;background:'.$row['rate_good_color'].';" height="8px" title="정상: '.number_format($row['rate_good'],1).'%">';
                            $row['rate_defect_percent'] = ($amount_max && $row['target']) ? $row['output_defect'] / $amount_max * 100 : 0 ;
                            $row['graph_defect'] = '<img class="graph_defect" src="../img/dot.gif" style="width:'.$row['rate_defect_percent'].'%;background:'.$row['rate_defect_color'].';" height="8px" title="불량: '.number_format($row['rate_defect'],1).'%">';
                            $row['rate_target'] = ($amount_max) ? $row['target'] / $amount_max * 100 : 0 ;
                            $row['graph_target'] = '<img class="graph_target" src="../img/dot.gif" style="width:'.$row['rate_target'].'%;background:#bbb;display:block" height="2px" title="목표">';
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
                        
                        if($i > 0){//($row['item_name'] != 'total'){
                            $per_good = @(float)($row['output_good'] / $row['output_total'])*100;
                            $per_good = sprintf("%2.2f",$per_good);
                            $per_defect = @(float)($row['output_defect'] / $row['output_total'])*100;
                            $per_defect = sprintf("%2.2f",$per_defect);
                            array_push($prd_annual_cat,array('name'=>'정상('.$row['output_good'].')', 'y'=>(float)$per_good));
                            array_push($prd_annual_cat,array('name'=>'불량('.$row['output_defect'].')', 'y'=>(float)$per_defect));
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
// 그래프 자바스크립트 _tail에서 호출합니다.
include_once ('./_tail.php');
?>

