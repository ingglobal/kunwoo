<?php
$sub_menu = "950300";
include_once("./_common.php");

//auth_check($auth[$sub_menu],'d');
if(!$member['mb_manager_yn']) {
    alert_close('메뉴 접근 권한이 없습니다.');
}

if(!$mb_id)
    alert_close("선택 회원이 존재하지 않습니다.");

$mb1 = get_table_meta('member','mb_id',$mb_id);

$g5['title'] = '회원권한 설정';
include_once('./_head.sub.php');
?>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="form01" id="form01" action="./manager_auth_setting_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="mb_id" value="<?php echo $mb_id ?>">
	<input type="hidden" name="token" value="">
    <div class=" new_win_con">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <tbody>
			<tr>
				<th scope="row" style="width:25%;">회원</th>
				<td>
                    <b><?=$mb1['mb_name']?></b> (<?=$mb1['mb_id']?>)
                    <br>
                    <?=$g5['department_up_names'][$mb1['mb_2']]?>
				</td>
			</tr>
			<tr>
				<th scope="row">회원선택</th>
				<td>
                    <?php echo help('선택한 회원과 동일한 권한으로 설정합니다.') ?>
					<input type="text" name="mb_id_saler" value="<?php echo $sra['mb_id_saler'] ?>" id="mb_id_saler" required class="frm_input required" style="width:35%;">
					<a href="./member_select.php?frm=form01&tar1=mb_id_saler" id="btn_member" class="btn_frmline ht26">검색</a>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sra_memo">메뉴권한</label></th>
				<td>
                    <?php
                    $sql = " SELECT * FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' ";
                    $rs = sql_query($sql,1);
                    for($i=0;$row=sql_fetch_array($rs);$i++) {
                        //print_r2($row);
                        $au_array[$row['au_menu']] = $row['au_auth'];
                    }
                    //print_r2($au_array);

                    //print_r2($menu);
                    foreach($menu as $key=>$value) {
                        //print_r2($menu[$key]).'<br>';
                        for($i=0;$i<sizeof($menu[$key]);$i++) {
                            //print_r2($menu[$key][$i]);
                            if( @array_key_exists($menu[$key][$i][0],$au_array) ) {
                                if( preg_match("/d/",$au_array[$menu[$key][$i][0]]) )
                                    $au_array[$menu[$key][$i][0]] = preg_replace("/d/","<span style='color:red;font-weight:bold;'>d</span>",$au_array[$menu[$key][$i][0]]);
                                echo '· '.$menu[$key][$i][1].' '.$au_array[$menu[$key][$i][0]].'<br>';
                            }
                        }
                    }
                    if(@count($au_array)==0)
                        echo '권한 없음';
                    ?>
                </td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <a href="../auth_list.php?sfl=a.mb_id&stx=<?=$mb_id?>" class="btn btn_04 btn_auth_list" style="margin-left:20px;display:<?=($is_admin!='super')?'none':''?>">상세설정</a>
        <input type="button" class="btn_close btn" value="창닫기" onclick="window.close();">
        <input type="button" class="btn_delete btn" value="삭제" style="display:<?=(!$sra_idx)?'none':'';?>;">
    </div>

    </form>

</div>

<script>
$(function() {

    $(".btn_auth_list").click(function() {
        var href = $(this).attr("href");
        opener.location = href;
        window.close();
    });

    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=110,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });

});

function form01_check(f)
{
	if (f.mb_id_saler.value=='') {
		alert("아이디를 입력하세요.");
		f.mb_id_saler.select();
		return false;
	}

    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>
