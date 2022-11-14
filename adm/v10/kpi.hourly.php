<?php
$sub_menu = "955400";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

if(!$mms_idx)
    alert_close('설비 정보가 존재하지 않습니다.');
$mms = get_table_meta('mms','mms_idx',$mms_idx);

$g5['title'] = '시간별 통계';
include_once('./_head.sub.php');
?>
<style>
.table01 {width:100%;border-collapse: collapse;border-spacing: 0;margin-top:10px;}
.table01 th {background:#d5e2e5;color:#424242;height:30px;}
.table01 td {border:solid 1px #ddd;padding:3px;}
.table01 tr:nth-child(2n) td {background:#fafafa;}
</style>
<div class="new_win">
    <h1><?php echo $g5['title']; ?> <span style="font-size:0.8em;margin-left:15px;"><?=$st_date?> ~ <?=$en_date?></span></h1>
    <div class=" new_win_con">

        <div><b style="font-size:1.2em;"><?php echo $mms['mms_name']; ?></b></div>

        <table class="table01">
            <thead class="tbl_head">
            <tr>
                <th scope="col" style="width:100px;">시간</th>
                <th scope="col" style="width:15%">생산</th>
                <th scope="col" style="width:15%">비율</th>
                <th scope="col" style="width:150px;">그래프</th>
            </tr>
            </thead>
            <tbody class="tbl_body">
            <?php
            $st_time = strtotime($st_date." 00:00:00"); // 해당 날짜의 시작
            $en_time = strtotime($en_date." 23:59:59"); // 해당 날짜의 끝

            $sql = "SELECT (CASE WHEN n='1' THEN dta_hour ELSE 'total' END) AS item_name
                        , SUM(dta_value) AS dta_sum
                    FROM
                    (
                        SELECT 
                        dta_hour
                            , SUM(dta_value) AS dta_value
                        FROM
                        (
                            (
                            SELECT 
                                LPAD(n,2,'0') dta_hour
                                , 0 AS dta_value
                            FROM g5_5_tally AS no_table
                            WHERE n < 24
                            ORDER BY dta_hour
                            )
                            UNION ALL
                            (
                            SELECT FROM_UNIXTIME(dta_dt,'%H') AS dta_hour
                                , SUM(dta_value) AS dta_value
                            FROM g5_1_data_output_".$mms_idx."
                            WHERE dta_dt >= '".$st_time."' AND dta_dt <= '".$en_time."'
                            GROUP BY dta_hour
                            ORDER BY dta_hour ASC
                            )
                        ) AS db_table
                        GROUP BY dta_hour
                    ) AS db_table1, g5_5_tally AS db_no
                    WHERE n <= 2
                    GROUP BY item_name
                    ORDER BY n DESC, item_name
            ";
            // echo $sql;
            $result = sql_query($sql,1);
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                // print_r2($row);

                // 합계인 경우
                if($row['item_name'] == 'total') {
                    $row['item_name'] = '합계';
                    $row['tr_class'] = 'tr_stat_total';
                    $item_sum = $row['dta_sum'];
                }
                else {
                    $row['tr_class'] = 'tr_stat_normal';
                }
                // echo $item_sum.'<br>';

                // 비율
                $row['rate'] = ($item_sum) ? $row['dta_sum'] / $item_sum * 100 : 0 ;
                $row['rate_color'] = '#d1c594';
                $row['rate_color'] = ($row['rate']>=80) ? '#72ddf5' : $row['rate_color'];
                $row['rate_color'] = ($row['rate']>=100) ? '#ff9f64' : $row['rate_color'];

                // 그래프
                if($item_sum && $row['dta_sum']) {
                    $row['rate_percent'] = $row['dta_sum'] / $item_sum * 100;
                    $row['graph'] = '<img class="graph_output" src="./img/dot.gif" style="width:'.$row['rate_percent'].'%;background:'.$row['rate_color'].';" height="8px">';
                }

                // First line total skip, start from second line.
                if($i>0) {
                    echo '
                    <tr class="'.$row['tr_class'].'">
                        <td class="text_left">'.$row['item_name'].'</td>
                        <td class="text_right pr_5">'.number_format($row['dta_sum']).'</td><!-- 발생수 -->
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


        <div class="btn_fixed_top">
            <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
        </div>

        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_mms_type='.$ser_mms_type.'&amp;page='); ?>
    </div>
</div>

<script>
// 윈도우 크기 재설정
window.onload = reSize;
window.onresize = reSize;
function reSize() {
	resizeTo(520, 780);    // 여는 페이지 설정 높이 80 차이
}
    

<?php
include_once('./_tail.sub.php');
?>