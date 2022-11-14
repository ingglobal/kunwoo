<?php
// 모바일을 위한 별도 설비관리 페이지입니다.
// 앱 설정을 위해서 별도로 만든 페이지입니다.
$sub_menu = "955600";
include_once('./_common.php');

check_access(3,'=');

auth_check($auth[$sub_menu],"r");

$sql_common = " FROM {$g5['mms_table']} AS mms
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = mms.com_idx
                    LEFT JOIN {$g5['imp_table']} AS imp ON imp.imp_idx = mms.imp_idx
                    LEFT JOIN {$g5['mms_group_table']} AS mmg ON mmg.mmg_idx = mms.mmg_idx
"; 

$where = array();
$where[] = " mms_status NOT IN ('trash','delete') AND mms.imp_idx = 13 ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
if (!$member['mb_manager_yn']) {
    $where[] = " mms.com_idx = '".$member['mb_4']."' ";
}

if (isset($stx)&&$stx!='') {
    switch ($sfl) {
		case ( $sfl == 'mms.com_idx' || $sfl == 'mms_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'mb_name' || $sfl == 'mb_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
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
    $sst = "mms_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT *
            , com.com_idx AS com_idx
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';


$g5['title'] = '설비(iMMS)관리';
//include_once('./_top_menu_company.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
