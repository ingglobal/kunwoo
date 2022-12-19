<?php
$sub_menu = "945110";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1
//$xls = G5_USER_ADMIN_SQL_PATH.'/xls/material_input.xlsx';

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];//$xls
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
//echo $filename;exit;
$up_date = G5_TIME_YMD;
$conArr = array();
$col_arr = array(
    '6' => array('기준일자','기준날짜')
    ,'10' => array('품목코드')
    ,'11' => array('품목명','품명')
    ,'12' => array('규격','품번','업체품번')
    ,'21' => array('입고수량','입고무게')
    ,'22' => array('입고단가','단가')
    ,'23' => array('입고금액','금액')
    ,'24' => array('LOT','로트넘버','로트','히트넘버','Lot','lot','LOT넘버')
    ,'25' => array('번들넘버','번들','번들번호')
    ,'27' => array('비고','길이')
);
$colIdxArr = array(
    'mtr_input_date' => '6'
    ,'bom_part_no' => '10'
    ,'mtr_name' => '11'
    ,'bom_std' => '12'
    ,'mtr_weight' => '21'
    ,'mtr_price' => '22'
    ,'mtr_sum_price' => '23'
    ,'mtr_lot' => '24'
    ,'mtr_bundle' => '25'
    ,'mtr_length' => '27'
);
$idxColArr = array();
try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();
    if($sheetsCount > 1){
        alert('엑셀시트는 단일시트로 작성해 주세요.');
        exit;
    }

    
	// 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++) { //시트갯수만큼 루프
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();          // 마지막 행
        $highestColumn = $sheet -> getHighestColumn();    // 마지막 컬럼
        // echo $highestRow;exit;
        // 한줄씩 읽기
        for($row = 1; $row <= $highestRow; $row++) { //첫줄부터 루프
            // if($row < 5) continue;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);

            if(!$rowData[$i][$colIdxArr['mtr_input_date']] 
                && !$rowData[$i][$colIdxArr['bom_part_no']]
                && !$rowData[$i][$colIdxArr['mtr_heat']]
                && !$rowData[$i][$colIdxArr['mtr_bundle']]){ //기준날자,품목번호,히트넘버,번들넘버가 없으면 마지막으로 간주하고 루프에서 나간다.
                break;
            }
            //컬럼(필드)명 수정여부 확인
            if($row == 1){
                // echo count($rowData[0]);
                foreach($col_arr as $idx => $varr){
                    // echo $rowData[$i][$idx]."<br>";
                    if(!in_array($rowData[$i][$idx],$varr)){
                        alert('엑셀파일의 ['.$rowData[$i][$idx].']는(은) 변경된 이름이거나,\n삭제 또는 위치가 수정된 것 같습니다.\n( 띄어쓰기 또는 공란도 제거해 주세요. )');
                        exit;
                    }

                    $idxColArr[$idx] = array_search($idx,$colIdxArr);
                }
                continue;
            }
            // print_r2($rowData[$i]);
            $arr = array();
            foreach($idxColArr as $idx => $val){
                $rowData[$i][$idx] = trim($rowData[$i][$idx]);
                //해당 품목번호로 등록된 bom_idx가 존재하는지 확인한다.
                $bom_sql = " SELECT bom_idx,bom_name,bom_part_no,bom_std FROM {$g5['bom_table']} WHERE bom_part_no = '{$rowData[$i][$colIdxArr['bom_part_no']]}' AND bom_status NOT IN ('delete','del','trash') ";
                $bom_res = sql_fetch($bom_sql);

                if(!$bom_res['bom_idx']){
                    $bom_reg_sql = " INSERT INTO {$g5['bom_table']} SET
                        com_idx = '{$_SESSION['ss_com_idx']}'
                        , com_idx_provider = '{$_SESSION['ss_com_idx']}'
                        , bct_id = '10'
                        , bom_name = '{$rowData[$i][$colIdxArr['mtr_name']]}'
                        , bom_part_no = '{$rowData[$i][$colIdxArr['bom_part_no']]}'
                        , bom_type = 'material'
                        , bom_std = '{$rowData[$i][$colIdxArr['bom_std']]}'
                        , bom_press_type = '1_1'
                        , bom_moq = '1'
                        , bom_status = 'ok'
                        , bom_reg_dt = '".G5_TIME_YMDHIS."'
                        , bom_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($bom_reg_sql,1);
                    $bom_idx = sql_insert_id();
                    $arr['bom_idx'] = $bom_idx;
                    $arr['mtr_name'] = $rowData[$i][$colIdxArr['mtr_name']];
                    $arr['bom_std'] = $rowData[$i][$colIdxArr['bom_std']];
                } else {
                    $arr['bom_idx'] = $bom_res['bom_idx'];
                    $arr['mtr_name'] = $bom_res['bom_name'];
                    $arr['bom_std'] = $bom_res['bom_std'];
                }

                if($val == 'mtr_input_date'){
                    $rowData[$i][$idx] = substr($rowData[$i][$idx],0,4).'-'.substr($rowData[$i][$idx],4,2).'-'.substr($rowData[$i][$idx],6,2);
                }
                else if($val == 'bom_part_no'){
                    if(!$rowData[$i][$idx]){
                        alert($row.'행의 품목코드가 빠져 있네요. 제대로 입력해 주세요.');
                        exit;
                    }
                }
                else if($val == 'bom_std'){
                    continue;
                }
                else if($val == 'mtr_heat'){
                    if(!$rowData[$i][$idx]){
                        alert('히트넘버가 누락된 항목 있으면 안됩니다.\n반드시 입력해 주세요.');
                        exit;
                    }
                }
                else if($val == 'mtr_bundle'){
                    if(!$rowData[$i][$idx]){
                        alert('번들넘버 필수항목 입니다.\n없으면 건우에서 만들어서 현장 작업자에게 공유해 주세요.');
                        exit;
                    }
                }
                else if($val == 'mtr_length'){
                    $rowData[$i][$idx] = preg_replace("/mm/","",$rowData[$i][$idx]);
                }

                $arr[$val] = $rowData[$i][$idx];
            }
            array_push($conArr,$arr);
        }
	}
} catch(exception $e) {
	echo $e;
}
// echo $file_name."<br>";
// echo $up_date."<br>";
// print_r2($colIdxArr);
// print_r2($idxColArr);
// print_r2($conArr);
// exit;
/*
[bom_idx] => 22
[mtr_name] => S10C_45Ø
[bom_std] => 세아
[mtr_input_date] => 2022-11-07
[bom_part_no] => 1010045
[mtr_weight] => 1580
[mtr_price] => 1300
[mtr_sum_price] => 2054000
[mtr_lot] => D16828
[mtr_heat] => SM45C
[mtr_bundle] => D22SAK1320-G018
[mtr_length] => 6000
*/
if(count($conArr)){
    for($i=0;$i<count($conArr);$i++){
        $mtr_sql = " SELECT mtr_idx FROM {$g5['material_table']} WHERE mtr_bundle = '{$conArr[$i]['mtr_bundle']}' AND mtr_status NOT IN ('delete','del','trash','cancel') ";
        $mtr_res = sql_fetch($mtr_sql);
        // print_r2($bom_res);
        if(!$mtr_res['mtr_idx']){ //없으면 INSERT
            $sql = " INSERT INTO {$g5['material_table']} SET
                        com_idx = '{$_SESSION['ss_com_idx']}'
                        , bom_idx = '{$bom_res['bom_idx']}'
                        , bom_part_no = '{$conArr[$i]['bom_part_no']}'
                        , mtr_name = '{$conArr[$i]['mtr_name']}'
                        , mtr_type = 'material'
                        , mtr_length = '{$conArr[$i]['mtr_lenth']}'
                        , mtr_weight = '{$conArr[$i]['mtr_weight']}'
                        , mtr_price = '{$conArr[$i]['mtr_price']}'
                        , mtr_sum_price = '{$conArr[$i]['mtr_sum_price']}'
                        , mtr_lot = '{$conArr[$i]['mtr_lot']}'
                        , mtr_heat = '{$conArr[$i]['mtr_lot']}'
                        , mtr_bundle = '{$conArr[$i]['mtr_bundle']}'
                        , mtr_status = 'stock'
                        , mtr_input_date = '{$conArr[$i]['mtr_input_date']}'
                        , mtr_reg_dt = '".G5_TIME_YMDHIS."'
                        , mtr_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql,1);
        }
        else{ //있으면 수정
            $sql = " UPDATE {$g5['material_table']} SET
                        com_idx = '{$_SESSION['ss_com_idx']}'
                        , bom_idx = '{$bom_res['bom_idx']}'
                        , bom_part_no = '{$conArr[$i]['bom_part_no']}'
                        , mtr_name = '{$conArr[$i]['mtr_name']}'
                        , mtr_type = 'material'
                        , mtr_length = '{$conArr[$i]['mtr_lenth']}'
                        , mtr_weight = '{$conArr[$i]['mtr_weight']}'
                        , mtr_price = '{$conArr[$i]['mtr_price']}'
                        , mtr_sum_price = '{$conArr[$i]['mtr_sum_price']}'
                        , mtr_lot = '{$conArr[$i]['mtr_lot']}'
                        , mtr_heat = '{$conArr[$i]['mtr_lot']}'
                        , mtr_bundle = '{$conArr[$i]['mtr_bundle']}'
                        , mtr_status = 'stock'
                        , mtr_input_date = '{$conArr[$i]['mtr_input_date']}'
                        , mtr_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE mtr_idx = '{$mtr_res['mtr_idx']}'
            ";
            sql_query($sql,1);
        }
    }
} // if(count($conArr))

// exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./'.$file_name.'.php?'.$qstr);