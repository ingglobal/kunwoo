<?
// 공통파일 추가
include_once("./_common.php");

//-- 화면 표시 
$countgap = 10; // 몇건씩 보낼지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?
$sleepsec = 200;  // 천분의 몇초간 쉴지 설정


//-- 설정값
$pg_array = array('offline','kcp','lgu','paypal','ksnet','etc');
$status_array = array('pending','ok','ok','ok','cancel');
$ampm_array = array('am','pm');

/*
Array
(
    [cam_idx] => 1
    [mb_id_company] => test90
    [mb_id_saler] => test40
    [prd_idx] => 4
    [prd_times_count] => 1
    [cam_mb_name] => 테스트90
    [cam_mb_tel] => 010-9149-9511
    [cam_mb_email] => test90@test.com
    [cam_mb_saler] => 테스트40
    [cam_company_name] => 회사이름1
    [cam_company_tel] => 010-9149-9511
    [cam_president_name] => 대표자1
    [prd_name] => 1회진행상품
    [cam_type] => visit
    [cam_name] => 캠페인명1
    [cam_brief] => cam_brief1
    [cam_reviewer_yn] => 1
    [cam_recruit_count] => 5
    [cam_info_reg_dt] => 2015-11-26 05:42:16
    [cam_recruit_start_dt] => 2015-11-26 05:42:16
    [cam_recruit_end_dt] => 2015-12-01 05:42:16
    [cam_notice_dt] => 2015-12-03 05:42:16
    [cam_notice_status] => pending
    [cam_review_start_dt] => 2015-12-04 05:42:16
    [cam_review_end_dt] => 2015-12-09 05:42:16
    [cam_select_dt] => 2015-12-10 05:42:16
    [cam_continue_count] => 0
    [cam_point_type] => group
    [cam_reviewer_point] => 0
    [cam_best_point] => 0
    [cam_head_content] => 
    [cam_content] => 
    [cam_notice] => 
    [cam_zip1] => 122
    [cam_zip2] => 35
    [cam_addr1] => 경기 남양주시 경춘로 883-36
    [cam_addr2] => 3번지
    [cam_addr3] => (금곡동, 마을공동회관)
    [cam_addr_jibeon] => R
    [cam_latitude] => 
    [cam_longitude] => 
    [cam_hit] => 0
    [cam_memo] => 
    [cam_status] => pending
    [cam_reg_dt] => 2015-11-26 05:42:16
)
*/
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>자료 입력</title>
</head>
<body>
<span style='font-size:9pt;'>
	<p>입력시작 ...<p><font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전에는 중간에 중지하지 마세요.<p>
</span>
<span id="cont"></span>


<?
// 디비 추출
$sql = "SELECT *
		FROM u_campaign
		WHERE cam_review_end_dt < now()
		";
$rs  = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
	
