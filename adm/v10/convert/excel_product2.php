<?
// 공통파일 추가
// https://test.woogle.kr/adm/v10/convert/excel_product2.php
include_once("./_common.php");

$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once "../lib/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once "../lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = './excel/product_local.xlsx'; // 읽어들일 엑셀 파일의 경로와 파일명을 지정한다.


if($member['mb_level'] < 8)
	alert('관리자로 로그인해 주세요.');


// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
$objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 읽기전용으로 설정
$objReader->setReadDataOnly(true);

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


$g5['title'] = '엑셀 입력 페이지';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="" style="padding:10px;">
	<span style=''>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>


<?php
// 변수설정
$it_price_types = array('제작'=>'make','광고'=>'ad','기타'=>'etc');
//$trm_names = array('15'=>'엔씨엠로컬1부','16'=>'엔씨엠로컬1팀','17'=>'엔씨엠로컬2부','18'=>'엔씨엠로컬2부산');
$trm_names = array('16'=>'엔씨엠로컬1팀','18'=>'엔씨엠로컬2부산');


$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();


for($i = 2 ; $i <= $maxRow ; $i++) {
    $cnt++;

    $dataA[$i] = $objWorksheet->getCell('A'.$i)->getValue(); // A열
    $dataB[$i] = $objWorksheet->getCell('B'.$i)->getValue(); // B열, 상품명
    $dataC[$i] = $objWorksheet->getCell('C'.$i)->getValue(); // C열
    $dataD[$i] = $objWorksheet->getCell('D'.$i)->getValue(); // D열
    $dataE[$i] = $objWorksheet->getCell('E'.$i)->getValue(); // E열
    $dataF[$i] = $objWorksheet->getCell('F'.$i)->getValue(); // F열
    $dataG[$i] = $objWorksheet->getCell('G'.$i)->getValue();
    $dataH[$i] = $objWorksheet->getCell('H'.$i)->getValue();
    
    //echo $dataD[$i].'<br>';
    
    // 변수 생성
    $it_id[$i] = substr(time(),0,-3).sprintf("%03d",$i);
    $it_info_value[$i] = 'a:8:{s:8:"material";s:22:"상품페이지 참고";s:5:"color";s:22:"상품페이지 참고";s:4:"size";s:22:"상품페이지 참고";s:5:"maker";s:22:"상품페이지 참고";s:7:"caution";s:22:"상품페이지 참고";s:16:"manufacturing_ym";s:22:"상품페이지 참고";s:8:"warranty";s:22:"상품페이지 참고";s:2:"as";s:22:"상품페이지 참고";}';
    $it_tel_inq[$i] = ($dataC[$i]) ? 0: 1;
    
    // 카테고리 번호
    $ca1 = sql_fetch(" SELECT ca_id, ca_name FROM g5_shop_category WHERE ca_name = '".$dataA[$i]."' ");
    $ca_id[$i] = $ca1['ca_id'];
    

    if($dataA[$i]) {

        // 엑셀 입력
        $sql =	" INSERT INTO g5_shop_item SET
                    it_id               = '".$it_id[$i]."',
                    ca_id               = '".$ca_id[$i]."',
                    it_name             = '".$dataB[$i]."',
                    it_basic            = '".$dataE[$i]."',
                    it_price            = '".preg_replace("/,/","",$dataC[$i])."',
                    it_use              = '1',
                    it_stock_qty        = '99999',
                    it_time             = '".G5_TIME_YMDHIS."',
                    it_update_time      = '".G5_TIME_YMDHIS."',
                    it_ip               = '{$_SERVER['REMOTE_ADDR']}',
                    it_tel_inq          = '".$it_tel_inq[$i]."',
                    it_info_gubun       = 'wear',
                    it_info_value       = '".$it_info_value[$i]."',
                    it_use_avg          = '0.0',
                    it_10               = ':15:,:16:,:17:,:18:,:3:,:9:,:10:,:11:,:4:,:12:,:13:'
        ";
        if($demo) {echo $sql.'<br>--------<br>';}
        if(!$demo) {sql_query($sql,1);}
        
        // 매출구분
        $ar['mta_db_table'] = 'shop_item';
        $ar['mta_db_id'] = $it_id[$i];
        $ar['mta_key'] = 'it_price_type';
        $ar['mta_value'] = $it_price_types[$dataF[$i]];
        if($demo) {print_r2($ar);}
        if(!$demo) {meta_update($ar);}
        unset($ar);
        

        // 상품분리
        $ar['mta_db_table'] = 'shop_item';
        $ar['mta_db_id'] = $it_id[$i];
        $ar['mta_key'] = 'it_cart_separate_yn';
        $ar['mta_value'] = $dataG[$i];
        if($demo) {print_r2($ar);}
        if(!$demo) {meta_update($ar);}
        unset($ar);
        
        
        // 제작여부
        $ar['mta_db_table'] = 'shop_item';
        $ar['mta_db_id'] = $it_id[$i];
        $ar['mta_key'] = 'it_make_yn';
        $ar['mta_value'] = $dataH[$i];
        if($demo) {print_r2($ar);}
        if(!$demo) {meta_update($ar);}
        unset($ar);
        

        // 수당 정보 입력 : 15,16,17,18 동대문팀 수당정보 추가
        if($dataD[$i]) {
            // %는 소수점으로 표현됨
            if( $dataD[$i] < 1 ) {
                $sra_price_type[$i] = 'rate';
                $sra_price[$i] = $dataD[$i]*100;
            }
            else {
                $sra_price_type[$i] = 'money';
                $sra_price[$i] = preg_replace("/,/","",$dataD[$i]);
            }
            
            // 팀별 수당 입력
            foreach($trm_names as $key=>$value) {
                
                $sql =	" INSERT INTO {$g5['share_rate_table']} SET
                            trm_idx_department = '".$key."'
                            , it_id = '".$it_id[$i]."'
                            , sra_type = 'order_team'
                            , sra_name = '".$value." 수당'
                            , sra_price_type = '".$sra_price_type[$i]."'
                            , sra_price = '".$sra_price[$i]."'
                            , sra_start_date = '".G5_TIME_YMD."'
                            , sra_end_date = '9999-12-31'
                            , sra_status = 'ok'
                            , sra_reg_dt = '".G5_TIME_YMDHIS."'
                ";
                if($demo) {echo $sql.'<br>========================<br>';}
                if(!$demo) {sql_query($sql,1);}
                
            }
            
        }


        // 메시지 보임
        echo "<script> document.all.cont.innerHTML += '".$cnt.". [".$dataA[$i]."] ".$dataB[$i]." > 처리완료<br>'; </script>\n";
        
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
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($cnt) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
