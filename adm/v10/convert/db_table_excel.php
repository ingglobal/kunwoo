<?php
include_once('./_common.php');

function column_char($i) { return chr( 65 + $i ); }

// 테이블 한글명
$tables = array('g5_1_alarm'=>'알람'
                ,'g5_1_alarm_send'=>'알람발송'
                ,'g5_1_bom'=>'BOM데이터'
                ,'g5_1_bom_category'=>'BOM카테고리데이터'
                ,'g5_1_bom_item'=>'BOM구성품데이터'
                ,'g5_1_code'=>'에러코드'
                ,'g5_1_company'=>'업체'
                ,'g5_1_company_member'=>'업체별담당자'
                ,'g5_1_company_saler'=>'업체별영업자'
                ,'g5_1_data'=>'데이터'
                ,'g5_1_data_error'=>'에러데이터'
                ,'g5_1_data_error_sum'=>'에러데이터합계'
                ,'g5_1_data_measure'=>'측정데이터'
                ,'g5_1_data_measure_10_1_1'=>'측정데이터'
                ,'g5_1_data_measure_sum'=>'측정데이터합계'
                ,'g5_1_data_output'=>'생산데이터'
                ,'g5_1_data_output_1'=>'생산데이터'
                ,'g5_1_data_output_sum'=>'생산데이터합계'
                ,'g5_1_data_run'=>'가동데이터'
                ,'g5_1_data_run_real'=>'가동데이터실시간'
                ,'g5_1_data_run_sum'=>'가동데이터합계'
                ,'g5_1_imp'=>'IMP'
                ,'g5_1_item'=>'완성품재고데이터'
                ,'g5_1_maintain'=>'정비내역'
                ,'g5_1_maintain_parts'=>'정비-부속품'
                ,'g5_1_material'=>'자재(구성품)재고데이터'
                ,'g5_1_member_dash'=>'대시보드설정'
                ,'g5_1_mms'=>'설비'
                ,'g5_1_mms_checks'=>'주요점검기준'
                ,'g5_1_mms_group'=>'설비그룹'
                ,'g5_1_mms_item'=>'생산기종'
                ,'g5_1_mms_parts'=>'주요부속품'
                ,'g5_1_offwork'=>'비가동시간'
                ,'g5_1_order'=>'수주데이터'
                ,'g5_1_order_item'=>'수주제품데이터'
                ,'g5_1_order_out'=>'출하계획데이터'
                ,'g5_1_order_out_practice'=>'생산계회출하관계'
                ,'g5_1_order_practice'=>'생산계획데이터'
                ,'g5_1_plan_send'=>'계획정비통지'
                ,'g5_1_shift'=>'작업구간'
                ,'g5_5_meta'=>'메타테이블'
                ,'g5_5_file'=>'첨부파일'
                ,'g5_5_setting'=>'환경설정'
                ,'g5_5_term'=>'코드(용어)'
                ,'g5_5_term_relation'=>'용어관계설정'
                ,'g5_auth'=>'메뉴권한설정'
                ,'g5_board'=>'게시판'
                ,'g5_board_file'=>'게시판첨부파일'
                ,'g5_config'=>'환경설정'
                ,'g5_group'=>'그룹설정'
                ,'g5_member'=>'회원'
                ,'g5_new_win'=>'팝업창'
                ,'g5_point'=>'포인트'
                ,'g5_shop_banner'=>'배너관리'
                ,'g5_write_company1'=>'업체코멘트'
                ,'g5_write_contact'=>'A/S연락처'
                ,'g5_write_drawing'=>'설비사양서'
                ,'g5_write_maintain'=>'정비이력'
                ,'g5_write_manual'=>'매뉴얼'
                ,'g5_write_notice1'=>'공지사항'
                ,'g5_write_parts'=>'부품재고'
                ,'g5_write_plan'=>'계획정비'
                ,'g5_write_tech1'=>'기술정보'
            );
// print_r2($tables);


// 각 항목 설정
$headers = array('테이블명','테이블ID','컬럼명','컬럼ID','Datatype','PK','FK','NULL허용','비고');
$widths  = array(18, 30, 15, 30, 20, 20, 20, 20, 20);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

$sql = " show tables ";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    $row['db_table'] = $row['Tables_in_epcs_www']; //////////////////////////// icmms_www
    if(!$tables[$row['db_table']]) continue; 
    // $sql2 = " desc `".$row['db_table']."` ";
    $sql2 = " show full columns from `".$row['db_table']."` ";
    $result = sql_query($sql2);
    //print_r2($result);
    while($field = sql_fetch_array($result)) {
        //print_r2($field);
        // for($j=0;$j<sizeof($field);$j++) {
        //     $row['db_field'][$j] = $field[$j];
        //     //echo $j.':'.$field[$j].', ';
        // }
        //echo "<br>";continue;
        //  print_r2($row['db_field']);

        // 테이블명
        $row['db_table_name'] = $tables[$row['db_table']];
        // 테이블ID
        $row['db_table_id'] = $row['db_table'];
        // 컬럼명
        $row['db_field_name'] = ($field['Comment']) ? $field['Comment'] : $field['Field'];
        //컬럼ID
        $row['db_field_id'] = $field['Field'];
        //데이터유형
        $row['db_field_type'] = $field['Type'];
        //PK
        $row['db_field_pk'] = ($field['Key'] == 'PRI') ? 'Y' : '';
        //FK
        $row['db_field_fk'] = (($field['Key'] != 'PRI' && preg_match("/^[a-z]{3,3}_idx$/",$field['Field']))
            || ($field['Key'] != 'PRI' && preg_match("/^[a-z]+_id$/",$field['Field']))) ? 'Y' : '';
        //Null허용
        $row['db_field_null'] = ($field['Null'] == 'NO') ? 'NOT NULL' : 'NULL';
        //비고
        $row['db_field_note'] = preg_match("/pass/",$field['Field']) ? 'sha256':'';

        $rows[] = array($row['db_table_name']
                        ,$row['db_table_id']
                        ,$row['db_field_name']
                        ,$row['db_field_id']
                        ,$row['db_field_type']
                        ,$row['db_field_pk']
                        ,$row['db_field_fk']
                        ,$row['db_field_null']
                        ,$row['db_field_note']);
        /*
        // PK
        $row['db_field'][4] = preg_match("/PRI/",$row['db_field'][4]) ? 'Y':'';
        
        // Null
        $row['db_field'][3] = preg_match("/NO/",$row['db_field'][3]) ? 'not null':'null';
        
        // 비고. 비밀번호인 경우
        $row['db_field'][20] = preg_match("/pass/",$row['db_field'][0]) ? 'sha256':'';

        
        $rows[] = array($row['db_table_name']
                      , $row['db_table']
                      , $row['db_field_name']
                      , $row['db_field'][0]
                      , $row['db_field'][1]
                      , $row['db_field'][4]
                      , ' '
                      , $row['db_field'][3]
                      , $row['db_field'][20]
                  );
        */
    }
    // print_r2($row);
}
//exit;
//print_r2($rows);exit;

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"db_tables-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>