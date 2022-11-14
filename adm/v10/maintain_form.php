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
$table_name = 'maintain';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_hours'] = 1;
    ${$pre}[$pre.'_price'] = 0;
    ${$pre}[$pre.'_sort'] = 1;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$mms = get_table_meta('mms','mms_idx',$mmp['mms_idx']);
	$com = get_table_meta('company','com_idx',$mms['com_idx']);

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
$g5['title'] = $mms['mms_name'].' 정비관리 '.$html_title;
include_once('./_head.sub.php');

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김
$items1 = array(
    "mms_idx"=>array("설비명","required",0,0,'','none')
    ,"mnt_subject"=>array("제목","required",300,0,'제목을 입력하세요.')
    ,"mnt_date"=>array("정비일자","required",80,0,'')
    ,"mnt_names"=>array("담당자","",0,'','')
    ,"mnt_hours"=>array("정비시간","required",40,'시간','시간을 숫자로 입력하세요. 30분은 0.5시간입니다.')
    ,"mnt_price"=>array("정비비용","",80,'원','비용을 숫자로 입력하세요.')
    ,"mnt_memo"=>array("메모","",0,0,'')
);
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc" style="display:none;">
        <p>부속품을 관리하는 페이지입니다.</p>
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
            <?php if($sample) { // 향후 사용을 위해 ?>
			<tr>
				<th scope="row">설비(장비)</th>
				<td>
                    <input readonly type="text" placeholder="설비ID" name="mms_idx" value="<?php echo $mms['mms_idx'] ?>" id="mms_idx"
                        required class="frm_input required" style="width:120px;">
                    <input readonly type="text" placeholder="설비명" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name"
                        class="frm_input" style="width:130px;">
                    <a href="./mms_select.php?frm=form01&com_idx=<?=$com['com_idx']?>" class="btn btn_02" id="btn_mms">검색</a>
				</td>
			</tr>
            <?php } ?>
            <?php
            // 폼 생성 (폼형태에 따른 다른 구조)
            $skips = array($pre.'_idx',$pre.'_status',$pre.'_reg_dt',$pre.'_update_dt');
            for($i=0;$i<sizeof($fields);$i++) {
                if(in_array($fields[$i],$skips)) {continue;}
//                echo $fields[$i].'<br>';
//                print_r2($items1[$fields[$i]]).'<br>';
                // 폭
                $form_width = ($items1[$fields[$i]][2]) ? 'width:'.$items1[$fields[$i]][2].'px' : '';
                // 단위
                $form_unit = ($items1[$fields[$i]][3]) ? ' '.$items1[$fields[$i]][3] : '';
                // 설명
                $form_help = ($items1[$fields[$i]][4]) ? ' '.help($items1[$fields[$i]][4]) : '';
                // tr 숨김
                $form_none = ($items1[$fields[$i]][5]) ? 'display:'.$items1[$fields[$i]][5] : '';
                
                $item_name = $items1[$fields[$i]][0];
                // 기본적인 폼 구조 먼저 정의
                $item_form = '<input type="text" name="'.$fields[$i].'" value="'.${$pre}[$fields[$i]].'" '.$items1[$fields[$i]][1].'
                                class="frm_input '.$items1[$fields[$i]][1].'" style="'.$form_width.'">'.$form_unit;

                // 폼이 다른 구조를 가질 때 재정의
                if(preg_match("/_price$/",$fields[$i])) {
                    $item_form = '<input type="text" name="'.$fields[$i].'" value="'.number_format(${$pre}[$fields[$i]]).'" '.$items1[$fields[$i]][1].'
                                class="frm_input '.$items1[$fields[$i]][1].'" style="'.$form_width.'">'.$form_unit;
                }
                else if(preg_match("/_memo$/",$fields[$i])) {
                    $item_form = '<textarea name="'.$fields[$i].'" id="'.$fields[$i].'">'.${$pre}[$fields[$i]].'</textarea>';
                }
                else if(preg_match("/_date$/",$fields[$i])) {
                    
                }
                else if($fields[$i]=='mms_idx') {
                    $item_form = '<input type="hidden" name="'.$fields[$i].'" value="'.$mms_idx.'">'.$mms['mms_name'];
                }
                ?>
                <tr style="<?=$form_none?>">
                    <th scope="row"><?=$item_name?></th>
                    <td>
                        <?=$form_help?>
                        <?=$item_form?>
                    </td>
                </tr>
                <?php
            }
            ?>
			<tr>
				<th scope="row">상태</th>
				<td>
                    <?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
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
    if (isNaN(f.mnt_hours.value) == true) {
        alert("정비시간은 숫자만 가능합니다.");
        f.mnt_hours.focus();
        return false;
    }
    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
