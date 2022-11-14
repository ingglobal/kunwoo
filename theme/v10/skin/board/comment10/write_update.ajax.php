<?php
header("Content-Type: text/plain; charset=utf-8");
include_once("./_common.php");
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


//-- 디폴트 상태 (실패)
$response = new stdClass();
$response->result=false;


// serialize된 변수를 분리
$params = array();
parse_str($data_serialized, $params);
foreach($params as $key => $value) {
	${$key} = $value;
}

// 정보 입력
if ($type == "put") {
    
    $wr_content = $wr_content1; // 앞에서 wr_content1로 넘어옴
    
    // 이름이 없으면 등록 불가
    if( !$wr_name ) {
        $response->err_code='no_wr_name';
        $response->msg = "이름을 입력해 주세요.";
    }
    if( !$wr_7 ) {
        $response->err_code='no_hp';
        $response->msg = "휴대폰 번호를 입력해 주세요.";
    }
    if( !$wr_content ) {
        $response->err_code='no_wr_content';
        $response->msg = "상담내용을 입력해 주세요.";
    }

    // 정보 입력
    if(!$response->err_code) {
        
        $write_table = $g5['write_prefix'].$bo_table;
        $wr_num = get_next_num($write_table);
        $wr_subject = $wr_name;
        $sql_common = "
                        wr_num = '".$wr_num."',
                        wr_reply = '',
                        wr_comment = 0,
                        wr_option = 'html2,secret',
                        wr_subject = '".$wr_subject."님의 온라인 상담입니다.',
                        wr_content = '".$wr_content."',
                        wr_link1 = '".$wr_link1."',
                        wr_link2 = '".$wr_link2."',
                        mb_id = '".$member['mb_id']."',
                        wr_password = '".$member['mb_password']."',
                        wr_name = '".addslashes($wr_name)."',
                        wr_email = '".$wr_email."',
                        wr_last = '".G5_TIME_YMDHIS."',
                        wr_ip = '".$_SERVER['REMOTE_ADDR']."',
                        wr_1 = '".$wr_1."',
                        wr_2 = '".$apply_dt."',
                        wr_3 = '".(int)$wr_3."',
                        wr_4 = '".$wr_4."',
                        wr_5 = '".$wr_5."',
                        wr_6 = '".(int)$wr_6."',
                        wr_7 = '".$wr_7."',
                        wr_8 = '".$wr_8."',
                        wr_9 = '".$board['set_default_status']."',
                        wr_10 = '".$wr_10."'
        ";
        $sql = " INSERT INTO {$write_table} SET
                    {$sql_common} 
                    , wr_datetime = '".G5_TIME_YMDHIS."'
        ";
        //echo $sql;
        sql_query($sql,1);
        $wr_id = sql_insert_id();
        // 부모 아이디에 UPDATE
        sql_query(" update $write_table set wr_parent = '$wr_id' where wr_id = '$wr_id' ");
        // 게시글 1 증가
        sql_query("update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}'");


        // 메일발송
        if ($config['cf_email_use'] && $board['bo_use_email']) {

            // 관리자의 정보를 얻고
            $super_admin = get_admin('super');
            $group_admin = get_admin('group');
            $board_admin = get_admin('board');

            $wr_subject = get_text(stripslashes($wr_subject));

            $tmp_html = 0;
            if (strstr($html, 'html1'))
                $tmp_html = 1;
            else if (strstr($html, 'html2'))
                $tmp_html = 2;

            $wr_content = '예약시간: '.$apply_dt.PHP_EOL;
            $wr_content .= '인원: '.(int)$wr_3.PHP_EOL;
            $wr_content .= '구분: '.$g5['set_reservation_type_value'][$wr_1].PHP_EOL;
            $wr_content .= '이름: '.$wr_name.PHP_EOL;
            $wr_content .= '성별: '.$wr_5.PHP_EOL;
            $wr_content .= '나이: '.$wr_6.PHP_EOL;
            $wr_content .= '휴대폰: '.$wr_7.PHP_EOL;
            $wr_content .= '이메일: '.$wr_email.PHP_EOL;
            $wr_content .= '현재예약상태: '.$g5['set_reservation_status_value'][$board['set_default_status']].PHP_EOL;
            $wr_content .= '신청일: '.G5_TIME_YMDHIS.PHP_EOL;
            $wr_content = conv_content(conv_unescape_nl(stripslashes($wr_content)), $tmp_html);

            $subject = '['.$config['cf_title'].'] '.$wr_subject.' 님의 예약입니다.';

            $link_url = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
            $site_url = G5_URL;

            include_once(G5_LIB_PATH.'/mailer.lib.php');

            ob_start();
            include_once ('./write_update_mail.php');
            $content = ob_get_contents();
            ob_end_clean();

            $array_email = array();
            // 게시판관리자에게 보내는 메일
            if ($config['cf_email_wr_board_admin']) $array_email[] = $board_admin['mb_email'];
            // 게시판그룹관리자에게 보내는 메일
            if ($config['cf_email_wr_group_admin']) $array_email[] = $group_admin['mb_email'];
            // 최고관리자에게 보내는 메일
            if ($config['cf_email_wr_super_admin']) $array_email[] = $super_admin['mb_email'];
            // 작성자에게 보내는 메일
            if ($config['cf_email_wr_write']) {
                $array_email[] = $wr_email;
            }

            // 중복된 메일 주소는 제거
            $unique_email = array_unique($array_email);
            $unique_email = array_values($unique_email);
            for ($i=0; $i<count($unique_email); $i++) {
                mailer($wr_name, $wr_email, $unique_email[$i], $subject, $content, 1);
            }
        }
        
        // 문자 메시지 발송
        // 이 부분은 나중에 필요하면 작업합니다.
        
        $response->bo_table = $bo_table;
        $response->board = $board['bo_table'];
        $response->result = true;
        $response->msg = "신청이 접수되었습니다. 감사합니다.";
    }

}
// 상태값 설정
else if ($type == "set") {
    
    $write = get_table_meta($bo_table,'wr_id',$wr_id,'board/'.$bo_table);
    if(is_serialized($write['wr_9']))
        $write = array_merge($write, get_serialized($write['wr_9']));
    //print_r2($write);
    
    // 작업자 정보 추출
    $mb1 = get_member($mb_id_worker);
    
    // keys 변경
    $wr_8_new = $write['wr_8'];
    $wr_8_new = keys_update('mb_id_worker',$mb_id_worker,$wr_8_new);
    $wr_8_new = keys_update('mb_name_worker',$mb_name_worker,$wr_8_new);
    //echo $wr_8_new.'<br>';
    
    // serialize값 변경
    //echo $write['wr_9'];
    $wr_9_new = $write['wr_9'];
    $wr_9_new = serialized_update('mb_id_worker',$mb_id_worker,$wr_9_new);
    $wr_9_new = serialized_update('mb_name_worker',$mb_name_worker,$wr_9_new);
    $wr_9_new = serialized_update('trm_idx_department_worker',$mb1['mb_2'],$wr_9_new);
    
    // wr_5 (작업등급)
    $wr5_sql = (isset($wr_5)) ? ", wr_5 = '".$wr_5."' " : "";
    
    $sql = "UPDATE ".$g5['write_prefix'].$bo_table." SET
                wr_5 = '".$wr_5."'
                , wr_6 = '".$wr_6."'
                , wr_8 = '".$wr_8_new."'
                , wr_9 = '".$wr_9_new."'
                , wr_10 = '".$wr_10."'
            WHERE wr_id = '".$wr_id."'
    ";
    sql_query($sql,1);
    
	$response->result = true;
	$response->msg = "설정을 변경하였습니다.";

}
// 상태값 초기화
else if ($type == "reset") {
    
    // keys 변경
    $wr_8_new = $write['wr_8'];
    $wr_8_new = keys_update('mb_id_worker','',$wr_8_new);
    $wr_8_new = keys_update('mb_name_worker','',$wr_8_new);
    $wr_8_new_array = get_keys($wr_8_new);
    
    // serialize값 변경
    $wr_9_new = $write['wr_9'];
    $wr_9_new = serialized_update('mb_id_worker','',$wr_9_new);
    $wr_9_new = serialized_update('mb_name_worker','',$wr_9_new);
    $wr_9_new = serialized_update('trm_idx_department_worker','',$wr_9_new);
    $wr_9_new_array = get_serialized($wr_9_new);
    
    $sql = "UPDATE ".$g5['write_prefix'].$bo_table." SET
                wr_5 = ''
                , wr_6 = ''
                , wr_8 = '".$wr_8_new."'
                , wr_9 = '".$wr_9_new."'
                , wr_10 = 'pending'
            WHERE wr_id = '".$wr_id."'
    ";
//	sql_query($sql,1);

	$response->result = true;
	$response->msg = "설정을 초기화하였습니다.";

}
// 창닫기 클릭
else if ($type == "close") {
    
    set_session("ss_side_quick_close_dt", G5_SERVER_TIME);

	$response->result = true;
	$response->msg = "퀵메뉴가 닫힌 상태로 고정됩니다.";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}

$response->wr_8 = $wr_8_new_array;
$response->wr_9 = $wr_9_new_array;
$response->sql = $sql;

echo json_encode($response);
exit;
?>