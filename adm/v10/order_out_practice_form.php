<?php
$sub_menu = "930100";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$sql = " SELECT * FROM {$g5['order_practice_table']} AS orp
            LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
            LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
            LEFT JOIN {$g5['order_table']} AS ord ON oop.ord_idx = ord.ord_idx
            LEFT JOIN {$g5['bom_table']} AS bom ON oop.bom_idx = bom.bom_idx
        WHERE oop_idx = '{$oop_idx}'
";
$row = sql_fetch($sql,1);


// print_r3($row);
$readonly = ' readonly';
$required= ' required';

//원자재관련 정보 추출
$mtr_tbl = " SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = (
    SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = '{$row['bom_idx']}'
) ";
$mtr_sql = " SELECT bom_idx,bom_name,bom_part_no,bom_std FROM ( {$mtr_tbl} ) AS bit
                LEFT JOIN {$g5['bom_table']} AS bom ON bit.bom_idx_child = bom.bom_idx ";
$mtr_res = sql_query($mtr_sql,1);
$mtr = array();
if($mtr_res->num_rows){
for($mrow=0;$mrow=sql_fetch_array($mtr_res);$mrow++){
    $mtr[$mrow['bom_idx']] = $mrow['bom_name'].'('.$mrow['bom_std'].')';
}
}

if($w == ''){

}
else if($w == 'u' || $w == 'c'){

}

$html_title = ($w=='')?'추가':'수정';
$html_title = ($w=='c')?'복제':$html_title;
$g5['title'] = '생산계획 '.$html_title;
// $g5['title'] = '(제품별)출하생산계획 '.$html_title;
$g5['title'] .= ($w != '') ? ' - '.$row['bom_name'].'['.$row['bom_part_no'].']' : '';

$qstr .= ($calendar)?'&start_date='.$first_date.'&end_date='.$last_date:'';

include_once('./_head.php');
?>
<style>
.align_right{text-align:right;}
#cnt_per_time{}
#cnt_per_time:after{display:block;visibility:hidden;clear:both;content:'';}
#cnt_per_time li{float:left;margin-right:10px;text-align:center;}
#sp_orp_chk{}
#sp_ord_chk.ord_err{color:red;}
#sp_ord_chk.ord_ok{color:orange;}
#sp_oro_chk{}
#sp_oro_chk.oro_err{color:red;}
#sp_oro_chk.oro_ok{color:orange;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="orp_idx" value="<?php echo $row["orp_idx"] ?>">
<input type="hidden" name="oop_idx" value="<?php echo $row["oop_idx"] ?>">
<input type="hidden" name="trm_idx_line" value="<?php echo $row['trm_idx_line'] ?>">
<?php if($calendar){ ?>
<input type="hidden" name="calendar" value="1">
<input type="hidden" name="start_date" value="<?=$start_date?>">
<input type="hidden" name="end_date" value="<?=$end_date?>">
<?php } ?>

