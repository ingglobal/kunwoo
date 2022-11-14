<style>
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
    <div class="allchk_box">
		<input type="checkbox" value="1" id="chkall">
		<label for="chkall">전체선택</label>
	</div>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>이름</option>
    <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
    <option value="mb_nick"<?php echo get_selected($_GET['sfl'], "mb_nick"); ?>>닉네임</option>
    <option value="mb_email"<?php echo get_selected($_GET['sfl'], "mb_email"); ?>>E-MAIL</option>
    <option value="mb_tel"<?php echo get_selected($_GET['sfl'], "mb_tel"); ?>>전화번호</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>휴대폰번호</option>
    <option value="mb_datetime"<?php echo get_selected($_GET['sfl'], "mb_datetime"); ?>>가입일시</option>
    <option value="mb_ip"<?php echo get_selected($_GET['sfl'], "mb_ip"); ?>>IP</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc sound_only">
    <p>고객관리와 업체관리는 분리되어 있습니다. 고객을 등록하신 후 업체연동을 따로 해 주시면 되겠습니다. 업체 연동을 하시려면 [수정]으로 들어가세요.</p>
</div>


<form name="fmemberlist" id="fmemberlist" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 업체명 & 직함 추출 (제일 최근 거 한개만 불러옴)
        $sql2 = "   SELECT * 
                    FROM {$g5['company_member_table']} AS cmm
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = cmm.com_idx
                    WHERE cmm_status NOT IN ('trash','delete')
                        AND cmm.mb_id = '".$row['mb_id']."'
                    ORDER BY cmm_reg_dt DESC
                    LIMIT 1
        ";
//        echo $sql2.'<br>';
        $row['cmm'] = sql_fetch($sql2,1);
//        print_r2($row['cmm']);

        $s_mod = '<a href="./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="a_mod"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';

        $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mb_id'] ?>">
        <td>
            <div class="td_chk">
                <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </div>
            <p><b><?php echo $row['cmm']['com_name'] ?></b></p>
            <p>성명 : <?php echo get_text($row['mb_name']); ?>(<?php echo $g5['set_mb_ranks_value'][$row['cmm']['cmm_title']] ?>)</p>
            <p>ID : <?php echo $row['mb_id']; ?></p>
            <?php if($row['mb_hp']){ ?><p>HP : <?php echo get_text($row['mb_hp']); ?></p><?php } ?>
            <p><?php echo $row['mb_email']; ?></p>
            <p>등록일 : <?php echo substr($row['mb_datetime'],2,8); ?></p>
            <?php echo $s_mod ?><!-- 수정 -->
        </td>
    </tr>

        <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <a href="./member_form.php" id="member_add" class="btn btn_01"><i class="fa fa-plus" aria-hidden="true"></i></a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
    $('#chkall').on('change',function(){
		//alert($(this).is(':checked'));
		var chk = document.getElementsByName("chk[]");
		//alert(chk.length);
		for (i=0; i<chk.length; i++)
			chk[i].checked = $(this).is(':checked');
	});
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
            
        },
        mouseleave: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });
    

function fmemberlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>