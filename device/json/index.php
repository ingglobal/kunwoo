<?php
header("Content-Type: text/plain; charset=utf-8");
include_once('./_common.php');
if(isset($_SERVER['HTTP_ORIGIN'])){
	header("Access-Control-Allow-Origin:{$_SERVER['HTTP_ORIGIN']}");
	header("Access-Control-Allow-Credentials:true");
	header("Access-Control-Max-Age:86400"); //cache for 1 day
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	exit(0);
}

// 받은 값 전체를 찍어보기
//3개 입력: http://local.ingsystem.com/device/data/?com_idx%5B%5D=6&com_idx%5B%5D=2&com_idx%5B%5D=20&imp_idx%5B%5D=883&imp_idx%5B%5D=27087&imp_idx%5B%5D=35133&cod_code%5B%5D=M0100&cod_code%5B%5D=M0101&cod_code%5B%5D=M0102&cod_group%5B%5D=err&cod_group%5B%5D=run&cod_group%5B%5D=product&cod_name%5B%5D=2&cod_name%5B%5D=3&cod_date%5B%5D=2020.01.01&cod_date%5B%5D=2020.01.02&cod_date%5B%5D=2020.01.03&cod_time%5B%5D=02.01.01&cod_time%5B%5D=03.01.01&cod_time%5B%5D=04.01.01&cod_value%5B%5D=30&cod_value%5B%5D=40&cod_value%5B%5D=50&cod_name%5B%5D=4&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.1&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.2&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.3
//1개 입력: http://local.ingsystem.com/device/data/?com_idx%5B%5D=20&imp_idx%5B%5D=351&cod_code%5B%5D=M0100&cod_group%5B%5D=err&cod_name%5B%5D=2&cod_date%5B%5D=2010.01.01&cod_time%5B%5D=01:02:03&cod_value%5B%5D=230&cod_memo%5B%5D=%ED%94%BC%EB%93%9C%EB%82%B4%EC%9A%A9%EC%9D%B4+%EB%93%A4%EC%96%B4%EA%B0%91%EB%8B%88%EB%8B%A4.
//print_r2($_REQUEST);exit;
//echo $_REQUEST['cod_name'][0];


if(is_array($_REQUEST['com_idx'])) {

    for($i=0;$i<sizeof($_REQUEST['imp_idx']);$i++) {

        $_REQUEST['cod_status'][$i] = 'ok';
        
        // 공통요소
        $sql_common[$i] = " com_idx = '".$_REQUEST['com_idx'][$i]."'
                        , imp_idx = '".$_REQUEST['imp_idx'][$i]."'
                        , cod_code = '".$_REQUEST['cod_code'][$i]."'
                        , cod_group = '".$_REQUEST['cod_group'][$i]."'
                        , cod_name = '".$_REQUEST['cod_name'][$i]."'
                        , cod_memo = '".$_REQUEST['cod_memo'][$i]."'
                        , cod_status = '".$_REQUEST['cod_status'][$i]."'
        ";

        //com_idx, imp_idx, cod_code, cod_group
        // 상기 4개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_cod = "   SELECT cod_idx FROM {$g5['code_table']} 
                        WHERE com_idx = '".$_REQUEST['com_idx'][$i]."'
                            AND imp_idx = '".$_REQUEST['imp_idx'][$i]."'
                            AND cod_code = '".$_REQUEST['cod_code'][$i]."'
                            AND cod_group = '".$_REQUEST['cod_group'][$i]."'
        ";
        //echo $sql_cod.'<br>';
		$cod = sql_fetch($sql_cod,1);
        
		// 정보 업데이트
		if($cod['cod_idx']) {
			
			$sql = "UPDATE {$g5['code_table']} SET 
						{$sql_common[$i]}
						, cod_update_dt = '".G5_TIME_YMDHIS."'
					WHERE cod_idx = '".$cod['cod_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['code_table']} SET 
						{$sql_common[$i]}
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