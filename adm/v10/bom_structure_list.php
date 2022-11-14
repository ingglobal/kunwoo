<?php
// 호출페이지들
// /adm/v10/bom_structure_form.php: 오른편에 나타남
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['bom_table']} AS bom
                    LEFT JOIN {$g5['bom_category_table']} AS bct ON bct.bct_id = bom.bct_id
                        AND bct.com_idx = '".$_SESSION['ss_com_idx']."'
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = bom.com_idx_customer
"; 

$where = array();
$where[] = " bom_status NOT IN ('delete','trash') AND bom.com_idx = '".$_SESSION['ss_com_idx']."' AND bom_type NOT IN('product') ";// 디폴트 검색조건

//echo $g5['file_name'];

// 카테고리 검색
if ($sca != "") {
    $where[] = " bom.bct_id LIKE '".trim($sca)."%' ";
}

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bct_id' ) :
			$where[] = " {$sfl} LIKE '".trim($stx)."%' ";
            break;
		case ( $sfl == 'bom_part_no' ) :
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
    $sst = "bom_reg_dt";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$rows = 10;
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

$g5['title'] = '제품(자재)리스트 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>
<style>
.scp_frame {padding:10px;}
.new_frame_con {margin-top:10px;}
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

    <div id="div_search">
        <select name="sfl" id="sfl">
            <option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
            <option value="com_idx_customer"<?php echo get_selected($_GET['sfl'], "com_idx_customer"); ?>>거래처번호</option>
            <option value="bom_maker"<?php echo get_selected($_GET['sfl'], "bom_maker"); ?>>메이커</option>
            <option value="bom_memo"<?php echo get_selected($_GET['sfl'], "bom_idx"); ?>>메모</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?file_name=<?=$file_name?>" class="btn btn_b10">검색취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_frame_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col"><?php echo subject_sort_link('bom_name') ?>품명</a></th>
            <th scope="col">파트넘버</th>
            <th scope="col">거래처</th>
            <th scope="col">단가</th>
            <th scope="col">타입</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {

            $bg = 'bg'.($i%2);
        ?>
        <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['bom_idx'] ?>">
            <td class="td_bom_name"><?=$row['bom_name']?></td><!-- 품명 -->
            <td class="td_bom_part_no"><?=$row['bom_part_no']?></td><!-- 파트넘버 -->
            <td class="td_com_name"><?=$row['com_name']?></td><!-- 거래처 -->
            <td class="td_bom_price"><?=number_format($row['bom_price'])?></td><!-- 단가 -->
            <td class="td_bom_type"><?=$g5['set_bom_type_value'][$row['bom_type']]?></td>
            <td class="td_mng td_mng_s">
                <button type="button" class="btn btn_03 btn_select"
                    bom_idx="<?=$row['bom_idx']?>"
                    bom_name="<?=$row['bom_name']?>"
                    bom_part_no="<?=$row['bom_part_no']?>"
                    com_name="<?=$row['com_name']?>"
                    bom_price="<?=number_format($row['bom_price'])?>"
                >선택</button>
            </td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="6" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

</div>

<script>
$('.btn_select').click(function(e){
    e.preventDefault();
    var bom_idx = $(this).attr('bom_idx');
    var bom_name = $(this).attr('bom_name');  // 
    var bom_part_no = $(this).attr('bom_part_no');
    var com_name = $(this).attr('com_name');
    var bom_price = $(this).attr('bom_price');    // 

    <?php
    // BOM 구성
    if($file_name=='bom_structure_form') {
    ?>
        parent.add_item(bom_idx, bom_name, bom_part_no, com_name, bom_price);
    <?php
    }
    // 부속품 수정
    else if($file_name=='bom_parts_form'
        ||$file_name=='bom_item_form'
    ) {
    ?>
        $("input[name=bom_idx]", opener.document).val( bom_idx );
        $("input[name=bom_name]", opener.document).val( bom_name );
    <?php
    }
    // 게시판 글쓰기
    if($file_name=='write'||$file_name=='error_code_form') {
    ?>
        $("input[name=com_name]", opener.document).val( com_name );
        $("input[name=bom_idx]", opener.document).val( bom_idx );
        $("input[name=bom_name]", opener.document).val( bom_name );
        $("#bom_info", opener.document).hide();
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>