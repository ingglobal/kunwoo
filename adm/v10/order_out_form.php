<?php
$sub_menu = "920110";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$sql = " SELECT oro.*,
                ord.ord_date,
                ori.bom_idx,
                com.com_name, 
                bom.bct_id,
                bom.bom_name,
                bom.bom_part_no,
                bom.bom_std
            FROM {$g5['order_out_table']} AS oro
            LEFT JOIN {$g5['order_table']} AS ord ON oro.ord_idx = ord.ord_idx
            LEFT JOIN {$g5['company_table']} AS com ON oro.com_idx_customer = com.com_idx
            LEFT JOIN {$g5['order_item_table']} AS ori ON oro.ori_idx = ori.ori_idx
            LEFT JOIN {$g5['bom_table']} AS bom ON ori.bom_idx = bom.bom_idx
        WHERE oro_idx = '{$oro_idx}'
";
// echo $sql;
$row = sql_fetch($sql);
//출하처 정보
$ship = get_table_meta('company', 'com_idx', $row['com_idx_shipto']);

$readonly = ' readonly';
$required = ' required';

$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    
}
else if ($w == 'u' || $w == 'c') {

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');




// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$row[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '출하정보 '.$html_title;
include_once ('./_head.php');
//print_r2($g5);exit;
// print_r3($row);
?>
<style>
.bop_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
.btn_bop_delete {color:#0c55a0;cursor:pointer;margin-left:20px;}
a.btn_price_add {color:#3a88d8 !important;cursor:pointer;}
#oro_ex{}
#oro_ex:after{display:block;visibility:hidden;clear:both;content:'';}
#oro_ex li{float:left;margin-right:10px;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="schrows" value="<?php echo $schrows ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="oro_idx" value="<?php echo $oro_idx ?>">
<input type="hidden" name="oro_1" value="<?php echo $row['oro_1'] ?>">
<input type="hidden" name="oro_2" value="<?php echo $row['oro_2'] ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p><span style="color:red;">[조정필요]빨간색 깜빡임</span>은 수주상품의 총갯수와 전체 납품 수량이 일치하지 않다는 의미 입니다.(갯수를 맞춰 주셔야 합니다.)</p> 
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
    </colgroup>
	<tbody>
	<tr>
        <th scope="row">제품</th>
        <td>
            <!-- <a href="javascript:" link="./order_out_bom_select.php?file_name=<?php ;//echo $g5['file_name']?>" class="btn btn_02" id="btn_bom">제품찾기</a> -->
            <input type="hidden" name="ori_idx" id="ori_idx" value="<?=$row['ori_idx']?>">
            <input type="hidden" name="bom_idx" id="bom_idx" value="<?=$row['bom_idx']?>">
            <input type="text" name="bom_name" id="bom_name" value="<?php echo $row['bom_name'] ?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>"> /
            <input type="text" id="bom_part_no" value="<?php echo $row['bom_part_no'] ?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>"> / 
            <input type="text" id="bom_std" value="<?php echo $row['bom_std'] ?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>"> 
        </td>
        <th scope="row">고객처</th>
        <td>
            <a href="./customer_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_customer">고객처찾기</a>
            <input type="hidden" name="com_idx_customer" id="com_idx_customer" value="<?=$row['com_idx_customer']?>"><!-- 거래처번호 -->
            <input type="text" name="com_name" id="com_name" value="<?php echo $row['com_name'] ?>" class="frm_input<?=($required.$readonly)?>"<?=($required.$readonly)?>>
        </td>
    </tr>
    <tr>
        <th scope="row">수주(수주일/ID)</th>
        <td>
            <!-- <a href="./order_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_order">수주선택</a> -->
            <input type="text" name="ord_date" id="ord_date" placeholder="수주날짜" value="<?=$row['ord_date']?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:90px;"> /
            <input type="text" name="ord_idx" id="ord_idx" value="<?=$row['ord_idx']?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>" style="width:80px;">
        </td>
        <th scope="row">출하처</th>
        <td>
            <a href="./customer_shipto_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_shipto_customer">출하처찾기</a>
            <input type="hidden" name="com_idx_shipto" id="com_idx_shipto" value="<?=$row['com_idx_shipto']?>"><!-- 출하처번호 -->
            <input type="text" name="com_name2" id="com_name2" value="<?php echo $ship['com_name'] ?>" class="frm_input<?=$required.$readonly?>"<?=$required.$readonly?>>
        </td>
    </tr>
    <tr>
        <th scope="row">출하계획(수량)</th>
        <td>
            <ul id="oro_ex">
                <li>
                    <label>주간</label><br>
                    <input type="text" name="oro_1" id="oro_1" value="<?php echo $row['oro_1']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)"><span style="position:relative;top:3px;">개</span>
                </li>
                <li style="margin-left:20px;">
                    <label>야간</label><br>
                    <input type="text" name="oro_2" id="oro_2" value="<?php echo $row['oro_2']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)"><span style="position:relative;top:3px;">개</span>
                </li>
            </ul>
        </td>
        <th scope="row">출하수량(합계)</th>
        <td>
            <input type="text" name="oro_count" id="oro_count" value="<?php echo $row['oro_count']; ?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:80px;text-align:right;"><span style="position:relative;top:3px;">개</span>
        </td>
    </tr>
    <tr>
        <th scope="row">출하예정일</th>
        <td>
            <input type="text" name="oro_date_plan" id="oro_date_plan" value="<?=(($row['oro_date_plan'])?$row['oro_date_plan']:'0000-00-00')?>"<?=$readonly?> class="tbl_input<?=$readonly?>" style="width:90px;background:#333 !important;text-align:center;">
        </td>
        <th scope="row">출하확정일</th>
        <td>
            <input type="text" name="oro_date" id="oro_date" value="<?=(($row['oro_date'])?$row['oro_date']:'0000-00-00')?>"<?=$readonly?> class="tbl_input<?=$readonly?>" style="width:90px;background:#333 !important;text-align:center;">
        </td>
    </tr>
    <tr>
        <th scope="row">상태</th>
        <td colspan="3">
            <select name="oro_status" id="oro_status"
                <?php if (auth_check($auth[$sub_menu],"w",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_oro_status_options']?>
            </select>
            
            <script>$('select[name="oro_status"]').val('<?=$row['oro_status']?>');</script>
        </td>
    </tr>
    <tr>
        <th scope="row">메모</th>
        <td colspan="3">
            <textarea name="oro_memo" id="oro_memo" rows="5">
                <?=$row['oro_memo']?>
            </textarea>
        </td>
    </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./order_out_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
var w = '<?=$w?>';
$(function() {
    $("input[name$=_date], #oro_date_plan").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 수주찾기 버튼 클릭
    $("#btn_order").click(function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        winOrderSelect = window.open(href, "winOrderSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winOrderSelect.focus();
    });

    // 제품찾기 버튼 클릭
    $("#btn_bom").click(function(e) {
        e.preventDefault();
        var ord_idx = $('input[name="ord_idx"]').val();
        if(!ord_idx){
            alert('먼저 수주번호를 선택해 주세요.');
            $('#ord_idx').focus();
            return false;
        }
        var href = $(this).attr('link')+'&ord_idx='+ord_idx;
        winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=600,scrollbars=1");
        winBomSelect.focus();
    });

    // 거래처찾기 버튼 클릭
	$("#btn_customer").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winCustomerSelect.focus();
	});

    // 출하처찾기 버튼 클릭
	$("#btn_shipto_customer").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winCustomerSelect.focus();
	});

    // 불량타입 숨김,보임
	$("input[name=oro_defect]").click(function(e) {
        if( $(this).val() == 1 ) {
            $('#oro_defect_type').show();
        }
        else
           $('#oro_defect_type').hide();
	});

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price], #bom_moq, #bom_lead_time',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

// 숫자만 입력, 합산계산입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
        var oro_sum = 0;
        $('.oro_ex').each(function(){
            oro_sum += Number($(this).val());
        }); 
        $('#oro_count').val(oro_sum);
    });
}

// 숫자만 입력
function only_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function form01_submit(f) {

    if(!f.oro_count.value){
        alert('출하수량을 입력해 주세요');
        f.oro_count.focus();
        return false;
    }

    if(!f.oro_date_plan.value){
        alert('출하예정일을 입력해 주세요.');
        f.oro_date_plan.focus();
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
