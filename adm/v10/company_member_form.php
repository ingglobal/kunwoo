<?php
$sub_menu = "950400";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

if ($w == 'u') {
    $cmm = get_table_meta('company_member','cmm_idx',$cmm_idx);
    $com_idx = $cmm['com_idx'];
}
//print_r2($cmm);
//exit;

$com = get_table_meta('company','com_idx',$com_idx);
//print_r2($com);
//exit;
if(!$com['com_idx'])
    alert('업체 정보가 존재하지 않습니다.');
//	print_r2($com);


if ($w == '') {
    $html_title = '추가';

    $mb['mb_id'] = time();
    $mb['mb_nick'] = time();
    $mb['cri_status'] = 'ok';
}
else if ($w == 'u') {
    $mb = get_table_meta('member','mb_id',$cmm['mb_id']);
//	print_r2($mb);

    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = '담당자 '.$html_title;
include_once('./_head.sub.php');
?>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>본 페이지는 담당자를 간단하게 관리하는 페이지입니다.(아이디, 비번 임의생성)</p>
        <p>휴대폰 번호 중복 불가! (중복인 경우 이전 회원정보에 추가됩니다.)</p>
        <p>회원가입을 시키시고 관리자 승인 후 사용하게 하는 것이 더 좋습니다.</p>
    </div>

    <form name="form01" id="form01" action="./company_member_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="com_idx" value="<?php echo $com_idx ?>">
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
				<th scope="row">업체명</th>
				<td>
                    <div><?php echo $com['com_name'];?></div>
                    <div class="font_size_9">대표: <?php echo $com['com_president'];?></div>
				</td>
			</tr>
			<tr>
				<th scope="row">담당자명</th>
				<td>
                    <input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id" required class="frm_input required">
                    <input type="hidden" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" required class="frm_input required">
                    <input type="text" name="mb_name" value="<?=$mb['mb_name']?>" required class="frm_input required" style="width:100px;">
					<select name="cmm_title">
						<option value="">직함</option>
                        <?=$g5['set_mb_ranks_options_value']?>
					</select>
					<script>$('select[name=cmm_title]').val('<?=$cmm['cmm_title']?>').attr('selected','selected');</script>
				</td>
			</tr>
			<tr>
				<th scope="row">휴대폰</th>
				<td>
                    <input type="text" name="mb_hp" value="<?=$mb['mb_hp']?>" required class="frm_input required">
				</td>
			</tr>
			<tr>
				<th scope="row">이메일</th>
				<td>
                    <input type="text" name="mb_email" value="<?=$mb['mb_email']?>" class="frm_input" style="width:100%;">
				</td>
			</tr>
			<tr>
				<th scope="row">사원번호</th>
				<td>
                    <input type="text" name="mb_5" value="<?=$mb['mb_5']?>" class="frm_input" style="width:50%;">
				</td>
			</tr>
			<tr>
				<th scope="row">메모</th>
				<td colspan="3"><textarea name="mb_memo" id="mb_memo"><?php echo $mb['mb_memo'] ?></textarea></td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn btn_02" value="목록" onClick="self.location='./company_member_list.php?com_idx=<?=$com_idx?>'">
        <input type="button" class="btn_close btn" value="창닫기" onclick="javascript:opener.location.reload();window.close();">
        <input type="button" class="btn_delete btn" value="삭제" style="display:<?=(!$cmm_idx)?'none':'';?>;">
    </div>

    </form>

</div>

<script>
$(function() {

    // 휴대폰 중복 체크 (중복 회원이 있으면 이메일 주소 자동 입력)
    $(document).on('click','#btn_member',function(e){

    });

    $(".btn_delete").click(function() {
		if(confirm('정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./company_member_form_update.php?token="+token+"&w=d&cmm_idx=<?=$cmm['cmm_idx']?>";
		}
	});
});

function form01_check(f) {
    
    if (f.mb_name.value=='') {
		alert("담당자를 입력하세요.");
		f.mb_name.select();
		return false;
	}
	if (f.mb_hp.value=='') {
		alert("휴대폰을 입력하세요.");
		f.mb_hp.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
