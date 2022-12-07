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

$sql_common = " FROM {$g5['material_table']} mat
                    INNER JOIN {$g5['bom_table']} mtr ON mat.bom_part_no = mtr.bom_part_no
                    INNER JOIN {$g5['bom_item_table']} boi ON mtr.bom_idx = boi.bom_idx_child
                    INNER JOIN {$g5['bom_table']} cut ON boi.bom_idx = cut.bom_idx
                    INNER JOIN {$g5['bom_item_table']} boi2 ON cut.bom_idx = boi2.bom_idx_child
                    INNER JOIN {$g5['bom_table']} bom ON boi2.bom_idx = bom.bom_idx
"; 

$where = array();
$where[] = " mat.mtr_type = 'material' ";
$where[] = " mat.mtr_status NOT IN ('delete','del','trash','cancel') ";
$where[] = " mtr.bom_status NOT IN ('delete','del','trash') ";
$where[] = " mtr.bom_type = 'material' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bom.bom_part_no' || $sfl == 'bom.bom_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'cut.bom_part_no' || $sfl == 'cut.bom_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mtr.bom_part_no' || $sfl == 'mtr.bom_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

$bom_press_type = $bom_press_type?$bom_press_type:'';
if($bom_press_type != '') {  // all 인 경우는 조건이 필요없음
    $where[] = " bom.bom_press_type = '{$bom_press_type}'";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "cut.bom_part_no";
    $sod = "";
}
if (!$sst2) {
    $sst2 = ", mtr.bom_part_no";
    $sod2 = "";
}
if (!$sst3) {
    $sst3 = ", bom.bom_part_no";
    $sod3 = "";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} {$sst3} {$sod3} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 20;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT bom.bom_idx
            , bom.bom_part_no
            , bom.bom_name
            , bom.bom_std
            , bom.bom_press_type
            , cut.bom_idx AS cut_idx
            , cut.bom_part_no AS cut_part_no
            , cut.bom_texture AS cut_texture
            , mtr.bom_idx AS mtr_idx
            , mtr.bom_part_no AS mtr_part_no
            , mtr.bom_name AS mtr_name
            , mtr.bom_std AS mtr_std
            , mat.mtr_lot AS mat_lot
            , mat.mtr_heat AS mat_heat
            , mat.mtr_bundle AS mat_bundle
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r2($sql);
$result = sql_query($sql,1);
$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&bom_press_type='.$bom_press_type; // 추가로 확장해서 넘겨야 할 변수들
?>

<div class="api_index_box">
    <div class="local_ov01 local_ov">
        <?php echo $listall ?>
        <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    </div>

    <form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom.bom_part_no"); ?>>완제품품번</option>
            <option value="bom.bom_name"<?php echo get_selected($_GET['sfl'], "bom.bom_name"); ?>>완제품품명</option>
            <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom.bom_std"); ?>>완제품규격</option>
            <option value="bom.bom_idx"<?php echo get_selected($_GET['sfl'], "bom.bom_idx"); ?>>완제품ID</option>
            <option value="mtr.bom_part_no"<?php echo get_selected($_GET['sfl'], "mtr.bom_part_no"); ?>>자재품품번</option>
            <option value="mtr.bom_name"<?php echo get_selected($_GET['sfl'], "mtr.bom_name"); ?>>자재품품명</option>
            <option value="mtr.bom_std"<?php echo get_selected($_GET['sfl'], "mtr.bom_std"); ?>>자재품규격</option>
            <option value="mtr.bom_idx"<?php echo get_selected($_GET['sfl'], "mtr.bom_idx"); ?>>자재품ID</option>
            <option value="mat.mtr_lot"<?php echo get_selected($_GET['sfl'], "mat.mtr_lot"); ?>>자재품Lot</option>
            <option value="mat.mtr_heat"<?php echo get_selected($_GET['sfl'], "mat.mtr_heat"); ?>>자재품히트넘버</option>
            <option value="mat.mtr_bundle"<?php echo get_selected($_GET['sfl'], "mat.mtr_bundle"); ?>>자재품번들넘버</option>
            <option value="cut.bom_part_no"<?php echo get_selected($_GET['sfl'], "cut.bom_part_no"); ?>>절단품품번</option>
            <option value="cut.bom_idx"<?php echo get_selected($_GET['sfl'], "cut.bom_idx"); ?>>절단품ID</option>
        </select>
        <select name="bom_press_type" id="bom_press_type">
            <option value="">프레스유형</option>
            <?=$g5['set_bom_press_type_value_options']?>
        </select>
        <script>$('select[name="bom_press_type"]').val('<?=$bom_press_type?>');</script>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" class="btn_submit" value="검색">
    </form>
    <div class="tbl01_head01 tbl_wrap">
        <table>
        <caption>샘플 등록 가능한 완제품 목록</caption>
        <thead>
        <tr>
            <th scope="col">완제품ID</th>
            <th scope="col">완제품목코드</th>
            <th scope="col">자재품Heat</th>
            <th scope="col">자재품Bundle</th>
            <th scope="col">완제품명</th>
            <th scope="col">완제품규격</th>
            <th scope="col">프레스타입</th>
            <th scope="col">절단품ID</th>
            <th scope="col">절단품목코드</th>
            <th scope="col">절단품재질</th>
            <th scope="col">자재품ID</th>
            <th scope="col">자재품목코드</th>
            <th scope="col">자재품명</th>
            <th scope="col">자재품규격</th>
            <th scope="col">자재품Lot</th>
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
            <td class="td_bom_part_no"><?=$row['bom_part_no']?></td><!-- 완제품목코드 -->
            <td class="td_mat_heat"><?=$row['mat_heat']?></td><!-- 자재품히트넘버 -->
            <td class="td_mat_bundle"><?=$row['mat_bundle']?></td><!-- 자재품번들넘버 -->
            <td class="td_bom_name"><?=$row['bom_name']?></td><!-- 완제품명 -->
            <td class="td_bom_std"><?=$row['bom_std']?></td><!-- 완제품규격 -->
            <td class="td_bom_press_type"><?=$g5['set_bom_press_type_value'][$row['bom_press_type']]?></td><!-- 프레스타입 -->
            <td class="td_cut_idx"><?=$row['cut_idx']?></td><!-- 절단품ID -->
            <td class="td_cut_part_no"><?=$row['cut_part_no']?></td><!-- 절단품목코드 -->
            <td class="td_cut_texture"><?=$row['cut_texture']?></td><!-- 자재품재질 -->
            <td class="td_mtr_idx"><?=$row['mtr_idx']?></td><!-- 자재품ID -->
            <td class="td_mtr_part_no"><?=$row['mtr_part_no']?></td><!-- 자재품목코드 -->
            <td class="td_mtr_name"><?=$row['mtr_name']?></td><!-- 자재품명 -->
            <td class="td_mtr_std"><?=$row['mtr_std']?></td><!-- 자재품규격 -->
            <td class="td_mat_lot"><?=$row['mat_lot']?></td><!-- 자재품Lot -->
        </tr>
        <?php
        }
        if ($i == 0)
            echo "<tr><td colspan='15' class=\"empty_table\">자료가 없습니다.</td></tr>";
        ?>
        </tbody>
        </table>
    </div>
    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');