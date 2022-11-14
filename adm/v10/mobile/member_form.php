<style>

</style>

<div class="local_desc01 local_desc" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>">
    <p>고객은 입퇴사에 따라 업체를 여러군데 옮겨다닐 수 있습니다. (기본 정보 등록 후 업체연결 정보를 관리하세요.)</p>
    <p style="display:<?=($w)?'none':''?>">고객 기본 정보를 먼저 등록하세요. 그 다음 등록된 업체와 연결할 수 있습니다.</p>
</div>

<form name="fmember" id="fmember" action="./member_form_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <tbody>
	<tr style="display:<?=($w)?:'none'?>">
		<td class="td_sales">
            <label for="mb_id_saler">업체별 직함</label><br>
			<?php
			$sql = "SELECT * 
					FROM {$g5['company_member_table']} AS cmm
						LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = cmm.com_idx
					WHERE cmm_status NOT IN ('trash','delete') AND mb_id = '".$mb['mb_id']."' ORDER BY cmm_reg_dt DESC ";
			$rs = sql_query($sql,1);
//			echo $sql.'<br>';
			for($i=0;$row=sql_fetch_array($rs);$i++) {
    			echo '<div class="div_member_company" style="font-size:1.2em;margin-top:10px;"><b>'.$row['com_name'].'</b><span style="margin-left:5px;">('.substr($row['cmm_reg_dt'],0,10).')</span> '.$mb['mb_name'].' '.$g5['set_mb_ranks_value'][$row['cmm_title']].'</div>';
			}
			?>
			<div class="div_salers" style="margin-bottom:5px;margin-top:5px;">
                <a href="javascript:" class="btn btn_02 btn_setting" id="btn_company">관리</a> (※ 고객이 여러 업체를 입퇴사를 할 수 있으므로 별도로 관리가 필요합니다.)
			</div>
		</td>
	</tr>
    <tr>
        <td>
            <label for="mb_id" class="sound_only">아이디</label>
            <input type="text" name="mb_id" value="<?php echo $mb['mb_id'] ?>" placeholder="아이디" id="mb_id" <?php echo $required_mb_id ?> class="frm_input <?php echo $required_mb_id_class ?>" size="15"  maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_password" class="sound_only">비밀번호</label>
            <input type="password" name="mb_password" id="mb_password" <?php echo $required_mb_password ?> placeholder="비밀번호" class="frm_input <?php echo $required_mb_password ?>" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_name" class="sound_only">이름(실명)<strong>필수</strong></label>
            <input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" placeholder="이름(실명)" id="mb_name" required class="required frm_input" size="15"  maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_nick" class="sound_only">닉네임<strong>필수</strong></label>
            <input type="text" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" placeholder="닉네임" id="mb_nick" required class="required frm_input" size="15"  maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_email" class="sound_only">E-mail<strong>필수</strong></label>
            <input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" placeholder="E-mail" id="mb_email" maxlength="100" required class="required frm_input email" size="30">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_homepage" class="sound_only">홈페이지</label>
            <input type="text" name="mb_homepage" value="<?php echo $mb['mb_homepage'] ?>" placeholder="홈페이지" id="mb_homepage" class="frm_input" maxlength="255" size="15">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_hp" class="sound_only">휴대폰번호</label>
            <input type="text" name="mb_hp" value="<?php echo $mb['mb_hp'] ?>" placeholder="휴대폰번호" id="mb_hp" class="frm_input" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_tel" class="sound_only">전화번호</label>
            <input type="text" name="mb_tel" value="<?php echo $mb['mb_tel'] ?>" placeholder="전화번호" id="mb_tel" class="frm_input" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_memo" class="sound_only">메모</label>
            <textarea name="mb_memo" placeholder="메모" id="mb_memo"><?php echo $mb['mb_memo'] ?></textarea>
        </td>
    </tr>

    <?php if ($w == 'u') { ?>
    <tr>
        <td>
            회원가입일 : <?php echo $mb['mb_datetime'] ?><br>
            최근접속일 : <?php echo $mb['mb_today_login'] ?><br>
            IP : <?php echo $mb['mb_ip'] ?>
        </td>
    </tr>
    <?php if ($config['cf_use_email_certify']) { ?>
    <tr>
        <td>
            <label for="passive_certify">인증일시</label><br>
            <?php if ($mb['mb_email_certify'] == '0000-00-00 00:00:00') { ?>
            <?php echo help('회원님이 메일을 수신할 수 없는 경우 등에 직접 인증처리를 하실 수 있습니다.') ?>
            <input type="checkbox" name="passive_certify" id="passive_certify">
            <label for="passive_certify">수동인증</label>
            <?php } else { ?>
            <?php echo $mb['mb_email_certify'] ?>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
    <?php } ?>

    <?php if ($config['cf_use_recommend']) { // 추천인 사용 ?>
    <tr>
        <td>추천인 : <?php echo ($mb['mb_recommend'] ? get_text($mb['mb_recommend']) : '없음'); // 081022 : CSRF 보안 결함으로 인한 코드 수정 ?></td>
    </tr>
    <?php } ?>

    <tr style="display:<?=($is_admin!='super')?'none':''?>;">
        <td>
            <label for="mb_leave_date" class="sound_only">탈퇴일자</label>
            <input type="text" name="mb_leave_date" value="<?php echo $mb['mb_leave_date'] ?>" placeholder="탈퇴일자" id="mb_leave_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_leave_date_set_today" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) {
this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }">
            <label for="mb_leave_date_set_today">오늘날짜로 지정</label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="mb_intercept_date" class="sound_only">접근차단일자</label>
            <input type="text" name="mb_intercept_date" value="<?php echo $mb['mb_intercept_date'] ?>" placeholder="접근차단일자" id="mb_intercept_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_intercept_date_set_today" onclick="if
(this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else {
this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }">
            <label for="mb_intercept_date_set_today">오늘날짜로 지정</label>
        </td>
    </tr>

    <?php
    //소셜계정이 있다면
    if(function_exists('social_login_link_account') && $mb['mb_id'] ){
        if( $my_social_accounts = social_login_link_account($mb['mb_id'], false, 'get_data') ){ ?>

    <tr>
        <td>
            <label class="sound_only">소셜계정목록</label>
            <ul class="social_link_box">
                <li class="social_login_container">
                    <h4>연결된 소셜 계정 목록</h4>
                    <?php foreach($my_social_accounts as $account){     //반복문
                        if( empty($account) ) continue;

                        $provider = strtolower($account['provider']);
                        $provider_name = social_get_provider_service_name($provider);
                    ?>
                    <div class="account_provider" data-mpno="social_<?php echo $account['mp_no'];?>" >
                        <div class="sns-wrap-32 sns-wrap-over">
                            <span class="sns-icon sns-<?php echo $provider; ?>" title="<?php echo $provider_name; ?>">
                                <span class="ico"></span>
                                <span class="txt"><?php echo $provider_name; ?></span>
                            </span>

                            <span class="provider_name"><?php echo $provider_name;   //서비스이름?> ( <?php echo $account['displayname']; ?> )</span>
                            <span class="account_hidden" style="display:none"><?php echo $account['mb_id']; ?></span>
                        </div>
                        <div class="btn_info"><a href="<?php echo G5_SOCIAL_LOGIN_URL.'/unlink.php?mp_no='.$account['mp_no'] ?>" class="social_unlink" data-provider="<?php echo $account['mp_no'];?>" >연동해제</a> <span class="sound_only"><?php echo substr($account['mp_register_day'], 2, 14); ?></span></div>
                    </div>
                    <?php } //end foreach ?>
                </li>
            </ul>
            <script>
            jQuery(function($){
                $(".account_provider").on("click", ".social_unlink", function(e){
                    e.preventDefault();

                    if (!confirm('정말 이 계정 연결을 삭제하시겠습니까?')) {
                        return false;
                    }

                    var ajax_url = "<?php echo G5_SOCIAL_LOGIN_URL.'/unlink.php' ?>";
                    var mb_id = '',
                        mp_no = $(this).attr("data-provider"),
                        $mp_el = $(this).parents(".account_provider");

                        mb_id = $mp_el.find(".account_hidden").text();

                    if( ! mp_no ){
                        alert('잘못된 요청! mp_no 값이 없습니다.');
                        return;
                    }

                    $.ajax({
                        url: ajax_url,
                        type: 'POST',
                        data: {
                            'mp_no': mp_no,
                            'mb_id': mb_id
                        },
                        dataType: 'json',
                        async: false,
                        success: function(data, textStatus) {
                            if (data.error) {
                                alert(data.error);
                                return false;
                            } else {
                                alert("연결이 해제 되었습니다.");
                                $mp_el.fadeOut("normal", function() {
                                    $(this).remove();
                                });
                            }
                        }
                    });

                    return;
                });
            });
            </script>

        </td>
    </tr>

    <?php
        }   //end if
    }   //end if
    ?>

    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
    <a href="./member_login.php?mb_id=<?=$mb_id?>" class="btn btn_02" style="font-size:1.5em;"><i class="fa fa-sign-in" aria-hidden="true"></i><span class="sound_only">임시로그인</span></a>
    <?php } ?>
    <a href="./member_list.php?<?php echo $qstr ?>" class="btn btn_02"><i class="fa fa-list" aria-hidden="true"></i></a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {

    // 업체관리 클릭
    $("#btn_company").click(function() {
        var href = "./member_company_list.php?mb_id=<?php echo $mb_id?>";
        winMemberCompany = window.open(href, "winMemberCompany", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMemberCompany.focus();
        return false;
    });
	
});


function fmember_submit(f)
{
    if (!f.mb_icon.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_icon.value) {
        alert('아이콘은 이미지 파일만 가능합니다.');
        return false;
    }

    if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
        alert('회원이미지는 이미지 파일만 가능합니다.');
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>