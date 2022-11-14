<?
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

// 기존 정보 추출
$sql = "SELECT * FROM g5_5_ymd WHERE ymd_date = '".$ymd_date."' ";
$ymd = sql_fetch($sql,1);
$unser = unserialize(stripslashes($ymd['ymd_more']));
if( is_array($unser) ) {
    foreach ($unser as $key=>$value) {
        $more[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
    }    
}


// 예약 정보 입력
if ($type == "put") {
    
    // 과거 시점은 예약 불가
    $apply_dt = $ymd_date.date(" H:i:s", strtotime($start_time));
    if( $apply_dt <= G5_TIME_YMDHIS ) {
        $response->err_code='oldtime';
        $response->msg = "예약시간이 과거 시점입니다. 예약 시간을 다시 선택해 주세요.";
    }

    $sql  = "SELECT SUM( IF( wr_2 = '".$apply_dt."' ,wr_3 ,0) ) AS time_sum
                    , SUM(wr_3) AS day_sum
                FROM ".$g5['write_prefix'].$bo_table."
                WHERE wr_9 NOT IN ('trash')
                    AND wr_2 BETWEEN '".$ymd_date." 00:00:00' AND '".$ymd_date." 23:59:59'
    ";
    //echo $sql;
    $reservation = sql_fetch($sql,1);
    $reservation['time_possible'] = $board['set_max_time_apply'] - $reservation['time_sum'];
    $reservation['day_possible'] = $board['set_max_apply'] - $reservation['day_sum'];
    // 예약 시간대 중복 예약 인원 체크 (신청하는 인원까지 포함해서 <= $board['set_max_time_apply']), 현재예약가능 인원은 O명입니다. 알림
    if( $reservation['time_possible'] - (int)$wr_3 < 0 ) {
        $response->err_code='time_max_over';
        $reservation['possible_msg'] = ($reservation['time_possible'] <= 0) ? "같은 시간대에는 예약이 종료되었습니다." : "같은 시간대에 예약하시려면 현재 ".$reservation['time_possible']."명까지 가능합니다.";
        $response->msg = "동일 시간대 예약 인원 초과입니다. \n".$reservation['possible_msg'];
    }

    // 예약 날짜 허용 인원 체크 (신청하는 인원까지 포함해서 <= $board['set_max_apply']), 현재예약가능 인원은 O명입니다. 알림
    if( $reservation['day_possible'] - (int)$wr_3 < 0 ) {
        $response->err_code='day_max_eover';
        $reservation['possible_msg'] = ($reservation['day_possible'] <= 0) ? "당일 예약은 종료되었습니다." : "당일 예약가능 인원은 ".$reservation['day_possible']."명입니다.";
        $response->msg = "당일 예약 인원 초과입니다. \n".$reservation['possible_msg'];
    }
    
    // 예약 정보 입력
    if(!$response->err_code) {
        
        $write_table = $g5['write_prefix'].$bo_table;
        $wr_num = get_next_num($write_table);
        $wr_subject = $wr_name;
        $sql_common = "
                        wr_num = '".$wr_num."',
                        wr_reply = '',
                        wr_comment = 0,
                        wr_option = 'html2',
                        wr_subject = '".$wr_subject."',
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
        
        $response->result = true;
        $response->msg = "예약이 접수되었습니다. 감사합니다.";
    }

}
// 시간정보 업데이트
else if ($type == "time") {
    
    $more['start_time'] = ($start_time) ? date("H:i:s", strtotime($start_time)) : '' ;
    $more['end_time'] = ($end_time) ? date("H:i:s", strtotime($end_time)) : '' ;
    $more['break_start_time'] = ($break_start_time) ? date("H:i:s", strtotime($break_start_time)) : '' ;
    $more['break_end_time'] = ($break_end_time) ? date("H:i:s", strtotime($break_end_time)) : '' ;
    $more['day_apply_yn'] = $day_apply_yn;
    if($setting_reset) {
        unset($more['start_time']);
        unset($more['end_time']);
        unset($more['break_start_time']);
        unset($more['break_end_time']);
        unset($more['day_apply_yn']);
    }
    $ymd_more = addslashes(serialize($more));

    $sql = " UPDATE g5_5_ymd SET ymd_more = '".$ymd_more."' WHERE ymd_date = '".$ymd_date."' ";
    sql_query($sql,1);

	$response->result = true;
	$response->msg = "데이타를 성공적으로 업데이트했습니다.";

}
// 공휴일 정보 업데이트
else if ($type == "holiday") {
    
    $more['holiday_name'] = $holiday_name;
    $more['holiday_description'] = $holiday_description;
    if($setting_reset) {
        unset($more['holiday_name']);
        unset($more['holiday_description']);
    }
    $ymd_more = addslashes(serialize($more));

    $sql = " UPDATE g5_5_ymd SET ymd_more = '".$ymd_more."' WHERE ymd_date = '".$ymd_date."' ";
    sql_query($sql,1);

	$response->result = true;
	$response->msg = "데이타를 성공적으로 업데이트했습니다.";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->ymd_date = $ymd_date;
$response->sql = $sql;

echo json_encode($response);
exit;
?>