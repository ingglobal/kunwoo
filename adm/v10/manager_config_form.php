<?php
$sub_menu = "915175";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

$g5['title'] = '관리자환경설정';
// include_once('./_top_menu_setting.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
/*
$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_message">메시지설정</a></li>
    <li><a href="#anc_cf_secure">관리설정</a></li>
</ul>';
*/
$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
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
		<tbody>
            <tr>
                <th scope="row">로그인실패 횟수</th>
                <td colspan="3">
                    <?php ;//echo help('0=특정기간,1=공통(전체기간)') ?>
                    <?php echo help('로그인 실패 허용 횟수를 기입하세요. 예) 5') ?>
                    <input type="number" name="set_loginfail" value="<?php echo (($g5['setting']['set_loginfail'])?$g5['setting']['set_loginfail']:'0') ?>" id="set_loginfail" required class="required frm_input" style="width:60px;">
                </td>
            </tr>
            <tr>
                <th scope="row">재로그인가능시간(분)</th>
                <td colspan="3">
                    <?php echo help('로그인실패후 재로그인 가능한 시간 분으로 기입하세요. 예) 10') ?>
                    <input type="number" name="set_relogin_min" value="<?php echo (($g5['setting']['set_relogin_min'])?$g5['setting']['set_relogin_min']:'0') ?>" id="set_relogin_min" required class="required frm_input" style="width:60px;">
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

    <?php ;//echo get_editor_js("set_expire_email_content"); ?>
    <?php ;//echo chk_editor_js("set_expire_email_content"); ?>
    <?php ;//echo get_editor_js("set_maintain_plan_content"); ?>
    <?php ;//echo chk_editor_js("set_maintain_plan_content"); ?>
    <?php ;//echo get_editor_js("set_error_content"); ?>
    <?php ;//echo chk_editor_js("set_error_content"); ?>

    f.action = "./manager_config_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>