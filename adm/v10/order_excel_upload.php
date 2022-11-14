<?php
$sub_menu = "920100";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

//전체 엑셀 데이터를 담을 배열을 선언한다.
$pnoArr = array(); //bom에 등록되지 않은 pno배열(등록해야함을 유도)
$gstkArr = array(); //guest_stock_array(고객처 재고 배열)
$gidxArr = array();
$dateArr = array(9 => '',10 => '',11 => '',12 => '',13 => '',14 => '',15 => '',16 => '',17 => '',18 => '',19 => '',20 => '',21 => '');
$ordArr = array();
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
$todate = G5_TIME_YMD;

//전체 엑셀 데이터를 담을 배열을 선언한다.
$catArr = array();
$caArr = array();
$itmArr = array();
$modBom = array();//update해야하는 상품
$addBom = array();//새로 추가해야 하는 상품
$c = 0;

try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();

	// 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++) {
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();   			           // 마지막 행
        $highestColumn = $sheet -> getHighestColumn();	// 마지막 컬럼
        // 한줄읽기
        for($row = 1; $row <= $highestRow; $row++) {
            //if($row > 41) break;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            $rowData[0][1] = trim($rowData[0][1]); //1차카테고리
			$rowData[0][2] = trim($rowData[0][2]); //2차카테고리
			$rowData[0][3] = trim($rowData[0][3]); //3차카테고리
			$rowData[0][4] = trim($rowData[0][4]); //4차카테고리
			$rowData[0][5] = trim($rowData[0][5]); //품번
			$rowData[0][6] = trim($rowData[0][6]); //품명
			$rowData[0][25] = trim($rowData[0][25]); //외부라벨코드

            if( $rowData[0][6] == '품명' ) {
                foreach($dateArr as $idx => $idv){
                    $rowData[0][$idx] = PHPExcel_Style_NumberFormat :: toFormattedString ($rowData[0][$idx], PHPExcel_Style_NumberFormat :: FORMAT_DATE_YYYYMMDD2);
                    $dateArr[$idx] = $rowData[0][$idx];
                    if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$rowData[0][$idx])){
                        alert('날짜형식에 맞지 않습니다. 날짜데이터는 날짜서식으로 엑셀파일을 정확히 설정해 주세요.');
                    }
                    $ordArr[$rowData[0][$idx]] = array();
                }
                if( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dateArr[9]) ){
                    $todate = $dateArr[9];
                }
            }
            if(
				preg_match('/[A-Z]/',$rowData[0][1])
				&& 	preg_match('/[A-Z]{1,3}[\/]?[A-Z]{1,}/',$rowData[0][2])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][3])
				&& 	preg_match('/[\/가-힣ㄱ-ㅎㅏ-ㅣ_A-Z]+/',$rowData[0][4])
				&& 	preg_match('/[A-Z\-0-9]+/',$rowData[0][5])
				&& 	preg_match('/[가-힣ㄱ-ㅎㅏ-ㅣ\/\_A-Z]+/',$rowData[0][6])
			){ //품번에 특정 규칙값이 있으면 이하 실행


                //고객처 재고 데이터가 있으면 따로 배열에 저장해라
                if( $rowData[0][7] ) {
                    $gstArr[$rowData[0][5]] = $rowData[0][7];
                }

                foreach($dateArr as $id => $date) {
                    if( $rowData[0][$id] ) {
                        $ordArr[$date][$rowData[0][5]] = $rowData[0][$id];
                    }
                }


				$c++;
            }
            else if(
				preg_match('/[A-Z\-0-9]+/',$rowData[0][5]) && ( !$rowData[0][1] || !$rowData[0][2] || !$rowData[0][3] || !$rowData[0][4] || !$rowData[0][6] )
			){
				alert('['.$rowData[0][5].'] 품번의 카테고리 또는 품명에 누락이 있습니다.\\n한 번 더 확인하시고 수정하여 다시 시도 해 주세요.');
				break;
			}
        }
	}
} catch( exception $e ) {
	echo $e;
    exit;
}


