<?php
// 크롬 요소검사 열고 확인하면 되겠습니다. 
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

// 받은 값 전체를 찍어보기
//3개 입력: http://local.ingsystem.com/device/data/?com_idx%5B%5D=6&com_idx%5B%5D=2&com_idx%5B%5D=20&imp_idx%5B%5D=883&imp_idx%5B%5D=27087&imp_idx%5B%5D=35133&cod_code%5B%5D=M0100&cod_code%5B%5D=M0101&cod_code%5B%5D=M0102&cod_group%5B%5D=err&cod_group%5B%5D=run&cod_group%5B%5D=product&cod_name%5B%5D=2&cod_name%5B%5D=3&cod_date%5B%5D=2020.01.01&cod_date%5B%5D=2020.01.02&cod_date%5B%5D=2020.01.03&cod_time%5B%5D=02.01.01&cod_time%5B%5D=03.01.01&cod_time%5B%5D=04.01.01&cod_value%5B%5D=30&cod_value%5B%5D=40&cod_value%5B%5D=50&cod_name%5B%5D=4&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.1&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.2&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.3
//1개 입력: http://local.ingsystem.com/device/data/?com_idx%5B%5D=20&imp_idx%5B%5D=351&cod_code%5B%5D=M0100&cod_group%5B%5D=err&cod_name%5B%5D=2&cod_date%5B%5D=2010.01.01&cod_time%5B%5D=01:02:03&cod_value%5B%5D=230&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.
//print_r2($_REQUEST);exit;

$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고
//print_r2($getData);
//echo $getData[0]['token'];
//print_r2($getData[0]['list']);
//echo json_encode(array('result_code' => '200', 'result'=>$getData));
//exit;

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if(is_array($getData[0]['list'])) {

    for($i=0;$i<sizeof($getData[0]['list']);$i++) {
        $arr = $getData[0]['list'][$i];

        $arr['cod_status'] = 'ok';

        // 값이 있을 때만 입력
        $sql_cod_memo = ($arr['cod_memo']) ? ", cod_memo = '".$arr['cod_memo']."'" : "";
        
        
        // 공통요소
        $sql_common[$i] = " com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , cod_code = '".$arr['cod_code']."'
                        , cod_group = '".$arr['cod_group']."'
                        , cod_type = '".$arr['cod_type']."'
                        , cod_interval = '".$arr['cod_interval']."'
                        , cod_count = '".$arr['cod_count']."'
                        {$sql_cod_memo}
                        , cod_status = '".$arr['cod_status']."'
        ";

        //com_idx, imp_idx, cod_code, cod_group
        // 상기 4개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_cod = "   SELECT cod_idx, cod_name FROM {$g5['code_table']} 
                        WHERE mms_idx = '".$arr['mms_idx']."'
                            AND cod_code = '".$arr['cod_code']."'
                            AND cod_group = '".$arr['cod_group']."'
        ";
        //echo $sql_cod.'<br>';
		$cod = sql_fetch($sql_cod,1);
        
		// 정보 업데이트
		if($cod['cod_idx']) {
			
            // 입력할 값이 [코드]만 있고 이전 값이 [코드+내용]이면 기존값 유지
            $arr['cod_name'] = (preg_match("/^.[A-Z0-9]+.$/",$arr['cod_name']) 
                                    && preg_match("/^.[A-Z0-9]+..*[가-힣]+/",$cod['cod_name'])) ? $cod['cod_name']
                                : $arr['cod_name'];

            // 값이 있을 때만 입력
            $sql_cod_name = ($arr['cod_name']) ? ", cod_name = '".$arr['cod_name']."'" : "";

			$sql = "UPDATE {$g5['code_table']} SET 
						{$sql_common[$i]}
                        {$sql_cod_name}
						, cod_update_dt = '".G5_TIME_YMDHIS."'
					WHERE cod_idx = '".$cod['cod_idx']."'
            ";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['code_table']} SET 
						{$sql_common[$i]}
						, cod_name = '".$arr['cod_name']."'
						, cod_reg_dt = '".G5_TIME_YMDHIS."'
            ";
			sql_query($sql,1);
            //echo $sql.'<br>';
            $cod['cod_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Inserted OK!";
        
        }

        $result_arr[$i]['cod_idx'] = $cod['cod_idx'];
    
    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>