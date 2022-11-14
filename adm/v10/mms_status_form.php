<?php
$sub_menu = "919110";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_status';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// print_r3($member);
// print_r3($_SESSION);

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = $_SESSION['ss_com_idx'];
    ${$pre}['mms_idx'] = 0;
    ${$pre}['mst_type'] = 'quality';    // 품질
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '설비상태코드 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "mms_idx"=>array("설비선택","required",60,0,'','',0)
    ,"mst_type"=>array("설비상태타입","",250,'','','',0)
    ,"mst_name"=>array("항목이름","",250,'','','',0)
    ,"mst_memo"=>array("메모","",70,0,'','',0)
);
?>
<style>
.frm_date {width:75px;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
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
        $form_span = (!$items1[$k1][6]) ? ' colspan="3"' : '';

        $item_name = $items1[$k1][0];
        // 기본적인 폼 구조 먼저 정의
        $item_form = '<input type="text" name="'.$k1.'" value="'.${$pre}[$k1].'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;

        // 폼이 다른 구조를 가질 때 재정의
        if(preg_match("/_price$/",$k1)) {
            $item_form = '<input type="text" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'"> '.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)) {
            $item_form = '<textarea name="'.$k1.'" id="'.$k1.'">'.${$pre}[$k1].'</textarea>';
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
        // 설비번호인 경우
        else if($k1=='mms_idx') {

            $sql2 = "SELECT mms_idx, mms_name
                    FROM {$g5['mms_table']}
                    WHERE com_idx = '".$_SESSION['ss_com_idx']."'
                    ORDER BY mms_idx
            ";
            // echo $sql2.'<br>';
            $result2 = sql_query($sql2,1);
            for ($i=0; $row2=sql_fetch_array($result2); $i++) {
                // print_r2($row2);
                $form_select .= '<option value="'.$row2['mms_idx'].'" '.get_selected($ser_mms_idx, $row2['mms_idx']).'>'.$row2['mms_name'].'</option>';
            }

            $item_form = '<select name="mms_idx" id="mms_idx" '.$items1[$k1][1].'>'.$form_select.'</select>';
            $item_form .= "<script>$('select[name=mms_idx]').val('".${$pre}[$k1]."').attr('selected','selected');</script>";
        }
        // 설비상태타입인 경우
        else if($k1=='mst_type') {
            $item_form = '<select name="mst_type" id="mst_type" '.$items1[$k1][1].'>'.$g5['set_mst_type_value_options'].'</select>';
            $item_form .= "<script>$('select[name=mst_type]').val('".${$pre}[$k1]."').attr('selected','selected');</script>";
        }

        // 이전(두줄 항목)값이 2인 경우 <tr>열지 않고 td 바로 연결
        if($span_old<=1) {
            echo '<tr style="'.$form_none.'">';
        }
        ?>
            <th scope="row"><?=$item_name?></th>
            <td <?=$form_span?>>
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
        // 이전값 저장 (2=한줄에 두개 항목을 넣는다는 의미다.)
        $span_old = $items1[$k1][6];
    }
    ?>
    </tr>
	<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
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
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function form01_submit(f) {
    // 교대시간 체크

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
