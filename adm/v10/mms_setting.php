<?php
$sub_menu = "919110";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
// $qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들
$w = 'u';

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_price'] = 0;
    ${$pre}[$pre.'_sort'] = 1;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert_close('존재하지 않는 자료입니다.');
	// $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
	$imp = get_table_meta('imp','imp_idx',${$pre}['imp_idx']);
    $mmg = get_table_meta('mms_group','mmg_idx',${$pre}['mmg_idx']);
    // print_r2($mms);

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
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = $mms['mms_name'].' 설정';
include_once('./_head.sub.php');
// print_r2($mms);

?>
<style>
.td_item_range {margin-bottom:4px;}
input[type=file] {width: 165px;}
</style>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc" style="display:none;">
        <p>존재하지 않는 항목값은 비워두세요.(2교대, 3교대 정보...)</p>
    </div>

    <form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_check(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
	<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
    <input type="hidden" name="mms_idx" value="<?php echo $mms['mms_idx']; ?>">
    <input type="hidden" name="com_idx" value="<?php echo $com['com_idx']; ?>">
    <input type="hidden" name="mms_status" value="<?php echo $mms['mms_status']; ?>">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:22%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">설비명(mms)</th>
				<td>
                   <input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required>
				</td>
			</tr>
			<tr>
				<th scope="row">관리번호</th>
				<td>
                    <input type="text" name="mms_idx2" value="<?php echo $mms['mms_idx2'] ?>" id="mms_idx2" class="frm_input required" required style="width:30px;">
				</td>
			</tr>
			<tr>
				<th scope="row">모델명</th>
				<td>
                    <input type="text" name="mms_model" value="<?php echo $mms['mms_model'] ?>" id="mms_model" class="frm_input required" required>
				</td>
			</tr>
			<tr>
				<th scope="row">설비분류</th>
				<td>
                    <select name="trm_idx_category" id="trm_idx_category" class="required" required>
                        <option value="">분류를 선택하세요.</option>
                        <?=$mms_type_form_options?>
                    </select>
                    <script>$('select[name="trm_idx_category"]').val('<?=$mms['trm_idx_category']?>');</script>
				</td>
			</tr>
			<tr>
				<th scope="row">장비그룹</th>
				<td>
                    <input readonly type="hidden" placeholder="그룹ID" name="mmg_idx" value="<?php echo $mms['mmg_idx'] ?>" id="mmg_idx"
                            required class="frm_input required" style="width:120px;<?=$style_member?>">
                    <input readonly type="text" placeholder="그룹명" name="mmg_name" value="<?php echo $mmg['mmg_name'] ?>" id="mmg_name" 
                            <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
                    <a href="./mms_group_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mmg">검색</a>
				</td>
			</tr>
			<tr>
				<th scope="row">설비번호</th>
				<td>
                    <input type="text" name="mms_number" value="<?php echo $mms['mms_number'] ?>" id="mms_number" class="frm_input">
				</td>
			</tr>
			<tr>
				<th scope="row">고유번호</th>
				<td>
                    <input type="text" name="mms_unique_number" value="<?php echo $mms['mms_unique_number'] ?>" id="mms_unique_number" class="frm_input">
				</td>
			</tr>
			<tr>
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
			</tr>
			<tr>
				<th scope="row">도입일자</th>
				<td>
                    <input type="text" name="mms_install_date" value="<?=(is_null_time($mms['mms_install_date']))?'':$mms['mms_install_date']?>" id="mms_install_date" class="frm_input" style="width:100px;">
				</td>
			</tr>
			<tr>
				<th scope="row">도입가격</th>
				<td>
                    <input type="text" name="mms_price" value="<?php echo number_format($mms['mms_price']) ?>" id="mms_price" class="frm_input" style="width:100px;">
				</td>
			</tr>
			<tr>
				<th scope="row">생산집계</th>
				<td>
                    <select name="mms_set_output" id="mms_set_output" class="required" required>
                        <option value="">생산통계 집계기준을 선택하세요.</option>
                        <?=$g5['set_mms_set_data_value_options']?>
                    </select>
                    <script>$('select[name="mms_set_output"]').val('<?=$mms['mms_set_output']?>');</script>
				</td>
			</tr>
			<tr>
				<th scope="row">대표이미지</th>
				<td>
                    <div style="float:left;margin-right:8px;"><?=$mms['mms_img'][0]['thumbnail_img']?></div>
                    <?php echo help("대표 이미지 파일을 등록해 주세요."); ?>
                    <input type="file" name="mms_img_file[0]" class="frm_input">
                    <?=$mms['mms_img'][0]['file']?>
				</td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn_close btn" value="창닫기" onclick="javascript:window.close();">
    </div>

    </form>

    <div class="btn_fixed_top">
        <a href="./mms_view.popup.php?mms_idx=<?=$mms_idx?>" id="btn_mms_view" class="btn btn_03" title="장비이력카드"><i class="fa fa-address-card-o"></i></a>
        <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
    </div>
</div>

<script>
// 윈도우 크기 재설정
window.onload = reSize;
window.onresize = reSize;
function reSize() {
	resizeTo(520, 680);    // 여는 페이지 설정 높이 80 차이
}

$(function() {

    // 장비그룹
    $(document).on('click','#btn_mmg',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('업체를 먼저 입력하세요.');
        }
        else {
            var href = $(this).attr('href');
            winMMSGroup = window.open(href+'&com_idx='+com_idx,"winMMSGroup","left=50,top=50,width=520,height=600,scrollbars=1");
            winMMSGroup.focus();
        }
    });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

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
});

function form01_check(f) {
    
    if (f.mms_name.value=='') {
		alert("설비명을 입력하세요.");
		f.mms_name.select();
		return false;
    }

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
