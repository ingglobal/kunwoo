<?php
include_once('./_common.php');
$sub_menu = $board['bo_1'];

$g5['title'] = $board['bo_subject'].' 환경설정';
include_once('./_head.php');

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

<div class="local_desc01 local_desc" style="display:no ne;">
    <ol>
        <li>게시판 관련 추가적인 설정(여분필드)을 하는 페이지입니다. 활용하실 여분 필드만 설정해 주시면 되겠습니다.</li>
        <li>기본적인 설정들은 게시판 목록 > 해당 게시판 수정 페이지에서 설정해 주세요.</li>
    </ol>
</div>

<form name="form01" class="config_form" action="./config_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">


<div id="anc_basic" class="tbl_frm01 tbl_wrap">
	<h2 class="h2_frm">기본정보</h2>
	<?php echo $pg_anchor ?>
	<table>
		<colgroup>
			<col class="grid_4" style="width:15%;">
			<col style="width:35%;">
			<col class="grid_4" style="width:15%;">
			<col style="width:35%;">
		</colgroup>
		<tbody>
            <tr>
                <th scope="row">관리자단 스킨</th>
                <td colspan="3">
                    <input type="text" name="set_skin_adm" value="<?php echo $board['set_skin_adm'] ?>" style="width:100px;" class="frm_input required" required placeholder="관리자단 스킨">
                </td>
            </tr>
            <tr>
                <th scope="row">상태값 (bo_9)</th>
                <td colspan="3">
                    <?=help('pending=대기,ok=정상,hide=숨김,trash=삭제')?>
                    <input type="text" name="bo_9" id="bo_9" value="<?php echo $board['bo_9'] ?>" style="width:85%;" class="frm_input" placeholder="상태값">
                </td>
            </tr>
            <tr>
                <th scope="row">타입 (bo_8)</th>
                <td colspan="3">
                    <?=help('ssak=싹산악회,seokuk=서국회,ansi=안시회,jaein=재인회,jaeseo=재서골...')?>
                    <input type="text" name="bo_8" id="bo_8" value="<?php echo $board['bo_8'] ?>" style="width:85%;" class="frm_input" placeholder="타입 설정">
                </td>
            </tr>
            <tr>
                <th scope="row">등록 초기상태값</th>
                <td colspan="3">
                    <?=help('상태값 항목값을 참고하셔서 값을 정확히 입력해 주세요. pending(대기)상태로 설정하면 완료로 바뀌기 전까지는 노출되지 않습니다.')?>
                    <input type="text" name="set_default_status" id="set_default_status" value="<?php echo $board['set_default_status'] ?>" style="width:100px;" class="frm_input" placeholder="예약초기상태값">
                </td>
            </tr>
        </tr>
		</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./board.php?bo_table=<?=$bo_table?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>


<script>
$(function(e){

});
</script>

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