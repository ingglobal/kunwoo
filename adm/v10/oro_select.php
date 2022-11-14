<?php
// 호출페이지들
// /adm/v10/bom_structure_form.php: 오른편에 나타남
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');


$sql_common = " FROM {$g5['order_out_table']} AS oro
                    LEFT JOIN {$g5['order_item_table']} AS ori ON oro.ori_idx = ori.ori_idx
                    LEFT JOIN {$g5['bom_table']} AS bom ON ori.bom_idx = bom.bom_idx
";


$where = array();
$where[] = " ori.bom_idx = '{$bom_idx}' ";
$where[] = " ori.ord_idx = '{$ord_idx}' ";
$where[] = " ori.ori_idx = '{$ori_idx}' ";
$where[] = " oro_status NOT IN ('trash','delete','del','cancel') ";
$where[] = " oro.com_idx = '".$_SESSION['ss_com_idx']."' ";


// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'oro.oro_date_plan' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'oro.oro_date' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'bom.bom_part_no' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'bom.bom_std' ) :
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
    $sst = "oro_date_plan";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", oro_idx";
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

$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$qstr .= '&sca='.$sca.'&file_name='.$file_name; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '출하리스트 ('.number_format($total_count).')';
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
            <option value="oro.oro_date_plan"<?php echo get_selected($_GET['sfl'], "oro.oro_date_plan"); ?>>출하예정일</option>
            <option value="oro.oro_date"<?php echo get_selected($_GET['sfl'], "oro.oro_date"); ?>>출하일</option>
            <option value="bom.bom_name"<?php echo get_selected($_GET['sfl'], "bom.bom_name"); ?>>품명</option>
            <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom.bom_part_no"); ?>>품번</option>
            <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom.bom_std"); ?>>규격</option>
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
            <th scope="col"><?php echo subject_sort_link('oro_idx') ?>출하ID</a></th>
            <th scope="col"><?php echo subject_sort_link('oro_date_plan') ?>출하예정일</a></th>
            <th scope="col"><?php echo subject_sort_link('oro_date') ?>출하일</a></th>
            <th scope="col">제품정보</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $bg = 'bg'.($i%2);
        ?>
        <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['bom_idx'] ?>">
            <td class="td_oro_idx"><?=$row['oro_idx']?></td>
            <td class="td_oro_date_plan" style="width:80px;"><?=(($row['oro_date_plan']!='0000-00-00')?substr($row['oro_date_plan'],2,10):'-')?></td>
            <td class="td_oro_date" style="width:80px;"><?=(($row['oro_date']!='0000-00-00')?substr($row['oro_date'],2,10):'-')?></td>
            <td class="td_bom_name">
                <?php if($row['bct_name_tree']){ echo '<span class="sp_cat">'.$row['bct_name_tree'].'</span><br>'; } ?>
                <?=$row['bom_name']?>
                <?php if($row['bom_part_no']){ ?><br><span class="sp_pno">[ <?=$row['bom_part_no']?> ]</span><?php } ?>
                <?php if($row['bom_std']){ ?><br><span class="sp_std">[ <?=$row['bom_std']?> ]</span><?php } ?>

            </td><!-- 품명 -->
            <td class="td_mng td_mng_s">
                <button type="button" class="btn btn_03 btn_select"
                    oro_idx="<?=$row['oro_idx']?>"
                    oro_date_plan="<?=$row['oro_date_plan']?>"
                    ord_idx="<?=$row['ord_idx']?>"
                    bom_idx="<?=$row['bom_idx']?>"
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
var i = <?=$i?>;
if(i == 0){
    alert('해당 제품의 출하데이터가 없습니다.\n출하데이터를 먼저 등록해 주세요.');
    window.close();
}
$('.btn_select').click(function(e){
    e.preventDefault();
    var oro_idx = $(this).attr('oro_idx');
    var oro_date_plan = $(this).attr('oro_date_plan');
    
    <?php
    // BOM 구성
    if($file_name=='order_out_practice_form') {
    ?>
        $("input[name=oro_idx]", opener.document).val( oro_idx );
        $("input[name=oro_date_plan]", opener.document).val( oro_date_plan );
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>