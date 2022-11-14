<?php
$sub_menu = "915130";
include_once('./_common.php');
include_once(G5_USER_ADMIN_LIB_PATH.'/category.lib.php');
auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'bom';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_bom_type='.$ser_bom_type; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_count'] = 1;
    ${$pre}[$pre.'_moq'] = 1;
    ${$pre}[$pre.'_start_date'] = G5_TIME_YMD;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // print_r3(${$pre});
    $com = get_table_meta('company','com_idx',$bom['com_idx_customer']);
    $com2 = get_table_meta('company','com_idx',$bom['com_idx_provider']);

    // 가격 (오늘날짜 기준가격)
    ${$pre}['bom_price'] = get_bom_price(${$pre."_idx"});

    //완성품만 이미지를 등록한다.
    if(${$pre}['bom_type'] == 'product'){
        //관련파일 추출
        $flesql = " SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'bom'
        AND fle_type IN ('bomf1','bomf2','bomf3','bomf4','bomf5','bomf6')
        AND fle_db_id = '".${$pre."_idx"}."' ORDER BY fle_reg_dt,fle_idx ";
        //print_r3($flesql);
        $fle_rs = sql_query($flesql,1);

        $rowb['bom_bomf1'] = array();//1번째 파일그룹
        $rowb['bom_bomf1_idxs'] = array();//(fle_idx) 목록이 담긴 배열
        $rowb['bom_bomf2'] = array();//2번째 파일그룹
        $rowb['bom_bomf2_idxs'] = array();//(fle_idx) 목록이 담긴 배열
        $rowb['bom_bomf3'] = array();//3번째 파일그룹
        $rowb['bom_bomf3_idxs'] = array();//(fle_idx) 목록이 담긴 배열
        $rowb['bom_bomf4'] = array();//4번째 파일그룹
        $rowb['bom_bomf4_idxs'] = array();//(fle_idx) 목록이 담긴 배열
        $rowb['bom_bomf5'] = array();//5번째 파일그룹
        $rowb['bom_bomf5_idxs'] = array();//(fle_idx) 목록이 담긴 배열
        $rowb['bom_bomf6'] = array();//6번째 파일그룹
        $rowb['bom_bomf6_idxs'] = array();//(fle_idx) 목록이 담긴 배열

        for($i=0;$flerow=sql_fetch_array($fle_rs);$i++){
            //print_r3($flerow);
            $file_del = (is_file(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name'])) ? $flerow['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name']).'&file_name_orig='.$flerow['fle_name_orig'].'" file_path="'.$flerow['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$flerow['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$flerow['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$flerow['fle_type'].'_del['.$flerow['fle_idx'].']" id="del_'.$flerow['fle_idx'].'" value="1"> 삭제</label><br><img src="'.G5_URL.$flerow['fle_path'].'/'.$flerow['fle_name'].'" style="width:200px;height:auto;">':''.PHP_EOL;
            @array_push($rowb['bom_'.$flerow['fle_type']],array('file'=>$file_del));
            @array_push($rowb['bom_'.$flerow['fle_type'].'_idxs'],$flerow['fle_idx']);
        }
        //print_r3($rowb['bom_bomf1']);
    }
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정';
$g5['title'] = '제품(BOM) '.$html_title;
// print_r2($g5['line_reverse']['1라인']);
// exit;
include_once ('./_head.php');
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<style>
.bop_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
.btn_bop_delete {color:#0c55a0;cursor:pointer;margin-left:20px;}
a.btn_price_add {color:#3a88d8 !important;cursor:pointer;}
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;border:1px solid #333;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#000;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
label[for="bom_notax_yes"]{margin-right:20px;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{display:inline-block;font-size:14px;border:1px solid #444;background:#333;padding:2px 5px;border-radius:3px;line-height:1.2em;margin-top:5px;}
#sp_notice,#sp_ex_notice{color:yellow;margin-left:10px;}
#sp_notice.sp_error,#sp_ex_notice.sp_error{color:red;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="ser_bom_type" value="<?php echo $ser_bom_type ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>가격 변경 이력을 관리합니다. (가격 변동 날짜 및 가격을 지속적으로 기록하고 관리합니다.)</p>
    <p>가격이 변경될 미래 날짜를 지정해 두면 해당 날짜부터 변경될 가격이 적용됩니다.</p>
</div>
<?php //echo $rowb['bom_bomf1'][0]['file'];//print_r3($rowb['bom_bomf1']); ?>
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
        <?php
        $ar['id'] = 'bom_name';
        $ar['name'] = '품명';
        $ar['type'] = 'input';
        $ar['width'] = '100%';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['required'] = 'required';
        $ar['help'] = '제품명 or 자재명';
        echo create_td_input($ar);
        unset($ar);
        ?>
        <th scope="row">타입</th>
		<td>
            <select name="bom_type" id="bom_type">
                <option value="">선택하세요</option>
                <?=$g5['set_bom_type_options']?>
            </select>
            <script>
                $('select[name="<?=$pre?>_type"]').val('<?=${$pre}[$pre.'_type']?>');
            </script>
		</td>
    </tr>
	<tr>
		<th scope="row">카테고리</th>
		<td>
            <?php
            $cat = new category_list(${$pre}['bct_id']);
            echo $cat->run();
            ?>
		</td>
		<th scope="row">납품회사(고객처)</th>
		<td>
            <input type="hidden" name="com_idx_customer" value="<?=$bom['com_idx_customer']?>"><!-- 고객처번호 -->
			<input type="text" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" class="frm_input readonly" readonly>
            <a href="javascript:" link="./customer_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_customer">고객처찾기</a>
		</td>
    </tr>
    <tr>
        <th scope="row">제품코드(P/NO)</th>
        <td>
            <input type="text" name="bom_part_no" value="<?php echo ${$pre}['bom_part_no'] ?>" id="bom_part_no" required class="frm_input required" style="width:150px;" onkeyup="javascript:chk_Code(this)">
            <span id="sp_notice"></span>
        </td>
        <th scope="row">공급회사(매입처)</th>
		<td>
            <input type="hidden" name="com_idx_provider" value="<?=$bom['com_idx_provider']?>"><!-- 고객처번호 -->
			<input type="text" name="com_name2" value="<?php echo $com2['com_name'] ?>" id="com_name2" class="frm_input required" required readonly>
            <a href="jvaascript:" link="./customer_provider_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_provider">공급처찾기</a>
		</td>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'bom_moq';
        $ar['name'] = '최소구매수량';
        $ar['type'] = 'input';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['value_type'] = 'number';
        $ar['help'] = '구매단위를 숫자로 입력하세요.';
        $ar['width'] = '50px';
        if(${$pre}['bom_type'] != 'product'){
        $ar['colspan'] = '3';
        }
        $ar['unit'] = '개';
        $ar['form_script'] = 'onClick="javascript:chk_Number(this)"';
        echo create_td_input($ar);
        unset($ar);
        ?>
        <th scope="row">규격</th>
		<td>
            <input type="text" name="bom_standard" value="<?php echo ${$pre}['bom_standard'] ?>" id="bom_standard" class="frm_input" style="width:300px;">
		</td>
    </tr>
    <tr>
        <th scope="row">가격정보</th>
        <td>
            <?php
            $sql = " SELECT * FROM {$g5['bom_price_table']} WHERE bom_idx = '".${$pre}['bom_idx']."' ORDER BY bop_start_date ";
            // echo $sql.'<br>';
            $rs = sql_query($sql,1);
            for($i=0;$row=sql_fetch_array($rs);$i++) {
                // print_r2($row);
                echo '  <div class="div_bop">'
                            .number_format($row['bop_price']).' 원 <span class="bop_price">'.$row['bop_start_date'].'~</span>
                            <span class="btn_bop_delete" bop_idx="'.$row['bop_idx'].'">삭제</span>
                        </div>';
            }
            if(!$i) {
                echo '<div class="div_empty">가격 정보를 입력하세요.</div>';
            }

            if($w=='u')
                echo '<a href="javascript:" class="btn_price_add">추가</a>';
            ?>
        </td>
        <?php
        $ar['id'] = 'bom_notax';
        $ar['name'] = '비과세';
        $ar['type'] = 'radio';
        $ar['width'] = '80px';
        $ar['help'] = "비과세 상품인 경우만 '예'를 선택하세요.";
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <?php if(${$pre}['bom_type'] == 'product'){ ?>
    <tr>
        <th scope="row">IO타입여부</th>
        <td>
            <span class="frm_info">Inner/Outer듀얼생성제품이면 "예"를 선택하세요.<br>싱글생성제품이면 "아니오"를 선택하세요.</span>
            <?php
            $bom_io_y_chk = (${$pre}['bom_io_yn']) ? ' checked="checked"':'';
            $bom_io_n_chk = (!${$pre}['bom_io_yn']) ? ' checked="checked"':'';
            ?>
            <input type="radio" name="bom_io_yn" value="1" id="bom_io_y"<?=$bom_io_y_chk?>>
            <label for="bom_io_y">예</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="bom_io_yn" value="0" id="bom_io_n"<?=$bom_io_n_chk?>>
            <label for="bom_io_n">아니오</label>
            <script>
                <?php if($w == 'u'){ ?>
                $('#bom_io_n').attr('checked',true);
                <?php } ?>
            </script>
        </td>
        <th scope="row">고객업체(외부)라벨</th>
        <td>
            <input type="text" name="bom_ex_label" value="<?php echo ${$pre}['bom_ex_label'] ?>" id="bom_ex_label" class="frm_input" style="width:150px;text-transform:uppercase;" onkeyup="javascript:chk_exCode(this)">
            <span id="sp_ex_notice"></span>
        </td>
    </tr>
    <?php } ?>
    <tr class="tr_price" style="display:<?=($w=='u')?'none':''?>">
        <?php
        $ar['id'] = 'bom_price';
        $ar['name'] = '단가';
        $ar['type'] = 'input';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['unit'] = '원';
        $ar['value_type'] = 'number';
        $ar['form_script'] = 'onClick="javascript:chk_Number(this)"';
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'bom_start_date';
        $ar['name'] = '적용시작일';
        $ar['type'] = 'input';
        $ar['width'] = '90px';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <?php
    $ar['id'] = 'bom_memo';
    $ar['name'] = '메모';
    $ar['type'] = 'textarea';
    $ar['value'] = ${$pre}[$ar['id']];
    $ar['colspan'] = 3;
    echo create_tr_input($ar);
    unset($ar);
    ?>
    <tr>
        <th scope="row">알림최소재고수량</th>
        <td>
            <input type="text" name="bom_min_cnt" id="bom_min_cnt" value="<?=${$pre}[$pre.'_min_cnt']?>" class="frm_input" style="width:50px;text-align:right;" onclick="javascript:chk_Number(this)">개
        </td>
        <th scope="row">상태</th>
        <td>
            <select name="<?=$pre?>_status" id="<?=$pre?>_status"
                <?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_bom_status_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
    <?php if(false){ //($w == 'u' && ${$pre}['bom_type'] == 'product'){ ?>
    <tr>
        <th scope="row"><label for="multi_file1">모니터 이미지파일#1</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#1을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file1" name="bom_f1[]" multiple class="bom_file">
            <?php
            //print_r3($row);
            if(@count($rowb['bom_bomf1'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf1']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf1'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="multi_file2">모니터 이미지파일#2</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#2을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file2" name="bom_f2[]" multiple class="bom_file">
            <?php
            if(@count($rowb['bom_bomf2'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf2']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf2'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="multi_file3">모니터 이미지파일#3</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#3을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file3" name="bom_f3[]" multiple class="bom_file">
            <?php
            if(@count($rowb['bom_bomf3'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf3']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf3'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="multi_file4">모니터 이미지파일#4</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#4을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file4" name="bom_f4[]" multiple class="bom_file">
            <?php
            if(@count($rowb['bom_bomf4'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf4']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf4'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="multi_file5">모니터 이미지파일#5</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#5을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file5" name="bom_f5[]" multiple class="bom_file">
            <?php
            if(@count($rowb['bom_bomf5'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf5']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf5'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="multi_file6">모니터 이미지파일#6</label></th>
        <td colspan="3">
            <?php echo help("모니터 이미지파일#6을 등록하고 관리해 주시면 됩니다."); ?>
            <input type="file" id="multi_file6" name="bom_f6[]" multiple class="bom_file">
            <?php
            if(@count($rowb['bom_bomf6'])){
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($rowb['bom_bomf6']);$i++) {
                    echo "<li>[".($i+1).']'.$rowb['bom_bomf6'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
            }
            ?>
        </td>
    </tr>
    <?php } ?>
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
    //코드형식에 맞는지 확인
    chk_Code(document.getElementById('bom_part_no'));
    <?php if(${$pre}['bom_type'] == 'product'){ ?>
    chk_exCode(document.getElementById('bom_ex_label'));
    <?php } ?>

    <?php if($w == 'u' && ${$pre}['bom_type'] == 'product'){ ?>
    var bom_file_cnt = $('.bom_file').length;
    for(var i=1; i<=bom_file_cnt; i++){
        $('#multi_file'+i).MultiFile({
            max: <?=$g5['setting']['set_monitor_cnt']?>,
            accept: 'gif|jpg|png'
        });
    }
    <?php } ?>

    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격정보 보임 숨김
	$(".btn_price_add").click(function(e) {
        if( $('.tr_price').is(':hidden') ) {
            $('.tr_price').show();
        }
        else
           $('.tr_price').hide();
	});

    // 거래처찾기 버튼 클릭
	$("#btn_customer").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('link');
		winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winCustomerSelect.focus();
	});

    // 공급처찾기 버튼 클릭
    $("#btn_provider").click(function(e) {
        e.preventDefault();
        var href = $(this).attr('link');
        winProviderSelect = window.open(href, "winProviderSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winProviderSelect.focus();
    });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price], #bom_moq, #bom_lead_time',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	// 가격삭제
	$(document).on('click','.btn_bop_delete',function(e) {
		e.preventDefault();
		var bop_idx = $(this).attr('bop_idx');

		if(confirm('가격 정보를 삭제하시겠습니까?')) {

			//-- 디버깅 Ajax --//
			$.ajax({
				url:g5_user_admin_url+'/ajax/bom_price.php',
				data:{"aj":"del","bop_idx":bop_idx},
				dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
			//$.getJSON(g5_user_admin_url+'/ajax/com_item.json.php',{"aj":"del","bop_idx":bop_idx},function(res) {
				//alert(res.sql);
				if(res.result == true) {
                    // self.location.reload();
                    $('span[bop_idx='+bop_idx+']').closest('div.div_bop').remove();
				}
				else {
					alert(res.msg);
				}

				}, error:this_ajax_error	//<-- 디버깅 Ajax --//
			});
		}
	});

});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function chk_Code(object){
    var ex = /[\{\}\[\]\/?.,;:|\)*~`!^\+┼<>@\#$%&\'\"\\\(\=ㄱ-ㅎㅏ-ㅣ가-힣]*/g;
    var hx = /[A-Z0-9-_]{3,20}/;
    //var pt = /^[^-_][a-zA-Z0-9]+[-_]?[a-zA-Z0-9]+[-_]?[a-zA-Z0-9]+[^-_]$/;
    //var hx = /^[^-_][a-zA-Z0-9]+[-][a-zA-Z0-9]+[-][a-zA-Z0-9]+[^-_]$/; //한국수지만의 패턴
    object.value = object.value.replace(ex,"");//-_제외한 특수문자,한글입력 불가
    var str = object.value; 
    
    if(hx.test(str)){
        var bom_idx = '<?=${$pre."_idx"}?>';
        var com_chk_url = './ajax/bom_part_no_overlap_chk.php';
        var st = $.trim(str.toUpperCase());
        var msg = '등록 가능한 코드입니다.';
        object.value = st;
        document.getElementById('sp_notice').textContent = msg;
        $('#sp_notice').removeClass('sp_error');
        //디비에 bom_part_no가 존재하는지 확인하고 존재하면 에러를 발생
        //console.log(st);
        $.ajax({
            type : 'POST',
            url : com_chk_url,
            dataType : 'text',
            data : {'bom_idx' : bom_idx,'bom_part_no' : st},
            success : function(res){
                //console.log(res);
                if(res == 'ok'){
                    document.getElementById('sp_notice').textContent = '등록 가능한 코드입니다.';
                    $('#sp_notice').removeClass('sp_error');
                }
                else if(res == 'overlap'){
                    document.getElementById('sp_notice').textContent = '이미 등록된 코드입니다.';
                    $('#sp_notice').removeClass('sp_error');
                    $('#sp_notice').addClass('sp_error');
                }
                else if(res == 'same'){
                    document.getElementById('sp_notice').textContent = '제품코드 설정완료';
                    $('#sp_notice').removeClass('sp_error');
                }
            },
            error : function(xmlReq){
                alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
            }
        });
    }
    else {
        document.getElementById('sp_notice').textContent = '코드규칙에 맞지않습니다.';
        $('#sp_notice').removeClass('sp_error');
        $('#sp_notice').addClass('sp_error');
    }
}

<?php if(${$pre}['bom_type'] == 'product'){ ?>
function chk_exCode(object){
    var ex = /[\{\}\[\]\/?.,;:|\)*~`!^\+┼<>@\#$%&\'\"\\\(\=ㄱ-ㅎㅏ-ㅣ가-힣]*/g;
    var hx = /[A-Z0-9-_]{5,20}/;
    
    //var pt = /^[^-_][a-zA-Z0-9]+[-_]?[a-zA-Z0-9]+[-_]?[a-zA-Z0-9]+[^-_]$/;
    //var hx = /^[^-_][a-zA-Z0-9]+[-][a-zA-Z0-9]+[-][a-zA-Z0-9]+[^-_]$/; //한국수지만의 패턴
    object.value = object.value.replace(ex,"");//-_제외한 특수문자,한글입력 불가
    var str = object.value;  
    
    if(hx.test(str)){
        var st = $.trim(str.toUpperCase());
        var msg = '등록 가능한 외부라벨코드입니다.';
        object.value = st;
        document.getElementById('sp_ex_notice').textContent = msg;
        $('#sp_ex_notice').removeClass('sp_error');
    }
    else {
        if(str){
            document.getElementById('sp_ex_notice').textContent = '코드규칙에 맞지않습니다.';
            $('#sp_ex_notice').removeClass('sp_error');
            $('#sp_ex_notice').addClass('sp_error');
        }
        else {
            document.getElementById('sp_ex_notice').textContent = '코드가 입력되지 않았습니다.';
            $('#sp_ex_notice').removeClass('sp_error');
        }
    }
}
<?php } ?>

function form01_submit(f) {

    if($('#sp_notice').hasClass('sp_error')){
        alert('올바른 제품코드를 입력해 주세요.');
        $('input[name="bom_part_no"]').focus();
        return false;
    }

    if($('#sp_ex_notice').hasClass('sp_error')){
        alert('올바른 외부라벨 코드를 입력해 주세요.');
        $('input[name="bom_ex_label"]').focus();
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