//	if($i>0)
//		break;
	
	$time = rand(strtotime($row['cam_recruit_start_dt']),strtotime($row['cam_recruit_end_dt']));
	$server_time = $time;
	$time_ymdhis = date('Y-m-d H:i:s', $server_time);
	$time_ymd = substr($time_ymdhis, 0, 10);
	$time_his = substr($time_ymdhis, 11, 8);
	
	//echo $row['cam_recruit_count'];
	//캠페인별 모집인원수 만큼 루프를 돌리며 신청자를 등록을 한다.
	
	if($row['cam_recruit_count'] > 0){
		for($j=0;$j<$row['cam_recruit_count'];$j++){
			$cam_idx[$j] = $row['cam_idx']; //캠페인 idx저장
			$mb_info = sql_fetch(" SELECT * FROM g5_member WHERE mb_level = 2 ORDER BY RAND() LIMIT 1 ");
			$mb_id[$j] = $mb_info['mb_id'];
			$cma_content[$j] = 'content_'.$mb_id[$j];
			$cma_selected_dt[$j] = $row['cam_notice_dt'];
			$cma_reg_end_dt[$j] = $row['cam_review_end_dt'];
			$cma_zip1[$j] = $mb_info['mb_zip1'];
			$cma_zip2[$j] = $mb_info['mb_zip2'];
			$cma_addr1[$j] = $mb_info['mb_addr1'];
			$cma_addr2[$j] = $mb_info['mb_addr2'];
			$cma_addr3[$j] = $mb_info['mb_addr3'];
			$cma_jibeon[$j] = $mb_info['mb_addr_jibeon'];
			$cma_status[$j] = "chosen"; //리뷰자로 채택
			$cma_reg_dt[$j] = $time_ymdhis;
			
			$sql1 = " INSERT INTO u_campaign_apply SET					".
				"	cam_idx	= '".$cam_idx[$j]."'						".
				"	, mb_id = '".$mb_id[$j]."'							".
				"	, cma_content = '".$cma_content[$j]."'				".
				"	, cma_selected_dt = '".$cma_selected_dt[$j]."'		".
				"	, cma_reg_end_dt = '".$cma_reg_end_dt[$j]."'		".
				"	, cma_zip1 = '".$cma_zip1[$j]."'					".
				"	, cma_zip2 = '".$cma_zip2[$j]."'					".
				"	, cma_addr1 = '".$cma_addr1[$j]."'					".
				"	, cma_addr2 = '".$cma_addr2[$j]."'					".
				"	, cma_addr3 = '".$cma_addr3[$j]."'					".
				"	, cma_jibeon = '".$cma_jibeon[$j]."'				".
				"	, cma_status = '".$cma_status[$j]."'				".
				"	, cma_reg_dt = '".$cma_reg_dt[$j]."'				";
			sql_query($sql1,1);	//<===============================
			//echo $sql1.'<br><br>';
			$insert_cma_idx = sql_insert_id();
			
			for($k=0;$k<5;$k++) {
				$time1 = rand(strtotime($row['cam_review_start_dt']),strtotime($row['cam_review_end_dt']));
				$server_time1 = $time1;
				$time_ymdhis1 = date('Y-m-d H:i:s', $server_time1);
				$time_ymd1 = substr($time_ymdhis1, 0, 10);
				$time_his1 = substr($time_ymdhis1, 11, 8);
				
				$rev_cam_idx[$k] = $row['cam_idx'];
				$rev_cma_idx[$k] = $insert_cma_idx;
				$rev_mb_id[$k] = $mb_id[$j];
				$rev_trm_idx[$k] = 481;
				$rev_url[$k] = 'http://www.daum.net';
				$rev_best_dt[$k] = '';
				$rev_status[$k] = 'ok';
				$rev_reg_dt[$k] = $time_ymdhis1;
				
				
				$sql2 = " INSERT INTO u_review SET								".
						"	cam_idx	= '".$rev_cam_idx[$k]."'					".
						"	, cma_idx = '".$rev_cma_idx[$k]."'					".
						"	, mb_id = '".$rev_mb_id[$k]."'						".
						"	, trm_idx = '".$rev_trm_idx[$k]."'					".
						"	, rev_url = '".$rev_url[$j]."'						".
						"	, rev_status = '".$rev_status[$j]."'				".
						"	, rev_reg_dt = '".$rev_reg_dt[$k]."'				";
				sql_query($sql2,1);	//<===============================
				//echo $sql2.'<br><br>';
			}
		}
	}
	
	
	//-- DOM 보도 자바스크립트가 좀 빨라서 0,1번이 안 보이고 2번부터 보이는 표현상 에러가 있음, 동작에는 무리가 없습니다.
	echo "<script> document.all.cont.innerHTML += '".($i+1).". ".addslashes($sql1)."<br><br>'; </script>\n";
	//echo "+";
    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    if ($i % $countgap == 0)
    {
        echo "<script> document.all.cont.innerHTML += '<br>'; document.body.scrollTop += 1000; </script>\n";
    }

    // 화면을 지운다... 부하를 줄임
    if ($i % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; document.body.scrollTop += 1000; </script>\n";
	
	
}

//특정 날짜에 일수를 더한 날짜를 반환해 주는 함수
function get_dayAddDate($dateInfo,$dayNum){//임채완이 재정의 한 함수(일수계산)
	$dtArr = explode('-',$dateInfo);
	$year_ = $dtArr[0];
	$month_ = $dtArr[1];
	$day_ = $dtArr[2];
	$dt = mktime(0,0,0,$month_,$day_+$dayNum,$year_);

	return date("Y-m-d",$dt);
} 
?>

<script> document.all.cont.innerHTML += "<br><br><br>총 <?php echo number_format($i) ?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>
</body>
</html>
