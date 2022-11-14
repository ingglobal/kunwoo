<?php
$sub_menu = "919110";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 일반적인 경우는 이 부분 없어도 됩니다. (부속품인 경우는 mms 소속이므로 필요함)
$mms = get_table_meta('mms', 'mms_idx', $mms_idx);
if (!$mms['mms_idx'])
    alert('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'shift';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

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
		alert('존재하지 않는 자료입니다.');
	$mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);

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
$g5['title'] = $mms['mms_name'].' 교대및목표 '.$html_title;
include_once('./_head.sub.php');

?>
<style>
.td_item_range {margin-bottom:4px;}
</style>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc" style="display:none;">
        <p>존재하지 않는 항목값은 비워두세요.(2교대, 3교대 정보...)</p>
    </div>

    <form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_check(this);" method="post">
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
    <input type="hidden" name="shf_status" value="ok">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:20%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr style="display:none;">
				<th scope="row">설비(장비)</th>
				<td>
                    <?=$mms['mms_name']?>
				</td>
			</tr>
			<tr>
				<th scope="row">적용기간</th>
				<td>
                    <?php echo help("ex) 2020-05-01 00:00:00 ~ 2020-05-31 23:59:59<br>계속 적용인 경우는 종료일자를 비워두셔도 됩니다."); ?>
                    <input type="text" placeholder="시작일시" name="shf_start_dt" value="<?php echo $shf['shf_start_dt'] ?>"
                        required class="frm_input required" style="width:130px;">
                    ~
                    <input type="text" placeholder="종료일시" name="shf_end_dt" value="<?=($shf['shf_end_dt']!='9999-12-31 23:59:59')?$shf['shf_end_dt']:''?>"
                        class="frm_input" style="width:130px;">
				</td>
			</tr>
			<tr>
				<th scope="row">교대및목표</th>
				<td>
                  <?php echo help("09:00:00~17:59:59<span style='color:red;'>(초 포함)</span> 와 같은 형식으로 입력해 주세요.
                                2교대, 3교대가 존재하지 않으면 비워두세요.
                                근무 시간이 다음 날로 넘어가면 <span style='color:red;'>~29:59:59</span> 형태로 입력하세요."); ?>
                    <?php
                    for($i=1;$i<=3;$i++) {
                        $shf['shf_range_arr_'.$i] = explode("~",$shf['shf_range_'.$i]);
                        if($i==1) {
                            $shf['shf_required_'.$i] = ' required';
                        }
                    ?>
                    <div class="td_item_range">
                        <?=$i?>교대:
                        <input type="text" placeholder="시작시간" name="shf_range_<?=$i?>[]" value="<?=$shf['shf_range_arr_'.$i][0]?>"
                            <?=$shf['shf_required_'.$i]?> class="frm_input<?=$shf['shf_required_'.$i]?>" style="width:60px;">
                        ~
                        <input type="text" placeholder="종료시간" name="shf_range_<?=$i?>[]" value="<?=$shf['shf_range_arr_'.$i][1]?>"
                        <?=$shf['shf_required_'.$i]?> class="frm_input<?=$shf['shf_required_'.$i]?>" style="width:60px;margin-right:20px;">
                        목표:
                        <input type="text" placeholder="목표수량" name="shf_target_<?=$i?>" value="<?=$shf['shf_target_'.$i]?>"
                            <?=$shf['shf_required_'.$i]?> class="frm_input<?=$shf['shf_required_'.$i]?>" style="width:60px;">
                    </div>
                    <?php
                    }
                    ?>
				</td>
			</tr>
			<tr>
				<th scope="row">메모</th>
				<td>
                    <textarea name="shf_memo" id="shf_memo" style="height:60px;"><?=$shf['shf_memo']?></textarea>
				</td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn btn_02" value="목록" onClick="self.location='./<?=$fname?>_list.php?<?=$qstr?>'">
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
});

function form01_check(f) {
    
    if (f.mms_idx.value=='') {
		alert("설비를 선택하세요.");
		f.mms_idx.select();
		return false;
    }

    // 시작일시, 종료일시 체크
    if (f.shf_start_dt.value!='') {
        var re = /[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]/;
        if( !re.test(f.shf_start_dt.value) ) {
            alert('시작일시를 정확하게 입력해 주세요.');
            return false;
        }
    }
    if (f.shf_end_dt.value!='') {
        var re = /[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]/;
        if( !re.test(f.shf_end_dt.value) ) {
            alert('종료일시를 정확하게 입력해 주세요.');
            return false;
        }
    }

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
