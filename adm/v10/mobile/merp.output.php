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
                                , SUM( dta_value ) AS output_sum
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                , SUM( dta_value * dta_mmi_no_price ) AS output_sum_price
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                                            , SUM( dta_value ) AS output_sum
                                            , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                            , SUM( dta_value * dta_mmi_no_price ) AS output_sum_price
                                            , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                        $amount_output_price = $row['output_sum_price'];
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
                            <td class="text_right pr_5">'.number_format($row['output_sum_price']).'</td><!-- 생산매출 -->
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
                                , SUM(dta_value) AS output_total
                                , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                            <td class="text_right pr_5">'.number_format($row['output_good_price']).'</td><!-- 정상매출 -->
                            <td class="text_right pr_5">'.number_format($row['output_defect_price']).'</td><!-- 불량매출 -->
                            <td class="text_right pr_5">'.number_format($row['output_total_price']).'</td><!-- 전체매출 -->
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
        <div class="div_title_02"><i class="fa fa-check" aria-hidden="true"> 기종별 매출</i></div>
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
                $sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx,'-',dta_mmi_no) ELSE 'total' END) AS item_name
                            , mms_idx
                            , dta_mmi_no
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
                                , dta_mmi_no
                                , SUM(dta_value) AS output_total
                                , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                    $row['mmi_name'] = ($row['dta_mmi_no']) ? ' <span style="color:#3ab4d2;">'.$row['dta_mmi_no'].'</span>': '';

                    // First line total skip, start from second line.
                    if($i>0) {
                        echo '
                        <tr class="'.$row['tr_class'].'">
                            <td class="text_left" '.$row['mms_title'].'>'.$row['mms_name'].$row['mmi_name'].'</td><!-- cache/mms-setting.php -->
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
                                    dta_date AS ymd_date
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                                    YEARWEEK(dta_date,4) AS ymd_week
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                                    substring( CAST(dta_date AS CHAR),1,7) AS ymd_month
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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
                                    substring( CAST(dta_date AS CHAR),1,4) AS ymd_year
                                    , SUM(dta_value) AS output_total
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value ELSE 0 END ) AS output_good
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value ELSE 0 END ) AS output_defect
                                    , SUM(dta_value * dta_mmi_no_price) AS output_total_price
                                    , SUM( CASE WHEN dta_defect = 0 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_good_price
                                    , SUM( CASE WHEN dta_defect = 1 THEN dta_value * dta_mmi_no_price ELSE 0 END ) AS output_defect_price
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