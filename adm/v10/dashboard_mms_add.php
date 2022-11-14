<?php
// 호출페이지들
// /adm/v10/dashboard_setting.php: 설비추가
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

// 권한이 없는 경우는 자기것만 리스트

// com_idx가 있는 경우
if(!$com_idx)
	alert_close('업체정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

$sql_common = " FROM {$g5['mms_table']} AS mms
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = mms.com_idx
                    ";

$where = array();
// 디폴트 검색조건
$where[] = " mms_status NOT IN ('trash','delete') ";

// 업체조건
$where[] = " mms.com_idx = '".$_REQUEST['com_idx']."' ";


// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'com_idx' || $sch_field == 'imp_idx' || $sch_field == 'mms_idx' ) :
			$where[] = " $sch_field = '".trim($sch_word)."' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}
else
    $sch_field = 'mms_name';

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


// 정렬기준
$sql_order = " ORDER BY mms_idx DESC ";


// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;

// 리스트 쿼리
$sql = "SELECT *
        " . $sql_common . $sql_search . $sql_order . "
        LIMIT $from_record, $rows
";
// echo $sql;
$result = sql_query($sql,1);

$qstr0 = 'file_name='.$file_name.'&com_idx='.$com_idx;
$qstr = $qstr0.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);

$g5['title'] = $com['com_name'].' 설비 추가';
include_once('./_head.sub.php');

add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js"></script>', 10);
?>
<style>
    .btn_select {cursor:pointer;}
    .btn_select:hover {color:red;}
    .btn_fixed_top {position:absolute;top: 12px;}
    </style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1 id="g5_title"><?php echo $g5['title'];?></h1>
    
    <form name="ftarget" method="get">
        <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
        <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">
        <input type="hidden" name="com_idx" value="<?php echo $_REQUEST['com_idx']; ?>">
        
        <div id="scp_list_find">
            <select name="sch_field" id="sch_field">
                <option value="mms_name">설비명(iMMS)</option>
                <option value="mms_idx">설비번호</option>
            </select>
            <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
            <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required style="width:130px;">
            <input type="submit" value="검색" class="btn_frmline" style="height:26px;">
            <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?<?=$qstr0?>" class="btn btn_b10">검색취소</a>
            <?php if($member['mb_manager_yn']) { ?>
                <a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>" id="btn_company" class="btn btn_b10">업체검색</a>
            <?php } ?>
        </div>
    </form>
            
    <div class="local_desc01 local_desc" style="display:no ne;">
        <p>추가할 항목들을 체크하시고 [선택추가]를 클릭하세요.</p>
    </div>

    <form name="form01" id="form01"  method="post">
    <input type="hidden" name="com_idx" value="<?php echo $_REQUEST['com_idx']; ?>">
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th>
                <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
            </th>
            <th scope="col">설비명(iMMS)</th>
            <th scope="col">관리번호</th>
            <th scope="col">모델</th>
            <th scope="col">그룹</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $row['mta'] = get_meta('mms',$row['imp_idx']);
            $row['group'] = get_table_meta('mms_group','mmg_idx',$row['mmg_idx']);
            // print_r2($row);
        ?>
        <tr>
            <td class="td_chk">
                <input type="hidden" name="mms_idx[<?php echo $i ?>]" value="<?php echo $row['mms_idx'] ?>" id="mms_idx_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mms_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td class="td_mms_name"><?php echo $row['mms_name']; ?></td>
            <td class="td_mms_idx2"><?php echo $row['mms_idx2']; ?></td>
            <td class="td_mms_model"><?php echo $row['mms_model']; ?></td>
            <td class="td_mms_group"><?php echo $row['group']['mmg_name']; ?></td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="6" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

    <div class="win_btn ">
        <input type="submit" name="act_button" value="선택추가" class="btn_01 btn">
        <button type="button" onclick="window.close();" class="btn btn_close">창닫기</button>
    </div>
</div>

<div class="btn_fixed_top">
    <a href="javascript:window.close();" class="btn btn_02">창닫기</a>
    <input type="submit" name="act_button" value="선택추가" class="btn_01 btn">
</div>
</form>


<script>
$(function() {
    $("#btn_company").click(function() {
        var href = $(this).attr("href");
        winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
        winCompany.focus();
        return false;
    });

    <?php
    // 대시보드 설정
    if($file_name=='dashboard_setting') {
    ?>
        $("#form01").submit(function(e){
            e.preventDefault();
            this.action = './dashboard_mms_add_update.php';

            this.submit();
        });
    <?php
    }
    ?>

});

</script>

<?php
include_once('./_tail.sub.php');
?>