<div class="local_desc01 local_desc" style="display:none;">
    <p>각종 고유번호(설비번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
	<p class="txt_redblink" style="display:no ne;">설비idx=0 인 경우는 전체설비(설비 비선택 추가해라!!!)<br>설비idx 가 있으면 특정설비의 작업구간</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
	</colgroup>
	<tbody>
        <tr>
            <th scope="row">상품선택</th>
            <td>
                <a href="./bom_select2.php?file_name=<?=$g5['file_name']?>" class="btn btn_02" id="btn_ori">상품선택</a>
                <input type="hidden" name="bom_idx" id="bom_idx" value="<?=$row['bom_idx']?>">
                <input type="text" name="bom_name" id="bom_name" value="<?=$row['bom_name']?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>" style="width:300px;">
            </td>
            <th scope="row">원자재</th>
            <td>
                <a href="./mtr_select.php?file_name=<?=$g5['file_name']?>" class="btn btn_02" id="btn_mtr">원자재선택</a>
                <input type="hidden" name="mtr_bom_idx" id="mtr_bom_idx" value="<?=$row['mtr_bom_idx']?>">
                <input type="text" name="mtr_bom_name" id="mtr_bom_name" value="<?=$mtr[$row['mtr_bom_idx']]?>"<?=($required.$readonly)?> class="frm_input<?=($required.$readonly)?>" style="width:300px;">
            </td>
        </tr>
        <tr>
            <th scope="row">수주(수주일/ID)</th>
            <td>
                <a href="./ord_select.php?file_name=<?=$g5['file_name']?>" class="btn btn_02" id="btn_ord">수주선택</a>
                <input type="hidden" name="ori_idx" id="ori_idx" value="<?=$row['ori_idx']?>">
                <input type="text" name="ord_date" id="ord_date" placeholder="수주날짜" value="<?=$row['ord_date']?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:90px;"> /
                <input type="text" name="ord_idx" id="ord_idx" placeholder="수주ID" value="<?=$row['ord_idx']?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:80px;">
                <button type="button" class="btn btn_04 ord_cancel">취소</button>
            </td>
            <th scope="row">출하(예정일/ID)</th>
            <td>
                <a href="./oro_select.php?file_name=<?=$g5['file_name']?>" class="btn btn_02" id="btn_oro">출하선택</a>
                <input type="text" name="oro_date_plan" id="oro_date_plan" placeholder="출하예정일" value="<?=$row['oro_date_plan']?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:90px;"> /
                <input type="text" name="oro_idx" id="oro_idx" placeholder="출하ID" value="<?=(($row['oro_idx'])?$row['oro_idx']:'')?>"<?=$readonly?> class="frm_input<?=$readonly?>" style="width:80px;">
                <button type="button" class="btn btn_04 oro_cancel">취소</button>
            </td>
        </tr>
        <tr>
            <th scope="row">절단설비</th>
            <td>
                <select name="cut_mms_idx" class="frm_input cut_mms_idx">
                    <option value="0">외주작업</option>
                    <?=$g5['cut_options']?>
                </select>
                <script>
                    $('.cut_mms_idx').val('<?=(($row['cut_mms_idx'])?$row['cut_mms_idx']:"0")?>');
                </script>
            </td>
            <th scope="row">단조설비</th>
            <td>
                <select name="forge_mms_idx" class="frm_input forge_mms_idx">
                    <option value="0">외주작업</option>
                    <?=$g5['forge_options']?>
                </select>
                <script>
                    $('.forge_mms_idx').val('<?=(($row['forge_mms_idx'])?$row['forge_mms_idx']:"0")?>');
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">작업지시번호</th>
            <td>
                <?php
                if($w == ''){
                    $tdcode = preg_replace('/[ :-]*/','',G5_TIME_YMDHIS);
                    $orp_order_no = "ORP-".$tdcode."999";
                    echo '<input type="text" name="orp_order_no" value="'.$orp_order_no.'"'.$readonly.' class="frm_input'.$readonly.'" style="width:200px;">';
                }
                else if($w == 'u' || $w == 'c'){ 
                    echo $row['orp_order_no'];
                }
                ?>
                
            </td>
            <th scope="row">지시수량</th>
            <td>
                <input type="hidden" name="oop_count" id="oop_count" value="<?=$row['oop_count']?>">
                <strong id="st_oop_count" style="color:orange"><?=(($row['oop_count'])?$row['oop_count']:0)?></strong>
            </td>
        </tr>
        <tr>
            <th scope="row">주간수량</th>
            <td>
                <input type="text" name="oop_1" value="<?=$row['oop_1']?>" onclick="javascript:chk_Number(this)" class="oop_ex frm_input" style="text-align:right;width:70px;">
            </td>
            <th scope="row">야간수량</th>
            <td>
                <input type="text" name="oop_2" value="<?=$row['oop_2']?>" onclick="javascript:chk_Number(this)" class="oop_ex frm_input" style="text-align:right;width:70px;">
            </td>
        </tr>
        <tr>
            <th scope="row">생산시작일</th>
            <td>
                <input type="text" name="orp_start_date" id="orp_start_date" value="<?=(($row['orp_start_date'])?$row['orp_start_date']:'0000-00-00')?>" readonly class="readonly tbl_input" style="width:90px;background:#333 !important;text-align:center;">
            </td>
            <th scope="row">해당제품만단조</th>
            <td>
                <p>멀티단조에서 본제품에 문제(불량)가 발생하여<br>본제품만 따로 재생산해야 할경우 "예"로 선택하세요.<br><br></p>
                <select name="oop_onlythis_yn" id="oop_onlythis_yn">
                    <?=$g5['set_noyes_value_options']?>
                </select>
                <script>
                $('#oop_onlythis_yn').val('<?=(($w=='')?'0':$row['oop_onlythis_yn'])?>');   
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">메모</th>
            <td>
                <input type="text" name="oop_memo" id="oop_memo" class="frm_input" value="<?=$row['oop_memo']?>" style="width:80%;">
            </td>
            <th scope="row">상태</th>
            <td>
                <select name="oop_status" id="oop_status">
                    <?=$g5['set_oop_status_value_options']?>
                </select>
                <script>
                $('#oop_status').val('<?=(($w=='')?'done':$row['oop_status'])?>');   
                </script>
            </td>
        </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php
    $order_out_practice_url = ($calendar) ? './order_out_practice_calendar_list.php?'.$qstr:'./order_out_practice_list.php?'.$qstr;
    ?>
    <a href="<?=$order_out_practice_url?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function(){
    //생산일선택을 하면 [생산계획ID] 선택을 해제해야 한다.
    $("#orp_start_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소', onClose:function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val('0000-00-00');}}});

    // 생산제품(상품)선택 버튼 클릭
    $('#btn_ori').click(function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        var winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=700,scrollbars=1");
        winBomSelect.focus();
        return false;
    });

    // 원자재찾기 버튼 클릭
	$("#btn_mtr").click(function(e) {
		e.preventDefault();
        if(!$('#bom_idx').val()){
            alert('상품을 먼저 선택해 주세요.');
            $('#bom_name').focus();
            return false;
        }
        var href = $(this).attr('href')+'&bom_idx='+$('#bom_idx').val();
		var winMaterialSelect = window.open(href, "winMaterialSelect", "left=300,top=150,width=650,height=700,scrollbars=1");
        winMaterialSelect.focus();
        return false;
	});

    // 수주ID찾기 버튼 클릭
	$("#btn_ord").click(function(e) {
		e.preventDefault();
        if(!$('#bom_idx').val()){
            alert('상품을 먼저 선택해 주세요.');
            $('#bom_name').focus();
            $('#ori_idx').val('');
            $('#ord_date').val('');
            $('#ord_idx').val('');
            return false;
        }
        if(!$('#mtr_bom_idx').val()){
            alert('원자재를 먼저 선택해 주세요.');
            $('#mtr_bom_name').focus();
            return false;
        }
        var href = $(this).attr('href')+'&bom_idx='+$('#bom_idx').val();
		var winOrderSelect = window.open(href, "winOrderSelect", "left=300,top=150,width=650,height=700,scrollbars=1");
        winOrderSelect.focus();
        return false;
	});

    // 출하ID찾기 버튼 클릭
	$("#btn_oro").click(function(e) {
		e.preventDefault();
        if(!$('#bom_idx').val()){
            alert('상품을 먼저 선택해 주세요.');
            $('#bom_name').focus();
            $('#ori_idx').val('');
            $('#ord_date').val('');
            $('#ord_idx').val('');
            $('#oro_date_plan').val('');
            $('#oro_idx').val('');
            return false;
        }
        if(!$('#mtr_bom_idx').val()){
            alert('원자재를 먼저 선택해 주세요.');
            $('#mtr_bom_name').focus();
            return false;
        }
        if(!$('#ord_idx').val()){
            alert('수주데이터를 먼저 지정해 주세요.');
            $('#ord_idx').focus();
            $('#oro_date_plan').val('');
            $('#oro_idx').val('');
            return false;
        }
        var href = $(this).attr('href')+'&bom_idx='+$('#bom_idx').val()+'&ord_idx='+$('#ord_idx').val()+'&ori_idx='+$('#ori_idx').val();
		var winOrderOutSelect = window.open(href, "winOrderOutSelect", "left=300,top=150,width=650,height=700,scrollbars=1");
        winOrderOutSelect.focus();
        return false;
	});
});
//수주ID 취소는 출하ID도 취소한다.
$('.ord_cancel').on('click',function(){
    $('#ori_idx').val('');
    $('#ord_date').val('');
    $('#ord_idx').val('');

    $('#oro_date_plan').val('');
    $('#oro_idx').val('');
});

