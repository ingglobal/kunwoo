<?php
$sub_menu = "950400";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

if ($w == '') {
    $html_title = '추가';

    $cmm['cmm_status'] = 'ok';
}
else if ($w == 'u')
{
    $cmm = get_table_meta('company_member','cmm_idx',$cmm_idx);
    $com = get_table_meta('company','com_idx',$cmm['com_idx']);
    $mb_id = $cmm['mb_id'];
//	print_r2($cmm);
//	print_r2($com);

    $html_title = '수정';

    $com['com_name'] = get_text($com['com_name']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = '업체관련 정보 '.$html_title;
include_once('./_head.sub.php');
?>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="form01" id="form01" action="./member_company_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="mb_id" value="<?php echo $mb_id ?>">
	<input type="hidden" name="cmm_idx" value="<?php echo $cmm_idx ?>">
	<input type="hidden" name="token" value="">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">업체</th>
				<td>
                    <div class="com_name_txt" id="com_name_txt"><?php echo $com['com_name'];?></div>
					<input type="text" name="com_idx" value="<?php echo $cmm['com_idx'] ?>" id="com_idx" class="frm_input" style="width:35%;">
					<a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>&frm=form01&tar1=com_idx&tar2=com_name_txt" id="btn_company" class="btn_frmline">업체검색</a>
				</td>
			</tr>
			<tr>
				<th scope="row">직함선택</th>
				<td>
                    <select name="cmm_title" id="cmm_title" style="width:100px;">
                        <option value="">직급선택</option>
                        <?=$g5['set_mb_ranks_options_value']?>
                    </select>
                    <script>$("select[id=cmm_title]").val("<?=$cmm['cmm_title']?>").attr("selected","selected");</script>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cmm_memo">메모</label></th>
				<td colspan="3"><textarea name="cmm_memo" id="cmm_memo"><?php echo $cmm['cmm_memo'] ?></textarea></td>
			</tr>
			<tr style="display:none;">
				<th scope="row">상태</th>
				<td>
					<select name="cmm_status">
						<option value="">상태값선택</option>
						<?=$g5['set_status_options_value']?>
					</select>
					<script>$('select[name=cmm_status]').val('<?=$cmm['cmm_status']?>').attr('selected','selected');</script>
				</td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn btn_02" value="목록" onClick="self.location='./member_company_list.php?mb_id=<?=$mb_id?>'">
        <input type="button" class="btn_close btn" value="창닫기" onclick="window.close();">
        <input type="button" class="btn_delete btn" value="삭제" style="display:<?=(!$cmm_idx)?'none':'';?>;">
    </div>

    </form>

</div>

<script>
$(function() {
    $("#btn_company").click(function() {
        var href = $(this).attr("href");
        winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
        winCompany.focus();
        return false;
    });

    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./member_company_form_update.php?token="+token+"&w=d&cmm_idx=<?=$cmm_idx?>&mb_id=<?=$cmm['mb_id']?>";
		}
	});
});

function form01_check(f)
{
	if (f.com_idx.value=='') {
		alert("업체를 선택하세요.");
		f.com_idx.select();
		return false;
	}
	if (f.cmm_title.value=='') {
		alert("직함을 선택하세요.");
		f.cmm_title.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
