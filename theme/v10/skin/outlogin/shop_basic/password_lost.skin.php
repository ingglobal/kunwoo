<!-- 회원정보 찾기 시작 { -->
<div id="find_info">
    <div class="find_info_bg"></div>
    <div class="find_info">
        <h1 class="find_info_title">회원정보 찾기</h1>
        <form name="fpasswordlost" action="<?php echo $password_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
            <fieldset class="info_fs">
                <p>
                    회원가입 시 등록하신 이메일 주소를 입력해 주세요.<br>
                    해당 이메일로 아이디와 비밀번호 정보를 보내드립니다.
                </p>
                <label for="mb_email" class="sound_only">E-mail 주소<strong class="sound_only">필수</strong></label>
                <input type="text" name="mb_email" id="mb_email" required class="required frm_input full_input email" size="30" placeholder="E-mail 주소">
            </fieldset>
            <div class="is_captcha_use captcha_find"></div>
            <div class="find_info_btn">
                <button type="button" class="btn_close">취소</button>  
                <button type="submit" class="btn_submit">확인</button>
            </div>
        </form>
    </div>
</div>

<script>
function fpasswordlost_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    return true;
}

</script>
<!-- } 회원정보 찾기 끝 -->