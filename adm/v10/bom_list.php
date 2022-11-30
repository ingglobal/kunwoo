<?php
$sub_menu = "915130";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '제품사양정보(BOM)';
include_once('./_top_menu_bom.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['bom_table']} AS bom
                    LEFT JOIN {$g5['bom_category_table']} AS bct ON bct.bct_id = bom.bct_id
                        AND bct.com_idx = '".$_SESSION['ss_com_idx']."'
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = bom.com_idx_customer
"; 

$where = array();
$where[] = " bom_status NOT IN ('delete','trash') AND bom.com_idx = '".$_SESSION['ss_com_idx']."' ";   // 디폴트 검색조건

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
        
		case ( $sfl == 'bom_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 타입
$ser_bom_type = $ser_bom_type ?: 'product';
if($ser_bom_type!='all') {  // all 인 경우는 조건이 필요없음
    $where[] = " bom_type = '".trim($ser_bom_type)."' ";
}

$ser_bom_press_type = $ser_bom_press_type?$ser_bom_press_type:'';
if($ser_bom_press_type != '') {  // all 인 경우는 조건이 필요없음
    $where[] = " bom_press_type = '{$ser_bom_press_type}'";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "bom_idx";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_bom_type='.$ser_bom_type.'&ser_bom_press_type='.$ser_bom_press_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_bom_name {text-align:left !important;width:270px;}
.td_bom_part_no, .td_com_name, .td_bom_maker
,.td_bom_items, .td_bom_items_title {text-align:left !important;}
.span_bom_price {margin-left:20px;}
.span_bit_count:before {content:'×';}
.td_bom_items {color:#818181 !important;}
.span_bom_part_no {margin-left:10px;}
.span_com_name {margin-left:20px;}
.span_com_name:before {content:'거래처:';font-size:0.8em;}
.span_bom_edit {margin-left:30px;}
.span_bom_edit a:link,.span_bom_edit a:visited {color:#3a3a3a !important;}
.span_bom_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<select name="ser_bom_type" id="ser_bom_type">
    <option value="all">전체타입</option>
    <?=$g5['set_bom_type_options']?>
</select>
<script>$('select[name="ser_bom_type"]').val('<?=$ser_bom_type?>');</script>

<select name="ser_bom_press_type" id="ser_bom_press_type">
    <option value="">프레스유형</option>
    <?=$g5['set_bom_press_type_value_options']?>
</select>
<script>$('select[name="ser_bom_press_type"]').val('<?=$ser_bom_press_type?>');</script>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
    <option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
    <option value="bom_std"<?php echo get_selected($_GET['sfl'], "bom_std"); ?>>규격</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>새로운 고객을 등록</p>
</div>


<form name="form01" id="form01" action="./bom_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="bom_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('bom_name') ?>품명</a></th>
        <th scope="col">품번</th>
        <th scope="col">규격</th>
        <th scope="col">카테고리</th>
        <th scope="col">지름(mm)</th>
        <th scope="col">길이(mm)</th>
        <th scope="col">무게(kg)</th>
        <th scope="col">단가</th>
        <th scope="col">프레스<br>카운팅유형</th>
        <th scope="col">타입</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
        if($row['bct_name']){
            $cat_tree = category_tree_array($row['bct_id']);
            $row['bct_name_tree'] = '';
            for($k=0;$k<count($cat_tree);$k++){
                $cat_str = sql_fetch(" SELECT bct_name,bct_desc FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                // $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_name'].'('.$cat_str['bct_desc'].')' : ' > '.$cat_str['bct_name'].'('.$cat_str['bct_desc'].')';
                $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_desc'] : ' > '.$cat_str['bct_desc'];
            }
        }
        $com_p = get_table_meta('company','com_idx',$row['com_idx_provider']);
        $com_c = get_table_meta('company','com_idx',$row['com_idx_customer']);
        // bom_item 에서 뽑아야 하는 제품만 (완재품, 반제품)
        if(in_array($row['bom_type'], $g5['set_bom_type_displays'])) {
            $sql1 = "SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status, com_name
                        , bit.bit_idx, bit.bom_idx_child, bit.bit_reply, bit.bit_count
                    FROM {$g5['bom_item_table']} AS bit
                        LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = bit.bom_idx_child
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = bom.com_idx_customer
                    WHERE bit.bom_idx = '".$row['bom_idx']."'
                    ORDER BY bit.bit_num DESC, bit.bit_reply
            ";
            // print_r3($sql1);
            $rs1 = sql_query($sql1,1);
            for ($j=0; $row1=sql_fetch_array($rs1); $j++) {
                // print_r2($row1);
                $len = strlen($row1['bit_reply']);
                for ($k=0; $k<$len; $k++) { $row1['nbsp'] .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; } // 들여쓰기공백
                $row['parts_list'][] = '<div class="div_part">
                                            <span class="span_bom_name">'.$row1['nbsp'].$row1['bom_name'].'</span>
                                            <span class="span_bom_part_no">'.$row1['bom_part_no'].'</span>
                                            <span class="span_com_name">'.$row1['com_name'].'</span>
                                            <span class="span_bom_price"><b>'.number_format($row1['bom_price']).'</b>원</span>
                                            <span class="span_bit_count"><b>'.$row1['bit_count'].'</b>개</span>
                                            <span class="span_bom_edit"><a href="./bom_form.php?w=u&bom_idx='.$row1['bom_idx_child'].'" target="_blank">수정</a></span>
                                        </div>';
                // 재료비합계
                $row['bom_price_material'] += $row1['bom_price']*$row1['bit_count'];
            }
            // 재료비합계표시
            $row['bom_price_material_text'] = number_format($row['bom_price_material']);
            // 재료비율
            $row['bom_profit_ratio'] = ($row['bom_price']) ? number_format(($row['bom_price_material']/$row['bom_price']*100),1).'%' : '-';
        }
        // 자재인 경우
        else {
            $row['bom_price_material_text'] = '';
        }

        $s_bom = '<a href="./bom_structure_form.php?'.$qstr.'&amp;w=u&amp;bom_idx='.$row['bom_idx'].'" class="btn btn_03">BOM</a>';
        $s_mod = '<a href="./bom_form.php?'.$qstr.'&amp;w=u&amp;bom_idx='.$row['bom_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['bom_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="bom_idx[<?php echo $i ?>]" value="<?php echo $row['bom_idx'] ?>" id="bom_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bom_name']); ?> <?php echo get_text($row['bom_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_bom_name">
            <label for="name_<?php echo $i; ?>" class="sound_only">품명</label>
            <input type="text" name="bom_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['bom_name'],250, "")); ?>" required class="tbl_input required" style="width:250px;">
        </td>
        <td class="td_bom_part_no">
            <input type="hidden" name="bom_part_no[<?php echo $i ?>]" value="<?php echo $row['bom_part_no'] ?>">
            <?=$row['bom_part_no']?>
        </td><!-- 파트넘버 -->
        <td class="td_bom_std">
            <input type="text" name="bom_std[<?php echo $i; ?>]" value="<?=$row['bom_std']?>" id="std_<?php echo $i; ?>" class="tbl_input" style="width:100%;" onClick="javascript:chk_en_ko_upper(this)">
        </td><!-- 규격 -->
        <td class="td_bct_name"><?=$row['bct_name_tree']?></td><!-- 카테고리 -->
        <td class="td_bom_pai" style="text-align:right;">
            <label for="pai_<?php echo $i; ?>" class="sound_only">지름</label>
            <input type="text" name="bom_pai[<?php echo $i; ?>]" value="<?=$row['bom_pai']?>" id="pai_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:80px;" onClick="javascript:chk_Number(this)">
        </td><!-- 지름 -->
        <td class="td_bom_length" style="text-align:right;">
            <label for="length_<?php echo $i; ?>" class="sound_only">길이</label>
            <input type="text" name="bom_length[<?php echo $i; ?>]" value="<?=$row['bom_length']?>" id="length_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:80px;" onClick="javascript:chk_Number(this)">
        </td><!-- 길이 -->
        <td class="td_bom_weight" style="text-align:right;">
            <label for="weight_<?php echo $i; ?>" class="sound_only">무게</label>
            <input type="text" name="bom_weight[<?php echo $i; ?>]" value="<?=$row['bom_weight']?>" id="weight_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:80px;" onClick="javascript:chk_float_Number(this)">
        </td><!-- 무게 -->
        <td class="td_bom_price">
            <label for="price_<?php echo $i; ?>" class="sound_only">단가</label>
            <input type="text" name="bom_price[<?php echo $i; ?>]" value="<?=number_format($row['bom_price'])?>" id="price_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:100px;" onClick="javascript:chk_Number(this)">
        </td>
        <td class="td_bom_press_type">
            <lable for="press_type_<?php echo $i; ?>">
                <select name="bom_press_type[<?php echo $i; ?>]" id="press_type_<?php echo $i; ?>">
                    <?=$g5['set_bom_press_type_value_options']?>
                </select>
            </lable>
            <script>
                $('#press_type_<?php echo $i; ?>').val('<?=$row['bom_press_type']?>');
            </script>
        </td>
        <td class="td_bom_type"><?=$g5['set_bom_type_value'][$row['bom_type']]?></td><!-- 타입 -->
        <td class="td_mng">
            <?=(($row['bom_type']!='material')?$s_bom:'')?><!-- 자재가 아닌 경우만 BOM 버튼 -->
			<?=$s_mod?>
		</td>
    </tr>
    <tr class="<?php echo $bg; ?>" tr_id="<?=$row['bom_idx']?>" style="display:<?=(!in_array($row['bom_type'],$g5['set_bom_type_displays']))?'none':''?>">
        <td>
        </td>
        <td class="td_bom_items_title">
            자재 (구성품)
        </td>
        <td class="td_bom_items" colspan="11">
            <?php
            if(is_array($row['parts_list'])) {
                echo implode(" ",$row['parts_list']);
            }
            else {
                echo '구성품 없음';
            }
            ?>
        </td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='12' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (false){ //(!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="javascript:" id="btn_excel_upload" class="btn btn_02" style="margin-right:50px;">엑셀등록</a>
    <?php } ?>
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./bom_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./bom_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>


<script>
// 엑셀등록 버튼
$( "#btn_excel_upload" ).on( "click", function() {
    $( "#modal01" ).dialog( "open" );
});
$( "#modal01" ).dialog({
    autoOpen: false
    , position: { my: "right-10 top-10", of: "#btn_excel_upload"}
});


// 마우스 hover 설정
$(".tbl_head01 tbody tr").on({
    mouseenter: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
        
    },
    mouseleave: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    }    
});

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=bom_price], input[name^=bom_count], input[name^=bom_lead_time], input[name^=bom_min_cnt]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

//영문숫자 대문자만 입력
function chk_en_upper(obj){
    $(obj).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9A-Za-z\_\.\-\(\)\s]/g,""));
        $(this).val($(this).val().toUpperCase());
    });
}

//영문숫자 대문자 한글만 입력
function chk_en_ko_upper(obj){
    $(obj).keyup(function(){
        $(this).val($(this).val().replace(/[^ㄱ-ㅎ가-힣0-9A-Za-z\_\.\-\(\)\s]/g,""));
        $(this).val($(this).val().toUpperCase());
    });
}

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

// 소숫점 숫자만 입력
function chk_float_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9\.]/g,""));
    });
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

function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
