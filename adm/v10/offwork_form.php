<?php
$sub_menu = "915165";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'offwork';
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
    ${$pre}['off_period_type'] = 1;
    // ${$pre}['mms_idx'] = rand(1,4);
    ${$pre}[$pre.'_start_time'] = G5_SERVER_TIME;
    ${$pre}[$pre.'_end_time'] = G5_SERVER_TIME+3600*3;
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

// 날짜 표현
${$pre}[$pre.'_start_date'] = date("Y-m-d",${$pre}[$pre.'_start_time']);
${$pre}[$pre.'_end_date'] = date("Y-m-d",${$pre}[$pre.'_end_time']);
${$pre}[$pre.'_start_his'] = date("H:i:s",${$pre}[$pre.'_start_time']);
${$pre}[$pre.'_end_his'] = date("H:i:s",${$pre}[$pre.'_end_time']);
$s_his_arr = explode(':',${$pre}[$pre.'_start_his']);
${$pre}[$pre.'_start_h'] = $s_his_arr[0];
${$pre}[$pre.'_start_i'] = $s_his_arr[1];
${$pre}[$pre.'_start_s'] = $s_his_arr[2];
$e_his_arr = explode(':',${$pre}[$pre.'_end_his']);
${$pre}[$pre.'_end_h'] = $e_his_arr[0];
${$pre}[$pre.'_end_i'] = $e_his_arr[1];
${$pre}[$pre.'_end_s'] = $e_his_arr[2];

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '공제시간 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "com_idx"=>array("업체번호","readonly",60,0,'','',2)
    ,"mms_idx"=>array("설비번호","required",60,0,'','',0)
    ,"off_name"=>array("공제시간명칭","",250,'','','',0)
    ,"off_period_type"=>array("적용기간","required",75,0,'전체기간을 선택하면 해당 설비에 대하여 전체 기간 동안 적용됩니다. 기간을 선택하고 입력하면 전체 기간 상관없이 우선 적용됩니다.','',0)
    ,"off_start_time"=>array("시작시간","required",70,0,'시작시간은 17:00:00와 같이 입력하세요.','',2)
    ,"off_end_time"=>array("종료시간","",70,0,'종료시간은 23:59:59와 같이 끝단위까지 모두 입력하세요.','',0)
    ,"off_memo"=>array("메모","",70,0,'','',0)
);

//설비선택여부
if($off['mms_idx']) {
    $mms_idx_1 = ' checked';
    $mms_idx_type = '';
}
else {
    ${'mms_idx_'.$off['mms_idx']} = ' checked';
    $mms_idx_type = ' hidden';
}
// 전체기간
if($off['off_period_type']) {
    $off_period_type = 'hidden';
    $off_span_display = 'display:none;';
    $off_period_1 = ' checked';
}
else {
    $off_period_type = 'text';
    $off_span_display = 'display:;';
    ${$pre}['off_start_his'] = date("H:i:s",${$pre}['off_start_time']);
    $off_period_0 = ' checked';
}
// if (!auth_check($auth[$sub_menu],"w",1)){
    // print_r2($off);
