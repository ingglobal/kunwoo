<?php
$sub_menu = "960100";
include_once('./_common.php');

if ($member['mb_level']<10)
    alert('해당 메뉴에 접근 권한이 없습니다.');


$mms = get_table_meta('mms','mms_idx',$ser_mms_idx);
if (!$mms['mms_idx'])
    alert('존재하지 않는 설비입니다.');
	
$g5['title'] = '생산데이터 일괄수정';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$dta_start_dt = ($dta_start_dt) ?: date("Y-m-d H:i:s");
$dta_end_dt = ($dta_end_dt) ?: date("Y-m-d H:i:s");

$set_dta_dt_case = ($set_dta_dt_case) ?: 'batch';
$set_count_case = ($set_count_case) ?: 'batch';
$set_dta_date_case = ($set_dta_date_case) ?: 'same';

?>
<style>
.div_01 {margin-bottom:20px;}
.div_02 {margin-top:5px;padding-left:30px;}
.div_03 {margin-top:5px;}
</style>

<form name="form01" id="form01" action="./data_output_batch_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="token" value="">

<div class="local_desc01 local_desc">
	<p>일괄 작업은 위험합니다. 반드시 필요한 경우에만 작업해 주시기 바랍니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:85%;">
	</colgroup>
	<tbody>
	<tr> 
		<th scope="row">설비선택</th>
		<td>
            <?php
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
            echo '<select name="mms_idx" id="mms_idx" '.$items1[$k1][1].' required>'.$form_select.'</select>';
            echo "<script>$('select[name=mms_idx]').val('".$ser_mms_idx."').attr('selected','selected');</script>";
            ?>
		</td>
    </tr>
	<tr>
		<th scope="row">기종번호</th>
		<td>
			<?php echo help("기종 번호를 입력하세요."); ?>
			<input type="text" name="dta_mmi_no" value="<?=($sfl=='dta_mmi_no')?$stx:''?>" required class="required frm_input" style="width:30px;">
		</td>
	</tr>
	<tr>
		<th scope="row">작업범위</th>
		<td>
           <?php echo help("작업범위에 해당하는 날짜를 정확하게 입력해 주세요. 작업대상의 범위를 너무 크게 하지 마세요."); ?>
            <input type="text" name="dta_start_dt" value="<?=$dta_start_dt?>" id="dta_start_dt" required class="required frm_input">
            ~
            <input type="text" name="dta_end_dt" value="<?=$dta_end_dt?>" id="dta_end_dt" required class="required frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">작업설정</th>
		<td>
            <div class="div_01">
                <input type="checkbox" value="1" name="set_dta_dt" id="set_dta_dt" <?=($set_dta_dt)?'checked':''?>>
                <label for="set_dta_dt">날짜변경</label>
                <div class="div_02">
                    <input type="radio" name="set_dta_dt_case" value="batch" id="set_dta_dt_case_batch" <?=($set_dta_dt_case=='batch')?'checked':''?>>
                    <label for="set_dta_dt_case_batch">전체 조정</label>
                    <input type="radio" name="set_dta_dt_case" value="same" id="set_dta_dt_case_same" <?=($set_dta_dt_case=='same')?'checked':''?>>
                    <label for="set_dta_dt_case_same">동일값으로 설정</label>
                    <div class="div_03">
                        <div class="div_03_batch" style="display:<?=($set_dta_dt_case!='batch')?'none':''?>">
                            기존값 +
                            <input type="text" name="dta_time" value="1800" id="dta_time" class="frm_input" style="width:40px;"> 초
                            <span style="color:#818181;">(음수값도 가능)</span>
                        </div>
                        <div class="div_03_same" style="display:<?=($set_dta_dt_case!='same')?'none':''?>">
                            <input type="text" name="dta_dt" value="<?php echo date("Y-m-d H:i:s") ?>" id="dta_dt" class="frm_input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="div_01">
                <input type="checkbox" value="1" name="set_count" id="set_count"<?=($set_count)?'checked':''?>>
                <label for="set_count">생산카운터변경</label>
                <div class="div_02">
                    <input type="radio" name="set_count_case" value="batch" id="set_count_case_batch" <?=($set_count_case=='batch')?'checked':''?>>
                    <label for="set_count_case_batch">전체 조정</label>
                    <input type="radio" name="set_count_case" value="same" id="set_count_case_same" <?=($set_count_case=='same')?'checked':''?>>
                    <label for="set_count_case_same">동일값으로 설정</label>
                    <div class="div_03">
                        <div class="div_03_batch" style="display:<?=($set_count_case!='batch')?'none':''?>">
                            기존값 +
                            <input type="text" name="dta_updown" value="1" id="dta_updown" class="frm_input" style="width:40px;">
                            <span style="color:#818181;">(음수값도 가능)</span>
                        </div>
                        <div class="div_03_same" style="display:<?=($set_count_case!='same')?'none':''?>">
                            <input type="text" name="dta_value" value="1" id="dta_value" class="frm_input" style="width:40px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="div_01">
                <input type="checkbox" value="1" name="set_dta_date" id="set_dta_date" <?=($set_dta_date)?'checked':''?>>
                <label for="set_dta_date">통계일자변경</label>
                <div class="div_02">
                    <div class="div_03">
                        <div class="div_03_same">
                            <input type="text" name="dta_day" value="<?php echo date("Y-m-d") ?>" id="dta_day" class="frm_input" style="width:75px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="div_01">
                <input type="checkbox" value="1" name="set_shift_no" id="set_shift_no" <?=($set_shift_no)?'checked':''?>>
                <label for="set_shift_no">교대번호변경</label>
                <div class="div_02">
                    <div class="div_03">
                        <div class="div_03_same">
                            <input type="text" name="dta_shift_no" value="1" id="dta_shift_no" class="frm_input" style="width:25px;"> 교대
                        </div>
                    </div>
                </div>
            </div>

		</td>
	</tr>
	</tbody>
	</table>
</div>


<div class="btn_fixed_top">
	<input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<script>
$(function() {
    $(document).on('click','input[name=set_dta_dt_case]',function(e){
        // console.log( $(this).val() );  // same or batch
        $(this).closest('div.div_02').find('.div_03 div').hide();
        $(this).closest('div.div_02').find('.div_03_'+$(this).val()).show();
    });

    $(document).on('click','input[name=set_count_case]',function(e){
        $(this).closest('div.div_02').find('.div_03 div').hide();
        $(this).closest('div.div_02').find('.div_03_'+$(this).val()).show();
    });
	
});


function form01_submit(f) {

	if( $("input[name=ct_id]").val() == '' ) {
		alert('상품을 선택하세요. 상품을 선택하신 후 신청해 주세요.');
		return false;
	}
	
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
