<?php
// 호출페이지들
// /adm/v10/bom_structure_form.php: 오른편에 나타남
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');



$sql_common = " FROM {$g5['order_item_table']} AS ori
                    LEFT JOIN {$g5['order_table']} AS ord ON ori.ord_idx = ord.ord_idx
";


$where = array();
$where[] = " ori.bom_idx = '{$bom_idx}' ";
$where[] = " ord.com_idx = '".$_SESSION['ss_com_idx']."' ";
$where[] = " ord_status NOT IN ('trash','delete','del','cancel') AND ord.com_idx = '".$_SESSION['ss_com_idx']."' ";


// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'ord_date' ) :
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
    $sst = "ord_date";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
// $rows = 20;//10
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r2($sql);exit;
$result = sql_query($sql,1);

$qstr .= '&sca='.$sca.'&file_name='.$file_name; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '수주리스트 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>
<style>
.scp_frame {padding:10px;}
.sp_cat{color:orange;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.new_frame_con {margin-top:10px;height:484px;overflow-y:auto;padding-bottom:25px;}
.td_ord_idx
 {width:20%;}
.td_ord_date {width:70%;}
.td_mng{width:10%;}
</style>

<div id="sch_target_frm" class="new_win scp_frame">

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">
    <input type="hidden" name="com_idx" value="<?php echo $_REQUEST['com_idx']; ?>">

    <div id="div_search">
        <select name="sfl" id="sfl">
            <option value="ord_date"<?php echo get_selected($_GET['sfl'], "ord_date"); ?>>수주일</option>
            <option value="ord_idx"<?php echo get_selected($_GET['sfl'], "ord_idx"); ?>>수주ID</option>
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
            <th scope="col"><?php echo subject_sort_link('ord_idx') ?>수주ID</a></th>
            <th scope="col">수주일</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $bg = 'bg'.($i%2);
        ?>
        <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['bom_idx'] ?>">
            <td class="td_ord_idx"><?=$row['ord_idx']?></td>
            <td class="td_ord_date"><?=$row['ord_date']?></td>
            <td class="td_mng td_mng_s">
                <button type="button" class="btn btn_03 btn_select"
                    ord_idx="<?=$row['ord_idx']?>"
                    ord_date="<?=$row['ord_date']?>"
                    ori_idx="<?=$row['ori_idx']?>"
                >선택</button>
            </td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="3" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

</div>

<script>
var i = <?=$i?>;
if(i == 0){
    alert('해당 제품의 수주데이터가 없습니다.\n수주데이터를 먼저 등록해 주세요.');
    window.close();
}
$('.btn_select').click(function(e){
    e.preventDefault();
    var ord_idx = $(this).attr('ord_idx');
    var ord_date = $(this).attr('ord_date');
    var ori_idx = $(this).attr('ori_idx');
    
    <?php
    // BOM 구성
    if($file_name=='order_out_practice_form') {
    ?>
        if($("input[name=ord_idx]", opener.document).val() != ord_idx){
            $("input[name=ord_idx]", opener.document).val( ord_idx );
            $("input[name=ord_date]", opener.document).val( ord_date );
            $("input[name=ori_idx]", opener.document).val( ori_idx );
            $("input[name=oro_idx]", opener.document).val('');
            $("input[name=oro_date_plan]", opener.document).val('');
        }
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>