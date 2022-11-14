<!-- 회원정보 입력/수정 시작 { -->

<div class="register">
<div class="register_bg"></div>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
    <script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
    <?php } ?>
    <div class="frm_box">
        <h4 class="register_ttl">회원가입정보 입력</h4>
        <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">
        <input type="hidden" name="agree" value="1">
        <input type="hidden" name="agree2" value="1">
        <input type="hidden" name="cert_type" value="">
        <input type="hidden" name="cert_no" value="">
        
        <div id="register_form" class="form_01">   
            <div class="register_form_inner">
                <h2>사이트 이용정보 입력</h2>
                <ul>
                    <li>
                        <label for="reg_mb_id">
                            아이디<strong class="sound_only">필수</strong>
                            <button type="button" class="tooltip_icon"><i class="fa fa-question-circle-o" aria-hidden="true"></i><span class="sound_only">설명보기</span></button>
                            <span class="tooltip">영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</span>
                        </label>
                        <input type="text" name="mb_id" value="" id="reg_mb_id" required class="frm_input full_input required" minlength="3" maxlength="20" placeholder="아이디">
                        <span id="msg_mb_id"></span>
                    </li>
                    <li class="half_input left_input margin_input">
                        <label for="reg_mb_password">비밀번호<strong class="sound_only">필수</strong></label>
                        <input type="password" name="mb_password" id="reg_mb_password" required class="frm_input full_input required" minlength="3" maxlength="20" placeholder="비밀번호">
                    </li>
                    <li class="half_input left_input">
                        <label for="reg_mb_password_re">비밀번호 확인<strong class="sound_only">필수</strong></label>
                        <input type="password" name="mb_password_re" id="reg_mb_password_re" required class="frm_input full_input required" minlength="3" maxlength="20" placeholder="비밀번호 확인">
                    </li>
                </ul>
            </div>
        
            <div class="tbl_frm01 tbl_wrap register_form_inner">
                <h2>개인정보 입력</h2>
                <ul>
                    <li>
                        <label for="reg_mb_name">이름<strong class="sound_only">필수</strong></label>
                        <input type="text" id="reg_mb_name" name="mb_name" value="" required class="frm_input full_input required" size="10" placeholder="이름">
                    </li>
                    <li>
                        <label for="reg_mb_nick">
                            닉네임<strong class="sound_only">필수</strong>
                            <button type="button" class="tooltip_icon"><i class="fa fa-question-circle-o" aria-hidden="true"></i><span class="sound_only">설명보기</span></button>
                            <span class="tooltip">공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)<br> 닉네임을 바꾸시면 앞으로 <?php echo (int)$config['cf_nick_modify'] ?>일 이내에는 변경 할 수 없습니다.</span>
                        </label>
                        
                        <input type="hidden" name="mb_nick_default" value="">
                        <input type="text" name="mb_nick" value="" id="reg_mb_nick" required class="frm_input required nospace full_input" size="10" maxlength="20" placeholder="닉네임">
                        <span id="msg_mb_nick"></span>	                
                    </li>
        
                    <li>
                        <label for="reg_mb_email">E-mail<strong class="sound_only">필수</strong>
                        
                        <?php if ($config['cf_use_email_certify']) {  ?>
                        <button type="button" class="tooltip_icon"><i class="fa fa-question-circle-o" aria-hidden="true"></i><span class="sound_only">설명보기</span></button>
                        <span class="tooltip">
                            <?php if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; }  ?>
                        </span>
                        <?php } ?>
                        </label>
                        <input type="hidden" name="old_email" value="">
                        <input type="text" name="mb_email" value="" id="reg_mb_email" required class="frm_input email full_input required" size="70" maxlength="100" placeholder="E-mail">
                    
                    </li>
        
                    <li>
                    <?php if ($config['cf_use_tel']) {  ?>
                        <label for="reg_mb_tel">전화번호<?php if ($config['cf_req_tel']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
                        <input type="text" name="mb_tel" value="" id="reg_mb_tel" <?php echo $config['cf_req_tel']?"required":""; ?> class="frm_input full_input <?php echo $config['cf_req_tel']?"required":""; ?>" maxlength="20" placeholder="전화번호">
                    <?php }  ?>
                    </li>
                    <li>
                    <?php if ($config['cf_use_hp'] || $config['cf_cert_hp']) {  ?>
                        <label for="reg_mb_hp">휴대폰번호<?php if ($config['cf_req_hp']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
                        
                        <input type="text" name="mb_hp" value="" id="reg_mb_hp" <?php echo ($config['cf_req_hp'])?"required":""; ?> class="frm_input full_input <?php echo ($config['cf_req_hp'])?"required":""; ?>" maxlength="20" placeholder="휴대폰번호">
                        <?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
                        <input type="hidden" name="old_mb_hp" value="">
                        <?php } ?>
                    <?php }  ?>
                    </li>
        
                    <?php if ($config['cf_use_addr']) { ?>
                    <li>
                        <label>주소</label>
                        <?php if ($config['cf_req_addr']) { ?><strong class="sound_only">필수</strong><?php }  ?>
                        <label for="reg_mb_zip" class="sound_only">우편번호<?php echo $config['cf_req_addr']?'<strong class="sound_only"> 필수</strong>':''; ?></label>
                        <input type="text" name="mb_zip" value="" id="reg_mb_zip" <?php echo $config['cf_req_addr']?"required":""; ?> class="frm_input twopart_input <?php echo $config['cf_req_addr']?"required":""; ?>" size="5" maxlength="6"  placeholder="우편번호">
                        <button type="button" class="btn_frmline" onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button><br>
                        <input type="text" name="mb_addr1" value="" id="reg_mb_addr1" <?php echo $config['cf_req_addr']?"required":""; ?> class="frm_input frm_address full_input <?php echo $config['cf_req_addr']?"required":""; ?>" size="50"  placeholder="기본주소">
                        <label for="reg_mb_addr1" class="sound_only">기본주소<?php echo $config['cf_req_addr']?'<strong> 필수</strong>':''; ?></label><br>
                        <input type="text" name="mb_addr2" value="" id="reg_mb_addr2" class="frm_input frm_address full_input" size="50" placeholder="상세주소">
                        <label for="reg_mb_addr2" class="sound_only">상세주소</label>
                        <br>
                        <input type="text" name="mb_addr3" value="" id="reg_mb_addr3" class="frm_input frm_address full_input" size="50" readonly="readonly" placeholder="참고항목">
                        <label for="reg_mb_addr3" class="sound_only">참고항목</label>
                        <input type="hidden" name="mb_addr_jibeon" value="">
                    </li>
                    <?php }  ?>
                </ul>
            </div>
            <div class="tbl_frm01 tbl_wrap register_form_inner">
                <h2>자동등록방지</h2>
                <ul>
                    <li class="is_captcha_use captcha_regi">
                        <?php echo captcha_html(); ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="btn_confirm">
            <a href="<?php echo G5_URL ?>" class="btn_close">취소</a>
            <button type="submit" id="btn_submit" class="btn_submit" accesskey="s">회원가입</button>
        </div>
        </form>
    </div>
</div>
<script>
$(function() {
    $("#reg_zip_find").css("display", "inline-block");
});

// submit 최종 폼체크
function fregisterform_submit(f)
{
    // 회원아이디 검사
    if (f.w.value == "") {
        var msg = reg_mb_id_check();
        if (msg) {
            alert(msg);
            f.mb_id.select();
            return false;
        }
    }

    if (f.w.value == "") {
        if (f.mb_password.value.length < 3) {
            alert("비밀번호를 3글자 이상 입력하십시오.");
            f.mb_password.focus();
            return false;
        }
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert("비밀번호가 같지 않습니다.");
        f.mb_password_re.focus();
        return false;
    }

    if (f.mb_password.value.length > 0) {
        if (f.mb_password_re.value.length < 3) {
            alert("비밀번호를 3글자 이상 입력하십시오.");
            f.mb_password_re.focus();
            return false;
        }
    }

    // 이름 검사
    if (f.w.value=="") {
        if (f.mb_name.value.length < 1) {
            alert("이름을 입력하십시오.");
            f.mb_name.focus();
            return false;
        }

        /*
        var pattern = /([^가-힣\x20])/i;
        if (pattern.test(f.mb_name.value)) {
            alert("이름은 한글로 입력하십시오.");
            f.mb_name.select();
            return false;
        }
        */
    }

    // 닉네임 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        var msg = reg_mb_nick_check();
        if (msg) {
            alert(msg);
            f.reg_mb_nick.select();
            return false;
        }
    }

    // E-mail 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) {
            alert(msg);
            f.reg_mb_email.select();
            return false;
        }
    }

    <?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
    // 휴대폰번호 체크
    var msg = reg_mb_hp_check();
    if (msg) {
        alert(msg);
        f.reg_mb_hp.select();
        return false;
    }
    <?php } ?>

    if (typeof f.mb_icon != "undefined") {
        if (f.mb_icon.value) {
            if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
                alert("회원아이콘이 이미지 파일이 아닙니다.");
                f.mb_icon.focus();
                return false;
            }
        }
    }

    if (typeof f.mb_img != "undefined") {
        if (f.mb_img.value) {
            if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
                alert("회원이미지가 이미지 파일이 아닙니다.");
                f.mb_img.focus();
                return false;
            }
        }
    }

    if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
        if (f.mb_id.value == f.mb_recommend.value) {
            alert("본인을 추천할 수 없습니다.");
            f.mb_recommend.focus();
            return false;
        }

        var msg = reg_mb_recommend_check();
        if (msg) {
            alert(msg);
            f.mb_recommend.select();
            return false;
        }
    }

    <?php echo chk_captcha_js();  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}

jQuery(function($){
	//tooltip
    $(document).on("click", ".tooltip_icon", function(e){
        $(this).next(".tooltip").fadeIn(400).css("display","inline-block");
    }).on("mouseout", ".tooltip_icon", function(e){
        $(this).next(".tooltip").fadeOut();
    });
});

</script>

<!-- } 회원정보 입력/수정 끝 -->