// }
?>
<style>
.tbl_frm01 th{background:#262626;}
.tbl_frm01 th,.tbl_frm01 td{padding:20px;}
.tbl_frm01 td{}
.frm_date {width:75px;}
.radio_lb{display:inline-block;margin-right:10px;}
.radio_lb input{margin-left:10px;}
input[type="radio"]{position:relative;top:-2px;}
select{height:26px;line-height:26px;}
#dt_box{display:block;}
#dt_box:after{display:block;visibility:hidden;clear:both;content:"";}
.his_box{float:left;margin-right:10px;position:relative;}
.his_box span{position:absolute;top:-20px;left:6px;}
.hidden{display:none;}
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
<input type="hidden" name="com_idx" value="<?php echo $_SESSION['ss_com_idx'] ?>">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">
<input type="hidden" name="off_start_his" id="off_start_his" value="<?php echo ${$pre}[$pre.'_start_his'] ?>">
<input type="hidden" name="off_end_his" id="off_end_his" value="<?php echo ${$pre}[$pre.'_end_his'] ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>각종 고유번호(설비번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
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
            <th scope="row">공제시간명칭</th>
            <td><input type="text" name="off_name" value="<?=${$pre}['off_name']?>" class="frm_input" style="width:250px;"></td>
            <th scope="row">설비번호</th>
            <td>
                <label id="mms_idx_0" class="radio_lb"><input type="radio" name="mms_idx_radio" id="mms_idx_0" value="0"<?=$mms_idx_0?>> 전체설비</label>
                <label id="mms_idx_1" class="radio_lb"><input type="radio" name="mms_idx_radio" id="mms_idx_1" value="1"<?=$mms_idx_1?>> 설비선택</label>
                <?php if($g5['set_mms_options']){ ?>
                <select name="mms_idx" id="mms_idx" class="select<?=$mms_idx_type?>">
                    <?php echo $g5['set_mms_options']; ?>
                </select>
                <?php } else { ?>
                <strong class="strong<?=$mms_idx_type?>">등록된 설비가 없음</strong>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">적용기간</th>
            <td colspan="3">
                <p style="margin-bottom:10px;">전체기간을 선택하면 해당 설비에 대하여 전체 기간 동안 적용됩니다. 기간을 선택하고 입력하면 전체 기간 상관없이 우선 적용됩니다.</p>
                <label id="off_period_type_0" class="radio_lb"><input type="radio" name="off_period_type" id="off_period_type_0" value="0"<?=$off_period_0?>> 기간선택</label>
                <label id="off_period_type_1" class="radio_lb"><input type="radio" name="off_period_type" id="off_period_type_1" value="1"<?=$off_period_1?>> 전체기간</label>
            </td>
        </tr>
        <tr>
            <th scope="row">시작시간</th>
            <td>
                <div id="dt_box">
                    <div class="his_box <?=$off_period_type?>">
                        <span>시작일</span>
                        <input type="<?=$off_period_type?>" name="off_start_date" id="from_date" required class="frm_input required frm_date" value="<?=${$pre}['off_start_date']?>">
                    </div>
                    <div class="his_box sh_box">
                        <span>시</span>
                        <select name="sh" id="sh" class="his sh" iput="off_start_his">
                        <?php for($i=0;$i<24;$i++){ $h = sprintf("%02d",$i); ?>
                            <option value="<?=$h?>"><?=$h?></option>
                            <?php } ?>
                        </select>
                        <script>$('#sh').val('<?=$off['off_start_h']?>');</script>
                    </div>
                    <div class="his_box si_box">
                        <span>분</span>
                        <select name="si" id="si" class="his si" iput="off_start_his">
                        <?php for($j=0;$j<60;$j++){ $i = sprintf("%02d",$j); ?>
                            <option value="<?=$i?>"><?=$i?></option>
                            <?php } ?>
                        </select>
                        <script>$('#si').val('<?=$off['off_start_i']?>');</script>
                    </div>
                    <div class="his_box ss_box">
                        <span>초</span>
                        <select name="ss" id="ss" class="his ss" iput="off_start_his">
                        <?php for($j=0;$j<60;$j++){ $s = sprintf("%02d",$j); ?>
                            <option value="<?=$s?>"><?=$s?></option>
                            <?php } ?>
                        </select>
                        <script>$('#ss').val('<?=$off['off_start_s']?>');</script>
                    </div>
                </div>
            </td>
            <th scope="row">종료시간</th>
            <td>
                <div id="dt_box">
                    <div class="his_box <?=$off_period_type?>">
                        <span>종료일</span>
                        <input type="<?=$off_period_type?>" name="off_end_date" id="to_date" required class="frm_input required frm_date" value="<?=${$pre}['off_end_date']?>">
                    </div>
                    <div class="his_box eh_box">
                        <span>시</span>
                        <select name="eh" id="eh" class="his eh" iput="off_end_his">
                        <?php for($i=0;$i<24;$i++){ $h = sprintf("%02d",$i); ?>
                            <option value="<?=$h?>"><?=$h?></option>
                            <?php } ?>
                        </select>
                        <script>$('#eh').val('<?=$off['off_end_h']?>');</script>
                    </div>
                    <div class="his_box ei_box">
                        <span>분</span>
                        <select name="ei" id="ei" class="his ei" iput="off_end_his">
                        <?php for($j=0;$j<60;$j++){ $i = sprintf("%02d",$j); ?>
                            <option value="<?=$i?>"><?=$i?></option>
                            <?php } ?>
                        </select>
                        <script>$('#ei').val('<?=$off['off_end_i']?>');</script>
                    </div>
                    <div class="his_box es_box">
                        <span>초</span>
                        <select name="es" id="es" class="his es" iput="off_end_his">
                        <?php for($j=0;$j<60;$j++){ $s = sprintf("%02d",$j); ?>
                            <option value="<?=$s?>"><?=$s?></option>
                            <?php } ?>
                        </select>
                        <script>$('#es').val('<?=$off['off_end_s']?>');</script>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">메모</th>
            <td colspan="3"><textarea name="off_memo" id="off_memo"><?=${$pre}['off_memo']?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="com_status">상태</label></th>
            <td colspan="3">
                <?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
                <select name="<?=$pre?>_status" id="<?=$pre?>_status"
                    <?php if (auth_check($auth[$sub_menu],"w",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                    <?=$g5['set_status_options']?>
                    <?php if($is_admin){ ?>
                    <option value="trash">삭제(trash)</option>
                    <?php } ?>
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
    // 기간선택, 전체기간
    $(document).on('click','input[name=off_period_type]',function(e){
        // console.log($(this).val());
        // 기간선택
        if( $(this).val() == '0' ) {
            // $('input[name=off_start_date]').attr('type','text').select().focus();
            $('input[name=off_start_date]').attr('type','text').parent().removeClass('hidden');
            $('input[name=off_end_date]').attr('type','text').parent().removeClass('hidden');
        }
        // 전체기간
        else {
            $('input[name=off_start_date]').attr('type','hidden').parent().addClass('hidden');
            $('input[name=off_end_date]').attr('type','hidden').parent().addClass('hidden');
        }
    });

    // 설비선택, 전체설비
    $(document).on('click','input[name=mms_idx_radio]',function(e){
        if( $(this).val() == '0' ) {
            $('#mms_idx').val('0').addClass('hidden');
        }
        else {
            $('#mms_idx').removeClass('hidden');
        }
    });

    $("#from_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("#to_date").datepicker('option','minDate',selectedDate);}});
    $("#to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("#from_date").datepicker('option','maxDate',selectedDate);}});
    
    $('.his').on('change',function(){
        // console.log($(this).attr('iput'));
        if($(this).attr('iput') == 'off_start_his'){ //시작시간 선택박스 변경 이벤트 발생시
            var start_his = $('#sh').val() + ':' + $('#si').val() + ':' + $('#ss').val();
            $('#off_start_his').val(start_his);
        }
        else { //종료시간 선택박스 변경 이벤트발생시
            var end_his = $('#eh').val() + ':' + $('#ei').val() + ':' + $('#es').val();
            $('#off_end_his').val(end_his);
        }
    });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function form01_submit(f) {
    if(!f.off_name.value){
        alert('공제시간 명칭을 입력해 주세요');
        f.off_name.focus();
        return false;
    }

    if(f.mms_idx_radio.value == '1'){
        if(f.mms_idx.value == '0'){
            alert('설비선택 모드에서는 설비를 선택해 주셔야 합니다.');
            f.mms_idx.focus();
            return false;
        }
    }


    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
