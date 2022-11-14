<?php
$sub_menu = '960120';
include_once('./_common.php');

$mb = get_table_meta('member','mb_id',$mb_id);
// print_r2($mb);

$g5['title'] = $mb['mb_name'].'님께 푸시발송';

include_once(G5_PATH.'/head.sub.php');
?>
<style>
/* html,body{overflow:hidden;} */
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_submit btn_close" onclick="window.close()">닫기</a>
	<?php } ?>

	<h1><?php echo $g5['title']; ?></h1>
    <div class="local_desc01 local_desc" style="display:none;">
        <p>본 페이지는 담당자를 간단하게 관리하는 페이지입니다.(아이디, 비번 임의생성)</p>
    </div>
	<div id="com_sch_list" class="new_win">

		<form name="form01" id="form01" action="./_win_push_one_send.php" onsubmit="return form01_check(this);" method="post">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="mb_id" value="<?php echo $mb_id ?>">
		<input type="hidden" name="token" value="">
		<div class=" new_win_con">
			<div class="tbl_frm01 tbl_wrap">
				<table>
				<caption><?php echo $g5['title']; ?></caption>
				<colgroup>
					<col class="grid_1" style="width:22%;">
					<col class="grid_3">
				</colgroup>
				<tbody>
				<tr>
					<th scope="row">푸시키</th>
					<td>
						<input type="text" name="push_key" value="<?=$mb['mb_6']?>" class="frm_input required" required style="width:100%;">
					</td>
				</tr>
				<tr>
					<th scope="row">타이틀</th>
					<td>
						<input type="text" name="push_title" value="" class="frm_input required" required style="width:100%;">
					</td>
				</tr>
				<tr>
					<th scope="row">메시지</th>
					<td colspan="3"><textarea name="push_content" required class="required"></textarea></td>
				</tr>
				<tr>
					<th scope="row">URL</th>
					<td>
						<input type="text" name="push_url" value="<?=G5_USER_ADMIN_URL?>" class="frm_input" style="width:100%;">
					</td>
				</tr>
				</tbody>
				</table>
			</div>
		</div>
		<div class="win_btn ">
			<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
		</div>
		</form>

	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(450,640)','onload':'parent.resizeTo(450,640)'});

function form01_check(f) {
    
	// if (f.mb_hp.value=='') {
	// 	alert("휴대폰을 입력하세요.");
	// 	f.mb_hp.select();
	// 	return false;
	// }

    return true;
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>