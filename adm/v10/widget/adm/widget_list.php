<?php
$sub_menu = "990150";
include_once('./_common.php');
auth_check($auth[$sub_menu], 'r');

$g5['title'] = '위젯목록';
include_once('../../_head.php');
$sql_common = " FROM {$g5['widget_table']} AS wig "; 
$sql_search = " WHERE wig_status NOT IN ('trash','delete') ";//" WHERE (1) ";
if($stx){
	$sql_search .= " AND ( ";
	switch($sfl){
		case 'wig_cd':
			$sql_search .= " ( wig_cd LIKE '%{$stx}%' ) ";
			break;
		case 'wig_device':
			$sql_search .= " ( wig_device LIKE '%{$stx}%' ) ";
			break;
		case 'wig_skin':
			$sql_search .= " ( wig_skin LIKE '%{$stx}%' ) ";
			break;
		case ($sfl == 'wig_status' ) :
            $sql_search .= " ( wig_status LIKE '%{$stx}%' ) ";
            break;
		default :
            $sql_search .= " ({$sfl} LIKE '%{$stx}%') ";
            break;
	}
	$sql_search .= " ) ";
}

if (!$sst) {
    $sst = "wig_reg_dt";
    $sod = "ASC";//"DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = 60;$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
		{$sql_common}
		{$sql_search} {$sql_order}
		LIMIT {$from_record}, {$rows} ";
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['widget_table']} AS wig WHERE wig_status = 'pending' ";
$row = sql_fetch($sql,1);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 9;
?>
<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="bwgs_cd"<?php echo get_selected($_GET['sfl'], "wig_cd"); ?>>위젯코드명</option>
	<option value="bwgs_device"<?php echo get_selected($_GET['sfl'], "wig_device"); ?>>디바이스</option>
	<option value="bwgs_skin"<?php echo get_selected($_GET['sfl'], "wig_skin"); ?>>스킨명</option>
    <option value="bwgs_status"<?php echo get_selected($_GET['sfl'], "wig_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>
<div id="bpwidget_list">
<form name="form01" id="form01" action="./widget_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" rowspan="2">
			<label for="chkall" class="label_checkbox first_child">
				<input type="checkbox" id="chkall" value="1" onclick="check_all_bwg(this.form)">
				<strong></strong>
				<span class="sound_only">전체체크</span>
			</label>
		</th>
		<th scope="col" style="width:120px;">이미지</th>
		<th scope="col" style="width:150px;"><?php echo subject_sort_link('wig_cd') ?>위젯코드</a></th>
		<th scope="col" style="width:50px;"><?php echo subject_sort_link('wig_device') ?>장치</a></th>
		<th scope="col" style="width:120px;">스킨명</th>
		<th scope="col">설명</th>
		<th scope="col" style="width:100px;">위젯분류</a></th>
		<th scope="col" style="width:80px;"><?php echo subject_sort_link('wig_status') ?>상태</a></th>
		<th scope="col" style="width:50px;" id="mb_list_mng">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
		//print_r2($row);
		// 수정 및 발송 버튼
		$s_mod = '<a class="bp_btn_s_primary" href="'.G5_WIDGET_ADMIN_URL.'/widget_form.php?'.$qstr.'&amp;w=u&amp;wig_idx='.$row['wig_idx'].'">수정</a>';
		$s_del = '<a class="bp_btn_s_danger" href="'.G5_WIDGET_ADMIN_URL.'/widget_form_update.php?'.$qstr.'&amp;w=d&amp;wig_idx='.$row['wig_idx'].'" onclick="return delete_confirm();">삭제</a>';
 
		// 삭제인 경우 그레이 표현
		//if($row['wig_status'] == 'trash')
		//	$row['wig_status_trash_class']	= " tr_trash";
		$mobile_row = ($row['wig_device'] == 'mobile') ? 'mb_row ' : '';
        $bg = 'bg'.($i%2);
		$style_font_color = ($row['wig_status'] != 'ok') ? 'style="color:#999;"' : '';
    ?>

	<tr class="<?php echo $mobile_row.$bg; ?> <?=$row['wig_status_trash_class']?>" tr_id="<?php echo $row['wig_idx'] ?>" <?=$style_font_color?>>
		<td headers="list_chk" class="td_chk">
			<label for="chk_<?php echo $i; ?>" class="label_checkbox first_child">
				<input type="checkbox" name="chk[<?=$row['wig_idx']?>]" value="1" id="chk_<?php echo $i ?>">
				<strong></strong>
				<span class="sound_only"><?php echo get_text($row['wig_name']); ?></span>
			</label>
		</td>
		<td headers="list_thumb" class="td_thumb" style="text-align:center;"><!-- 섬네일 -->
			<?php
			$thumb_path = G5_WIDGET_SKIN_PATH.'/'.$row['wig_device'].'/'.$row['wig_skin'].'/screenicon.gif';
			$thumb_url = G5_WIDGET_SKIN_URL.'/'.$row['wig_device'].'/'.$row['wig_skin'].'/screenicon.gif';
			//if(false){
			if(file_exists($thumb_path)){
				echo '<img class="bp_screenicon_img" src="'.$thumb_url.'">'.PHP_EOL;
			}else{
				echo '<div class="bp_screenicon_no">No IMG</div>'.PHP_EOL;
			}
			?>
		</td>
		<td headers="list_wig_cd" class="td_wig_cd" style="text-align:left;"><!-- 위젯코드 -->
			<b><?php echo get_text($row['wig_cd']); ?></b>
		</td>
		<td headers="list_wig_device" class="td_wig_device"style="text-align:center;"><!-- 위젯디바이스 -->
			<b><?php echo strtoupper(get_text($row['wig_device'])); ?></b>
		</td>
		<td headers="list_wig_skin"><b><?php echo strtoupper(get_text($row['wig_skin'])); ?></b></td>
		<td headers="list_wig_description" class="td_wig_description" style="text-align:left;"><!-- 위젯설명 -->
			<?php
			/*
			$desc_path = G5_WIDGET_SKIN_PATH.'/'.$row['wig_device'].'/'.$row['wig_skin'].'/readme.txt';
			if(file_exists($desc_path)){
				$content = file($desc_path, false);
				$content = array_map('trim', $content);
				preg_match('#^Description:(.+)$#i', $content[5], $m5);
				$wig_description = trim($m5[1]);
				echo $wig_description;
			}else{
				echo '<div style="text-align:center;">-</div>'.PHP_EOL;
			}
			*/
			echo cut_str(get_text($row['wig_content']),12,'...');
			?>
		</td>
		<td headers="list_wig_db_category" class="td_wig_db_category" style="text-align:center;">
			<?php echo strtoupper(($row['wig_db_category']) ? get_text($row['wig_db_category']) : '공통'); ?>
		</td>
		<td headers="list_wig_status" class="td_wig_status" style="text-align:center;">
			<select name="wig_status[<?=$row['wig_idx']?>]">
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="wig_status[<?=$row['wig_idx']?>]"]').val('<?=$row['wig_status']?>');</script>
		</td>
		<td headers="list_mng" class="td_mngsmall">
			<?php echo $s_mod ?><br>
			<?php echo $s_del ?>
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
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="<?=G5_WIDGET_ADMIN_URL?>/widget_form.php" id="bo_add" class="btn_01 btn">위젯추가</a>
</div>
</form>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<script>
function form01_submit(f){
	if (!is_checked_bwg("chk")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
	
	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	
	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
}

function delete_confirm(){
	if(!confirm("선택한 위젯을 정말 삭제하시겠습니까?"))
		return false;
	
	return true;
}
</script>
<?php
include_once ('../../_tail.php');