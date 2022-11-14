<?php
include_once('./_common.php');

$sql_common = " FROM {$g5['company_table']} AS com";

$where = array();
$where[] = " com_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " com_type NOT IN ('buyer') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "com_reg_dt";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT 
			com_idx,
			com_name,
			( SELECT cra_rate FROM {$g5['company_rate_table']} WHERE com_idx = com.com_idx ORDER BY cra_start_date,cra_idx DESC LIMIT 1 ) AS cra_rate
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";

$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

//검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
?>
<div id="com_sch_list">
	<div class="local_ov01 local_ov">
		<?php echo $listall ?>
		<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
		<span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
	</div>
	<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
	<label for="sfl" class="sound_only">검색대상</label>
	<select name="sfl" id="sfl">
		<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
	</select>
	<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
	<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
	<input type="submit" class="btn_submit" value="검색">
	</form>
	<ul class="ul_com">
		<?php
		for ($i=0; $row=sql_fetch_array($result); $i++){
			print_r2($row);
		?>
		
		<?php
		}
		if ($i == 0)
			echo "<li>자료가 없습니다.</li>";
		?>
	</ul>
	<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>
</div><!--#com_sch_list-->