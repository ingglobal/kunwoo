<?php
$sub_menu = "917230";
include_once('./_common.php');

if(auth_check($auth[$sub_menu],"d",1)) {
    alert('메뉴에 접근 권한이 없습니다.');
}

function column_char($i) { return chr( 65 + $i ); }

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$sql_common = " FROM {$g5_table_name} AS ".$pre." "; 

$where = array();
$where[] = " ".$pre."_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " cod.com_idx IN (".$_SESSION['ss_com_idx'].") ";

// cod_group 조건
if($ser_cod_group)
    $where[] = " cod_group = '".$ser_cod_group."' ";

// cod_type 조건
if($ser_cod_type)
    $where[] = " cod_type = '".$ser_cod_type."' ";

if ($stx) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
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
    $sst = $pre."_idx";
    $sod = "ASC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";


$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT ".$pre.".*
		{$sql_common}
		{$sql_search}
        {$sql_order}
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
if (!$total_count)
    alert("출력할 내역이 없습니다.");


// 각 항목 설정
$headers = array('고유번호','업체번호','IMP번호','MMS번호','코드','분류','비가동영향','품질영향','그룹(pre=PLC예지)','타입(r,a,p,p2)','주기시간(초)','횟수','하루최대','내용','메모(알림내용)');
$widths  = array(10,      10,      10,     10,       10,   10,  10,        10,     15,               15,             13,          6,    10,     40,    60);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');



// 두번째 줄부터 실제 데이터 입력
for($i=1; $row=sql_fetch_array($result); $i++) {
    // 상태
    $row['evt_status_text'] = $g5['set_evt_status_value'][$row['evt_status']];
    
    $rows[] = array($row['cod_idx']
                  , $row['com_idx']
                  , $row['imp_idx']
                  , $row['mms_idx']
                  , $row['cod_code']
                  , $row['trm_idx_category']
                  , $row['cod_offline_yn']
                  , $row['cod_quality_yn']
                  , $row['cod_group']
                  , $row['cod_type']
                  , $row['cod_interval']
                  , $row['cod_count']
                  , $row['cod_count_limit']
                  , $row['cod_name']
                  , $row['cod_memo']
              );
}
// print_r2($headers);
// print_r2($widths);
// print_r2($rows);
// exit;


$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"code-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>