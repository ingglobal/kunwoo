<?php
$sub_menu = "960120";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

$items = explode("^",$item);
$mms_idx = $items[0];
$shift_no = $items[1];
$item_no = $items[2];

// 일반적인 경우는 이 부분 없어도 됩니다. (부속품인 경우는 mms 소속이므로 필요함)
$mms = get_table_meta('mms', 'mms_idx', $mms_idx);
//print_r2($mms);
if (!$mms['mms_idx'])
    alert_close('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 기종 추출
$sql = " SELECT * FROM {$g5['mms_item_table']} WHERE mms_idx = '".$mms_idx."' AND mmi_no = '".$item_no."' ";
$mmi = sql_fetch($sql,1);

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_status';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명


$g5['title'] = '품질정보 추가';
include_once('./_head.sub.php');

?>
<style>
    .div_mip {color:#818181;}
    .mip_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
    .btn_mip_delete {border:solid 1px #ddd;border-radius:3px;padding:1px 4px;font-size:0.7em;margin-left:10px;}
    .div_empty {color:#818181;}
    .btn_mip_delete {cursor:pointer;}
</style>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc" style="display:none;">
        <p>기종을 관리하는 페이지입니다.</p>
    </div>

    <form name="form01" id="form01" action="./manual_output_add_form_update.php" onsubmit="return form01_check(this);" method="post" autocomplete="off">
    <input type="hidden" name="token" value="">
	<input type="hidden" name="mms_idx" value="<?=$mms_idx?>">
	<input type="hidden" name="shift_no" value="<?=$shift_no?>">
	<input type="hidden" name="item_no" value="<?=$item_no?>">
	<input type="hidden" name="time" value="<?=$time?>">
	<input type="hidden" name="idx" value="<?=$idx?>">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">설비명</th>
				<td><?=$mms['mms_name']?></td>
			</tr>
            <?php
            $ar['id'] = 'shift_no';
            $ar['name'] = '교대';
            $ar['type'] = 'text';
            $ar['value'] = $shift_no.' 교대';
            echo create_tr_input($ar);
            unset($ar);

            $ar['id'] = 'item_no';
            $ar['name'] = '기종';
            $ar['type'] = 'text';
            $ar['value'] = $item_no.'번 (기종명: '.$mmi['mmi_name'].')';
            echo create_tr_input($ar);
            unset($ar);

            if($output>0) {
                $ar['id'] = 'output';
                $ar['name'] = '생산량';
                $ar['type'] = 'text';
                $ar['value'] = number_format($output);
                echo create_tr_input($ar);
                unset($ar);
            }

            $ar['id'] = 'mst_name';
            $ar['name'] = '품질명';
            $ar['type'] = 'input';
            $ar['value'] = '';
            $ar['required'] = 'required';
            $ar['width'] = '120px';
            $ar['help'] = '품질명을 입력하세요. ex)찍힘불량, 형상불량...';
            echo create_tr_input($ar);
            unset($ar);

            $ar['id'] = 'dta_value';
            $ar['name'] = '수량';
            $ar['type'] = 'input';
            $ar['width'] = '40px';
            $ar['value'] = 0;
            $ar['unit'] = '개';
            $ar['required'] = 'required';
            echo create_tr_input($ar);
            unset($ar);

            ?>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn_close btn" value="창닫기" onclick="javascript:window.close();">
    </div>

    </form>

</div>

<script>
// 윈도우 크기 재설정
window.onload = reSize;
window.onresize = reSize;
function reSize() {
	resizeTo(520, 680);    // 여는 페이지 설정 높이 80 차이
}

$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function form01_check(f) {
    
    if (f.dta_value.value=='' || f.dta_value.value=='0') {
		alert("수량을 숫자로 입력하세요.");
		f.mms_idx.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
