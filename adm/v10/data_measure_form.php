<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_measure';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr1 = "&st_date=$st_date&st_time=$st_time&en_date=$en_date&en_time=$en_time";
$qstr .= $qstr1."&ser_mms_idx=$ser_mms_idx&ser_dat_type=$ser_dta_type&ser_dta_no=$ser_dta_no&ser_mms_dta_type=$ser_mms_dta_type";


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = rand(18,67);
    ${$pre}['imp_idx'] = rand(1,17);
    ${$pre}['mms_idx'] = rand(1,4);
    ${$pre}['shf_idx'] = rand(1,19);
    ${$pre}[$pre.'_shf_no'] = rand(1,2);
    ${$pre}[$pre.'_shf_max'] = rand(2,3);
    ${$pre}[$pre.'_mmi_no'] = rand(0,1);
    ${$pre}[$pre.'_group'] = 'mea';
    ${$pre}[$pre.'_type'] = rand(1,9);
    ${$pre}[$pre.'_price'] = 0;
    ${$pre}[$pre.'_no'] = 1;
    ${$pre}[$pre.'_dt'] = time();
    ${$pre}[$pre.'_value'] = rand(1,100);
    ${$pre}[$pre.'_status'] = 0;
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
	$imp = get_table_meta('imp','imp_idx',${$pre}['imp_idx']);
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
$g5['title'] = '측정데이터 '.$html_title;
include_once('./_top_menu_data.php');
include_once ('./_head.php');
echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "com_idx"=>array("업체번호","required",60,0,'','',2)
    ,"imp_idx"=>array("IMP번호","required",60,0,'','')
    ,"mms_idx"=>array("MMS번호","required",60,0,'','',2)
    ,"shf_idx"=>array("교대고유번호","required",60,0,'','')
    ,"dta_shf_no"=>array("교대번호","required",30,'번','','',2)
    ,"dta_shf_max"=>array("총교대수","required",30,'개','')
    ,"dta_group"=>array("데이터그룹","required",0,0,'err=에러,pre=예지,run=가동시간,product=생산,mea=측정','',2)
    ,"dta_type"=>array("데이터타입","required",0,0,'1=온도,2=토크,3=전류,4=전압,5=진동,6=소리,7=습도,8=압력,9=속도')
    ,"dta_no"=>array("측정번호","",30,'번','온도1, 온도2 등이 있어서 일련번호가 필요합니다.','',2)
    ,"dta_dt"=>array("일시","required",100,0,'일시는 timestamp값입니다.')
    ,"dta_value"=>array("측정값","",70,0,'','',2)
    ,"dta_mmi_no"=>array("기종번호","",70,0,'','',0)
    ,"dta_status"=>array("상태","required",60,'','0=정상, 1=비정상')
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
<input type="hidden" name="st_date" value="<?php echo $st_date ?>">
<input type="hidden" name="st_time" value="<?php echo $st_time ?>">
<input type="hidden" name="en_date" value="<?php echo $en_date ?>">
<input type="hidden" name="en_time" value="<?php echo $en_time ?>">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">
<input type="hidden" name="ser_dta_type" value="<?php echo $ser_dta_type ?>">
<input type="hidden" name="ser_mms_dta_type" value="<?php echo $ser_mms_dta_type ?>">

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
    <tr><!-- 첫줄은 무조건 출력 -->
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
