<?php
$sub_menu = "945110";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '자재재고관리(번들넘버별)';
include_once('./_head.php');
include_once('./_top_menu_mtr.php');
// echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['material_table']} AS mtr
                    LEFT JOIN {$g5['bom_table']} AS bom ON mtr.bom_part_no = bom.bom_part_no
                    LEFT JOIN {$g5['company_table']} AS com ON bom.com_idx_provider = com.com_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " mtr_status NOT IN ('delete','del','trash','cancel','used') ";
$where[] = " mtr.com_idx = '".$_SESSION['ss_com_idx']."' ";
$where[] = " mtr.mtr_type = 'material' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mtr.bom_part_no' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mtr.mtr_lot' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mtr.mtr_heat' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'mtr.mtr_bundle' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($mtr_input2_date) $where[] = " mtr_input_date = '".$mtr_input2_date."' ";
if($mtr2_status) $where[] = " mtr_status = '".$mtr2_status."' ";
// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "mtr_idx";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 50;//$config['cf_page_rows'];//1000
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
//print_r3($sql);
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:128px;z-index:100;}
#ui-datepicker-div{z-index:100 !important;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
#top_form:after{display:block;visibility:hidden;clear:both;content:'';}
#top_form #fsearch{float:left;}
#top_form #finput{float:right;margin:10px 0;}
.td_mtr_name,.td_mtr_lot,.td_mtr_heat,.td_mtr_bundle {text-align:left !important;}
.td_mtr_part_no, .td_com_name, .td_mtr_maker
,.td_mtr_items, .td_mtr_items_title {text-align:left !important;}
.td_mtr_weight
,.td_mtr_price
,.td_mtr_sum_price{text-align:right !important;}
.span_mtr_price {margin-left:20px;}
.span_mtr_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.loading{display:inline-block;}
.loading_hide{display:none;}
label[for="mtr_input2_date"]{position:relative;}
label[for="mtr_input2_date"] i{position:absolute;top:-10px;right:0px;z-index:2;cursor:pointer;}
.slt_label{position:relative;}
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
<div id="top_form">
    <form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <option value="mtr.mtr_bundle"<?php echo get_selected($_GET['sfl'], "mtr_bundle"); ?>>번들넘버</option>
            <option value="mtr.mtr_heat"<?php echo get_selected($_GET['sfl'], "mtr_heat"); ?>>히트넘버</option>
            <option value="mtr.mtr_lot"<?php echo get_selected($_GET['sfl'], "mtr_lot"); ?>>Lot넘버</option>
            <option value="mtr_name"<?php echo get_selected($_GET['sfl'], "mtr_name"); ?>>품명</option>
            <option value="mtr.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
            <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom_std"); ?>>규격</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <select name="mtr2_status" id="mtr2_status">
            <option value="">-상태선택-</option>
            <?=$g5['set_mtr_status_value_options']?>
        </select>
        <label for="mtr_input2_date"><strong class="sound_only">입고일 필수</strong>
        <i class="fa fa-times" aria-hidden="true"></i>
        <input type="text" name="mtr_input2_date" value="<?php echo $mtr_input2_date ?>" placeholder="입고일" id="mtr_input_date" readonly class="frm_input readonly" style="width:95px;">
        </label>
        <script>
        <?php
        $sfl = ($sfl == '') ? 'mtr.mtr_bundle' : $sfl;
        ?>
        $('#sfl').val('<?=$sfl?>');
        $('#mtr2_status').val('<?=$mtr2_status?>');
        </script>
        <input type="submit" class="btn_submit" value="검색">
    </form>
</div>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>새로운 자재를 엑셀파일로 등록/수정하는 페이지입니다.</p>
    <p><b style="color:skyblue;">엑셀파일</b>로 등록할때는 반드시 <b style="color:red;">단일시트로</b> 작성해서 등록해 주시기 바랍니다.</p>
    <p>목록중에 중간에 내용이 비어있는 라인이 있으면 안됩니다.</p>
    <p>데이터의 셀위치를 함부로 변경하시면 등록이 안되오니 주의해 주시기 바랍니다.</p>
</div>

