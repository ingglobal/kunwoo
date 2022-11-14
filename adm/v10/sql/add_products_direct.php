<?php
include_once('./_head.sub.php');

// g5_0_products
// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/g5_0_products.xls';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

$sleepsec = 10000;

try {
    // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
    $objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();
    $col_name_num = 1;
    $col_names = array();
    // $col_values = array();
    $datas = array();
    // 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++){
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();// 마지막 행
        $highestColumn = $sheet -> getHighestColumn();// 마지막 컬럼
        $highestColumn = 'AD';//마지막 컬럼을 불필요하게 'AE'까지 읽으므로 바로 앞단의 'AD'까지 읽도록 오프셋한다.30개로
        // 한줄읽기
        for($row = 0; $row < $highestRow; $row++) {
            if($row == 0) continue;
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            if($row == $col_name_num){
                $col_names = $rowData[0];
                continue;
            }
			// array_push($col_values,$rowData[0]);
            $data = array(
                $col_names[0] => trim($rowData[0][0])
                ,$col_names[1] => trim($rowData[0][1])
                ,$col_names[2] => trim($rowData[0][2])
                ,$col_names[3] => trim($rowData[0][3])
                ,$col_names[4] => trim($rowData[0][4])
                ,$col_names[5] => trim($rowData[0][5])
                ,$col_names[6] => trim($rowData[0][6])
                ,$col_names[7] => trim($rowData[0][7])
                ,$col_names[8] => trim($rowData[0][8])
                ,$col_names[9] => trim($rowData[0][9])
                ,$col_names[10] => trim($rowData[0][10])
                ,$col_names[11] => trim($rowData[0][11])
                ,$col_names[12] => trim($rowData[0][12])
                ,$col_names[13] => trim($rowData[0][13])
                ,$col_names[14] => trim($rowData[0][14])
                ,$col_names[15] => trim($rowData[0][15])
                ,$col_names[16] => trim($rowData[0][16])
                ,$col_names[17] => trim($rowData[0][17])
                ,$col_names[18] => trim($rowData[0][18])
                ,$col_names[19] => trim($rowData[0][19])
                ,$col_names[20] => trim($rowData[0][20])
                ,$col_names[21] => trim($rowData[0][21])
                ,$col_names[22] => trim($rowData[0][22])
                ,$col_names[23] => trim($rowData[0][23])
            );
            array_push($datas,$data);
        }
    }
    // print_r2($col_names);
    // print_r2($col_values);
} catch(exception $e) {
    print_r2($e);
}
//################ 시작: 루프 (시간지연) #####################
// echo count($datas);
// if(false){
if(count($datas)){
    //거래처 테이블을 텅비우고 초기화 한다.
    $truncate_sql = " TRUNCATE `g5_0_products` ";
    sql_query($truncate_sql,1);
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
$countgap = 10; //몇건씩 보내는가?
$sleepsec = 10000;//10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지 설정

flush();
ob_flush();

//초기 데이터 설정 작업
$cnt = 0;
$result = count($datas);
for($i=0;$i<$result;$i++){
    $cnt++;
    $in_cnt = 0;
    $common_sql = '';
    foreach($datas[$i] AS $k=>$v){
        $common_sql .= ($in_cnt == 0)?' '.$k.' = "'.$v.'" ':' , '.$k.' = "'.$v.'" ';
        $in_cnt++;
    }
    $sql = ' INSERT INTO `g5_0_products` SET
        '.$common_sql.'
    ';
    // echo $sql."<br>";
    sql_query($sql,1);
    echo "<script>document.all.cont.innerHTML += '".$cnt." - 처리됨<br>';</script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);

    //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if($cnt % $countgap == 0){
        echo "<script>document.all.cont.innerHTML += '<br>';</script>\n";
    }

    //화면 정리! 부하를 줄임 (화면을 싹 지움)
    if($cnt % $maxscreen == 0){
        echo "<script>document.all.cont.innerHTML = '';</script>\n";
    }
}
?>
<script>
    document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>
<?php
}
//################ 종료: 루프 (시간지연) #####################
include_once('./_tail.sub.php');
?>