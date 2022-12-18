<?php
$sub_menu = "930100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '생산실행계획(제품별)';
// include_once('./_top_menu_orp.php');
include_once('./_head.php');
include_once('./_top_menu_practice.php');
// echo $g5['container_sub_title'];
$sql_common = " FROM {$g5['order_out_practice_table']} AS oop
    LEFT JOIN {$g5['bom_table']} AS bom ON oop.bom_idx = bom.bom_idx
    LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
    LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
    LEFT JOIN {$g5['order_table']} AS ord ON oro.ord_idx = ord.ord_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " oop.oop_status NOT IN ('del','delete','trash') AND orp.com_idx = '".$_SESSION['ss_com_idx']."' ";



//print_r2($g5['line_reverse'][$stx]);
// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'oop.orp_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
    }
}

// 검색어 설정
if ($stx2 != "") {
    switch ($sfl2) {
		case ( $sfl2 == 'bom.bom_part_no' ) :
			$where[] = " {$sfl2} = '".trim($stx2)."' ";
            break;
        case ( $sfl2 == 'bom.bom_name' ) :
            $where[] = " {$sfl2} LIKE '%".trim($stx2)."%' ";
            break;
        case ( $sfl2 == 'trm_name' ) :
            $trm_idx = $g5['line_reverse'][$stx2];
            $where[] = " orp.trm_idx_line = '{$trm_idx}' ";
            break;
        default :
			$where[] = " $sfl2 LIKE '%".trim($stx2)."%' ";
            break;
    }
}


if($order_date){
    $where[] = " ord.ord_date = '".$order_date."' ";
    $qstr .= '&order_date='.$order_date;
}

if($orp_start_date){
    $where[] = " orp.orp_start_date = '".$orp_start_date."' ";
    $qstr .= '&orp_start_date='.$orp_start_date;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "orp_start_date";
    $sod = "desc";
}
if (!$sst2) {
    $sst2 = ", oop.oop_idx";
    $sod2 = "desc";
}
// if (!$sst3) {
//     $sst3 = ", orp.trm_idx_line";
//     $sod3 = "";
// }

// $sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} {$sst3} {$sod3} ";
$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";
$sql_group = "";//" GROUP BY oop.orp_idx ";
$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
//한 페이지 목록수를 100개를 넘길수 없도록 해라 로딩속도 때문에
if($schrows){
    if($schrows > 60) $rows = 60;
    else if($schrows < 30) $rows = 30;
    else $rows = $schrows;
}
else{
    $rows = 30;
}
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT *
        {$sql_common} {$sql_search} {$sql_group} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&order_date='.$order_date; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.tbl_head01 thead tr th span{display:none;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
