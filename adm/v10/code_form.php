<?php
$sub_menu = "950140";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = rand(18,67);
    ${$pre}['imp_idx'] = rand(1,16);
    ${$pre}['mms_idx'] = rand(1,10);
    ${$pre}[$pre.'_code'] = 'M'.rand(1000,1100);
    ${$pre}[$pre.'_group'] = 'err';
    ${$pre}[$pre.'_type'] = 'r';
    ${$pre}[$pre.'_interval'] = 86400;
    ${$pre}[$pre.'_count'] = 3;
    ${$pre}[$pre.'_condition'] = 'M'.rand(1000,1100);
    ${$pre}[$pre.'_start_dt'] = G5_TIME_YMDHIS;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);

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
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '코드 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "com_idx"=>array("업체번호","required",60,0,'','',2)
    ,"imp_idx"=>array("IMP번호","required",60,0,'','',0)
    ,"mms_idx"=>array("MMS번호","required",60,0,'','',2)
    ,"cod_code"=>array("코드","required",0,0,'','',0)
    ,"cod_name"=>array("코드명","required",0,0,'','',2)
    ,"cod_group"=>array("코드그룹","required",50,'','영문자로 입력하세요. ex)err=에러, pre=예지','',0)
    ,"cod_type"=>array("코드타입","",30,'','영문자로 입력하세요. ex)r=저장, a=알람, p=주기','',2)
    ,"cod_interval"=>array("단위시간","",60,'초','단위시간을 초단위로 입력하세요. 86400=1일','',0)
    ,"cod_count"=>array("발생빈도","",60,'번 이상 발생','','',2)
    ,"cod_condition"=>array("예지조건","",0,'','예지발송 조건을 입력하세요.','',0)
    ,"cod_memo"=>array("메모","",70,0,'','',2)
);
?>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
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
    <tr style="display:"><!-- 첫줄은 무조건 출력 -->
    <?php
    // 폼 생성 (폼형태에 따른 다른 구조)
    $skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
    foreach($items1 as $k1 => $v1) {
        if(in_array($k1,$skips)) {continue;}
//        echo $k1.'<br>';
//        print_r2($items1[$k1]).'<br>';
        // 폭
        $form_width = ($items1[$k1][2]) ? 'width:'.$items1[$k1][2].'px' : '';
        // 단위
        $form_unit = ($items1[$k1][3]) ? ' '.$items1[$k1][3] : '';
        // 설명
        $form_help = ($items1[$k1][4]) ? ' '.help($items1[$k1][4]) : '';
        // tr 숨김
        $form_none = ($items1[$k1][5]) ? 'display:'.$items1[$k1][5] : '';
        // 한줄 두항목
        $form_span = ($items1[$k1][6]) ? ' colspan="'.$items1[$k1][6].'"' : '';

        $item_name = $items1[$k1][0];
        // 기본적인 폼 구조 먼저 정의
        $item_form = '<input type="text" name="'.$k1.'" value="'.${$pre}[$k1].'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;

        // 폼이 다른 구조를 가질 때 재정의
        if(preg_match("/_price$/",$k1)) {
            $item_form = '<input type="text" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)) {
            $item_form = '<textarea name="'.$k1.'" id="'.$k1.'">'.${$pre}[$k1].'</textarea>';
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
        // 이전(두줄 항목)값이 2인 경우 <tr>열지 않고 td 바로 연결
        if($span_old<=1) {
            echo '<tr style="'.$form_none.'">';
        }
        ?>
            <th scope="row"><?=$item_name?></th>
            <td>
                <?=$form_help?>
                <?=$item_form?>
            </td>
            <?php
            // 현재(두줄 항목)값이 2가 아닌 경우만 </tr>닫기
            if($items1[$k1][6]<=1) {
                echo '</tr>'.PHP_EOL;
            }
            ?>
        <?php
        // 이전값 저장
        $span_old = $items1[$k1][6];
    }
    ?>
    </tr>
	<tr>
		<th scope="row"><label for="com_status">상태</label></th>
		<td colspan="3">
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

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
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

});

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
