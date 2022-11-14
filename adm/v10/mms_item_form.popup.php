<?php
$sub_menu = "925220";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 일반적인 경우는 이 부분 없어도 됩니다. (부속품인 경우는 mms 소속이므로 필요함)
$mms = get_table_meta('mms', 'mms_idx', $mms_idx);
//print_r2($mms);
if (!$mms['mms_idx'])
    alert('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_item';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_no'] = 0;
    ${$pre}[$pre.'_sort'] = 1;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$mms = get_table_meta('mms','mms_idx',$mmi['mms_idx']);
    $com = get_table_meta('company','com_idx',$mms['com_idx']);
    
    // 가격 & 적용시작일, the last one record is beding used.
    $sql = " SELECT * FROM {$g5['mms_item_price_table']} WHERE mmi_idx = '".$mmi['mmi_idx']."' ORDER BY mip_start_date DESC LIMIT 1 ";
    $mip = sql_fetch($sql,1);
    // print_r2($mip);
    $mmi['mmi_price'] = $mip['mip_price'];
    $mmi['mmi_start_date'] = $mip['mip_start_date'];

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = '".$pre."' AND fle_db_id = '".${$pre}[$pre.'_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
//	echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		${$pre}[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');



// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = $mms['mms_name'].' 기종 '.$html_title;
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

    <form name="form01" id="form01" action="./mms_item_form_update.popup.php" onsubmit="return form01_check(this);" method="post" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
	<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
    <input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr style="display:none;">
				<th scope="row">설비(장비)</th>
				<td>
                    <input readonly type="text" placeholder="설비ID" name="mms_idx" value="<?php echo $mms['mms_idx'] ?>" id="mms_idx"
                        required class="frm_input required" style="width:120px;">
                    <input readonly type="text" placeholder="설비명" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name"
                        class="frm_input" style="width:130px;">
                    <a href="./mms_select.php?frm=form01&com_idx=<?=$com['com_idx']?>" class="btn btn_02" id="btn_mms">검색</a>
				</td>
			</tr>
            <?php
            $ar['id'] = 'mmi_no';
            $ar['name'] = '기종번호';
            $ar['type'] = 'input';
            $ar['value'] = $mmi['mmi_no'];
            $ar['required'] = 'required';
            $ar['width'] = '40px';
            $ar['help'] = 'PLC에서 설정한 생산번호입니다.(디폴트는 0입니다.)';
            echo create_tr_input($ar);
            unset($ar);

            $ar['id'] = 'mmi_name';
            $ar['name'] = '기종명';
            $ar['type'] = 'input';
            $ar['value'] = $mmi['mmi_name'];
            $ar['required'] = 'required';
            echo create_tr_input($ar);
            unset($ar);

            $ar['id'] = 'mmi_price';
            $ar['name'] = '가격';
            $ar['type'] = 'input';
            $ar['value'] = $mmi['mmi_price'];
            $ar['value_type'] = 'number';
            echo create_tr_input($ar);
            unset($ar);

            $ar['id'] = 'mmi_start_date';
            $ar['name'] = '적용시작일';
            $ar['type'] = 'input';
            $ar['width'] = '80px';
            $ar['value'] = $mmi['mmi_start_date'];
            echo create_tr_input($ar);
            unset($ar);
            ?>
			<tr>
				<th scope="row">가격정보</th>
				<td>
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
            echo create_tr_input($ar);
            unset($ar);

            ?>
			<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>">
				<th scope="row">상태</th>
				<td>
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
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn btn_02" value="목록" onClick="self.location='./mms_item_list.popup.php?<?=$qstr?>'">
        <input type="button" class="btn_close btn" value="창닫기" onclick="javascript:opener.location.reload();window.close();">
        <input type="button" class="btn_delete btn" value="삭제" style="display:<?=(!${$pre."_idx"})?'none':'';?>;">
    </div>

    </form>

</div>

<script>
$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 설비
    $(document).on('click','#btn_mms',function(e){
        e.preventDefault();
        var href = $(this).attr("href");
        winMMS = window.open(href, "winMMS", "left=50,top=50,width=520,height=600,scrollbars=1");
        winMMS.focus();
        return false;
    });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./<?=$g5['file_name']?>_update.php?token="+token+"&w=d&<?=$pre?>_idx=<?=${$pre."_idx"}?>";
		}
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

function form01_check(f) {
    
    if (f.mms_idx.value=='') {
		alert("설비를 선택하세요.");
		f.mms_idx.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
