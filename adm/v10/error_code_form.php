<?php
$sub_menu = "917230";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}[$pre.'_group'] = 'err';
    ${$pre}[$pre.'_type'] = 'a';
    ${$pre}['trm_idx_category'] = 0;
    ${$pre}[$pre.'_interval'] = 86400;
    ${$pre}[$pre.'_count_limit'] = 5;
    ${$pre}[$pre.'_send_type'] = 'email';
    ${$pre}[$pre.'_start_dt'] = G5_TIME_YMDHIS;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $imp = get_table_meta('imp','imp_idx',${$pre}['imp_idx']);
    $mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);
    // print_r3(${$pre}['imp_idx']);
    // print_r3($imp);

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

    // towhom_info variable
    $reports = json_decode($cod['cod_reports'], true);
    if(is_array($reports)) {
        foreach($reports as $k1 => $v1) {
            // echo $k1.'<br>';
            // print_r2($v1);
            for($i=0;$i<@sizeof($v1);$i++) {
                $towhom_li[$i][$k1] = $v1[$i];
            }
        }
    }
    // print_r2($towhom_li);
    

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// cod_type에 따른 숨김 설정
if($cod['cod_type']=='a') {
    $tr_code_type_p_detail = 'none';
    $tr_report_detail = 'none';
    $tr_send_type = 'none';
}
else if($cod['cod_type']=='r'||$cod['cod_type']=='') {
    $tr_code_type_p_detail = 'none';
    $tr_report_detail = 'none';
    $tr_send_type = 'none';
}
else if($cod['cod_type']=='p'||$cod['cod_type']=='p2') {
    $tr_code_type_p_detail = '';
    $tr_report_detail = '';
    if($cod['cod_type']=='p2') {
        $items_p1 = 'none';
    }
    $tr_send_type = '';
}


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '예지 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