.td_bom_name {text-align:left !important;}
.sp_cat{font-size:0.85em;color:orange;}
.sp_pno{color:skyblue;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.td_orp_part_no, .td_com_name, .td_orp_maker
,.td_orp_items, .td_orp_items_title {text-align:left !important;}
.td_ord_date{width:80px;}
.td_orp_count{text-align:right !important;}
.td_ord_date{position:relative;}
.td_ord_date .sp_ord_idx{display:block;position:absolute;top:0;left:0;font-size:0.7em;color:orange;}
.span_orp_price {margin-left:20px;}
.span_orp_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.div_product_detail {margin-top:-5px;font-size:0.8em;}
.span_oop_count {margin-left:10px;color:yellow;}
.span_oro_date_plan {margin-left:10px;}
.span_oro_date_plan:before {content:'~';}

.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.sch_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;margin-right:20px;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>
<?php
echo $g5['container_sub_title'];
?>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <label for="schrows" class="sch_label" style="margin-right:10px;">
        <span>최대표시갯수<!--i class="fa fa-times data_blank" aria-hidden="true"></i--></span>
        <input type="number" name="schrows" min="30" max="60" value="<?php echo $rows ?>" id="schrows" class="frm_input readonly" style="width:100px;text-align:left;">
    </label>
    <select name="sfl" id="sfl">
        <option value="oop.orp_idx"<?php echo get_selected($_GET['sfl'], "oop.orp_idx"); ?>>생산계획ID</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:80px;margin-right:10px;">
    <label for="sfl2" class="sound_only">검색대상</label>
    <select name="sfl2" id="sfl2">
        <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl2'], "bom_part_no"); ?>>품번</option>
        <option value="bom.bom_name"<?php echo get_selected($_GET['sfl2'], "bom_name"); ?>>품명</option>
        <option value="trm_name"<?php echo get_selected($_GET['sfl2'], "trm_name"); ?>>라인설비명</option>
    </select>
    <label for="stx2" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx2" value="<?php echo $stx2 ?>" id="stx2" class="frm_input">
    <label for="order_date" class="sch_label">
        <span>수주일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="order_date" value="<?php echo $order_date ?>" id="order_date" readonly class="frm_input readonly" placeholder="수주일" style="width:100px;" autocomplete="off">
    </label>
    <label for="orp_start_date" class="sch_label">
        <span>생산시작일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="orp_start_date" value="<?php echo $orp_start_date ?>" id="orp_start_date" readonly class="frm_input readonly" placeholder="시작(생산)일" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <!--p>지시수량에 필요한 자재가 부족한 경우 <span class="color_red">빨간색</span>으로 표시됩니다. 자재 창고위치에 따라 현장 오차가 있을 수 있으므로 반드시 확인하시고 진행하세요.</p-->
    <p>생산작업이 진행되는 동안에는 생산계획 상품의 [확정]상태값을 수정하지 마세요.</p>
    <p style="display:none;">'생산수량' 항목의 값은 생산이 진행중일 때 표시됩니다.</p>
</div>

<div class="select_input">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="o_status" class="slt_label">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_oop_status_value_options']?>
            </select>
        </label>
        <input type="button" id="slt_input" onclick="slet_input(document.getElementById('form01'));" value="선택항목 일괄입력" class="btn btn_02">
    </p>
</div>
<script>
$('.data_blank').on('click',function(e){
    e.preventDefault();
    //$(this).parent().siblings('input').val('');
    var obj = $(this).parent().next();
    if(obj.prop("tagName") == 'INPUT'){
        if(obj.attr('type') == 'hidden'){
            obj.val('');
            obj.siblings('input').val('');
        }else if(obj.attr('type') == 'text'){
            obj.val('');
        }
    }else if(obj.prop("tagName") == 'SELECT'){
        obj.val('');
    }
});
</script>
<form name="form01" id="form01" action="./order_out_practice_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="sst3" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod3" value="<?php echo $sod2 ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="stx2" value="<?php echo $stx2 ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="order_date" value="<?php echo $order_date ?>">
<input type="hidden" name="token" value="">
<!--차종/품명/구분/전일재고/단가/납품/과부족/생산지시/시간1/시간2/시간3/시간4/시간5/시간6/시간7/시간8-->
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="orp_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col">품명</th>
        <th scope="col">수주ID</th>
        <!-- <th scope="col">생산계획ID</th> -->
        <th scope="col">출하계획<br>수주일</th>
        <th scope="col">단조유형</th>
        <th scope="col">원자재</th>
        <th scope="col">절단설비</th>
        <th scope="col">단조설비</th>
        <th scope="col">생산시작일</th>
        <th scope="col">납품수량</th>
        <th scope="col">지시수량</th>
        <th scope="col">주간수량<span></th>
        <th scope="col">야간수량<span></th>
        <th scope="col">본제품만<br>재단조</th>
        <th scope="col">상태</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $s_mod = '<a href="./order_out_practice_form.php?'.$qstr.'&amp;w=u&amp;oop_idx='.$row['oop_idx'].'" class="btn btn_03">수정</a>';

        $bom = get_table_meta('bom','bom_idx',$row['bom_idx']);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['orp_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="orp_idx[<?php echo $row['orp_idx'] ?>]" value="<?php echo $row['orp_idx'] ?>" id="orp_idx_<?php echo $row['oop_idx'] ?>">
            <input type="hidden" name="oop_idx[<?php echo $row['oop_idx'] ?>]" value="<?php echo $row['oop_idx'] ?>" id="oop_idx_<?php echo $row['oop_idx'] ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['orp_name']); ?> <?php echo get_text($row['orp_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['oop_idx'] ?>" id="chk_<?php echo $i ?>">
            <div class="chkdiv_btn" chk_no="<?=$i?>"></div>
        </td>
        <td class="td_oop_idx"><?=$row['oop_idx']?></td>
        <td class="td_bom_name">
            <?php
            $bom_sql = sql_fetch(" SELECT bom_name, bct_id FROM {$g5['bom_table']} WHERE bom_idx = '{$row['bom_idx']}' ");
            $bct_id = $bom_sql['bct_id'];
            $cat_tree = category_tree_array($bct_id);
            $row['bct_name_tree'] = '';
            for($k=0;$k<count($cat_tree);$k++){
                $cat_str = sql_fetch(" SELECT bct_name, bct_desc FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_desc'] : ' > '.$cat_str['bct_desc'];
            }
            $bom_name = '<b>'.$bom_sql['bom_name'].'</b><br>';
            echo ($row['bct_name_tree'])?'<span class="sp_cat">'.$row['bct_name_tree'].'</span><br>':'';
            echo $bom_name;
            echo '<span class="sp_pno">[ '.$row['bom_part_no'].' ]</span><br>';
            echo '<span class="sp_std">[ '.$row['bom_std'].' ]</span>';
            ?>
        </td>
        <td class="td_ord_idx">
            <?php if($row['ord_idx']){ ?>
                <a href="./order_out_practice_list.php?sfl=oop.ord_idx&stx=<?=$row['ord_idx']?>"><?=$row['ord_idx']?></a>
            <?php } else { ?>
                -
            <?php } ?>
        </td>
        <!-- <td class="td_orp_idx"><a href="./order_practice_list.php?sfl=orp.orp_idx&stx=<?=$row['orp_idx']?>"><?=$row['orp_idx']?></a></td> -->
        <td class="td_ord_date">
            <?php if($row['ord_date']){ ?>
            <a href="./order_out_list.php?sfl=oro.ord_idx&stx=<?=$row['ord_idx']?>" class="sp_ord_idx">출하관리</a>
            <?php } ?>
            <?=(($row['ord_date'])?substr($row['ord_date'],2,8):' - ')?>
        </td>
        <td class="td_press_type">
            <?=$g5['set_bom_press_type_value'][$row['bom_press_type']]?>
        </td>
        <td class="td_mtr">
            <input type="hidden" name="orp_order_no[<?=$row['oop_idx']?>]" value="<?=$row['orp_order_no']?>">
            <?php
            $mtr_tbl = " SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = (
                SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = '{$row['bom_idx']}'
            ) ";

            $mtr_sql = " SELECT bom.bom_idx,bom.bom_name,bom.bom_part_no,bom.bom_std FROM ( {$mtr_tbl} ) boi
                            LEFT JOIN {$g5['bom_table']} bom ON boi.bom_idx_child = bom.bom_idx ";
            $mtr_res = sql_query($mtr_sql,1);
            $mopts = '';
            if($mtr_res->num_rows){
                $mtr_boms = array(); 
                $mopts .= '<select name="mtr_bom_idx['.$row['oop_idx'].']" oop_idx="'.$row['oop_idx'].'" class="mtr_bom_idx mtr_bom_idx_'.$row['oop_idx'].'">'.PHP_EOL;
                for($mrow=0;$mrow=sql_fetch_array($mtr_res);$mrow++){
                    $mopts .= '<option value="'.$mrow['bom_idx'].'">'.$mrow['bom_name'].'('.$mrow['bom_std'].')</option>'.PHP_EOL;
                    array_push($mtr_boms,$mrow['bom_idx']);
                }
                // print_r2($mtr_boms);
                $mtr_bom = ($row['mtr_bom_idx'] != 0) ? $row['mtr_bom_idx'] : $mtr_boms[0];
                // print_r2($mtr_bom);
                $mopts .= '</select>'.PHP_EOL;
                $mopts .= '<script>'.PHP_EOL;
                $mopts .= "var oop_val_".$i." = ".$mtr_bom.';'.PHP_EOL;
                $mopts .= '$(".mtr_bom_idx_'.$row['oop_idx'].'").val(oop_val_'.$i.');'.PHP_EOL;
                $mopts .= '</script>'.PHP_EOL;
            }
            else{
                $mopts .= '<input type="hidden" name="mtr_bom_idx['.$row['oop_idx'].']" value="'.$row['mtr_bom_idx'].'">'.PHP_EOL;
                $mopts .= '-';
            }
            echo $mopts;
            // $mtr_res = sql_fetch(" SELECT GROUP_CONCAT(bom_idx_child) AS bom_idxs FROM {$g5['bom_item_table']} WHERE bom_idx = '{$half_res['bom_idx_child']}' ");
            // echo $mtr_res['bom_idxs'];
            ?>
        </td>
        <td class="td_cut_mms">
            <select name="cut_mms[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" class="cut_mms_idx cut_mms_<?=$row['oop_idx']?>">
                <option value="0">외주작업</option>
                <?=$g5['cut_options']?>
            </select>
            <script>
                $('.cut_mms_<?=$row['oop_idx']?>').val('<?=$row['cut_mms_idx']?>');
                </script>
        </td>
        <td class="td_forge_mms">
            <select name="forge_mms[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" class="forge_mms_idx forge_mms_<?=$row['oop_idx']?>">
                <option value="0">외주작업</option>
                <?=$g5['forge_options']?>
            </select>
            <script>
                $('.forge_mms_<?=$row['oop_idx']?>').val('<?=(($row['forge_mms_idx'])?$row['forge_mms_idx']:'0')?>');
                </script>
        </td>
        <td class="td_start_date">
            <input type="text" name="orp_start_date[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" value="<?=(($row['orp_start_date'] == '0000-00-00')?'-':$row['orp_start_date'])?>" readonly class="readonly tbl_input" style="width:90px;background:#333 !important;text-align:center;">
        </td>
        <td class="td_oro_cnt">
            <?php if($row['oro_count']){ ?>
                <span class="oro_count_<?=$row['oop_idx']?>"><?=$row['oro_count']?></span>
            <?php } else { ?>
                -
            <?php } ?>
        </td>
        <td class="td_oop_cnt">
            <input type="text" name="oop_count[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" value="<?=number_format($row['oop_count'])?>" readonly class="readonly tbl_input sit_mat oop_count_<?=$row['oop_idx']?>" style="width:60px;background:#000 !important;text-align:right;">
        </td>
        <td class="td_oop_1"><input type="text" oop="1" oop_idx="<?=$row['oop_idx']?>" name="oop_1[<?=$row['oop_idx']?>]" value="<?=$row['oop_1']?>" class="tbl_input shf_one oop_1_<?=$row['oro_idx']?>" style="width:60px;text-align:right;"></td>
        <td class="td_oop_2"><input type="text" oop="2" oop_idx="<?=$row['oop_idx']?>" name="oop_2[<?=$row['oop_idx']?>]" value="<?=$row['oop_2']?>" class="tbl_input shf_one oop_2_<?=$row['oro_idx']?>" style="width:60px;text-align:right;"></td>
        <td class="td_oop_onlythis_yn td_oop_onlythis_yn_<?=$row['oop_idx']?>" style="width:60px;">
            <select name="oop_onlythis_yn[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" class="oop_onlythis_yn oop_onlythis_yn_<?=$row['oop_idx']?>" style="width:67px;">
                <?=$g5['set_noyes_value_options']?>
            </select>
            <script>
                $('.oop_onlythis_yn_<?=$row['oop_idx']?>').val('<?=$row['oop_onlythis_yn']?>');
            </script>
        </td><!-- 본제품만재단조 -->
        <td class="td_orp_status td_oop_status_<?=$row['oop_idx']?>" style="width:60px;">
            <select name="oop_status[<?=$row['oop_idx']?>]" oop_idx="<?=$row['oop_idx']?>" class="oop_status oop_status_<?=$row['oop_idx']?>">
                <?=$g5['set_oop_status_value_options']?>
            </select>
            <script>
                $('.oop_status_<?=$row['oop_idx']?>').val('<?=$row['oop_status']?>');
            </script>
        </td><!-- 상태 -->
        <td class="td_mng">
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='17' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./order_out_practice_form.php" id="member_add" class="btn btn_01">생산계획추가</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>



<script>
$("input[name*=_date],input[id*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소', onClose:function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('-'); } } });

var first_no = '';
var second_no = '';
$('.chkdiv_btn').on('click',function(e){
    //시프트키 또는 알트키와 클릭을 같이 눌렀을 경우
    if(e.shiftKey || e.altKey){
        //first_no정보가 없으면 0번부터 shift+click한 체크까지 선택을 한다.
        if(first_no == ''){
            first_no = 0;
        }
        //first_no정보가 있으면 first_no부터 second_no까지 체크를 선택한다.
        else{
            ;
        }
        second_no = Number($(this).attr('chk_no'));
        var key_type = (e.shiftKey) ? 'shift' : 'alt';
        //multi_chk(first_no,second_no,key_type);
        (function(first_no,second_no,key_type){
            //console.log(first_no+','+second_no+','+key_type+':func');return;
            var start_no = (first_no < second_no) ? first_no : second_no;
            var end_no = (first_no < second_no) ? second_no : first_no;
            //console.log(start_no+','+end_no);return;
            for(var i=start_no;i<=end_no;i++){
                if(key_type == 'shift')
                    $('.chkdiv_btn[chk_no="'+i+'"]').siblings('input[type="checkbox"]').attr('checked',true);
                else
                    $('.chkdiv_btn[chk_no="'+i+'"]').siblings('input[type="checkbox"]').attr('checked',false);
            }

            first_no = '';
            second_no = '';
        })(first_no,second_no,key_type);
    }
    //클릭만했을 경우
    else{
        //이미 체크되어 있었던 경우 체크를 해제하고 first_no,second_no를 초기화해라
        if($(this).siblings('input[type="checkbox"]').is(":checked")){
            first_no = '';
            second_no = '';
            $(this).siblings('input[type="checkbox"]').attr('checked',false);
        }
        //체크가 안되어 있는 경우 체크를 넣고 first_no에 해당 체크번호를 대입하고, second_no를 초기화한다.
        else{
            $(this).siblings('input[type="checkbox"]').attr('checked',true);
            first_no = $(this).attr('chk_no');
            second_no = '';
        }
    }
});

$('.shf_one').on('keyup',function(e){
    var ask = e.keyCode;
    var oop_idx = $(e.target).attr('oop_idx');
    var oop_n = $(e.target).attr('oop');

    var RegExp = /[\{\}\[\]\/?.,;:|\)*~`!^\-_+┼<>@\#$%&\'\"\\\(\=]/gi; //특수문자 패턴
    var instr = $(this).val();
    if(RegExp.test(instr)){
        $(this).val('');
        return false;
    }


    if(ask == 38){ //위쪽 화살표 눌렀을 경우
        var trobj = $(this).parent().parent();
        if(trobj.prev().find('td').find('input[oop="'+oop_n+'"]').length)
            trobj.prev().find('td').find('input[oop="'+oop_n+'"]').focus();
        return false;
    }
    else if(ask == 40){ //아래쪽 화살표를 눌렀을 경우
        var trobj = $(this).parent().parent();
        if(trobj.next().find('td').find('input[oop="'+oop_n+'"]').length)
            trobj.next().find('td').find('input[oop="'+oop_n+'"]').focus();
        return false;
    }
    else if((ask < 48 || ask > 57) && (ask < 96 || ask > 105) && (ask < 37 || ask > 40) && ask != 16 && ask != 9 && ask != 46 && ask != 8){
        $(this).val('');
        calsum(oop_idx);
        return false;
    }

    calsum(oop_idx);
});

function calsum(oop_idx){
    var oop1 = ($('input[name="oop_1['+oop_idx+']"]').val() != '') ? Number($('input[name="oop_1['+oop_idx+']"]').val()) : 0;
    var oop2 = ($('input[name="oop_2['+oop_idx+']"]').val() != '') ? Number($('input[name="oop_2['+oop_idx+']"]').val()) : 0;
    var oopt = oop1 + oop2;
    var oop_input = $('input[name="oop_count['+oop_idx+']"]');
    //과부족.lack_oop_idx = 전일재고.prev_stock_oop_idx + 생산지시수량.oop_count_oop_idx - 출하계획납품수량.oro_count_oop_idx
    var lack_cnt = Number($('.prev_stock_'+oop_idx).text()) + oopt - Number($('.oro_count_'+oop_idx).text());
    $('.lack_'+oop_idx).text(lack_cnt);
    if(!isNaN(oop_input.val().replace(/,/g,'')))
        oop_input.val(thousand_comma(oopt));
}

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function slet_input(f){
    var chk_count = 0;
    var chk_idx = [];
    //var dt_pattern = new RegExp("^(\d{4}-\d{2}-\d{2})$");
    var dt_pattern = /^(\d{4}-\d{2}-\d{2})$/;
    for(var i=0; i<f.length; i++){
        if(f.elements[i].name == "chk[]" && f.elements[i].checked){
            chk_idx.push(f.elements[i].value);
            chk_count++;
        }
    }
    if (!chk_count) {
        alert("일괄입력할 출하목록을 하나 이상 선택하세요.");
        return false;
    }

    var o_status = document.getElementById('o_status').value;

    for(var idx in chk_idx){
        //console.log(idx);continue;
        if(o_status){
            $('.td_oop_status_'+chk_idx[idx]).find('.oop_status').val(o_status);
        }
    }
}

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}


</script>

<?php
include_once ('./_tail.php');
?>