//89G70-CG980USY 나파그레이_최후석
/*
echo $com_idx."<br>";
echo $todate."<br>";
print_r2($dateArr);
echo 'pnoArr<br>';
print_r2($pnoArr);
echo 'gstArr<br>';
print_r2($gstArr);
echo 'ordArr<br>';
print_r2($ordArr);
exit;
*/
//print_r2($gstArr);
if( @count($gstArr) ){
    foreach($gstArr as $gk => $gv){
        //고객처 갯수가 0/null 이 아닌 값이 있는 것만 등록/수정을 한다.
        if($gv){
            //해당 bom_part_no의 고객처, bom_idx를 조회한다.
            $gbom = sql_fetch(" SELECT com_idx_customer,bom_idx FROM {$g5['bom_table']} WHERE bom_status NOT IN('delete','del','trash') AND bom_part_no = '{$gk}' ");
            //오늘날짜의 해당 bom_idx값의 레코드가 존재하는 확인
            $gst = sql_fetch(" SELECT gst_idx FROM {$g5['guest_stock_table']}
                                WHERE gst_date = '{$todate}'
                                    AND gst_status NOT IN('delete','del','trash')
                                    AND bom_idx = '{$gbom['bom_idx']}'
            ");
            //이미 오늘날짜의 고객처재고로 등록된 해당 bom_idx의 레코드가 존재하면 수정
            if($gst['gst_idx']){
                $gsql = " UPDATE {$g5['guest_stock_table']} SET
                                gst_count = '{$gv}'
                                ,gst_date = '{$todate}'
                                ,gst_update_dt = '".G5_TIME_YMDHIS."'
                            WHERE gst_idx = '{$gst['gst_idx']}'
                ";
                sql_query($gsql,1);
                $gst_idx = $gst['gst_idx'];
            }
            //이미 오늘날짜의 고객처재고로 등록된 해당 bom_idx의 레코드가 없으면 등록
            else {
                $gsql = " INSERT INTO {$g5['guest_stock_table']} SET
                            com_idx = '{$_SESSION['ss_com_idx']}'
                            ,com_idx_customer = '{$gbom['com_idx_customer']}'
                            ,bom_idx = '{$gbom['bom_idx']}'
                            ,gst_count = '{$gv}'
                            ,gst_date = '{$todate}'
                            ,gst_status = 'ok'
                            ,gst_reg_dt = '".G5_TIME_YMDHIS."'
                            ,gst_update_dt = '".G5_TIME_YMDHIS."'
                ";
                sql_query($gsql,1);
                $gst_idx = sql_insert_id();
            }

            array_push($gidxArr,$gst_idx);
            // echo $gsql."<br>";
        }
    }

    //위에 당일 날짜로 새로 등록되거나 업데이트 되지 않은 gst_idx값들은 전부 삭제 한다.
    $gdsql = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_date = '{$todate}' AND gst_idx NOT IN(".implode(',',$gidxArr).")
    ";
    sql_query($gdsql,1);
    // echo "<br><br>";
    // echo $gdsql."<br>";
}
//만약 엑셀상에서 오늘날짜의 해당하는 수량값이 한 개도 없는경우
else {
    //당일날짜로 등록된 레코드들이 혹시라도 있으면 전부 삭제한다.
    $gdsql = " DELETE FROM {$g5['guest_stock_table']} WHERE gst_date = '{$todate}'
    ";
    sql_query($gdsql,1);
}


//수주데이터 작업
foreach($ordArr as $ok => $ov){
    //echo "<br><br>".$ok.'-count:'.count($ov).'<br>';
    //해당날짜로 등록된 수주레코드가 있는지 확인
    $ord_sql = " SELECT ord_idx FROM {$g5['order_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND ord_status NOT IN('delete','del','trash','cancel') AND ord_date = '{$ok}' ";
    //echo $ord_sql."<br>";
    $ord = sql_fetch($ord_sql);

    //엑셀로부터의 해당 날짜에 속하는 제품이 1개로 들어있으면 실행
    if( @count($ov) ){
        //$ocnt = 1;
        //해당 ord_idx가 없으면 수주레코드부터 등록하고, ord_idx를 추출한다.
        if( !$ord['ord_idx'] ){
            $osql = " INSERT INTO {$g5['order_table']} SET 
                com_idx = '{$_SESSION['ss_com_idx']}'
                , ord_price = ''
                , ord_ship_date = ''
                , ord_status = 'ok'
                , ord_date = '{$ok}'
                , ord_reg_dt = '".G5_TIME_YMDHIS."'
                , ord_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($osql,1);
            $ord_idx = sql_insert_id();
        }
        //해당 ord_idx가 있으면 ord_idx변수에만 대입한다.
        else {
            $ord_idx = $ord['ord_idx'];
        }

        //해당날짜의 수주데이터별 존재하는 수주상품 ori_idx를 담을 배열 정의
        $oriArr = array();
        //하나의 수주레코드에서 제품별 단가를 모두 합산해서 담을 배열 정의
        $ord_price = 0;
        foreach($ov as $ik => $iv){ //ik : 품번 , ik : 갯수
            //echo '( '.$ocnt.' )[ '.$ik.' ] cnt:'.$iv."<br>";
            //해당 수주상품에 대한 oro_idx가 존재하는지 확인
            $oro = sql_fetch(" SELECT oro_idx, ori.ori_idx,bom_price FROM {$g5['order_out_table']} AS oro
                            LEFT JOIN {$g5['order_item_table']} AS ori ON oro.ori_idx = ori.ori_idx
                            LEFT JOIN {$g5['bom_table']} AS bom ON ori.bom_idx = bom.bom_idx
                WHERE oro.ord_idx = '{$ord_idx}'
                    AND bom.bom_part_no = '{$ik}'
            ");

            //이미 해당 수주상품(ori_idx -> bom_idx -> bom_part_no) 에 대한 출하상품(oro_idx)가 존재하면 업데이트 할 수 없다.
            if( $oro['oro_idx'] ) {
                $ori_idx = $oro['ori_idx'];
                $ord_price += $oro['bom_price'];
                array_push($oriArr,$ori_idx);
                continue;//다음루프로 넘어가라
            }
            // 해당 수주상품(ori_idx -> bom_part_no)에 대한 출하상품(oro_idx)이 없으면 추가하거나, 업데이트 한다.
            else {
                //해당 날짜에 해당 ori_idx가 존재하는지 확인한다.
                $ori = sql_fetch(" SELECT ori_idx, bom_price FROM {$g5['order_item_table']} AS ori
                                        LEFT JOIN {$g5['bom_table']} AS bom ON ori.bom_idx = bom.bom_idx
                                    WHERE ord_idx = '{$ord_idx}' 
                                        AND bom_part_no = '{$ik}'
                                        AND ori_status NOT IN('delete','del','trash')
                ");

                //해당 ori_idx가 존재하면 업데이트
                if( $ori['ori_idx'] ) {
                    $orisql = " UPDATE {$g5['order_item_table']} SET
                                    ori_count = '{$iv}'
                                    , ori_price = '{$ori['bom_price']}'
                                    , ori_status = 'ok'
                                    , ori_update_dt = '".G5_TIME_YMDHIS."'
                                WHERE ori_idx = '{$ori['ori_idx']}'
                    ";
                    sql_query($orisql,1);
                    $ord_price += $ori['bom_price'];
                    $ori_idx = $ori['ori_idx'];
                }
                //해당 ori_idx가 없으면 추가
                else {
                    $bom = sql_fetch(" SELECT bom_idx, com_idx_customer FROM {$g5['bom_table']} WHERE bom_part_no = '{$ik}' AND bom_status NOT IN('delete','del','trash') ");
                    $orisql = " INSERT INTO {$g5['order_item_table']} SET
                                    com_idx = '{$_SESSION['ss_com_idx']}'
                                    , com_idx_customer = '{$bom['com_idx_customer']}'
                                    , ord_idx = '{$ord_idx}'
                                    , bom_idx = '{$bom['bom_idx']}'
                                    , ori_count = '{$iv}'
                                    , ori_price = '{$bom['bom_price']}'
                                    , ori_status = 'ok'
                                    , ori_reg_dt = '".G5_TIME_YMDHIS."'
                                    , ori_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($orisql,1);
                    $ori_idx = sql_insert_id();
                    
                    $ord_price += $bom['bom_price'];
                }

            }

            array_push($oriArr,$ori_idx);
            //$ocnt++;
        }
        //누적 총합계 수주금액 업데이트
        sql_query(" UPDATE {$g5['order_table']} SET ord_price = '{$ord_price}' WHERE ord_idx = '{$ord_idx}' ");

        //위에 당일 날짜로 새로 등록되거나 업데이트 되지 않은 ori_idx값들은 전부 삭제 한다.
        if( @count($oriArr) ){
            $oridsql = " DELETE FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' AND ori_idx NOT IN(".implode(',',$oriArr).") ";
            sql_query($oridsql,1);
        }
    }
    //엑셀로부터 해당 날짜에 속하는 수주제품이 하나도 없을때
    else {
        //수주상품 데이터는 없는데 수주 ord_idx는 존재할때
        if( $ord['ord_idx'] ) {
            //해당 수주 데이터의 출하데이터가 존재하는지 확인해라
            $oro_res = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_out_table']} WHERE ord_idx = '{$ord['ord_idx']}' AND oro_status NOT IN('delete','del','trash')
            ");
            //해당 출하레코드가 하나도 없으면 해당 ord_idx로 등록된 수주상품과 수주데이터를 삭제해라
            if(!$oro_res['cnt']){
                //수주상품 데이터를 삭제해라
                $oridel = " DELETE FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord['ord_idx']}' ";
                sql_query($oridel,1);

                //수주데이터를 삭제해라.
                $orddel = " DELETE FROM {$g5['order_table']} WHERE ord_idx = '{$ord['ord_idx']}' ";
                sql_query($orddel,1);
            }
            // 출하레코드가 존재하면
            else {
                continue; 
            }
        }
        //수주상품 데이터도 없고, 수주 ord_idx도 없을때
        else {
            continue;
        }
    }
}

// exit;
goto_url('./order_list.php?'.$qstr, false);
