<?php
include_once('./_head.sub.php');

// g5_0_companies
// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/g5_0_companies.xls';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

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
            );
            array_push($datas,$data);
        }
    }
    // print_r2($col_names);
    // print_r2($col_values);
    // print_r2($datas);
} catch(exception $e) {
    print_r2($e);
}
// exit;
//################ 시작: 루프 (시간지연) #####################
if(count($datas)){
    //테이블 초기화(텅비운다)
    $truncate_sql = " TRUNCATE `g5_0_companies` ";
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
    
    // print_r2($datas);
    // exit;
    //초기 데이터 설정 작업
    $cnt = 0;
    $result = count($datas);
    for($i=0;$i<$result;$i++){
        $cnt++;
        $in_cnt = 0;
        $common_sql = '';
        foreach($datas[$i] AS $k=>$v){
            $common_sql .= ($in_cnt == 0)?" {$k} = '{$v}' ":" , {$k} = '{$v}'  ";
            $in_cnt++;
        }
        $sql = " INSERT INTO `g5_0_companies` SET
            {$common_sql}
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
        echo "<script>document.all.cont.innerHTML += '".$sql." - 처리됨<br>';</script>\n";
    
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
include_once('./_tail.sub.php');
?>