<div class="select_input">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="" class="slt_label">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_mtr_status_options']?>
                <?php if($is_admin){ ?>
                <option value="trash">삭제</option>
                <?php } ?>
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
<form name="form01" id="form01" action="./material_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="file_name" value="<?=$g5['file_name']?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="mtr_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col"><?php echo subject_sort_link('mtr_name') ?>품명</a></th>
        <th scope="col">품목코드</th>
        <th scope="col">규격</th>
        <th scope="col">입고무게(kg)</th>
        <th scope="col">입고단가</th>
        <th scope="col">입고금액</th>
        <th scope="col">Lot</th>
        <th scope="col">히트넘버</th>
        <th scope="col">번들넘버</th>
        <th scope="col">입고일</th>
        <th scope="col">상태</th>
        <!-- <th scope="col">관리</th> -->
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        // print_r2($row);

        $s_mod = '<a href="./material_form.php?'.$qstr.'&amp;w=u&amp;mtr_idx='.$row['mtr_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mtr_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="mtr_idx[<?php echo $row['mtr_idx'] ?>]" value="<?php echo $row['mtr_idx'] ?>" id="mtr_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mtr_name']); ?> <?php echo get_text($row['mtr_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['mtr_idx'] ?>" id="chk_<?php echo $i ?>">
            <div class="chkdiv_btn" chk_no="<?=$i?>"></div>
        </td>
        <td class="td_mtr_idx"><?=$row['mtr_idx']?></td><!-- ID -->
        <td class="td_mtr_name"><?=$row['mtr_name']?></td><!-- 품명 -->
        <td class="td_mtr_part_no"><?=$row['bom_part_no']?></td><!-- 파트넘버 -->
        <td class="td_mtr_std"><?=$row['bom_std']?></td><!-- 규격 -->
        <td class="td_mtr_weight"><?=number_format($row['mtr_weight'])?></td><!-- 무게 -->
        <td class="td_mtr_price"><?=number_format($row['mtr_price'])?></td><!-- 단가 -->
        <td class="td_mtr_sum_price"><?=number_format($row['mtr_sum_price'])?></td><!-- 금액 -->
        <td class="td_mtr_lot"><?=$row['mtr_lot']?></td><!-- Lot넘버 -->
        <td class="td_mtr_heat"><?=$row['mtr_heat']?></td><!-- 히트넘버 -->
        <td class="td_mtr_bundle"><?=$row['mtr_bundle']?></td><!-- 번들넘버 -->
        <td class="td_mtr_input_date"><?=$row['mtr_input_date']?></td><!-- 입고일 -->
        <td class="td_mtr_status td_mtr_status_<?=$row['mtr_idx']?>" style="width:180px;">
            <input type="hidden" name="mtr_status[<?php echo $row['mtr_idx'] ?>]" class="mtr_status_<?php echo $row['mtr_idx'] ?>" value="<?php echo $row['mtr_status']?>">
            <input type="text" value="<?php echo $g5['set_mtr_status'][$row['mtr_status']]?>" readonly class="tbl_input readonly mtr_status_name_<?php echo $row['mtr_idx'] ?>" style="width:170px;text-align:center;">
        </td><!-- 상태 -->
        <!-- <td class="td_mng">
            <?=($row['mtr_type']!='material')?$s_bom:''?>
			<?=$s_mod?>
		</td> -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='13' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="javascript:" id="btn_excel_upload" class="btn btn_02" style="margin-right:50px;">엑셀등록</a>
    <?php } ?>
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button2" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button2" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <!--
    <a href="./material_form.php" id="member_add" class="btn btn_01">추가하기</a>
    -->
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./material_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <input type="hidden" name="file_name" value="<?=$g5['file_name']?>">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
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
                <p class="loading loading_hide" style="padding-left:10px;">
                    <img src="<?=G5_USER_ADMIN_IMG_URL?>/loading_small.gif">
                    <b style="color:yellow;padding-left:10px;">실행중...</b>
                </p>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>


<script>
$("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

$('label[for="mtr_input_date"] i').on('click',function(){
    $(this).siblings('input').val('');
});

// 제품찾기 버튼 클릭
$("#bom_name").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('link');
    winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=600,scrollbars=1");
    winBomSelect.focus();
});
//입고날짜선택에서 X버튼 클릭시 값제거
$('label[for="mtr_input2_date"] i').on('click',function(){
    $(this).siblings('input').val('');
});
// 엑셀등록 버튼
$( "#btn_excel_upload" ).on( "click", function() {
    $( "#modal01" ).dialog( "open" );
});
$( "#modal01" ).dialog({
    autoOpen: false
    , position: { my: "right-10 top-10", of: "#btn_excel_upload"}
    , width: 350
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
$(document).on( 'keyup','input[name^=mtr_price], input[name^=mtr_count], input[name^=mtr_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

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
        alert("일괄입력할 자재재고목록을 하나 이상 선택하세요.");
        return false;
    }

    var o_status = document.getElementById('o_status').value;
    var o_status_name = $('#o_status').find('option[value="'+o_status+'"]').text();

    for(var idx in chk_idx){
        //console.log(idx);continue;
        if(o_status){
            $('.td_mtr_status_'+chk_idx[idx]).find('input[type="hidden"]').val(o_status);
            $('.td_mtr_status_'+chk_idx[idx]).find('input[type="text"]').val(o_status_name);
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

    $('<input type="hidden" name="act_button" value="'+document.pressed+'">').prependTo('#form01');

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

    if(!confirm("대량데이터처리이므로 시간이 1분이상 소요될수 있습니다.\n실행하는 동안 창을 닫거나, 다른버튼을 클릭해서는 안됩니다.\n작업을 진행하시겠습니까?")){
        return false;
    }
    $('.loading').removeClass('loading_hide');
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
