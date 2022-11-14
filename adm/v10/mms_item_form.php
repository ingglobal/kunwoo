<?php
// 이건 구지 안 해도 될 듯 해서 일단 중지합니다.
$sub_menu = "925220";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_item';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    $mms = get_table_meta('mms','mms_idx',$mmi['mms_idx']);
    // print_r3($mms);

    // 가격 & 적용시작일, the last one record is beding used.
    $sql = " SELECT * FROM {$g5['mms_item_price_table']} WHERE mmi_idx = '".$mmi['mmi_idx']."' ORDER BY mip_start_date DESC LIMIT 1 ";
    $mip = sql_fetch($sql,1);
    // print_r2($mip);
    $mmi['mmi_price'] = $mip['mip_price'];
    $mmi['mmi_start_date'] = $mip['mip_start_date'];

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


    // 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '기종 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

?>
<style>
    .mms_name {margin-left:15px;}
    .div_mip {color:#818181;}
    .mip_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
    .btn_mip_delete {border:solid 1px #ddd;border-radius:3px;padding:1px 4px;font-size:0.7em;margin-left:10px;}
    .div_empty {color:#818181;}
    .btn_mip_delete {cursor:pointer;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="ser_cod_group" value="<?php echo $ser_cod_group ?>">
<input type="hidden" name="ser_cod_type" value="<?php echo $ser_cod_type ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>각종 고유번호(업체번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
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
		<th scope="row">설비선택</th>
		<td colspan="3">
            <input type="hidden" name="mms_idx" value="<?=$mms['mms_idx']?>"><!-- 설비번호 -->
			<input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required readonly>
            <a href="./mms_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mms">설비찾기</a>
		</td>
    </tr>
	<tr> 
        <?php
        $ar['id'] = 'mmi_no';
        $ar['name'] = '기종번호';
        $ar['type'] = 'input';
        $ar['value'] = $mmi['mmi_no'];
        $ar['required'] = 'required';
        $ar['help'] = 'PLC에서 설정한 생산번호입니다.(디폴트는 0입니다.)';
        $ar['width'] = '40px';
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'mmi_name';
        $ar['name'] = '기종명';
        $ar['type'] = 'input';
        $ar['value'] = $mmi['mmi_name'];
        $ar['required'] = 'required';
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'mmi_price';
        $ar['name'] = '가격';
        $ar['type'] = 'input';
        $ar['value'] = $mmi['mmi_price'];
        $ar['value_type'] = 'number';
        $ar['form_script'] = 'onClick="javascript:chk_Number(this)"';
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'mmi_start_date';
        $ar['name'] = '적용시작일';
        $ar['type'] = 'input';
        $ar['width'] = '80px';
        $ar['value'] = $mmi['mmi_start_date'];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <tr>
        <th scope="row">가격정보</th>
        <td colspan="3">
            <?php
            $sql = " SELECT * FROM {$g5['mms_item_price_table']} WHERE mmi_idx = '".$mmi['mmi_idx']."' ORDER BY mip_start_date ";
            // echo $sql.'<br>';
            $rs = sql_query($sql,1);
            for($i=0;$row=sql_fetch_array($rs);$i++) {
                // print_r2($row);
                echo '  <div class="div_mip">'
                            .number_format($row['mip_price']).' 원 <span class="mip_price">'.$row['mip_start_date'].'~</span>
                            <span class="btn_mip_delete" mip_idx="'.$row['mip_idx'].'">삭제</span>
                        </div>';
            }
            if(!$i) {
                echo '<div class="div_empty">가격 정보를 입력하세요.</div>';
            }
            ?>
        </td>
    </tr>
    <?php
    $ar['id'] = 'mmi_memo';
    $ar['name'] = '메모';
    $ar['type'] = 'textarea';
    $ar['value'] = $mmi['mmi_memo'];
    $ar['colspan'] = 3;
    echo create_tr_input($ar);
    unset($ar);
    ?>
    <tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>">
        <th scope="row">상태</th>
        <td colspan="3">
            <select name="<?=$pre?>_status" id="<?=$pre?>_status"
                <?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_status_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winShftSelect = window.open(href, "winShftSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winShftSelect.focus();
	});

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	// 가격삭제
	$(document).on('click','.btn_mip_delete',function(e) {
		e.preventDefault();
		var mip_idx = $(this).attr('mip_idx');

		if(confirm('가격 정보를 삭제하시겠습니까?')) {

			//-- 디버깅 Ajax --//
			$.ajax({
				url:g5_user_admin_url+'/ajax/mms_item.php',
				data:{"aj":"del","mip_idx":mip_idx},
				dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
			//$.getJSON(g5_user_admin_url+'/ajax/mms_item.json.php',{"aj":"del","mip_idx":mip_idx},function(res) {
				//alert(res.sql);
				if(res.result == true) {
                    // self.location.reload();
                    $('span[mip_idx='+mip_idx+']').closest('div.div_mip').remove();
				}
				else {
					alert(res.msg);
				}

				}, error:this_ajax_error	//<-- 디버깅 Ajax --//
			});
		}
	});

});

// 숫자만 입력
function chk_Number(object){
$(object).keyup(function(){
      $(this).val($(this).val().replace(/[^0-9|-]/g,""));
  });
}

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
