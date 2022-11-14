<?php
$sub_menu = "950910";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

$g5['title'] = '환경설정';
//include_once('./_top_menu_setting.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_secure">관리자설정</a></li>
</ul>';
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_default">
	<h2 class="h2_frm">기본설정</h2>
	<?php echo $pg_anchor ?>
	
	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>기본설정</caption>
		<colgroup>
			<col class="grid_4">
			<col>
			<col class="grid_4">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row">업체명</th>
			<td colspan="3">
				<?php echo help('메일발송 시 발신자명으로 나타나는 업체명입니다.') ?>
				<input type="text" name="de_admin_company_name" value="<?php echo $default['de_admin_company_name'] ?>" required class="required frm_input" style="width:120px;">
			</td>
		</tr>
		<tr>
			<th scope="row">대표전화번호</th>
			<td colspan="3">
				<?php echo help('메일발송 시 하단의 연락처입니다.') ?>
				<input type="text" name="de_admin_company_tel" value="<?php echo $default['de_admin_company_tel'] ?>" required class="required frm_input" style="width:120px;">
			</td>
		</tr>
		<tr>
			<th scope="row">대표메일</th>
			<td colspan="3">
				<?php echo help('메일발송 시 발신자 이메일에 나타나는 메일주소입니다.') ?>
				<input type="text" name="de_admin_info_email" value="<?php echo $default['de_admin_info_email'] ?>" required class="required frm_input" style="width:200px;">
			</td>
		</tr>
        <tr>
            <th scope="row">만료공지 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {법인명} {업체명} {담당자} {년월일} {승인명} {남은기간} {HOME_URL} {연락처} {이메일}'); ?>
                <input type="text" name="set_expire_email_subject" value="<?php echo $g5['setting']['set_expire_email_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_expire_email_content", get_text($g5['setting']['set_expire_email_content'], 0)); ?>
            </td>
        </tr>
		<tr>
			<th scope="row">인증코드설정</th>
			<td colspan="3">
				<?php echo help('한 줄에 한 항목씩 입력해 주세요.') ?>
                <textarea name="set_cri_code" id="set_cri_code"><?php echo get_text($g5['setting']['set_cri_code']); ?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">업체분류</th>
			<td colspan="3">
				<?php echo help('electricity=전기,electronic=전자,facility=설비,food=식품,parts=자재') ?>
				<input type="text" name="set_com_type" value="<?php echo $g5['setting']['set_com_type'] ?>" id="set_com_type" required class="required frm_input" style="width:90%;">
			</td>
		</tr>
		</tbody>
		</table>
	</div>
</section>

<section id="anc_cf_secure">
    <h2 class="h2_frm">관리자설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>관리자 관련 설정입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>관리자설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">관리자메모</th>
            <td>
                <?php echo help('관리자 메모입니다.') ?>
                <textarea name="set_memo_admin" id="set_memo_admin"><?php echo get_text($g5['setting']['set_memo_admin']); ?></textarea>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){

});

function fconfigform_submit(f) {

    <?php echo get_editor_js("set_expire_email_content"); ?>
    <?php echo chk_editor_js("set_expire_email_content"); ?>

    f.action = "./config_form_user_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
