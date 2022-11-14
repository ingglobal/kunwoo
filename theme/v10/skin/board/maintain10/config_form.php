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
                <input type="text" name="bo_9" id="bo_9" value="<?php echo $board['bo_9'] ?>" style="width:80%;" required class="frm_input required" placeholder="상태값">
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
                <label class="frm_label">알림주기</label>
                <input type="text" name="bo_8" id="bo_8" value="<?php echo $board['bo_8'] ?>" style="width:80%;" class="frm_input" placeholder="알림주기">
                <span class="frm_info">
                    first=첫날만,every=매일, e2=2일마다, e3=3일마다,e7=7일마다,last=마지막날만
                </span>                
            </li>
            <li>
                <label class="frm_label">발송시간</label>
                <input type="text" name="bo_6" id="bo_6" value="<?php echo $board['bo_6'] ?>" style="width:80%;" class="frm_input" placeholder="발송시간">
                <span class="frm_info">
                    9=9시,13=13시, 18=18시, 23=23시
                </span>                
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