<?php
$sub_menu = "915125";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '카테고리';
include_once('./_top_menu_bom.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['bom_category_table']} ";

$where = array();
$where[] = " com_idx ='".$_SESSION['ss_com_idx']."' ";   // 디폴트 검색조건

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bct_id' ) :
			$where[] = " {$sfl} LIKE '".trim($stx)."%' ";
            break;
		case ( $sfl == 'bom_part_no' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 50;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "bct_id";
    $sod = "asc";
}
$sql_order = "order by $sst $sod";

// 출력할 레코드를 얻음
$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// echo $sql.'<br>';
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
    .td_mng {width:200px;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">생성된  분류 수</span><span class="ov_num">  <?php echo number_format($total_count); ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="bct_name"<?php echo get_selected($sfl, "bct_name", true); ?>>항목명</option>
    <option value="bct_id"<?php echo get_selected($sfl, "bct_id", true); ?>>분류코드</option>
    <option value="bct_mb_id"<?php echo get_selected($sfl, "bct_mb_id", true); ?>>회원아이디</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fcategorylist" method="post" action="./bom_category_list_update.php" onsubmit="return form01_submit(this);" autocomplete="off">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div id="sct" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col"><?php echo subject_sort_link("bct_id"); ?>분류코드</a></th>
        <th scope="col" id="sct_cate"><?php echo subject_sort_link("bct_name"); ?>항목명</a></th>
        <th scope="col" id="sct_desc"><?php echo subject_sort_link("bct_desc"); ?>항목설명</a></th>
        <th scope="col" id="sct_amount">제품수</th>
        <th scope="col" id="sct_imgcol">정렬순서</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $s_add = $s_vie = $s_upd = $s_del = '';
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $level = strlen($row['bct_id']) / 2 - 1;
        $p_bct_name = '';

        if ($level > 0) {
            $class = 'class="name_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 항목명
            $p_bct_id = substr($row['bct_id'], 0, $level*2);
            $sql = " select bct_name from {$g5['bom_category_table']} where bct_id = '$p_bct_id' ";
            $temp = sql_fetch($sql);
            $p_bct_name = $temp['bct_name'].'의하위';
        } else {
            $class = '';
        }

        $s_level = '<div><label for="bct_name_'.$i.'" '.$class.'><span class="sound_only">'.$p_bct_name.''.($level+1).'단 분류</span></label></div>';
        $s_level_input_size = 25 - $level *2; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./bom_category_form.php?bct_id='.$row['bct_id'].'&amp;'.$qstr.'" class="btn btn_03">추가</a> '; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./bom_category_form.php?w=u&amp;bct_id='.$row['bct_id'].'&amp;'.$qstr.'" class="btn btn_02"><span class="sound_only">'.get_text($row['bct_name']).' </span>수정</a> ';

        if ($is_admin == 'super'){ //(auth_check($auth[$sub_menu],"w",1)) { //($is_admin == 'super')
            $s_del = '<a href="./bom_category_form_update.php?w=d&amp;bct_id='.$row['bct_id'].'&amp;'.$qstr.'" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only">'.get_text($row['bct_name']).' </span>삭제</a> ';
        }
        // 해당 분류에 속한 제품의 수
        $sql1 = " SELECT COUNT(*) AS cnt FROM {$g5['bom_table']} WHERE bct_id = '{$row['bct_id']}' ";
        $row1 = sql_fetch($sql1,1);

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_code" style="text-align:left;">
            <input type="hidden" name="bct_id[<?php echo $i; ?>]" value="<?php echo $row['bct_id']; ?>">
            <a href="<?php echo shop_category_url($row['bct_id']); ?>"><?php echo $row['bct_id']; ?></a>
        </td>
        <td headers="sct_cate" class="sct_name<?php echo $level; ?>"><?php echo $s_level; ?> <input type="text" name="bct_name[<?php echo $i; ?>]" value="<?php echo get_text($row['bct_name']); ?>" id="bct_name_<?php echo $i; ?>" required class="tbl_input full_input required"></td>
        <td headers="sct_desc" class="sct_desc<?php echo $level; ?>"><?php echo $s_level; ?> <input type="text" name="bct_desc[<?php echo $i; ?>]" value="<?php echo get_text($row['bct_desc']); ?>" id="bct_desc_<?php echo $i; ?>" required class="tbl_input full_input required"></td>
        <td headers="sct_amount" class="td_amount"><a href="./bom_list.php?sca=<?php echo $row['bct_id']; ?>"><?php echo $row1['cnt']; ?></a></td>
        <td headers="sct_imgw">
            <label for="bct_out_width<?php echo $i; ?>" class="sound_only">정렬번호</label>
            <input type="text" name="bct_order[<?php echo $i; ?>]" value="<?php echo get_text($row['bct_order']); ?>" id="bct_out_width<?php echo $i; ?>" required class="required tbl_input" size="3" > <span class="sound_only">픽셀</span>
        </td>
        <td class="td_mng">
            <?php echo $s_add; ?>
            <?php echo $s_vie; ?>
            <?php echo $s_upd; ?>
            <?php echo $s_del; ?>
        </td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan='6' class=\"empty_table\">자료가 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_level'] >= 10){ ?>
    <input type="submit" name="act_button" value="분류환경변수설정반영" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <input type="submit" name="act_button2" value="일괄수정" class="btn_02 btn">

    <?php if ($is_admin == 'super') {?>
    <a href="./bom_category_form.php" id="cate_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $("select.skin_dir").on("change", function() {
        var type = "";
        var dir = $(this).val();
        if(!dir)
            return false;

        var id = $(this).attr("id");
        var $sel = $(this).siblings("select");
        var sval = $sel.find("option:selected").val();

        if(id.search("mobile") > -1)
            type = "mobile";

        $sel.load(
            "./ajax.skinfile.php",
            { dir : dir, type : type, sval: sval }
        );
    });
});

function form01_submit(f)
{
    if(document.pressed == "일괄수정") {
        ;
    }
    else if(document.pressed == "분류환경변수설정반영") {
        if(!confirm("\"환경설정 > 솔루션설정\"에서 1차분류,2차분류,3차분류,4차분류에 설정한 값으로 새로 반영이 됩니다.\n기존설정내용과 순서의 차이가 있으면 각 제품(BOM)에서의 분류값을 다시 확인/설정을 해야 할 수 있습니다.\n정말로 환경설정값으로 반영하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
