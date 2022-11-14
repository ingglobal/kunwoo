<?php
// mms_idx=1, list_column=2(2줄)
include_once('./_common.php');

function column_char($i) { return chr( 65 + $i ); }

if(!$mbd_idx)
    alert('그래프 고유번호가 없습니다.');

// $g5['title'] = '대시보드 그래프 엑셀 다운로드';
// include_once('./_head.sub.php');

$sql = "SELECT * FROM {$g5['member_dash_table']}
        WHERE mbd_idx = '".$mbd_idx."'
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    $row['sried'] = get_serialized($row['mbd_setting']);
    $row['data'] = json_decode($row['sried']['data_series'],true);
    unset($row['mbd_setting']);
    unset($row['sried']['data_series']);
    // print_r2($row); // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    for($j=0;$j<sizeof($row['data']);$j++) {
        // print_r2($row['data'][$j]);
        $row['chr_names'][] = $row['data'][$j]['name'];
        $row['chr_mms_idxs'][] = $row['data'][$j]['id']['mms_idx']; // in order to check multi mms
        // target should be from local
        if($row['data'][$j]['id']['dta_json_file']=='output.target') {
            $row['data'][$j]['id']['dta_data_url'] = strip_http(G5_ADMIN_URL).'/v10/ajax';
        }
    }

    // 그래프 이름 (tag name array if not desiganated name.)
    $row['mbd_graph_name'] = $row['sried']['graph_name'] ?: implode(", ",$row['chr_names']);
    // print_r2($row['chr_mms_idxs']);
    // print_r2($row['data']); // script내부에 표현이 안 되므로 여기 찍어요.

    // for loop as many times as graph count.
    $items[] = '시간'; // 첫줄 첫 항목명
    $ws[] = 20; // 첫줄 폭
    for($j=0;$j<sizeof($row['data']);$j++) {
        // echo 'console.log("'.$row['data'][$j]['id']['dta_data_url'].'");';
        // in case for http://demo.bogwang.epcs.co.kr
        if( preg_match("/demo\./",G5_URL) ) {
            $row['data'][$j]['id']['dta_data_url'] = (!preg_match("/demo\./",$row['data'][$j]['id']['dta_data_url'])) ? 'demo.'.$row['data'][$j]['id']['dta_data_url']
                                                        : $row['data'][$j]['id']['dta_data_url'];
            $row['data'][$j]['id']['dta_data_url'] = (preg_match("/demo\.test\./",$row['data'][$j]['id']['dta_data_url'])) ? preg_replace("/test\./","",$row['data'][$j]['id']['dta_data_url'])
                                                        : $row['data'][$j]['id']['dta_data_url'];
        }

        $dta_data_url = $row['data'][$j]['id']['dta_data_url'];
        $dta_json_file = $row['data'][$j]['id']['dta_json_file'];
        $dta_group = $row['data'][$j]['id']['dta_group'];
        $mms_idx = $row['data'][$j]['id']['mms_idx'];
        $mms_name = $row['data'][$j]['id']['mms_name'];
        $dta_type = $row['data'][$j]['id']['dta_type'];
        $dta_no = $row['data'][$j]['id']['dta_no'];
        $shf_no = $row['data'][$j]['id']['shf_no'];
        $dta_mmi_no = $row['data'][$j]['id']['dta_mmi_no'];
        $dta_defect = $row['data'][$j]['id']['dta_defect'];
        $dta_defect_type = $row['data'][$j]['id']['dta_defect_type']; // 1,2,3,4...
        $dta_code = $row['data'][$j]['id']['dta_code'];    // only if err, pre
        $graph_type = $row['data'][$j]['type'];
        $graph_line = $row['data'][$j]['dashStyle'];
        $mbd_idx = $row['mbd_idx'];
        $graph_name = $row['data'][$j]['id']['graph_name'];

        $dta_item = $row['sried']['dta_item'];   // 일,주,월,년,분,초
        $dta_file = ($dta_item=='minute'|| $dta_item=='second') ? '' : '.sum'; // measure.php(그룹핑), measure.sum.php(일자이상)
        $dta_unit = $row['sried']['dta_unit'];   // 10,20,30,60

        // the latest time range from right now!
        $en_timestamp = strtotime($row['sried']['en_date'].' '.$row['sried']['en_time']);
        $st_timestamp = strtotime($row['sried']['st_date'].' '.$row['sried']['st_time']);
        $diff_timestamp = $en_timestamp - $st_timestamp;
        $row['df_seconds'][$row['mbd_idx']][$j] = $diff_timestamp;
        $row['df_unit'][$row['mbd_idx']][$j] = $seconds[$row['sried']['dta_item']][0]*$row['sried']['dta_unit'];    // seconds per unit * item_count = interval seconds
        
        $row['en_date'] = date("Y-m-d",G5_SERVER_TIME);
        $row['en_time'] = date("H:i:s",G5_SERVER_TIME);
        $row['st_date'] = date("Y-m-d",G5_SERVER_TIME-$diff_timestamp);
        $row['st_time'] = date("H:i:s",G5_SERVER_TIME-$diff_timestamp);

        $en_date = $row['en_date'];
        $en_time = $row['en_time'];
        $st_date = $row['st_date'];
        $st_time = $row['st_time'];

        $dta_url = 'http://'.$dta_data_url.'/'.$dta_json_file.$dta_file.'.php?token=1099de5drf09'
                        .'&mms_idx='.$mms_idx.'&dta_group='.$dta_group.'&shf_no='.$shf_no.'&dta_mmi_no='.$dta_mmi_no
                        .'&dta_type='.$dta_type.'&dta_no='.$dta_no
                        .'&dta_defect='.$dta_defect.'&dta_defect_type='.$dta_defect_type
                        .'&dta_code='.$dta_code
                        .'&dta_item='.$dta_item.'&dta_unit='.$dta_unit
                        .'&st_date='.$st_date.'&st_time='.$st_time.'&en_date='.$en_date.'&en_time='.$en_time;
        // echo urldecode($graph_name).'<br>';
        // echo $dta_url.'<br>';

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $dta_url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $json = json_decode($data, true);
        // print_r2($json);
        $items[] = urldecode($graph_name); //첫번째 항목은 이름
        $ws[] = 20; // 항목의 폭
        for($k=0;$k<sizeof($json);$k++) {
            if( is_array($json[$k]) ) {
                // print_r2($json[$k]);
                foreach ($json[$k] as $k1=>$v1) {
                    // echo $j.'-'.$k.'. '.$k1.'/'.$v1.'<br>';
                    // 첫줄 시간은 한번만 생성
                    if($j==0 && $k1=='x') {
                        $rows[$k][0] = ' '.date("m-d H:i:s", $v1*0.001);
                        $rows[$k][0] = date("m-d H:i:s", $v1*0.001);
                    }
                    if($k1=='y') {
                        $rows[$k][$j+1] = ' '.$v1;
                        $rows[$k][$j+1] = $v1;
                    }
                }
            }
            // echo '-----------------<br>';
        }
        // echo '====================================<br>';

    }
}
// print_r2($items);
// print_r2($ws);
// print_r2($rows);
// exit;

// 각 항목 설정
$headers = $items;
$widths  = $ws;
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);


// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"dashboard-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');


// include_once('./_tail.sub.php');
?>