?>
<style>
.towhom_wrapper {border:solid 1px #333;background:#000;padding:10px;color:#fff;}
.towhom_form {margin-top:1px;}
.towhom_info {min-height:80px;border:solid 1px #555;background:#444;color:#fff;padding:10px;margin-top:5px;}
.set_send_type {margin-right:5px;}
.set_send_type input {margin-right:4px;}
label[disabled] {color:#fff;}
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
<input type="hidden" name="ser_cod_group" value="<?php echo $ser_cod_group ?>">
<input type="hidden" name="ser_cod_type" value="<?php echo $ser_cod_type ?>">
<input type="hidden" name="cod_code_count" value="<?php echo $cod['cod_code_count'] ?>">

<div class="local_desc01 local_desc" style="display:none;">
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
	<tr>
		<th scope="row">설비명(MMS)</th>
		<td>
            <input type="hidden" name="com_idx" value="<?=$cod['com_idx']?>"><!-- 업체번호 -->
            <input type="hidden" name="mms_idx" value="<?=$cod['mms_idx']?>"><!-- 설비번호 -->
            <input type="hidden" name="com_name" value="<?=$com['com_name']?>"><!-- 업체명 -->
			<input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required readonly>
            <a href="./mms_select.php?frm=fwrite&file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mms">설비찾기</a>
		</td>
		<th scope="row">IMP</th>
		<td>
            <input type="hidden" name="imp_idx" value="<?=$cod['imp_idx']?>"><!-- IMP번호 -->
            <input readonly type="text" placeholder="IMP명" name="imp_name" value="<?php echo $imp['imp_name'] ?>" id="imp_name" 
                    <?=$required_imp?> class="frm_input <?=$required_imp_class?>" style="width:130px;<?=$style_imp?>">
            <a href="./imp_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_imp">검색</a>
        </td>
	</tr>
	<tr>
        <th scope="row">코드</th>
		<td>
            <input type="text" name="cod_code" value="<?php echo $cod['cod_code'] ?>" id="cod_code" class="frm_input">
		</td>
		<th scope="row">분류</th>
		<td>
            <select name="trm_idx_category" id="trm_idx_category">
                <option value="0">에러코드 분류를 선택하세요.</option>
                <?=$category_select_options?>
			</select>
			<script>
                $('select[name="trm_idx_category"]').val('<?=${$pre}['trm_idx_category']?>');
            </script>
        </td>
	</tr>
	<tr>
        <th scope="row">알람내용</th>
		<td colspan="3">
			<input type="text" name="cod_name" value="<?php echo $cod['cod_name'] ?>" id="cod_name" class="frm_input"
            <?=(!$member['mb_manager_yn'])?'style="width:80%;border:no ne;" read only':'style="width:80%;"'?>>
		</td>
	</tr>
	<tr>
		<th scope="row">코드타입</th>
		<td>
            <?php echo help("알람이 너무 과할 때 '알람중지'로 변경하면 됩니다."); ?>
            <select name="cod_type" id="cod_type">
                <option value="">코드타입 선택</option>
                <?=$g5['set_cod_type_value_options']?>
			</select>
			<script>
                // 선택상태 변경
                $('select[name="cod_type"]').val('<?=${$pre}['cod_type']?>');

                // 코드타입 변경 시
                $(document).on('change','#cod_type',function(e){
                    if( $(this).val() == 'a' ) {
                        $('.tr_code_type_p_detail').hide();
                        $('.tr_report_detail').hide();
                        $('.tr_send_type').hide();
                    }
                    else if( $(this).val() == 'r' || $(this).val() == '' ) {
                        $('.tr_code_type_p_detail').hide();
                        $('.tr_report_detail').hide();
                        $('.tr_send_type').hide();
                    }
                    else if( $(this).val() == 'p'||$(this).val() == 'p2' ) {
                        $('.tr_code_type_p_detail').show();
                        $('.tr_report_detail').show();
                        if( !$('#cod_interval').val() )
                            $('#cod_interval').val('86400');
                        if( $(this).val() == 'p2' ) {
                            $('.items_p1').hide();
                        }
                        else {
                            $('.items_p1').show();
                        }
                        $('.tr_send_type').show();

                    }
                });
            </script>
        </td>
		<th scope="row">코드그룹</th>
		<td>
            <?php echo help("iMP에서 설정된 값입니다."); ?>
            <select name="cod_group" id="cod_group" style="display:none;">
                <?=$g5['set_data_group_options']?>
			</select>
			<script>
                $('select[name="cod_group"] option:gt(1)').remove(); // err, pre만 남겨두고 제거
                $('select[name="cod_group"]').val('<?=${$pre}['cod_group']?>');
            </script>
            <?=$g5['set_cod_group_value'][${$pre}['cod_group']]?>
        </td>
	</tr>
	<tr>
        <th scope="row">비가동영향</th>
		<td>
            <label for="cod_offline_yn">
               <input type="checkbox" name="cod_offline_yn" id="cod_offline_yn" value="1" <?=($cod['cod_offline_yn'])?'checked':''?> class="frm_input">
               비가동에 영향을 주는 코드입니다.
            </label>
		</td>
        <th scope="row">품질영향</th>
		<td>
            <label for="cod_quality_yn">
               <input type="checkbox" name="cod_quality_yn" id="cod_quality_yn" value="1" <?=($cod['cod_quality_yn'])?'checked':''?> class="frm_input">
               품질에 영향을 주는 코드입니다.
            </label>
		</td>
    </tr>
	<tr class="tr_code_type_p_detail" style="display:<?=$tr_code_type_p_detail?>;">
		<th scope="row">설정내용</th>
		<td colspan="3">
            <span class="items_p1" style="display:<?=$items_p1?>;">
                <select name="cod_interval" id="cod_interval">
                    <?=$g5['set_cod_interval_value_options']?>
                </select>
                <script>$('select[name="cod_interval"]').val('<?=${$pre}['cod_interval']?>');</script>
                <input type="text" name="cod_count" value="<?php echo $cod['cod_count'] ?>" id="cod_count" class="frm_input" style="width:50px;" placeholder="숫자">
                회 이상 알람 발생 시 예지,
                <span style="display:inline-block;width:108px;"></span>
            </span>
            하루 최대
			<input type="text" name="cod_count_limit" value="<?php echo $cod['cod_count_limit'] ?>" id="cod_count_limit" class="frm_input" style="width:50px;" placeholder="숫자">
            회까지만 예지발송 (과하게 발생시 발송중지)
		</td>
	</tr>
	<tr class="tr_report_detail" style="display:<?=$tr_report_detail?>;">
		<th scope="row"><label for="cod_towhom">알림대상 설정</label></th>
		<td colspan="3">

            <div class="towhom_wrapper">
                <div class="towhom_form">
                    <input type="text" name="mb_name" class="frm_input" style="width:100px;" placeholder="이름">
                    <input type="text" name="mb_role" class="frm_input" style="width:80px;" placeholder="직책">
                    <input type="text" name="mb_hp" class="frm_input" style="width:120px;" placeholder="휴대폰">
                    <input type="text" name="mb_email" class="frm_input" style="width:200px;" placeholder="이메일">
                    <a href="javascript:" class="btn btn_02 btn_mb_report" style="height:26px;line-height:26px;">추가</a>
                    <a href="javascript:" class="btn btn_00 btn_mb_add float_right" style="height:26px;line-height:26px;">일괄추가</a>
                    <a href="javascript:" class="btn btn_02 btn_mb_del float_right" style="height:26px;line-height:26px;margin-right:5px;">전체삭제</a>
                </div>
                <div class="towhom_info">
                    <ul>
                        <?php
                        for($i=0;$i<@sizeof($towhom_li);$i++) {
                            echo '<li>
                                    <span><i class="fa fa-remove"></i></span>
                                    <span class="r_name">'.$towhom_li[$i]['r_name'].'</span>
                                    <span class="r_role">'.$towhom_li[$i]['r_role'].'</span>
                                    <span class="r_hp">'.$towhom_li[$i]['r_hp'].'</span>
                                    <span class="r_email">'.$towhom_li[$i]['r_email'].'</span>
                                </li>
                            ';
                        }
                        ?>
                    </ul>
                </div>
            </div>

        </td>
	</tr>
	<tr class="tr_send_type" style="display:<?=$tr_send_type?>;">
		<th scope="row">메시지발송설정</th>
		<td colspan="3">
			<?php
            $ar['prefix'] = 'cod';
            $ar['com_idx'] = $_SESSION['ss_com_idx'];
            $ar['value'] = $com['com_send_type'];
            echo set_send_type($ar);
            unset($ar);
			// $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_send_type']));
			// foreach ($set_values as $set_value) {
            //     list($key, $value) = explode('=', $set_value);
            //     // 해당 업체 발송 설정을 먼저 체크해서 비활성 표현
            //     if(!preg_match("/".$key."/i",$com['com_send_type'])) {
            //         ${"disable_".$key} = ' disabled'; 
            //     }
			// 	${"checked_".$key} = (preg_match("/".$key."/i",$cod['cod_send_type'])) ? 'checked':''; 
			// 	echo '<label for="set_send_type_'.$key.'" class="set_send_type" '.${"disable_".$key}.'>
			// 			<input type="checkbox" id="set_send_type_'.$key.'" name="cod_send_type[]" value="'.$key.'" '.${"checked_".$key}.${"disable_".$key}.'>'.$value.'('.$key.')
			// 		</label>';
			// }
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="cod_memo">알림내용 (메모)</label></th>
		<td colspan="3"><textarea name="cod_memo" id="cod_memo" style="height:100px;"><?php echo $cod['cod_memo'] ?></textarea></td>
	</tr>
	<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
		<th scope="row"><label for="cod_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_cod_status_options']?>
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
	// 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		win_mms_select = window.open(href, "win_mms_select", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_mms_select.focus();
	});

    // IMP
    $(document).on('click','#btn_imp',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('설비를 먼저 선택하세요.');
        }
        else {
            var href = $(this).attr('href');
            winIMPSelect = window.open(href+'&com_idx='+com_idx,"winIMPSelect","left=100,top=100,width=520,height=600,scrollbars=1");
            winIMPSelect.focus();
        }
    });

    // 담당자 일괄추가
    $(document).on('click','.btn_mb_add',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('설비를 먼저 선택하세요.');
        }
        else {
            var href = './company_member_add.php?file_name=<?=$g5['file_name']?>';
            wimMemberSelect = window.open(href+'&com_idx='+com_idx,"wimMemberSelect","left=100,top=100,width=520,height=600,scrollbars=1");
            wimMemberSelect.focus();
        }
    });

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

    $('.towhom_info span[class^="r_"]').each(function(e){
        // console.log( $(this).html() );
        var this_val = $(this).text();
        var this_name = $(this).attr('class');
        $(this).append( $('<input type="hidden" name="'+this_name+'[]" value="'+this_val+'">') );
    });

    return true;
}

