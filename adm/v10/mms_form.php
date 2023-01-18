<?php
$sub_menu = "917100";
include_once('./_common.php');
if (!$is_admin)
	auth_check($auth[$sub_menu],'r');

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '설비정보 '.$html_title;
//include_once('./_top_menu_mms.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    $mms['com_idx'] = $_SESSION['ss_com_idx'];
	$com = get_table_meta('company','com_idx',$mms['com_idx']);
	$mms['mms_sort'] = 1;
    $mms['mms_set_output'] = $mms['mms_set_error'] = 'shift';
    $mms['mms_data_url'] = 'kunwoo.epcs.co.kr/device/json';
    $mms['mms_output_yn'] = 'Y';
    $mms['mms_status'] = 'ok';
	$html_title = '추가';
    
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$mms = get_table_meta('mms','mms_idx',$mms_idx);
	if (!$mms['mms_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',$mms['com_idx']);
	$imp = get_table_meta('imp','imp_idx',$mms['imp_idx']);
    $mmg = get_table_meta('mms_group','mmg_idx',$mms['mmg_idx']);
    // print_r2($mms);
	
	$html_title = '수정';
	
	$mms['mms_price'] = number_format($mms['mms_price']);
    $mms['mms_set_output'] = $mms['mms_set_output'] ?: 'shift';
    $mms['mms_set_error'] = $mms['mms_set_error'] ?: 'shift';

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'mms' AND fle_db_id = '".$mms['mms_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
//	echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		$mms[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		$mms[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		$mms[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		$mms[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}
	
    
	// 대표이미지
	$fle_type3 = "mms_img";
	if($mms[$fle_type3][0]['fle_name']) {
		$mms[$fle_type3][0]['thumbnail'] = thumbnail($mms[$fle_type3][0]['fle_name'], 
						G5_PATH.$mms[$fle_type3][0]['fle_path'], G5_PATH.$mms[$fle_type3][0]['fle_path'],
						45, 45, 
						false, true, 'center', true, $um_value='80/0.5/3'
		);	// is_create, is_crop, crop_mode
	}
	else {
		$mms[$fle_type3][0]['thumbnail'] = 'default.png';
		$mms[$fle_type3][0]['fle_path'] = '/data/'.$fle_type3;
	}
	//$mms[$fle_type3][0]['thumbnail_img'] = '<img src="'.G5_URL.$mms[$fle_type3][0]['fle_path'].'/'.$mms[$fle_type3][0]['thumbnail'].'" width="45" height="45">';
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$mms[$check_array[$i]]} = ' checked';
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
//add_javascript(G5_USER_ADMIN_URL.'/js/mms_form.js', 0);
//print_r3($g5);
?>

