<?php
$sub_menu = "990165";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

if ($w == '') {
    $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = $config['cf_register_level'];
    $mb['mb_sales_notax'] = 1;
    $mb['mb_4'] = $member['mb_4'];    // 디폴트 = 나랑 같은 법인
    $html_title = '추가';
}
else if ($w == 'u')
{
    //$mb = get_member($mb_id);
    $mb = get_table_meta('member','mb_id',$mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] > $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    $required_mb_id = 'readonly';
    $required_mb_password = '';
    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 본인확인방법
switch($mb['mb_certify']) {
    case 'hp':
        $mb_certify_case = '휴대폰';
        $mb_certify_val = 'hp';
        break;
    case 'ipin':
        $mb_certify_case = '아이핀';
        $mb_certify_val = 'ipin';
        break;
    case 'admin':
        $mb_certify_case = '관리자 수정';
        $mb_certify_val = 'admin';
        break;
    default:
        $mb_certify_case = '';
        $mb_certify_val = 'admin';
        break;
}

// 본인확인
$mb_certify_yes  =  $mb['mb_certify'] ? 'checked="checked"' : '';
$mb_certify_no   = !$mb['mb_certify'] ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  $mb['mb_adult']      ? 'checked="checked"' : '';
$mb_adult_no        = !$mb['mb_adult']      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  $mb['mb_mailling']   ? 'checked="checked"' : '';
$mb_mailling_no     = !$mb['mb_mailling']   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  $mb['mb_sms']        ? 'checked="checked"' : '';
$mb_sms_no          = !$mb['mb_sms']        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';


if ($mb['mb_intercept_date']) $g5['title'] = "차단된 ";
else $g5['title'] .= "";

$g5['title'] = '관리자 '.$html_title;
include_once('./_head.php');


// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs;

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>

<form name="fmember" id="fmember" action="./manager_form_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs ?>">
<input type="hidden" name="mb_2_old" value="<?php echo $mb['mb_2'] ?>">
<input type="hidden" name="mb_level" value="<?php echo $mb['mb_level'] ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?php echo $sound_only ?></label></th>
        <td>
            <input type="text" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="reg_mb_id" <?php echo $required_mb_id ?> class="frm_input <?php echo $required_mb_id_class ?>" size="15"  maxlength="20">
            <a href="./member_select.php?file_name=<?php echo $g5['file_name']?>&mb_where=mb_level=2" class="btn btn_02" id="btn_member">회원검색</a>
        </td>
        <th scope="row"><label for="mb_password">비밀번호<?php echo $sound_only ?></label></th>
        <td>
            <?php if($w == 'u') { ?>
            <input type="password" name="mb_password" id="mb_password" <?php echo $required_mb_password ?> class="frm_input <?php echo $required_mb_password ?>" size="15" maxlength="20">
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_name">이름(실명)<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" required class="required frm_input" size="15"  maxlength="20" <?php if(auth_check($auth[$sub_menu],'d',1)) echo 'readonly';?>></td>
        <th scope="row"><label for="mb_nick">닉네임<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="reg_mb_nick" required class="required frm_input" size="15"  maxlength="20" <?php if(auth_check($auth[$sub_menu],'d',1)) echo 'readonly';?>></td>
    </tr>
	<tr>
		<th scope="row"><label for="mb_3">직급(직함)<strong class="sound_only">필수</strong></label></th>
		<td>
            <?php
            if(auth_check($auth[$sub_menu],'d',1)&&$member['mb_1']<=4)
                echo '<select name="mb_3" id="mb_3" title="직급선택" class=""  onFocus="this.initialSelect = this.selectedIndex;" onChange="this.selectedIndex = this.initialSelect;">';
            else
                echo '<select name="mb_3" id="mb_3" title="직급선택" class="">';
            ?>
				<option value="">직급선택</option>
                <?php echo get_set_options_select('set_mb_ranks',1, $member['mb_3'], $row['mb_3'], $sub_menu) ?>
			</select>
			<script>$('select[name=mb_3]').val('<?=$mb['mb_3']?>').attr('selected','selected');</script>
		</td>
        <th scope="row"><label for="mb_hp">휴대폰번호</label></th>
        <td><input type="text" name="mb_hp" value="<?php echo $mb['mb_hp'] ?>" id="reg_mb_hp" class="frm_input" size="15" maxlength="20"></td>
	</tr>
    <tr>
        <th scope="row"><label for="mb_email">E-mail<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_email" value="<?php echo $mb['mb_email'] ?>" id="reg_mb_email" maxlength="100" required class="required frm_input email" size="30"></td>
        <th scope="row">운영권한</th>
        <td>
            <label><input type="radio" name="mb_manager_yn" value="1" <?php echo ($mb['mb_manager_yn']) ? "checked" : ""; ?>> 있음</label>
            <label style="margin-left:10px;"><input type="radio" name="mb_manager_yn" value="0" <?php echo ($mb['mb_manager_yn']) ? "" : "checked"; ?>> 없음</label>
        </td>
    </tr>
    <tr style="display:<?php if($member['mb_1']<6) echo 'none';?>;">
        <th scope="row"><label for="mb_memo">메모</label></th>
        <td colspan="3"><textarea name="mb_memo" id="mb_memo"><?php echo $mb['mb_memo'] ?></textarea></td>
    </tr>
    <tr style="display:none;">
        <th scope="row">부가세포함매출</th>
        <td>
            <label><input type="radio" name="mb_sales_notax" value="1" <?php echo ($mb['mb_sales_notax']) ? "checked" : ""; ?>> 부가세포함안함(공급가)</label>
            <label style="margin-left:10px;"><input type="radio" name="mb_sales_notax" value="0" <?php echo ($mb['mb_sales_notax']) ? "" : "checked"; ?>> 부가세포함</label>
        </td>
        <th scope="row">원가반영매출</th>
        <td>
            <label><input type="radio" name="mb_sales_cost_yn" value="1" <?php echo ($mb['mb_sales_cost_yn']) ? "checked" : ""; ?>> 원가공제</label>
            <label style="margin-left:10px;"><input type="radio" name="mb_sales_cost_yn" value="0" <?php echo ($mb['mb_sales_cost_yn']) ? "" : "checked"; ?>> 원가공제안함</label>
        </td>
    </tr>

    <?php if ($w == 'u') { ?>
    <tr>
        <th scope="row">회원가입일</th>
        <td><?php echo $mb['mb_datetime'] ?></td>
        <th scope="row">최근접속일</th>
        <td><?php echo $mb['mb_today_login'] ?></td>
    </tr>
    <tr>
        <th scope="row">IP</th>
        <td><?php echo $mb['mb_ip'] ?></td>
        <th scope="row">디폴트업체번호</th>
        <td><input type="text" name="mb_4" value="<?php echo $mb['mb_4'] ?>" class="frm_input" size="3"></td>
    </tr>
    <?php if ($config['cf_use_email_certify']) { ?>
    <tr>
        <th scope="row">인증일시</th>
        <td colspan="3">
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

    <tr style="display:<?php if($is_admin!='super') echo 'none';?>;">
        <th scope="row"><label for="mb_leave_date">탈퇴일자</label></th>
        <td>
            <input type="text" name="mb_leave_date" value="<?php echo $mb['mb_leave_date'] ?>" id="mb_leave_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_leave_date_set_today" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) {
this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }">
            <label for="mb_leave_date_set_today">오늘날짜로 지정</label>
        </td>
        <th scope="row">접근차단일자</th>
        <td>
            <input type="text" name="mb_intercept_date" value="<?php echo $mb['mb_intercept_date'] ?>" id="mb_intercept_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_intercept_date_set_today" onclick="if
(this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else {
this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }">
            <label for="mb_intercept_date_set_today">오늘날짜로 지정</label>
        </td>
    </tr>
    <tr style="display:<?php if($is_admin!='super') echo 'none';?>;">
        <th scope="row">푸시키</th>
        <td colspan="3">
            <input type="text" name="mb_6" value="<?php echo $mb['mb_6'] ?>" id="mb_6" class="frm_input" style="width:400px;">
        </td>
    </tr>
    <tr style="display:<?php if(auth_check($auth[$sub_menu],'d',1)) echo 'none';?>;">
        <th scope="row"><label for="mb_employee_memo">관리자메모</label></th>
        <td colspan="3"><textarea name="mb_employee_memo" id="mb_employee_memo"><?php echo $mb['mb_employee_memo'] ?></textarea></td>
    </tr>

    <?php
    //소셜계정이 있다면
    if(function_exists('social_login_link_account') && $mb['mb_id'] ){
        if( $my_social_accounts = social_login_link_account($mb['mb_id'], false, 'get_data') ){ ?>

    <tr>
    <th>소셜계정목록</th>
    <td colspan="3">
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
    <?php if($member['mb_level'] >= 9) { ?>
    <a href="./member_login.php?mb_id=<?=$mb_id?>" class="btn btn_03">임시로그인</a>
    <?php } ?>
    <?php if(!auth_check($auth[$sub_menu],'d',1)) { ?>
    <a href="./manager_auth_setting.php?mb_id=<?=$mb_id?>" id="btn_member_auth" class="btn btn_02" style="margin-right:100px;">권한설정</a>
    <?php } ?>
    <a href="./manager_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    // 권한설정
    $(document).on('click','#btn_member_auth',function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
        memberauthwin = window.open(href,"memberauthwin","left=50,top=100,width=520,height=600,scrollbars=1");
        memberauthwin.focus();
    });

    // 회원검색
    $(document).on('click','#btn_member',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        memberfindwin = window.open(href,"memberfindwin","left=100,top=100,width=520,height=600,scrollbars=1");
        memberfindwin.focus();
    });
    
    $("#mb_enter_date, #mb_birth").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

});

function fmember_submit(f)
{
    // 닉네임 검사
    // empty_mb_nick, valid_mb_nick, count_mb_nick, exist_mb_nick, reserve_mb_nick 차례대로 다 검사합니다. 위치: /bbs/ajax.mb_nick.php (함수위치는 /lib/register.lib.php)
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        var msg = reg_mb_nick_check();
        if (msg) {
            alert(msg);
            f.reg_mb_nick.select();
            return false;
        }
    }

    // E-mail 검사
    // empty_mb_email, valid_mb_email, prohibit_mb_email, exist_mb_email 차례대로 다 검사합니다. 위치: /bbs/ajax.mb_hp.php (함수위치는 /lib/register.lib.php)
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) {
            alert(msg);
            f.reg_mb_email.select();
            return false;
        }
    }

    // 휴대폰번호 체크
    // 휴대폰은 valid_mb_hp 체크만 하고 중복(exist_mb_hp) 체크는 안 하고 있네. 왜지? 위치: /bbs/ajax.mb_hp.php (함수위치는 /lib/register.lib.php)
    var msg = reg_mb_hp_check();
    if (msg) {
        alert(msg);
        f.reg_mb_hp.select();
        return false;
    }

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