// 알림대상 추가
$(document).on('click','.btn_mb_report',function(e){
    e.preventDefault();
    var mb_name = $('.towhom_form').find('input[name=mb_name]').val();
    var mb_role = $('.towhom_form').find('input[name=mb_role]').val();
    var mb_hp = $('.towhom_form').find('input[name=mb_hp]').val();
    var mb_email = $('.towhom_form').find('input[name=mb_email]').val();
    if(mb_name=='') {
        alert('이름을 입력하세요.');
        return false;
    }
    if(mb_role=='') {
        alert('직책을 입력하세요.');
        return false;
    }
    if(mb_hp==''&&mb_email=='') {
        alert('휴대폰 또는 이메일 중 하나는 입력해 주셔야 합니다.');
        return false;
    }
    else {
        mb_dom = '<li>';
        mb_dom += ' <span><i class="fa fa-remove"></i></span>';
        mb_dom += ' <span class="r_name">'+mb_name+'</span>';
        mb_dom += ' <span class="r_role">'+mb_role+'</span>';
        mb_dom += ' <span class="r_hp">'+mb_hp+'</span>';
        mb_dom += ' <span class="r_email">'+mb_email+'</span>';
        mb_dom += '</li>';
        $('.towhom_info ul').append(mb_dom);
        $('.towhom_form input').val('');
    }
});
// report people remove 
$(document).on('click','.towhom_info .fa',function(e){
    $(this).closest('li').slideUp('mormal').promise().done(function() { $(this).remove(); });
});
// Remove all of report people
$(document).on('click','.btn_mb_del',function(e){
    $('.towhom_info ul li').slideUp('mormal').promise().done(function() { $(this).remove(); });
});

</script>

<?php
include_once ('./_tail.php');
?>
