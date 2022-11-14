<?php
$sub_menu = "917230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

$demo = 1;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);


// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
$objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 읽기전용으로 설정
// $objReader->setReadDataOnly(true);

// 엑셀파일을 읽는다
$objExcel = $objReader->load($filename);

// 첫번째 시트를 선택
$objExcel->setActiveSheetIndex(0);

$objWorksheet = $objExcel->getActiveSheet();

$rowIterator = $objWorksheet->getRowIterator();
foreach ($rowIterator as $row) { // 모든 행에 대해서
	$cellIterator = $row->getCellIterator();
	$cellIterator->setIterateOnlyExistingCells(false); 
}
$maxRow = $objWorksheet->getHighestRow();
$maxColumn = $objWorksheet->getHighestDataColumn();
//echo $maxRow.'<br>';
//echo $maxColumn.'<br>';


$g5['title'] = '엑셀 업로드';
// include_once('./_top_menu_stat_data.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once ('./_tail.php');
?>

<?php
$eidx = 0;  // 엑셀 카운터

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();


for($i = 2 ; $i <= $maxRow ; $i++) {
    $cnt++;

    $dataA[$i] = $objWorksheet->getCell('A'.$i)->getValue(); // 고유번호
    $dataB[$i] = $objWorksheet->getCell('B'.$i)->getValue(); // 업체번호
    $dataC[$i] = $objWorksheet->getCell('C'.$i)->getValue(); // IMP번호
    $dataD[$i] = $objWorksheet->getCell('D'.$i)->getValue(); // MMS번호
    $dataE[$i] = $objWorksheet->getCell('E'.$i)->getValue(); // 코드(iMMS)
    $dataF[$i] = $objWorksheet->getCell('F'.$i)->getValue(); // 분류
    $dataG[$i] = $objWorksheet->getCell('G'.$i)->getValue(); // 비가동영향
    $dataH[$i] = $objWorksheet->getCell('H'.$i)->getValue(); // 품질영향
    $dataI[$i] = $objWorksheet->getCell('I'.$i)->getValue(); // 코드그룹
    $dataJ[$i] = $objWorksheet->getCell('J'.$i)->getValue(); // 코드타입
    $dataK[$i] = $objWorksheet->getCell('K'.$i)->getValue(); // 주기시간
    $dataL[$i] = $objWorksheet->getCell('L'.$i)->getValue(); // 횟수
    $dataM[$i] = $objWorksheet->getCell('M'.$i)->getValue(); // 하루최대
    $dataN[$i] = $objWorksheet->getCell('N'.$i)->getValue(); // 내용
    $dataO[$i] = $objWorksheet->getCell('O'.$i)->getValue(); // 비고
    
//    echo '===================================== '.$dataL[$i].'<br>';
    
    // 변수 생성
    // $dataA[$i] = ($dataA[$i]) ? $dataA[$i] : $item['매출기준일'][$i];

    if( is_numeric($dataA[$i]) && is_numeric($dataB[$i]) && is_numeric($dataC[$i]) && is_numeric($dataD[$i]) ) {

        // remove all characters which is not number
        $dataA[$i] = trim( preg_replace("/[^0-9]*/s", "", $dataA[$i]) );
        $dataB[$i] = trim( preg_replace("/[^0-9]*/s", "", $dataB[$i]) );
        $dataC[$i] = trim( preg_replace("/[^0-9]*/s", "", $dataC[$i]) );
        $dataD[$i] = trim( preg_replace("/[^0-9]*/s", "", $dataD[$i]) );    //MMS번호
        $dataE[$i] = trim( $dataE[$i] );
        $dataF[$i] = trim( $dataF[$i] );    // 분류
        $dataG[$i] = trim( $dataG[$i] );    // 비가동영향
        $dataH[$i] = trim( $dataH[$i] );    // 품질영향
        $dataI[$i] = trim( $dataI[$i] );    // 코드그룹
        $dataJ[$i] = trim( $dataJ[$i] );    // 코드타입
        $dataK[$i] = trim( $dataK[$i] );    // 주기시간
        $dataL[$i] = trim( $dataL[$i] );
        $dataM[$i] = trim( $dataM[$i] );
        $dataN[$i] = trim( $dataN[$i] );
        $dataO[$i] = trim( $dataO[$i] );
        
        $sql_common = " com_idx	            = '".$dataB[$i]."',
                        imp_idx	            = '".$dataC[$i]."',
                        mms_idx	            = '".$dataD[$i]."',
                        cod_code	        = '".$dataE[$i]."',
                        trm_idx_category    = '".$dataF[$i]."',
                        cod_offline_yn      = '".$dataG[$i]."',
                        cod_quality_yn      = '".$dataH[$i]."',
                        cod_group	        = '".$dataI[$i]."',
                        cod_type	        = '".$dataJ[$i]."',
                        cod_interval	    = '".$dataK[$i]."',
                        cod_count	        = '".$dataL[$i]."',
                        cod_count_limit     = '".$dataM[$i]."',
                        cod_name	        = '".$dataN[$i]."',
                        cod_memo	        = '".$dataO[$i]."'
        ";
        
        // create if not exists, update for existing
        $sql = "	SELECT cod_idx FROM {$g5['code_table']} 
                                WHERE mms_idx = '".$dataD[$i]."'
                                    AND cod_code = '".$dataE[$i]."'
                                    AND cod_group = '".$dataI[$i]."'
                                    AND cod_status = 'ok'
        ";
        // print_r3($sql);
        $cod = sql_fetch($sql,1);
        if(!$cod['cod_idx']) {
            $sql = "INSERT INTO {$g5['code_table']} SET
                        cod_send_type = 'email,push',
                        cod_status = 'ok',
                        cod_reg_dt = '".G5_TIME_YMDHIS."',
                        cod_update_dt = '".G5_TIME_YMDHIS."',
                        {$sql_common}
            ";
            sql_query($sql,1);
        }
        else {
            $sql = "UPDATE {$g5['code_table']} SET
                        {$sql_common}
                    WHERE cod_idx = '".$cod['cod_idx']."'
            ";
            sql_query($sql,1);
        }
        // print_r3($sql);
			
        $eidx++;

        // 메시지 보임
        echo "<script> document.all.cont.innerHTML += '".$cnt.". MMS(".$dataD[$i].") ".$dataE[$i].", 비가동=".$dataG[$i].", 품질=".$dataH[$i].", ".$dataN[$i]." >> 처리완료<br>'; </script>\n";
        
        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);
        
        // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if ($cnt % $countgap == 0)
            echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
        
        // 화면 정리! 부하를 줄임 (화면 싹 지움)
        if ($cnt % $maxscreen == 0)
            echo "<script> document.all.cont.innerHTML = ''; </script>\n";
        
    }

}






// 관리자 디버깅 메시지
if( is_array($g5['debug_msg']) ) {
    for($i=0;$i<sizeof($g5['debug_msg']);$i++) {
        echo '<div class="debug_msg">'.$g5['debug_msg'][$i].'</div>';
    }
?>
    <script>
    $(function(){
        $("#container").prepend( $('.debug_msg') );
    });
    </script>
<?php
}
?>



<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($eidx) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>