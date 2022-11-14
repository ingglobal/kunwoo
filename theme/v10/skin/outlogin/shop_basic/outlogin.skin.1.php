<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
$register_action_url = G5_BBS_URL.'/register_form_update.php';
$password_url = G5_HTTPS_BBS_URL."/password_lost2.php";
$req_nick = !isset($member['mb_nick_date']) || (isset($member['mb_nick_date']) && $member['mb_nick_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400)));
?>

<!-- 로그인 전 아웃로그인 시작 { -->
<section id="ol_before" class="ol">
    <h2>
        <strong class="sound_only">회원로그인</strong>
        <img src="<?=G5_THEME_IMG_URL?>/logo_bright4.png">
        <p>제조기업예측제어시스템<br>v 1.02</p>
    </h2>
    <form name="foutlogin" action="<?php echo $outlogin_action_url ?>" onsubmit="return fhead_submit(this);" method="post" autocomplete="off">
    <fieldset>
        <input type="hidden" name="outlogin_skin_path" value="<?php echo $outlogin_skin_path ?>">
        <input type="hidden" name="url" value="<?php echo $outlogin_url ?>">
        <label for="ol_id" id="ol_idlabel" class="sound_only">회원아이디<strong>필수</strong></label>
        <input type="text" id="ol_id" name="mb_id" required class="required frm_input" maxlength="20" placeholder="아이디">
        <label for="ol_pw" id="ol_pwlabel" class="sound_only">비밀번호<strong>필수</strong></label>
        <input type="password" name="mb_password" id="ol_pw" required class="required frm_input" maxlength="20" placeholder="비밀번호">
        <div id="ol_auto">
            <div class="chk_box">
                <input type="checkbox" name="auto_login" value="1" id="auto_login" class="selec_chk">
                <label for="auto_login" id="auto_login_label"><span></span>자동로그인</label>
            </div>
        </div>
        <input type="submit" id="ol_submit" value="로그인" class="btn_b02">
        <div id="ol_svc">
            <!--
            <a href="javascript:" class="reg_btn"><b>회원가입</b></a> /
            -->
            <a href="javascript:" class="ol_password_lost">정보찾기</a>
        </div>

    </fieldset>
    </form>
</section>

<script>
$omi = $('#ol_id');
$omp = $('#ol_pw');
$omi_label = $('#ol_idlabel');
$omi_label.addClass('ol_idlabel');
$omp_label = $('#ol_pwlabel');
$omp_label.addClass('ol_pwlabel');

$(function() {

    $("#auto_login").click(function(){
        if ($(this).is(":checked")) {
            if(!confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?"))
                return false;
        }
    });
    //.captcha_find, .captcha_regi
    $('.reg_btn').on('click',function(){
        $('.register').toggleClass('focus');
    });
    $('.register .register_bg, .register .btn_close').on('click',function(){
        $('.register').removeClass('focus');
    });

    //.captcha_find, .captcha_regi
    $('.ol_password_lost').on('click',function(){
        $('#find_info').toggleClass('focus');
        $('.captcha_regi').find('script').appendTo('.captcha_find');
        $('.captcha_regi').find('#captcha').appendTo('.captcha_find');
    });
    $('#find_info .find_info_bg, #find_info .btn_close').on('click',function(){
        $('#find_info').removeClass('focus');
        $('.captcha_find').find('script').appendTo('.captcha_regi');
        $('.captcha_find').find('#captcha').appendTo('.captcha_regi');
    });
});

function fhead_submit(f)
{
    return true;
}
</script>
<!-- } 로그인 전 아웃로그인 끝 -->
<?php
include_once($outlogin_skin_path.'/register_form.skin.php');
include_once($outlogin_skin_path.'/password_lost.skin.php');

