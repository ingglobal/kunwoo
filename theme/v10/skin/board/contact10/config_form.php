<?php
include_once('./_common.php');

$g5['title'] = $board['bo_subject'].' 환경설정';
include_once('./_head.php');

if(!$is_admin)
    alert('관리자만 접속할 수 있습니다.');

// bo_7=>longtext 환경설정 확장변수로 사용하기 위함
$q = sql_query( 'DESCRIBE '.$g5['board_table'] );
while($row = sql_fetch_array($q)) {
    if($row['Field']=='bo_7' && $row['Type']=='varchar(255)') {
        //echo $row['Field'].' - '.$row['Type'].'<br>';
        sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_7` `bo_7` longtext ", true);
    }
}


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>

<section id="bo_config">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

	<div class="config_caution">
		<ol>
			<li>환경 설정 페이지입니다. 설정 항목들을 확인하시고 필요한 설정을 해 주시기 바랍니다.</li>
            <li>기타 게시판 관련 상세 설정은 게시판관리 페이지에서 설정해 주세요.</li>
		</ol>
	</div>
    
    <form name="form01" class="config_form" action="./config_form_update.php" onsubmit="return form_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    
    <div class="tbl_frm01 tbl_wrap">
        <h2>기본 환경설정</h2>
        <ul>
            <li>
                <label class="frm_label">상태값</label>
                <input type="text" name="bo_9" id="bo_9" value="<?php echo $board['bo_9'] ?>" style="width:80%;" required class="frm_input required" placeholder="예약상태값">
                <span class="frm_info">
                    pending=대기,ok=완료,trash=삭제
                </span>                
            </li>
            <li>
                <label class="frm_label">등록 초기상태값</label>
                <input type="text" name="set_default_status" id="set_default_status" value="<?php echo $board['set_default_status'] ?>" style="width:100px;" class="frm_input" placeholder="등록초기상태값">
                <span class="frm_info">
                    상태값 항목값을 참고하셔서 값을 정확히 입력해 주세요. pending(대기)상태로 설정하면 신청 내용을 관리자가 확인한 후 상태값을 완료로 바꿔 주어야 합니다.
                </span>                
            </li>
            <li>
                <label class="frm_label">작업등급</label>
                <input type="text" name="bo_8" id="bo_8" value="<?php echo $board['bo_8'] ?>" style="width:80%;" class="frm_input" placeholder="작업등급">
                <span class="frm_info">
                    S=S(3시간이상),A=A(3시간),B=B(2시간),C=C(1시간),D=D(30분),E=E(15분),F=F(5분미만)
                </span>                
            </li>
            <li>
                <label class="frm_label">등급보기 가능아이디</label>
                <input type="text" name="bo_2" id="bo_2" value="<?php echo $board['bo_2'] ?>" style="width:80%;" class="frm_input" placeholder="등급보기 가능아이디">
                <span class="frm_info">
                    <?php
                    $bo_2s = explode(',', preg_replace("/\s+/", "", $board['bo_2']));
                    for($i=0;$i<sizeof($bo_2s);$i++) {
                        $mb1 = get_member($bo_2s[$i]);
                        echo ($mb1['mb_name']) ? $mb1['mb_name'].', ' : $mb1['mb_id'].', ';
                    }
                    ?>
                    <br>
                    작업등급을 볼 수 있는 아이디들을 입력하세요. 구분자는 쉼표입니다.
                </span>                
            </li>
            <li>
                <label class="frm_label">등급관리 조직코드</label>
                <input type="text" name="bo_3" id="bo_3" value="<?php echo $board['bo_3'] ?>" style="width:80%;" class="frm_input" placeholder="운영관리 조직코드(ex. CS팀,기획팀)">
                <span class="frm_info">
                    <?php
                    $bo_3s = explode(',', preg_replace("/\s+/", "", $board['bo_3']));
                    for($i=0;$i<sizeof($bo_3s);$i++) {
                        echo $g5['department_name'][$bo_3s[$i]].', ';
                    }
                    ?>
                    <br>
                    게시판을 운영관리해야 하는 조직 코드들을 입력하세요. 구분자는 쉼표입니다.
                </span>                
            </li>
            <li>
                <label class="frm_label">작업관리 조직코드</label>
                <input type="text" name="bo_4" id="bo_4" value="<?php echo $board['bo_4'] ?>" style="width:80%;" class="frm_input" placeholder="작업 조직코드(ex. 디자인팀,개발팀)">
                <span class="frm_info">
                    <?php
                    $bo_4s = explode(',', preg_replace("/\s+/", "", $board['bo_4']));
                    for($i=0;$i<sizeof($bo_4s);$i++) {
                        echo $g5['department_name'][$bo_4s[$i]].', ';
                    }
                    ?>
                    <br>
                    디자인팀, 개발팀과 같이 작업을 부여받고 작업하는 팀의 조직 코드들을 입력하세요. 구분자는 쉼표입니다.
                </span>                
            </li>
            <li style="display:none;">
                <label for="set_hp_yn" class="frm_label">휴대폰 정보</label>
                <label class="check_radio"><input type="checkbox" name="set_hp_yn" value="1" id="set_hp_yn" <?php if($board['set_hp_yn']) echo 'checked';?>>휴대폰번호 항목을 포함합니다.</label>
                <label class="check_radio"><input type="checkbox" name="set_hp_required" value="1" id="set_hp_required" <?php if($board['set_hp_required']) echo 'checked';?>>휴대폰번호 필수</label>
                <span class="frm_info">
                    예약 항목에 휴대폰 번호를 입력받게 하시려면 체크하세요. 필수 입력항목으로 설정하시려면 체크하세요.
                </span>
            </li>
            <li style="display:none;">
                <label for="set_email_required" class="frm_label">이메일 필수</label>
                <label class="set_email_required"><input type="checkbox" name="set_email_required" value="1" id="set_email_required" <?php if($board['set_email_required']) echo 'checked';?>>이메일 필수</label>
                <span class="frm_info">
                    반드시 입력해야 하는 필수항목으로 설정하시려면 체크하세요.
                </span>
            </li>
            <li style="display:none;">
                <label for="set_name_type" class="frm_label">작성자표시</label>
                <label class="check_radio"><input type="radio" name="set_name_type" value="0" id="set_name_type" <?php if(!$board['set_name_type']) echo 'checked';?>>홍길동</label>
                <label class="check_radio"><input type="radio" name="set_name_type" value="1" id="set_name_type" <?php if($board['set_name_type']==1) echo 'checked';?>>홍♡♡</label>
                <label class="check_radio"><input type="radio" name="set_name_type" value="2" id="set_name_type" <?php if($board['set_name_type']==2) echo 'checked';?>>♡길♡</label>
                <label class="check_radio"><input type="radio" name="set_name_type" value="3" id="set_name_type" <?php if($board['set_name_type']==3) echo 'checked';?>>♡♡동</label>
                <span class="frm_info">
                    작성자 이름이 표시되는 스타일을 선택하세요. 기본값은 홍길동 형태입니다.
                </span>
            </li>
            <li style="display:none;">
                <label for="set_policy_yn" class="frm_label">약관</label>
                <label class="check_radio"><input type="checkbox" name="set_policy_yn" value="1" id="set_policy_yn" <?php if($board['set_policy_yn']) echo 'checked';?>>개인정보보호정책을 표시합니다.</label>
                <span class="frm_info">
                    개인정보보호정책이 표시되면 반드시 동의를 해야 예약을 할 수 있습니다.
                    <br>
                    표준약관을 사용하실 경우 이름, 연락처 정보 등을 수정해 주셔야 합니다. 홍길동, 02-1111-2222와 같은 정보들을 그대로 두시면 안 됩니다.
                </span>
                <textarea name="set_policy_content" id="set_policy_content" class="set_policy_content" placeholder="약관내용"><?php echo stripslashes(base64_decode($board['set_policy_content'])) ?></textarea>
            </li>
        </ul>
        
        <div class="btn_confirm write_div">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?=$bo_table?>" class="btn_cancel btn">취소</a>
            <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
        </div>
        
    </div>
    </form>

</section>

<script>
function form_submit(f)
{

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}
</script>


<?php
include_once('./_tail.php');
?>