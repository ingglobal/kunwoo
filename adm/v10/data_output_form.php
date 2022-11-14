<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$g5_table_name = 'g5_1_data_output_'.$ser_mms_idx;
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= "&st_date=$st_date&st_time=$st_time&en_date=$en_date&en_time=$en_time&ser_mms_idx=$ser_mms_idx";
// $qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}[$pre.'_shf_no'] = rand(1,2);
    ${$pre}[$pre.'_mmi_no'] = rand(0,1);
    ${$pre}[$pre.'_group'] = 'product';
    ${$pre}[$pre.'_type'] = rand(1,9);
    ${$pre}[$pre.'_defect'] = 0;
    ${$pre}[$pre.'_defect_type'] = 0;
    ${$pre}[$pre.'_dt'] = date("Y-m-d H:i:s",G5_SERVER_TIME);
    ${$pre}[$pre.'_date'] = date("Y-m-d",G5_SERVER_TIME);
    ${$pre}[$pre.'_value'] = rand(1,3);
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$sql = "SELECT * FROM {$g5_table_name} WHERE dta_idx = '".${$pre."_idx"}."' ";
    ${$pre} = sql_fetch($sql,1);
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    $mms = get_table_meta('mms','mms_idx',$ser_mms_idx);

    ${$pre}[$pre.'_dt'] = date("Y-m-d H:i:s",${$pre}[$pre.'_dt']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '생산데이터 '.$html_title;
include_once('./_top_menu_data.php');
include_once ('./_head.php');
echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "dta_shf_no"=>array("교대번호","required",30,'번','','',2)
    ,"dta_mmi_no"=>array("기종번호","",70,0,'','')
    ,"dta_group"=>array("데이터그룹","required",0,0,'product=자동입력,manual=수동입력','',2)
    ,"dta_value"=>array("생산카운터","",70,0,'','')
    ,"dta_defect"=>array("불량여부","required",0,0,'0=정상,1=불량','',2)
    ,"dta_defect_type"=>array("불량코드","",30,'','1=사출불량,2=형상불량 등 설비별 품질코드입니다.','')
    ,"dta_dt"=>array("일시","required",130,0,'','실제로는 timestamp값이 입력됩니다.',2)
    ,"dta_date"=>array("통계일시","required",100,0,'2교인 경우 통계일자가 다를 수 있습니다.','')
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
<input type="hidden" name="en_date" value="<?php echo $en_date ?>">
<input type="hidden" name="st_time" value="<?php echo $st_time ?>">
<input type="hidden" name="en_time" value="<?php echo $en_time ?>">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">

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
