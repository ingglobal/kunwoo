<?php
include_once('../common.php');
define('_INDEX_', true);

$g5['title'] = 'APIs홈';
include_once(G5_PATH.'/head.sub.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_DEVICE_URL.'/_css/default.css">', 0);
include_once(G5_DEVICE_PATH.'/head_menu.php');
/*
{$g5['material_table']}
{$g5['bom_table']}
{$g5['bom_item_table']}
{$g5['order_out_practice_table']}
{$g5['order_practice_table']}
*/
$sql = " SELECT bom.bom_idx
        , bom.bom_name
        , bom.bom_part_no
        , bom.bom_std
        , bom.bom_press_type
        , half.half_idx
        , half.half_name
        , half.half_part_no
        , half.half_std
        , half.mtr_idx
        , half.mtr_name
        , half.mtr_part_no
        , half.mtr_std
    FROM (
        SELECT boi.bom_idx AS half_idx
            , bom.bom_part_no AS half_part_no
            , bom.bom_name AS half_name
            , bom.bom_std AS half_std
            , mtr.mtr_idx
            , mtr.mtr_part_no
            , bom2.bom_name AS mtr_name
            , bom2.bom_std AS mtr_std
        FROM (
            SELECT bom_idx AS mtr_idx
                , bom_part_no AS mtr_part_no    
            FROM {$g5['material_table']}
            GROUP BY bom_part_no
        ) mtr
        LEFT JOIN {$g5['bom_item_table']} boi ON mtr.mtr_idx = boi.bom_idx_child
        LEFT JOIN {$g5['bom_table']} bom ON boi.bom_idx = bom.bom_idx
        LEFT JOIN {$g5['bom_table']} bom2 ON boi.bom_idx_child = bom2.bom_idx
        ORDER BY mtr.mtr_part_no, bom.bom_part_no   
    ) half
    LEFT JOIN {$g5['bom_item_table']} boi ON half.half_idx = boi.bom_idx_child
    LEFT JOIN {$g5['bom_table']} bom ON boi.bom_idx = bom.bom_idx
    ORDER BY half.mtr_part_no, half.half_part_no, bom.bom_part_no
";
$result = sql_query($sql,1);
?>
<style>
.api_index_box{padding:20px;}
.tbl_head01 thead tr th{position:sticky;top:0px;z-index:100;}
.tbl_head01 tbody tr{}
.tbl_head01 tbody tr.bg0{background:#37475e !important;}
.tbl_head01 tbody tr.bg1{background:#243349 !important;}
.tbl_head01 tbody tr th{}
.tbl_head01 tbody tr th,
.tbl_head01 tbody tr td{}
.tbl_head01 tbody tr td{}
</style>
<div class="api_index_box">
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>샘플 등록 가능한 완제품 목록</caption>
    <thead>
    <tr>
        <th scope="col">완제품ID</th>
        <th scope="col">완제품명</th>
        <th scope="col">완제품목코드</th>
        <th scope="col">완제품규격</th>
        <th scope="col">프레스타입</th>
        <th scope="col">절단품ID</th>
        <th scope="col">절단품명</th>
        <th scope="col">절단품목코드</th>
        <th scope="col">절단품규격</th>
        <th scope="col">자재품ID</th>
        <th scope="col">자재품명</th>
        <th scope="col">자재품목코드</th>
        <th scope="col">자재품규격</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_bom_idx"><?=$row['bom_idx']?></td><!-- 완제품ID -->
        <td class="td_bom_name"><?=$row['bom_name']?></td><!-- 완제품명 -->
        <td class="td_bom_part_no"><?=$row['bom_part_no']?></td><!-- 완제품목코드 -->
        <td class="td_bom_std"><?=$row['bom_std']?></td><!-- 완제품규격 -->
        <td class="td_bom_press_type"><?=$row['bom_press_type']?></td><!-- 프레스타입 -->
        <td class="td_half_idx"><?=$row['half_idx']?></td><!-- 절단품ID -->
        <td class="td_half_name"><?=$row['half_name']?></td><!-- 절단품명 -->
        <td class="td_half_part_no"><?=$row['half_part_no']?></td><!-- 절단품목코드 -->
        <td class="td_half_std"><?=$row['half_std']?></td><!-- 절단품규격 -->
        <td class="td_mtr_idx"><?=$row['mtr_idx']?></td><!-- 자재품ID -->
        <td class="td_mtr_name"><?=$row['mtr_name']?></td><!-- 자재품명 -->
        <td class="td_mtr_part_no"><?=$row['mtr_part_no']?></td><!-- 자재품목코드 -->
        <td class="td_mtr_std"><?=$row['mtr_std']?></td><!-- 자재품규격 -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='12' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');