<?php
$sub_menu = "915160";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'shift';
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
    ${$pre}['shf_period_type'] = 0;
    // ${$pre}['mms_idx'] = rand(1,4);
    //${$pre}[$pre.'_range_1'] = date("H:i:00").'~'.date("H:i:00",time()+43200);
    //${$pre}[$pre.'_target_1'] = 100;
    ${$pre}[$pre.'_start_dt'] = date("Y-m-d H:i:00");
    ${$pre}[$pre.'_end_dt'] = date("Y-m-d H:i:00",time()+86400*3);
    ${$pre}[$pre.'_end_nextday'] = 0;
    ${$pre}[$pre.'_period_type'] = 1;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

    ${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    // print_r3(${$pre});
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
//print_r3($$pre);

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정';
$html_title = ($w=='c')?'복제':$html_title;
$g5['title'] = '작업구간 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

/*
array(
    'type' => 'text/password/url/radio/checkbox/textarea/select/hidden/none'
    'ttl' => '타이틀명',
    'required' => true or false,
    'readonly' => true or false,
    'width' => 60,
    'unit' => '단위명(개,개월,시,분,...)',
    'desc' => '설명',
    'colspan' => 0,
    'value' => ,
    'radio' => ex) array(1=>"예",0=>"아이오"),
    'select' => ex) array(1=>"예",0=>"아이오"),
    'checkbox' => ex) '예,맞습니다.',
    'textarea' => '속성 관련 배열',
    'id'=>'',
    'class'=>'',
    'tr_s' => true or false,
    'tr_e' => true or false,
    'th' => true or false,
    'td' => true or false,
    'td_s' => true or false,
    'td_e' => true or false
)
*/
$f1 = array(
    "shf_idx" => array('type'=>'hidden','value'=>$$pre['shf_idx'])
    ,"com_idx" => array('type'=>'hidden','value'=>$$pre['com_idx'])
    ,"shf_name" => array('type'=>'text','ttl'=>'작업구간명','required'=>true,'value'=>$$pre['shf_name'],'tr_s'=>true,'th'=>true,'td'=>true)
    ,"mms_idx" => array('type'=>'text','ttl'=>'설비명','readonly'=>true,'value'=>$$pre['mms_idx'],'tr_e'=>true,'th'=>true,'td'=>true)
    ,"shf_start_time" => array('type'=>'text','ttl'=>'시작시간','required'=>true,'width'=>50,'value'=>$$pre['shf_start_time'],'tr_s'=>true,'th'=>true,'td'=>true)
    ,"shf_end_time" => array('type'=>'text','ttl'=>'종료시간','required'=>true,'width'=>50,'value'=>$$pre['shf_end_time'],'th'=>true,'td'=>true,'tr_e'=>true)
    ,"shf_end_nextday" => array('type'=>'radio','ttl'=>'종료시간익일여부','radio'=>$g5['set_noyes_value'],'value'=>$$pre['shf_ned_nextday'],'tr_s'=>true,'th'=>true,'td'=>true)
    ,"shf_period_type" => array('type'=>'radio','ttl'=>'기간타입','radio'=>$g5['set_period_type_value'],'value'=>$$pre['shf_period_type'],'th'=>true,'td'=>true,'tr_e'=>true)
    ,"shf_start_dt" => array('type'=>'text','ttl'=>'적용시작일시','value'=>$$pre['shf_start_dt'],'width'=>80,'tr_s'=>true,'th'=>true,'td'=>true)
    ,"shf_end_dt" => array('type'=>'text','ttl'=>'적용종료일시','value'=>$$pre['shf_end_dt'],'width'=>80,'th'=>true,'td'=>true,'tr_e'=>true)
    ,"shf_memo" => array('type'=>'textarea','ttl'=>'메모','textarea'=>'','value'=>$$pre['shf_memo'],'colspan'=>4,'tr_s'=>true,'tr_e'=>true,'th'=>true,'td'=>true)
    ,"shf_status" => array('type'=>'select','ttl'=>'상태','select'=>$g5['set_status_value'],'width'=>'auto','value'=>$$pre['shf_status'],'readonly'=>true,'required'=>true,'colspan'=>4,'tr_s'=>true,'tr_e'=>true,'th'=>true,'td'=>true)
);

//print_r3($f1);
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
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">
<?php
$hskip = array();
foreach($f1 as $hk=>$hv){
    if($hv['type'] != 'hidden' || $hv['type'] == 'none' || in_array($hk,$hskip)) continue;
    echo input_hidden($hk,$hv);
}
?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>각종 고유번호(설비번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
	<p class="txt_redblink" style="display:no ne;">설비idx=0 인 경우는 전체설비(설비 비선택 추가해라!!!)<br>설비idx 가 있으면 특정설비의 작업구간</p>
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
    <?php
    $fskip = array();//제외되는 필드명
    $fcust = array('mms_idx','shf_start_time','shf_end_time');//커스터마이징해야 하는 필드명

    foreach($f1 as $fk=>$fv){
        if($fv['type'] == 'hidden' || $fv['type'] == 'none' || in_array($fk,$fskip)) continue;
        if($fk == 'shf_start_dt' || $fk == 'shf_end_dt'){
            $fv['value'] = substr($fv['value'],0,10);
        }

        //필드 폼을 커스터마이징 해야 할 경우
        if(in_array($fk,$fcust)){
            $fctag = '';//form customize tag 편집태그
            $colspan = ($fv['colspan']) ? ' colspan="'.$fv['colspan'].'"' : '';
            $fctag .= ($fv['tr_s']) ? '<tr>'.PHP_EOL : '';
            $fctag .= ($fv['th']) ? '<th>'.$fv['ttl'].'</th>'.PHP_EOL : '';
            $fctag .= ($fv['td']) ? '<td'.$colspan.'>'.PHP_EOL : '';
            $fctag .= ($fv['td_e']) ? '<td>'.PHP_EOL : '';

            $id_str = ($fv['id']) ? ' id="'.$fv['id'].'"' : '';
            $class_nm = ($fv['class']) ? $fv['class'] : '';
            $wd_style = ($fv['width']) ? 'width:'.((preg_match('/[0-9]{1,3}%$/',$fv['width']) || preg_match('/auto/',$fv['width'])) ? $fv['width'] : $fv['width'].'px') : 'width:100%;';
            $style_str = ($wd_style) ? ' style="'.$wd_style.'"' : '';
            $required = ($fv['required']) ? ' required' : '';
            $readonly = ($fv['readonly']) ? ' readonly' : '';

            $tag .= ($fv['desc']) ? '<p>'.$fv['desc'].'</p>' : '';
            //######################### 커스터마이징 필드별 소스 추가 : 시작 #################################
            if($fk == 'mms_idx'){
                $mmn = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '{$fv['value']}' ");
                $fctag .= '<input type="hidden" name="'.$fk.'" value="'.$fv['value'].'">'.PHP_EOL;
                $fctag .= '<input type="text" name="mms_name" value="'.$mmn['mms_name'].'"'.$required.$readoly.' class="frm_input'.$required.$readoly.'" placeholder="설비명" style="width:140px;">'.PHP_EOL;
                $fctag .= '<button type="button" class="btn btn_02" id="btn_mms">설비찾기</button>'.PHP_EOL;
                $fctag .= '<button type="button" class="btn btn_03" id="btn_tms">전체설비</button>'.PHP_EOL;
            }
            else if($fk == 'shf_start_time' || $fk == 'shf_end_time'){
                $fctag .= '<input type="'.$fv['type'].'" name="'.$fk.'" value="'.substr($fv['value'],0,5).'"'.$id_str.$required.$readonly.' class="frm_input '.$class_nm.$required.$readonly.'"'.$style_str.'>'.PHP_EOL;
            }
            //######################### 커스터마이징 필드별 소스 추가 : 종료 #################################
            $fctag .= ($fv['td_e'])?'</td>'.PHP_EOL:'';
            $fctag .= ($fv['td'])?'</td>'.PHP_EOL:'';
            $fctag .= ($fv['tr_e'])?'</tr>'.PHP_EOL:'';
            echo $fctag;
        }
        //기본 디폴트로 사용할 경우
        else{
            echo form_tag($fk,$fv);
        }
    }
    ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
var w = '<?=$w?>';
var shf_period_type = <?=$$pre["shf_period_type"]?>;
var timeformat = /^([01][0-9]|2[0-3]):([0-5][0-9])$/;
var cftime;
var cttime;
$(function() {

    $('input[name="shf_start_time"],input[name="shf_end_time"]').datetimepicker({
        datepicker:false,
        theme:'dark',
        format:'H:i'
    });

    if(shf_period_type) $('input[name=shf_start_dt]').parent().parent().hide();
    // 기간선택, 전체기간
    $(document).on('click','input[name=shf_period_type]',function(e){
        // 기간선택
        if( $(this).val() == '0' ) {
            $('input[name=shf_start_dt]').parent().parent().show();
            $('input[name=shf_start_dt]').select().focus();
        }
        // 전체기간
        else {
            $('input[name=shf_start_dt]').parent().parent().hide();
        }
    });

    // 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
		var url = g5_user_admin_url+"/mms_select.php?frm=fwrite&file_name=<?php echo $g5['file_name']?>";
		win_mms_select = window.open(url, "win_mms_select", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_mms_select.focus();
	});
    // 전체설비 버튼 클릭
    $('#btn_tms').click(function(e){
        e.preventDefault();
        $('input[name="mms_idx"]').val('');
        $('input[name="mms_name"]').val('');
    });

    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.popup.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
    });

    //$("input[name$=_start_dt]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    $("input[name$=_start_dt]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name$=_end_dt]").datepicker('option','minDate',selectedDate);} });
    $("input[name$=_end_dt]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("input[name$=_start_dt]").datepicker('option','maxDate',selectedDate); }});

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
