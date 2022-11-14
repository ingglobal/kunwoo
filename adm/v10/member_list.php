<?php
$sub_menu = "950400";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " FROM {$g5['member_table']} AS mb 
                LEFT JOIN {$g5['company_member_table']} AS cmm ON cmm.mb_id = mb.mb_id
"; 

$where = array();
$where[] = " mb_level = 4 ";   // 디폴트 검색조건

// 운영권한이 없으면 자기것만
if (!$member['mb_manager_yn']) {
    // company_saler 교차 테이블에서 내 것만 추출
    $where[] = " com_idx IN ( SELECT com.com_idx
        FROM {$g5['company_table']} AS com
            LEFT JOIN {$g5['company_saler_table']} AS cms ON cms.com_idx = com.com_idx
        WHERE mb_id_saler = '".$member['mb_id']."'
        GROUP BY com.com_idx ) ";
}

// 법인접근 권한이 없으면 자기 법인만 조회 가능
if(!$member['mb_manager_yn']) {
    $where[] = " mb_4 = '".$member['mb_4']."' ";
}
// 선택적 법인접근이면 해당 법인만
else if($member['mb_firm_idxs']) {
    $where[] = " mb_4 IN (".$member['mb_firm_idxs'].") ";
}

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_point' ) :
			$where[] = " {$sfl} >= '".trim($stx)."' ";
            break;
		case ( $sfl == 'mb_level' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
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

$sql_group = " GROUP BY mb.mb_id ";


if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " SELECT count(*) AS cnt FROM (select cmm_idx as cnt {$sql_common} {$sql_search} {$sql_group}) AS db1 ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
//print_r3($sql).'<br>';

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '담당자관리';
include_once('./_top_menu_company.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql = "select mb.*, cmm.com_idx, cmm.cmm_title 
        {$sql_common} {$sql_search} {$sql_group} {$sql_order}
        limit {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql);

$colspan = 16;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
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

<div class="local_desc01 local_desc">
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
    <tr>
        <th scope="col" id="mb_list_chk" rowspan="2" >
            <label for="chkall" class="sound_only">회원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
        <th scope="col"><?php echo subject_sort_link('cmm_title') ?>직함</a></th>
        <th scope="col"><?php echo subject_sort_link('com_name') ?>업체명</a></th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
        <th scope="col">휴대폰</th>
        <th scope="col">이메일</th>
        <th scope="col" style="width:100px;"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>등록일</a></th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
    </tr>
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

        $s_mod = '<a href="./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="btn btn_03">수정</a>';

        $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mb_id'] ?>">
        <td class="td_chk">
            <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_mb_name"><?php echo get_text($row['mb_name']); ?></td>
        <td class="td_cmm_title"><?php echo $g5['set_mb_ranks_value'][$row['cmm']['cmm_title']] ?></td>
        <td class="td_com_name"><?php echo $row['cmm']['com_name'] ?></td>
        <td class="td_mb_id"><?php echo $row['mb_id']; ?></td>
        <td class="td_hp"><?php echo get_text($row['mb_hp']); ?></td>
        <td class="td_mb_email"><?php echo $row['mb_email']; ?></td>
        <td class="td_date"><?php echo substr($row['mb_datetime'],2,8); ?></td>
        <td class="td_mng td_mng_s">
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
    <a href="./member_form.php" id="member_add" class="btn btn_01">고객추가</a>
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
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
