<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$sql_common = " FROM {$g5['item_table']} AS itm
                    LEFT JOIN {$g5['bom_table']} AS bom ON itm.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['bom_category_table']} AS bct ON bom.bct_id = bct.bct_id
                    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON itm.oop_idx = oop.oop_idx
                    LEFT JOIN {$g5['order_practice_table']} AS orp ON oop.orp_idx = orp.orp_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " itm.itm_status NOT IN ('delete','del','trash') AND itm.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

$sql_group = " GROUP BY itm.bom_idx, itm_date ";

if (!$sst) {
    $sst = "orp.orp_start_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", itm_reg_dt";
    $sod2 = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " SELECT COUNT(DISTINCT itm.bom_idx, itm_date) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $total_count.'<br>';

$rows = 12;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// $sql = "SELECT *
//               ,SUM(itm.itm_weight) AS sum
//               ,COUNT(*) AS cnt
//               ,( SELECT SUM(itm_weight) FROM {$g5['item_table']} WHERE oop_idx = itm.oop_idx AND itm_status = 'finish' ) AS sum2
//               ,( SELECT COUNT(itm_idx) FROM {$g5['item_table']} WHERE oop_idx = itm.oop_idx AND itm_status = 'finish' ) AS cnt2
//         {$sql_common} {$sql_search} {$sql_group}  {$sql_order}
//         LIMIT {$from_record}, {$rows}
// ";
$sql = "SELECT *
              ,SUM(itm.itm_weight) AS sum
              ,COUNT(*) AS cnt
              ,( SELECT SUM(itm_weight) FROM {$g5['item_table']} WHERE oop_idx = itm.oop_idx AND itm_status = 'finish' ) AS sum2
              ,( SELECT COUNT(itm_idx) FROM {$g5['item_table']} WHERE oop_idx = itm.oop_idx AND itm_status = 'finish' ) AS cnt2
        {$sql_common} {$sql_search} {$sql_group}  {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r2($sql);
$result = sql_query($sql,1);

$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '완제품 ';
?>
<link rel="stylesheet" href="<?=G5_DEVICE_URL?>/monitor/css/monitor.css">
<style>
.td_itm_part_no{text-align:left !important;}
.td_itm_sum{text-align:right !important;}
.td_itm_cnt{}
.td_itm_sum2{text-align:right !important;}
.td_itm_cnt2{}
</style>
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 생산현황</caption>
    <thead>
    <tr>
        <th scope="col">통계일</th>
        <th scope="col">카테고리</th>
        <th scope="col">파트넘버</th>
        <th scope="col">생산량(kg)</th>
        <th scope="col">생산갯수(톤백)</th>
        <th scope="col">재고량(kg)</th>
        <th scope="col">재고갯수(톤백)</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        //print_r3($row);
        if($row['bct_id']){
            $cat_tree = category_tree_array($row['bct_id']);
            $row['bct_name_tree'] = '';
            for($k=0;$k<count($cat_tree);$k++){
                $cat_str = sql_fetch(" SELECT bct_name FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_name'] : ' > '.$cat_str['bct_name'];
            }
        }

        $s_mod = '<a href="./item_form.php?'.$qstr.'&amp;w=u&amp;itm_idx='.$row['itm_idx'].'" class="btn btn_03">수정</a>';

        // history there items form the last. It is not gooe to see if many are being seen.
        $row['itm_histories'] = explode("\n",$row['itm_history']);
        // print_r2($row['itm_histories']);
        if(sizeof($row['itm_histories']) > 2) {
            $row['itm_history_array'][0] = "...";
            $x=1;
            for($j=sizeof($row['itm_histories'])-2;$j<sizeof($row['itm_histories']);$j++) {
                $row['itm_history_array'][$x] = $row['itm_histories'][$j];
                $x++;
            }
        }
        else {
            $row['itm_history_array'] = $row['itm_histories'];
        }



        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['itm_idx'] ?>">
        <td class="td_itm_date"><?=$row['itm_date']?></td><!-- 통계일 -->
        <td class="td_itm_cat" style="text-align:left;color:orange;">
            <?php if($row['bct_name_tree']){ ?>
            <span class="sp_cat"><?=$row['bct_name_tree']?></span>
            <?php } ?>    
        </td><!-- 카테고리 -->
        <td class="td_itm_part_no"><?=$row['bom_part_no']?></td><!-- 파트넘버 -->
        <td class="td_itm_sum"><?=number_format($row['sum'])?></td><!-- 생산량 -->
        <td class="td_itm_cnt"><?=$row['cnt']?></td><!-- 생산개수(톤백) -->
        <td class="td_itm_sum2"><?=number_format($row['sum2'])?></td><!-- 재고량 -->
        <td class="td_itm_cnt2"><?=$row['cnt2']?></td><!-- 재고개수(톤백) -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='8' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>
