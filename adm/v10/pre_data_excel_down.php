<?php
$sub_menu = "919110";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

function column_char($i) { return chr( 65 + $i ); }

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'alarm';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


// $sql_common = " FROM {$g5_table_name} AS ".$pre." "; 
$sql_common = " FROM {$g5_table_name} AS ".$pre."
                    LEFT JOIN g5_1_mms AS mms ON mms.mms_idx = arm.mms_idx
"; 

$where = array();
$where[] = " ".$pre."_status NOT IN ('trash','delete') AND arm_cod_type IN ('p','p2') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " arm.com_idx IN (".$_SESSION['ss_com_idx'].") ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'cod_code') :
            $where[] = " arm_keys REGEXP 'cod_code=.*".trim($stx).".*~' ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 기간 검색
if ($st_date) {
    if ($st_time) {
        $where[] = " arm_reg_dt >= '".$st_date.' '.$st_time."' ";
    }
    else {
        $where[] = " arm_reg_dt >= '".$st_date.' 00:00:00'."' ";
    }
}
if ($en_date) {
    if ($en_time) {
        $where[] = " arm_reg_dt <= '".$en_date.' '.$en_time."' ";
    }
    else {
        $where[] = " arm_reg_dt <= '".$en_date.' 23:59:59'."' ";
    }
}

// 설비번호 검색
if ($ser_mms_idx) {
    $where[] = " arm_keys REGEXP 'mms_idx=".$ser_mms_idx."~' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = $pre."_idx";
    $sod = "ASC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";


$sql = " SELECT *
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
$headers = array('일시','코드','분류','알람타입','설비명','알람내용');
$widths  = array(20, 10, 25, 10, 30, 40);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

// 두번째 줄부터 실제 데이터 입력
for($i=1; $row=sql_fetch_array($result); $i++) {
    // arm_keys 값을 배열에 추가
    $row = array_merge($row, get_keys($row['arm_keys'],'~'));

    $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
    $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
    $row['cod'] = sql_fetch(" SELECT cod_name, trm_idx_category FROM {$g5['code_table']} WHERE cod_idx = '".$row['cod_idx']."' ");
    // print_r2($row);

    $rows[] = array($row['arm_reg_dt']
                  , $row['cod_code']
                  , $g5['category_up_names'][$row['cod']['trm_idx_category']]
                  , $g5['set_cod_type_value'][$row['arm_cod_type']]
                  , $row['mms']['mms_name']
                  , $row['cod']['cod_name']
              );
}
// print_r2($rows);
// exit;


$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"predict-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>