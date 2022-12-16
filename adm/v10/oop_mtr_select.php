<?php
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');


$sql_common = " FROM {$g5['order_out_practice_table']} oop
                    LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
                    LEFT JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['bom_item_table']} boi ON bom.bom_idx = boi.bom_idx
                    LEFT JOIN {$g5['bom_table']} bom2 ON boi.bom_idx_child = bom2.bom_idx
";


$where = array();
// $where[] = " oop_status NOT IN ('trash','delete','del','cancel') ";
$where[] = " oop_status = ('confirm') ";
$where[] = " orp.com_idx = '".$_SESSION['ss_com_idx']."' ";


// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'oop.oop_idx' || $sfl == 'bom2.bom_part_no' || $sfl == 'bom.bom_part_no' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "orp.orp_start_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", oop.oop_idx";
    $sod2 = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
// $rows = 50;//10
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT oop.oop_idx
            ,orp.orp_start_date
            ,orp.cut_mms_idx
            ,oop.bom_idx
            ,bom.bom_part_no
            ,bom.bom_name
            ,bom.bom_std
            ,bom2.bom_idx AS cut_idx
            ,bom2.bom_part_no AS cut_part_no
            ,bom2.bom_name AS cut_name
            ,bom2.bom_std AS cut_std
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$qstr .= '&sca='.$sca.'&fname='.$fname; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '생산계획리스트 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>
<style>
.scp_frame {padding:10px;}
.sp_cat{color:orange;font-size:0.85em;}
.sp_pno{color:skyblue;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.new_frame_con {margin-top:10px;height:484px;overflow-y:auto;padding-bottom:25px;}
.td_bom_name
,.td_bom_part_no
,.td_com_name
 {text-align:left !important;}
.td_bom_price {text-align:right !important;}
</style>

<div id="sch_target_frm" class="new_win scp_frame">

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">
    <input type="hidden" name="com_idx" value="<?php echo $_REQUEST['com_idx']; ?>">

    <div id="div_search" style="display:none;">
        <select name="sfl" id="sfl">
            <option value="oop.oop_idx"<?php echo get_selected($_GET['sfl'], "oop.oop_idx"); ?>>생산계획ID</option>
            <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom.bom_part_no"); ?>>완제품품번</option>
            <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom.bom_std"); ?>>완제품규격</option>
            <option value="bom.bom_name"<?php echo get_selected($_GET['sfl'], "bom.bom_name"); ?>>완제품명</option>
            <option value="bom2.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom2.bom_part_no"); ?>>절단품품번</option>
            <option value="bom2.bom_std"<?php echo get_selected($_GET['sfl'], "bom2.bom_std"); ?>>절단품규격</option>
            <option value="bom2.bom_name"<?php echo get_selected($_GET['sfl'], "bom2.bom_name"); ?>>절단품품명</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:160px;">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?file_name=<?=$file_name?>" class="btn btn_b10">취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_frame_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col"><?php echo subject_sort_link('oop_idx') ?>생산계획ID</a></th>
            <th scope="col">완제품정보</th>
            <th scope="col">절단품정보</th>
            <th scope="col">생산시작일</th>
            <th scope="col">절단외주여부</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $bg = 'bg'.($i%2);
        ?>
        <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['bom_idx'] ?>">
            <td class="td_oop_idx"><?=$row['oop_idx']?></td>
            <td class="td_bom_name">
                <b><?=$row['bom_name']?></b>
                <?php if($row['bom_part_no']){ ?><br><span class="sp_pno">[ <?=$row['bom_part_no']?> ]</span><?php } ?>
                <?php if($row['bom_std']){ ?><br><span class="sp_std">[ <?=$row['bom_std']?> ]</span><?php } ?>
            </td><!-- 완제품정보 -->
            <td class="td_cut_name">
                <b><?=$row['cut_name']?></b>
                <?php if($row['cut_part_no']){ ?><br><span class="sp_pno">[ <?=$row['cut_part_no']?> ]</span><?php } ?>
                <?php if($row['cut_std']){ ?><br><span class="sp_std">[ <?=$row['cut_std']?> ]</span><?php } ?>
            </td><!-- 절단품정보 -->
            <td class="td_cut_mms_idx">
                <?php if(!$row['cut_mms_idx']){ ?>외주절단<?php } ?>
            </td>
            <td class="td_mng td_mng_s">
                <button type="button" class="btn btn_03 btn_select"
                    oop_idx="<?=$row['oop_idx']?>"
                    bom_part_no="<?=$row['cut_part_no']?>"
                    bom_idx="<?=$row['cut_idx']?>"
                    bom_name="<?=$row['cut_name']?>"
                >선택</button>
            </td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="5" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

</div>

<script>
var fname = '<?=$fname?>';
var i = <?=$i?>;
if(i == 0){
    alert('해당 제품의 출하데이터가 없습니다.\n출하데이터를 먼저 등록해 주세요.');
    window.close();
}
$('.btn_select').click(function(e){
    e.preventDefault();
    var oop_idx = $(this).attr('oop_idx');
    var bom_part_no = $(this).attr('bom_part_no');
    var bom_idx = $(this).attr('bom_idx');
    var bom_name = $(this).attr('bom_name');
    // alert(fname);return false;
    if(fname == 'half_oop_list'){
        // alert(oop_idx);return false;
        $("input[name=oop_idx]", opener.document).val( oop_idx );
        $("input[name=bom_part_no]", opener.document).val( bom_part_no );
        $("input[name=bom_idx]", opener.document).val( bom_idx );
        $("input[name=bom_name]", opener.document).val( bom_name );
    }
    

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>