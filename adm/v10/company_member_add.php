<?php
// 호출페이지들
// /adm/v10/error_code_form.php: 알람/예지수정
include_once("./_common.php");

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

if(!$com_idx)
    alert_close('업체 정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

$sql_common = " FROM {$g5['company_member_table']} AS cmm
                 LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cmm.mb_id
";

$where = array();
$where[] = " cmm_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// 업체조건
$where[] = " cmm.com_idx = '".$_REQUEST['com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "cmm_reg_dt";
    $sod = "DESC";
}

$sql_order = " order by {$sst} {$sod} ";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '업체담당자';
include_once('./_head.sub.php');

$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order} ";
//echo $sql.'<br>';
$result = sql_query($sql);

?>
<style>
    .btn_fixed_top {top: 9px;}
    .member_company_brief {margin:10px 0;}
    .member_company_brief span {font-size:1.3em;}
</style>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>
    <div class=" new_win_con">
        <div class="member_company_brief">
        <span><?=$com['com_name']?></span> (대표: <?=$com['com_president']?>)
        </div>
        
        <form name="form01" id="form01"  method="post">
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
            <tr>
                <th scope="col" id="mb_list_chk" style="display:no ne;">
                    <label for="chkall" class="sound_only">담당자 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col">이름</th>
                <th scope="col">직급</th>
                <th scope="col">휴대폰</th>
                <th scope="col">이메일</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {

                $s_mod = '<a href="./company_member_form.php?'.$qstr.'&amp;w=u&amp;cmm_idx='.$row['cmm_idx'].'" class="btn btn_03">수정</a>';

                $bg = 'bg'.($i%2);
            ?>

            <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['cmm_idx'] ?>" >
                <td headers="mb_list_chk" class="td_chk" style="display:no ne;">
                    <input type="hidden" name="cmm_idx[<?php echo $i ?>]" value="<?php echo $row['cmm_idx'] ?>" id="cmm_idx_<?php echo $i ?>">
                    <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
                    <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                </td>
                <td class="td_mb_name"><?php echo get_text($row['mb_name']); ?></td>
                <td class="td_mb_rank"><?=$g5['set_mb_ranks_value'][$row['cmm_title']]?></td>
                <td class="td_mb_hp"><?=$row['mb_hp']?></td>
                <td class="td_mb_email"><?=$row['mb_email']?></td>
            </tr>
            <?php
            }
            if ($i == 0)
                echo "<tr><td colspan='5' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
            </table>

            <div class="win_btn ">
                <input type="submit" name="act_button" value="선택추가" class="btn_01 btn">
                <button type="button" onclick="window.close();" class="btn btn_close">창닫기</button>
            </div>

        </div>
        </form>


        <div class="btn_fixed_top">
            <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
            <?php if($member['mb_manager_yn']) { ?>
            <a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>" id="btn_company" class="btn btn_03">업체검색</a>
            <?php } ?>
        </div>        
        
    </div>
</div>

<script>
$(function() {
    // 업체검색
    $("#btn_company").click(function() {
        var href = $(this).attr("href");
        winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
        winCompany.focus();
        return false;
    });

    <?php
    // 담당자추가
    if($file_name=='error_code_form') {
    ?>
        $("#form01").submit(function(e){
            e.preventDefault();
            // 체크한 항목을 추가
            if (!is_checked("chk[]")) {
                alert("추가하실 항목을 하나 이상 선택하세요.");
                return false;
            }
            else {

                mb_dom = '';
                $('input[name="chk[]"]').each(function(e){
                    if($(this).is(':checked')) {
                        // console.log( $(this).closest('tr').html() );
                        this_tr = $(this).closest('tr');
                        mb_dom += '<li>';
                        mb_dom += ' <span><i class="fa fa-remove"></i></span>';
                        mb_dom += ' <span class="r_name">'+this_tr.find('.td_mb_name').text()+'</span>';
                        mb_dom += ' <span class="r_role">'+this_tr.find('.td_mb_rank').text()+'</span>';
                        mb_dom += ' <span class="r_hp">'+this_tr.find('.td_mb_hp').text()+'</span>';
                        mb_dom += ' <span class="r_email">'+this_tr.find('.td_mb_email').text()+'</span>';
                        mb_dom += '</li>';
                    }
                });
                $('.towhom_info ul', opener.document).append(mb_dom);
            }

            window.close();
        });
    <?php
    }
    ?>

});

</script>


<?php
include_once('./_tail.sub.php');
?>
