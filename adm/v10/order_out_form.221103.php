<?php
$sub_menu = "920110";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'order_out';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_start_date'] = G5_TIME_YMD;
    ${$pre}[$pre.'_status'] = 'pending';

    if($ord_idx){
        $ord_sql = " SELECT com_idx_customer FROM {$g5['order_table']} WHERE ord_idx = '{$ord_idx}' ";
        $ord = sql_fetch($ord_sql);
        $com = get_table_meta('company', 'com_idx', $ord['com_idx_customer']);
        $com2 = get_table_meta('company', 'com_idx', $ord['com_idx_customer']);
        $ori['ord_idx'] = $ord_idx;
    }
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // print_r3(${$pre});
	$ori = get_table_meta('order_item', 'ori_idx', ${$pre}['ori_idx']);
	$com = get_table_meta('company', 'com_idx', ${$pre}['com_idx_customer']);
	$com2 = get_table_meta('company', 'com_idx', ${$pre}['com_idx_shipto']);
	$bom = get_table_meta('bom', 'bom_idx', $ori['bom_idx']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');




// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '출하정보 '.$html_title;
include_once ('./_head.php');
//print_r2($g5);exit;
//print_r3(${$pre});
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
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="<?=$pre?>_1" value="<?php echo ${$pre}['oro_1'] ?>">
<input type="hidden" name="<?=$pre?>_2" value="<?php echo ${$pre}['oro_2'] ?>">
<input type="hidden" name="<?=$pre?>_3" value="<?php echo ${$pre}['oro_3'] ?>">
<input type="hidden" name="<?=$pre?>_4" value="<?php echo ${$pre}['oro_4'] ?>">
<input type="hidden" name="<?=$pre?>_5" value="<?php echo ${$pre}['oro_5'] ?>">
<input type="hidden" name="<?=$pre?>_6" value="<?php echo ${$pre}['oro_6'] ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p><span style="color:red;">[조정필요]빨간색 깜빡임</span>은 수주상품의 총갯수와 전체 납품 수량이 일치하지 않다는 의미 입니다.(갯수를 맞춰 주셔야 합니다.)</p> 
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
    </colgroup>
	<tbody>
	<tr>
        <th scope="row">제품</th>
        <td>
            <input type="hidden" name="ori_idx" value="<?=$ori['ori_idx']?>">
            <input type="hidden" name="bom_idx" value="<?=$ori['bom_idx']?>">
            <input type="text" name="bom_name" value="<?php echo $bom['bom_name'] ?>" id="bom_name" class="frm_input required readonly" required readonly>
            <?php if($w == ''){ ?>
            <a href="javascript:" link="./order_out_bom_select.php?file_name=<?php echo $g5['file_name']?>&w=<?=$w?>&ord_idx=<?=$ori['ord_idx']?>" class="btn btn_02" id="btn_bom">제품찾기</a>
            <?php } ?>
        </td>
        <th scope="row">고객처</th>
		<td>
            <a href="./customer_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_customer">고객처찾기</a>
            <input type="hidden" name="com_idx_customer" value="<?=${$pre}['com_idx_customer']?>"><!-- 거래처번호 -->
			<input type="text" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" class="frm_input readonly" readonly>
            <button type="button" class="btn btn_04 com_cancel">취소</button>
		</td>
    </tr>
    <tr>
        <th scope="row">수주(수주일/ID)</th>
        <td>
            <?php if($w == ''){ ?>
            <input type="text" name="ord_idx" value="<?=$oro['ord_idx']?>" readonly class="frm_input required readonly" style="width:80px;">
            <a href="./order_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_order">수주찾기</a>
            <?php } else { ?>
            <input type="text" name="ord_idx" value="<?=$oro['ord_idx']?>" readonly required class="frm_input readonly required" style="width:80px;">
            <?php } ?>
        </td>
        <?php
        /*
        $ar['id'] = 'oro_count';
        $ar['name'] = '출하수량';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['unit'] = '개';
        $ar['value_type'] = 'number';
        $ar['form_script'] = 'onClick="javascript:chk_Number(this)"';
        echo create_td_input($ar);
        unset($ar);
        */
        //ori_idx상품의 갯수와 ori_idx해당 전체 oro_idx.들의 합을 계산
        $ori_sql = sql_fetch(" SELECT ori_count FROM {$g5['order_item_table']} WHERE ord_idx = '{$ori['ord_idx']}' AND ori_idx = '{$ori['ori_idx']}' AND ori_status NOT IN('trash','delete','del','cancel') ");
        $oro_sql = sql_fetch(" SELECT SUM(oro_count) AS total_cnt FROM {$g5['order_out_table']} WHERE ord_idx = '{$ori['ord_idx']}' AND ori_idx = '{$ori['ori_idx']}' AND oro_status NOT IN('trash','delete','del','cancel') ");
        $cnt_mod = ($ori_sql['ori_count'] != $oro_sql['total_cnt']) ? 'txt_redblink' : '';
        
        ?>
        <th scope="row">출하수량</th>
        <td>
            <input type="text" name="oro_count" id="oro_count" value="<?php echo $oro['oro_count']; ?>" readonly class="frm_input readonly" style="width:80px;text-align:right;">개
        </td>
    </tr>
    <tr>
        <th scope="row">출하계획</th>
        <td colspan="3">
            <ul id="oro_ex">
                <li>
                    <label>주간(09:00)</label><br>
                    <input type="text" name="oro_1" id="oro_1" value="<?php echo $oro['oro_1']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)">
                </li>
                <li>
                    <label>주간(12:00)</label><br>
                    <input type="text" name="oro_2" id="oro_2" value="<?php echo $oro['oro_2']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)">
                </li>
                <li>
                    <label>주간(15:00)</label><br>
                    <input type="text" name="oro_3" id="oro_3" value="<?php echo $oro['oro_3']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)">
                </li>
                <li>
                    <label>야간(17:00)</label><br>
                    <input type="text" name="oro_4" id="oro_4" value="<?php echo $oro['oro_4']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)">
                </li>
                <li>
                    <label>야간(19:00)</label><br>
                    <input type="text" name="oro_5" id="oro_5" value="<?php echo $oro['oro_5']; ?>" class="frm_input oro_ex" style="width:80px;text-align:right;" onclick="javascript:chk_Number(this)">
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'oro_date_plan';
        $ar['name'] = '출하예정일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'oro_date';
        $ar['name'] = '출하일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <tr>
        <th scope="row">출하처</th>
		<td>
            <input type="hidden" name="com_idx_shipto" value="<?=${$pre}['com_idx_shipto']?>"><!-- 출하처번호 -->
			<input type="text" name="com_name2" value="<?php echo $com2['com_name'] ?>" id="com_name2" class="frm_input required readonly" required readonly>
            <a href="./customer_shipto_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_shipto_customer">출하처찾기</a>
		</td>
        <th scope="row">상태</th>
        <td>
            <select name="<?=$pre?>_status" id="<?=$pre?>_status"
                <?php if (auth_check($auth[$sub_menu],"w",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_oro_status_options']?>
            </select>
            
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
    <?php
    $ar['id'] = 'oro_memo';
    $ar['name'] = '메모';
    $ar['type'] = 'textarea';
    $ar['value'] = ${$pre}['oro_memo'];
    $ar['colspan'] = 3;
    echo create_tr_input($ar);
    unset($ar);
    ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
var w = '<?=$w?>';
$(function() {
    $("input[name$=_date], #oro_date_plan").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 거래처찾기 버튼 클릭
	$("#btn_customer").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winCustomerSelect.focus();
	});

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
        var href = $(this).attr('link');
        if(w == ''){
            var ord_idx = $('input[name="ord_idx"]').val();
            if(!ord_idx){
                alert('수주번호를 입력해 주세요.');
                return false;
            }
            href += ord_idx
        }
		winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=600,scrollbars=1");
        winBomSelect.focus();
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