<form name="form01" id="form01" action="./mms_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="mms_idx" value="<?php echo $mms_idx; ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>설비와 연결된 iMP 및 장비그룹을 선택해 주셔야 합니다.</p>
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
	<tr style="display:none;">
		<th scope="row">업체명</th>
		<td colspan="3">
            <input readonly type="hidden" placeholder="업체ID" name="com_idx" value="<?php echo $mms['com_idx'] ?>" id="com_idx"
                    required class="frm_input required" style="width:120px;<?=$style_company?>">
            <input readonly type="text" placeholder="업체명" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./company_select.popup.php?frm=form01&d=<?php echo $d;?>" class="btn btn_02" id="btn_com_idx">검색</a>
		</td>
	</tr>
	<tr>
		<th scope="row">분류</th>
		<td colspan="3">
            <select name="trm_idx_category" id="trm_idx_category">
                <option value="0">분류를 선택하세요.</option>
                <?=$mms_type_form_options?>
			</select>
			<script>$('select[name="trm_idx_category"]').val('<?=$mms['trm_idx_category']?>');</script>
        </td>
	</tr>
	<tr>
		<th scope="row">iMP선택</th>
		<td>
            <input readonly type="hidden" placeholder="IMP선택" name="imp_idx" value="<?php echo $mms['imp_idx'] ?>" id="imp_idx"
                    required class="frm_input required" style="width:120px;<?=$style_member?>">
            <input readonly type="text" placeholder="iMP명" name="imp_name" value="<?php echo $imp['imp_name'] ?>" id="imp_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./imp_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_imp">검색</a>
        </td>
		<th scope="row">그룹선택</th>
		<td>
            <input readonly type="hidden" placeholder="그룹ID" name="mmg_idx" value="<?php echo $mms['mmg_idx'] ?>" id="mmg_idx"
                    required class="frm_input required" style="width:120px;<?=$style_member?>">
            <input readonly type="text" placeholder="그룹명" name="mmg_name" value="<?php echo $mmg['mmg_name'] ?>" id="mmg_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./mms_group_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mmg">검색</a>
        </td>
	</tr>
	<tr> 
		<th scope="row">설비명</th>
		<td>
			<input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required>
		</td>
		<th scope="row">관리번호</th>
		<td>
			<input type="text" name="mms_idx2" value="<?php echo $mms['mms_idx2'] ?>" id="mms_idx2" class="frm_input required" required>
		</td>
	</tr>
	<tr> 
	<th scope="row">모델명</th>
		<td>
			<input type="text" name="mms_model" value="<?php echo $mms['mms_model'] ?>" id="mms_model" class="frm_input required" required>
		</td>
		<th scope="row">도입일자</th>
		<td>
			<input type="text" name="mms_install_date" value="<?=(is_null_time($mms['mms_install_date']))?'':$mms['mms_install_date']?>" id="mms_install_date" class="frm_input" style="width:100px;" autocomplete="off">
		</td>
	</tr>
	<tr>
		<th scope="row">설비번호</th>
		<td>
			<input type="text" name="mms_number" value="<?php echo $mms['mms_number'] ?>" id="mms_number" class="frm_input">
		</td>
		<th scope="row">도입가격</th>
		<td>
			<input type="text" name="mms_price" value="<?php echo $mms['mms_price'] ?>" id="mms_price" class="frm_input" style="width:100px;">
		</td>
	</tr>
	<tr>
		<th scope="row">(설비)고유번호</th>
		<td>
			<input type="text" name="mms_unique_number" value="<?php echo $mms['mms_unique_number'] ?>" id="mms_unique_number" class="frm_input">
		</td>
		<th scope="row">구입처</th>
		<td>
			<input type="text" name="mms_dealer" value="<?php echo $mms['mms_dealer'] ?>" id="mms_dealer" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">제원(규격)</th>
		<td>
			<input type="text" name="mms_size" value="<?php echo $mms['mms_size'] ?>" id="mms_size" class="frm_input">
		</td>
		<th scope="row">생산통계기준</th>
		<td>
			<select name="mms_set_output" id="mms_set_output">
                <option value="">생산통계기준을 선택하세요.</option>
                <?=$g5['set_mms_set_data_options']?>
			</select>
			<script>$('select[name="mms_set_output"]').val('<?=$mms['mms_set_output']?>');</script>
		</td>
	</tr>
	<tr>
		<th scope="row">정렬번호</th>
		<td>
			<input type="text" name="mms_sort" value="<?php echo $mms['mms_sort'] ?>" id="mms_sort" class="frm_input" style="width:50px;">
		</td>
		<th scope="row">데이타 URL</th>
		<td>
			<input type="text" name="mms_data_url" value="<?php echo $mms['mms_data_url'] ?>" id="mms_data_url" class="frm_input" style="width:200px;">
		</td>
	</tr>
	<tr>
		<th scope="row">라인코드</th>
		<td>
			<select name="mms_linecode" id="mms_linecode" style="">
				<option value="">라인선택</option>
				<?=$line_form_options?>
			</select>
			<script>$("select[id=mms_linecode]").val("<?=$mms['mms_linecode']?>").attr("selected","selected");</script>
		</td>
		<th scope="row">생산통계</th>
		<td>
			<label for="mms_output_yn">
               <input type="checkbox" name="mms_output_yn" id="mms_output_yn" value="Y" <?=($mms['mms_output_yn']=='Y')?'checked':''?> class="frm_input">
               KPI 생산통계 표현합니다.
            </label>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_img_0">대표이미지</label></th>
		<td colspan="3">
			<div style="float:left;margin-right:8px;"><?=$mms['mms_img'][0]['thumbnail_img']?></div>
			<?php echo help("대표 이미지 파일을 등록해 주세요."); ?>
			<input type="file" name="mms_img_file[0]" class="frm_input">
			<?=$mms['mms_img'][0]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_memo">메모</label></th>
		<td colspan="3"><textarea name="mms_memo" id="mms_memo"><?php echo $mms['mms_memo'] ?></textarea></td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_data_0">첨부 파일#1</label></th>
		<td colspan="3">
			<?php echo help("설비와 관련해서 추가로 관리해야 할 자료가 있으면 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" name="mms_data_file[0]" class="frm_input">
			<?=$mms['mms_data'][0]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_data_1">첨부 파일#2</label></th>
		<td colspan="3">
			<input type="file" name="mms_data_file[1]" class="frm_input">
			<?=$mms['mms_data'][1]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="mms_status" id="mms_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="mms_status"]').val('<?=$mms['mms_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>
<div class="btn_fixed_top">
	<a href="./mms_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
	<?php if($is_admin){ ?>
	<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
	<?php } ?>
</div>
</form>

<script>
$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

    // IMP
    $(document).on('click','#btn_imp',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('업체를 먼저 입력하세요.');
        }
        else {
            var href = $(this).attr('href');
            winIMPSelect = window.open(href+'&com_idx='+com_idx,"winIMPSelect","left=100,top=100,width=520,height=600,scrollbars=1");
            winIMPSelect.focus();
        }
    });

    // 장비그룹
    $(document).on('click','#btn_mmg',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('업체를 먼저 입력하세요.');
        }
        else {
            var href = $(this).attr('href');
            winMMSGroup = window.open(href+'&com_idx='+com_idx,"winMMSGroup","left=100,top=100,width=520,height=600,scrollbars=1");
            winMMSGroup.focus();
        }
    });

    // 업체검색
    $("#btn_com_idx").click(function(e) {
        e.preventDefault();
        var href = $(this).attr("href");
        companyselectwin = window.open(href, "companyselectwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        companyselectwin.focus();
        return false;
    });

});

function form01_submit(f) {


    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