//출하ID 취소
$('.oro_cancel').on('click',function(){
    $('#oro_date_plan').val('');
    $('#oro_idx').val('');
});


//숫자만 입력
function chk_num(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

// 숫자만 입력, 합산계산입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
        var oop_sum = 0;
        $('.oop_ex').each(function(){
            oop_sum += Number($(this).val());
        });
        var oop_sum_str = (oop_sum) ? oop_sum : '';
        $('#oop_count').val(oop_sum_str);
        $('#st_oop_count').text(oop_sum_str);
    });
}


function form01_submit(f){
    
    //생산할 상품을 선택하세요
    if(!f.bom_idx.value){
        alert('생산할 상품을 선택해 주세요.');
        f.bom_name.focus();
        return false;
    }

    //원자재를 선택하세요
    if(!f.mtr_bom_idx.value){
        alert('원자재를 선택해 주세요.');
        f.mtr_bom_name.focus();
        return false;
    }

    //생산시작일을 설정해 주세요
    if(f.orp_start_date.value == '' || f.orp_start_date.value == '0000-00-00'){
        alert('생산시작일을 선택해 주세요.');
        f.orp_start_date.focus();
        return false;
    }
    
    //지시수량을 설정하세요.
    if(!f.oop_count.value){
        alert('지시수량을 설정해 주세요.');
        f.oop_count.focus();
        return false;
    }
    //상태값을 설정해 주세요
    if(!f.oop_status.value){
        alert('상태값을 선택해 주세요.');
        f.oop_status.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');