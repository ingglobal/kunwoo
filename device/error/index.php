<?php
// 크롬 요소검사 열고 확인하면 되겠습니다. 
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

//print_r2($_REQUEST);exit;
//echo $_REQUEST['shf_type'][0];
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if(is_array($getData[0]['list'])) {

    for($i=0;$i<sizeof($getData[0]['list']);$i++) {
        $arr = $getData[0]['list'][$i];

        $arr['dta_status'] = '0';
        $arr['dta_dt'] = strtotime(preg_replace('/\./','-',$arr['dta_date'])." ".$arr['dta_time']);
        $arr['dta_date1'] = date("Y-m-d",$arr['dta_dt']);   // 2 or 4 digit format(20 or 2020) no problem.

        // company info
        $com = get_table_meta('company','com_idx',$arr['com_idx']);

        // 에러 분류 추출 (data/cache/mms-code.php, 설정은 user.07.default.php)
        $arr['trm_idx_category'] = $g5['code'][$arr['mms_idx']][$arr['dta_code']]['trm_idx_category'];

        // 공통요소
        $sql_common[$i] = " com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , cod_idx = '".$arr['cod_idx']."'
                        , trm_idx_category = '".$arr['trm_idx_category']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_shf_max = '".$arr['dta_shf_max']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_code = '".$arr['dta_code']."'
                        , dta_message = '".$arr['dta_message']."'
                        , dta_dt = '".$arr['dta_dt']."'
                        , dta_status = '".$arr['dta_status']."'
        ";
        
        //com_idx, imp_idx, mms_idx, dta_code, dta_group, dta_type, dta_no, dta_date, dta_time
        // 상기 8개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_dta = "   SELECT dta_idx FROM {$g5['data_error_table']} 
                        WHERE com_idx = '".$arr['com_idx']."'
                            AND imp_idx = '".$arr['imp_idx']."'
                            AND mms_idx = '".$arr['mms_idx']."'
                            AND trm_idx_category = '".$arr['trm_idx_category']."'
                            AND dta_group = '".$arr['dta_group']."'
                            AND dta_code = '".$arr['dta_code']."'
                            AND dta_dt = '".$arr['dta_dt']."'
        ";
        //echo $sql_dta.'<br>';
		$dta = sql_fetch($sql_dta,1);
		// 정보 업데이트
		if($dta['dta_idx']) {
			
			$sql = "UPDATE {$g5['data_error_table']} SET 
						{$sql_common[$i]}
						, dta_update_dt = '".G5_SERVER_TIME."'
					WHERE dta_idx = '".$dta['dta_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['data_error_table']} SET 
						{$sql_common[$i]}
						, dta_reg_dt = '".G5_SERVER_TIME."'
						, dta_update_dt = '".G5_SERVER_TIME."'
            ";
            // echo $sql.'<br>';
            $result = sql_query($sql, 1);
            $dta['dta_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Insert OK!";


            // 코드 정보 기록, 새로운 정보가 들어오면 코드에 입력 or 코드쪽 누적 카운터 증가
            $sql = "SELECT *
                    FROM {$g5['code_table']}
                    WHERE mms_idx = '".$arr['mms_idx']."'
                        AND cod_code = '".$arr['dta_code']."'
            ";
            $cod = sql_fetch($sql,1);
            // if exists, update
            if($cod['cod_idx']) {

                // 값이 있을 때만 입력
                $sql_cod_name = ($arr['dta_message']) ? ", cod_name = '".$arr['dta_message']."'" : "";

                $sql = "UPDATE {$g5['code_table']} SET
                            cod_group = '".$arr['dta_group']."'
                            {$sql_cod_name}
                            , cod_code_count = cod_code_count + 1
                        WHERE cod_idx = '".$cod['cod_idx']."'
                ";
                $result = sql_query($sql,1);

            }
            // if not, insert db record.
            // You have to get the nessesary fields (cod_type, cod_send_type(plular), cod_idx..)
            else {

                // Set cod_type variable. (if dta_group = pre, cod_type=p2(plc predict))
                $cod['cod_type'] = ($arr['dta_group']=='pre') ? 'p2':'a';
                // 맨 처음 PLC예지 들어오면 발송대상자가 불분명하므로 맨 처음 등록자 한사람을 자동 할당함
                if($cod['cod_type']=='p2') {
                    $sql = "SELECT cmm.mb_id, mb_name, mb_hp, mb_email
                            FROM {$g5['company_member_table']} AS cmm
                                LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cmm.mb_id
                            WHERE com_idx = '".$arr['com_idx']."'
                                AND cmm_status = 'ok'
                            ORDER BY cmm_reg_dt ASC
                            LIMIT 1
                    ";
                    $cmm = sql_fetch($sql);
                    if($cmm['mb_id']) {
                        $reports['r_name'][] = $cmm['mb_name'];
                        $reports['r_hp'][] = $cmm['mb_hp'];
                        $reports['r_email'][] = $cmm['mb_email'];
                        $cod['cod_reports'] = json_encode( $reports, JSON_UNESCAPED_UNICODE );
                    }
                }
 
                // 발송타입 설정: email,sms,push... from company info.
                $cod['cod_send_type'] = $com['com_send_type'];

                $sql = " INSERT INTO {$g5['code_table']} SET
                            com_idx = '".$arr['com_idx']."'
                            , imp_idx = '".$arr['imp_idx']."'
                            , mms_idx = '".$arr['mms_idx']."'
                            , trm_idx_category = '".$arr['trm_idx_category']."'
                            , cod_code = '".$arr['dta_code']."'
                            , cod_group = '".$arr['dta_group']."'
                            , cod_type = '".$cod['cod_type']."'
                            , cod_count_limit = 5
                            , cod_name = '".$arr['dta_message']."'
                            , cod_send_type = '".$cod['cod_send_type']."'
                            , cod_code_count = 1
                            , cod_status = 'ok'
                            , cod_reg_dt = '".G5_TIME_YMDHIS."'
                            , cod_update_dt = '".G5_TIME_YMDHIS."'
                ";
                $result = sql_query($sql);
                $cod['cod_idx'] = sql_insert_id();
            }



            // Alarm/Predit register (if conditions is right.)
            $send_flag = 0;             // initialize
            $towhom_hp = array();       // initialize
            $towhom_email = array();    // initialize
            if($cod['cod_type']) {

                // alarm table insert, update later for arm_no
                $arm_keys = keys_update('mms_idx',$arr['mms_idx'],'','~');  // 최초값
                $arm_keys = keys_update('cod_code',$arr['dta_code'],$arm_keys,'~'); // 업데이트
                $arm_keys = keys_update('cod_interval',$cod['cod_interval'],$arm_keys,'~');
                $arm_keys = keys_update('cod_count',$cod['cod_count'],$arm_keys,'~');
                $sql = " INSERT INTO {$g5['alarm_table']} SET
                            com_idx = '".$arr['com_idx']."'
                            , mms_idx = '".$arr['mms_idx']."'
                            , cod_idx = '".$cod['cod_idx']."'
                            , dta_idx = '".$dta['dta_idx']."'
                            , arm_shf_no = '".$arr['dta_shf_no']."'
                            , arm_cod_code = '".$arr['dta_code']."'
                            , arm_send_type = '".$cod['cod_send_type']."'
                            , arm_keys = '".$arm_keys."'
                            , arm_reg_dt = '".G5_TIME_YMDHIS."'
                ";
                sql_query($sql,1);
                $arm_idx = sql_insert_id();

                // if p2 (PLC predict), store and send msg directly.
                if($cod['cod_type']=='p2') {
                    $send_flag = 1;
                }
                // if p, store error timely and if it is due time, store and send msg.
                else if($cod['cod_type']=='p') {
                    $cod['cod_time_interval'] = G5_SERVER_TIME - $cod['cod_interval'];
                    // Figure out how many times happened so far.
                    // 앞에서 에러 입력을 먼저 하고 왔으므로 구지 + 1을 안 해도 바로 입력된 레코드 카운트합니다.
                    $sql = "SELECT COUNT(dta_idx) AS dta_count_recent
                            FROM {$g5['data_error_table']}
                            WHERE mms_idx = '".$arr['mms_idx']."'
                                AND dta_code = '".$arr['dta_code']."'
                                AND dta_status = 0
                                AND dta_dt > ".$cod['cod_time_interval']."
                    ";
                    // echo $sql.'<br><br>';
                    $recent = sql_fetch($sql,1);
                    // $arm_no = $recent['dta_count_recent']+1; // db count + 1
                    $arm_no = $recent['dta_count_recent']; // db count + 1 할 필요까지는 없음
                    // Set the send flag variable. if
                    // echo $arm_no.'=?='.$cod['cod_count'].'<br>';
                    if($cod['cod_count'] && $arm_no >= $cod['cod_count']) {
                        $send_flag = 1;
                    }
                    // 예지 조건 이하로 발생하면 알람(a)으로 타입을 바꿔서 입력해야 함
                    else {
                        $cod['cod_type'] = 'a';
                    }
                }

                // Send message(email. sms, push) under the condition of max time daily.
                if($send_flag) {
                    // mms info
                    $mms = get_table_meta('mms','mms_idx',$arr['mms_idx']);
                    // 발신자번호
                    $send_number = preg_replace("/[^0-9]/", "", $sms5['cf_phone']);
 
                    // 문자 내용
                    $sms_contents = '설비명:'.$mms['mms_name'].PHP_EOL;
                    $sms_contents .= '['.$cod['cod_code'].']'.PHP_EOL;
                    $sms_contents .= ($cod['cod_name']) ? '알람:'.$cod['cod_name'].PHP_EOL : '';
                    $sms_contents .= $cod['cod_memo'];

                    // towhom_info variable
                    $reports = json_decode($cod['cod_reports'], true);
                    if(is_array($reports)) {
                        foreach($reports as $k1 => $v1) {
                            // echo $k1.'<br>';
                            // print_r2($v1);
                            for($j=0;$j<sizeof($v1);$j++) {
                                // cell phone array
                                if($k1=='r_name') {
                                    $towhom_name[] = trim($v1[$j]);
                                }
                                // cell phone array, remove '-' mark from hp numbers.
                                if($k1=='r_hp') {
                                    $towhom_hp[] = preg_replace("/[^0-9]/","",trim($v1[$j]));
                                }
                                // set email array
                                else if($k1=='r_email') {
                                    $towhom_email[] = trim($v1[$j]);
                                }
                            }
                        }
                    }
                    // print_r2($towhom_hp);
                    // print_r2($towhom_email);

                    // figure out how namy times sent in recent 24 hours (today max limit.)
                    // I move this inside the sending part.
                    // $sql = "SELECT COUNT(ars_idx) AS ars_count_recent
                    //         FROM {$g5['alarm_send_table']}
                    //         WHERE mms_idx = '".$arr['mms_idx']."'
                    //             AND ars_cod_code = '".$arr['dta_code']."'
                    //             AND ars_status = 'ok'
                    //             AND ( REPLACE(ars_hp,'-','') IN ('".implode("','",$towhom_hp)."') OR ars_email IN ('".implode("','",$towhom_email)."') )
                    //             AND ars_reg_dt > date_add(now(), interval -1 day)
                    // ";
                    // echo $sql.'<br>';
                    // $ars = sql_fetch($sql,1);
                    // if it is under limit, send messages.
                    // if( $cod['cod_count_limit'] && $ars['ars_count_recent'] < $cod['cod_count_limit'] ) {
                    if( $cod['cod_count_limit'] ) {

                        // email send, it should also check if company condition.
                        if( preg_match("/email/",$cod['cod_send_type']) && preg_match("/email/",$com['com_send_type']) ) {

                            for($j=0;$j<sizeof($towhom_email);$j++) {

                                // figure out how namy times sent in recent 24 hours (today max limit.)
                                $sql = "SELECT COUNT(ars_idx) AS ars_count_recent
                                        FROM {$g5['alarm_send_table']}
                                        WHERE mms_idx = '".$arr['mms_idx']."'
                                            AND ars_cod_code = '".$arr['dta_code']."'
                                            AND ars_status = 'ok'
                                            AND ars_email = '".$towhom_email[$j]."'
                                            AND ars_reg_dt > date_add(now(), interval -1 day)
                                ";
                                // echo $sql.'<br>';
                                $ars = sql_fetch($sql,1);

                                $sw = preg_match("/[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*@[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*/", $towhom_email[$j]);
                                // 올바른 메일 주소 & if is is under today limit
                                if ($sw == true && $ars['ars_count_recent'] < $cod['cod_count_limit']) {
                                    // echo $towhom_email[$j].'<br>';
                                    $patterns = array ( '/{이름}/','/{제목}/'
                                                        ,'/{설비명}/','/{구분}/'
                                                        ,'/{코드}/','/{내용}/'
                                                        ,'/{년월일}/','/{HOME_URL}/'
                                                    );
                                                    // print_r2($patterns);
                                    $replace = array (  $towhom_name[$j], $arr['dta_message']
                                                        ,$mms['mms_name'], $g5['set_cod_group_value'][$arr['dta_group']]
                                                        ,$arr['dta_code'], conv_content($cod['cod_memo'],2)
                                                        ,G5_TIME_YMD, G5_URL
                                                    );
                                                    // print_r2($replace);
                
                                    $towhom['subject'] = preg_replace($patterns,$replace
                                                                    ,$g5['setting']['set_error_subject']);
                                    $towhom['content'] = preg_replace($patterns,$replace
                                                                    ,$g5['setting']['set_error_content']);
                                    // echo $towhom['subject'].'<br>';
                                    // echo $towhom['content'].'<br>';
                
                                    // 메일발송
                                    mailer($config['cf_admin_email_name'].'(발신전용)', $config['cf_admin_email'], $towhom_email[$j], $towhom['subject'], $towhom['content'], 1);

                                    // 발송기록 저장
                                    $sql = " INSERT INTO {$g5['alarm_send_table']} SET
                                                arm_idx = '".$arm_idx."'
                                                , mms_idx = '".$arr['mms_idx']."'
                                                , ars_cod_code = '".$arr['dta_code']."'
                                                , ars_send_type = 'email'
                                                , ars_email = '".$towhom_email[$j]."'
                                                , ars_status = 'ok'
                                                , ars_reg_dt = '".G5_TIME_YMDHIS."'
                                    ";
                                    $result = sql_query($sql,1);

                                }
                
                            }

                        }
                        // sms send, only if company sms setting is possible.
                        if( preg_match("/sms/",$cod['cod_send_type']) && preg_match("/sms/",$com['com_send_type']) ) {

                            // 문자 발송
                            if ($config['cf_sms_use'] == 'icode' && count($towhom_hp) > 0)
                            {
                                if($config['cf_sms_type'] == 'LMS') {
                                    include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                                    $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

                                    // SMS 모듈 클래스 생성
                                    if($port_setting !== false) {
                                        $SMS = new LMS;
                                        $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                                        // $strDest     = array();
                                        for($j=0;$j<sizeof($towhom_hp);$j++) {

                                            // figure out how namy times sent in recent 24 hours (today max limit.)
                                            $sql = "SELECT COUNT(ars_idx) AS ars_count_recent
                                                    FROM {$g5['alarm_send_table']}
                                                    WHERE mms_idx = '".$arr['mms_idx']."'
                                                        AND ars_cod_code = '".$arr['dta_code']."'
                                                        AND ars_status = 'ok'
                                                        AND REPLACE(ars_hp,'-','') = '".$towhom_hp[$j]."'
                                                        AND ars_reg_dt > date_add(now(), interval -1 day)
                                            ";
                                            // echo $sql.'<br>';
                                            $ars = sql_fetch($sql,1);
                                            // only if it is under limit
                                            if( $ars['ars_count_recent'] < $cod['cod_count_limit'] ) {
                                                $strDest[]   = preg_replace("/[^0-9]/", "", $towhom_hp[$j]);

                                                // 발송기록 저장, 일단 발송했다고 봄
                                                $sql = " INSERT INTO {$g5['alarm_send_table']} SET
                                                            arm_idx = '".$arm_idx."'
                                                            , mms_idx = '".$arr['mms_idx']."'
                                                            , ars_cod_code = '".$arr['dta_code']."'
                                                            , ars_send_type = 'sms'
                                                            , ars_hp = '".$towhom_hp[$j]."'
                                                            , ars_status = 'ok'
                                                            , ars_reg_dt = '".G5_TIME_YMDHIS."'
                                                ";
                                                sql_query($sql,1);
                                            }

                                        }
                                        // $strDest[]   = $receive_number;
                                        $strCallBack = $send_number;
                                        $strCaller   = iconv_euckr(trim($config['cf_title']));
                                        $strSubject  = '';
                                        $strURL      = '';
                                        $strData     = iconv_euckr($sms_contents);
                                        $strDate     = '';
                                        $nCount      = count($strDest);

                                        $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                                        $SMS->Send();
                                        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                                    }
                                }
                                else {
                                    include_once(G5_LIB_PATH.'/icode.sms.lib.php');

                                    $SMS = new SMS; // SMS 연결
                                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
                                    // $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
                                    for($j=0;$j<sizeof($towhom_hp);$j++) {

                                        // figure out how namy times sent in recent 24 hours (today max limit.)
                                        $sql = "SELECT COUNT(ars_idx) AS ars_count_recent
                                                FROM {$g5['alarm_send_table']}
                                                WHERE mms_idx = '".$arr['mms_idx']."'
                                                    AND ars_cod_code = '".$arr['dta_code']."'
                                                    AND ars_status = 'ok'
                                                    AND REPLACE(ars_hp,'-','') = '".$towhom_hp[$j]."'
                                                    AND ars_reg_dt > date_add(now(), interval -1 day)
                                        ";
                                        // echo $sql.'<br>';
                                        $ars = sql_fetch($sql,1);
                                        // only if it is under limit
                                        if( $ars['ars_count_recent'] < $cod['cod_count_limit'] ) {

                                            $SMS->Add(preg_replace("/[^0-9]/", "", $towhom_hp[$j]), $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");

                                            // 발송기록 저장, 일단 발송했다고 봄
                                            $sql = " INSERT INTO {$g5['alarm_send_table']} SET
                                                        arm_idx = '".$arm_idx."'
                                                        , mms_idx = '".$arr['mms_idx']."'
                                                        , ars_cod_code = '".$arr['dat_code']."'
                                                        , ars_send_type = 'sms'
                                                        , ars_hp = '".$towhom_hp[$j]."'
                                                        , ars_status = 'ok'
                                                        , ars_reg_dt = '".G5_TIME_YMDHIS."'
                                            ";
                                            sql_query($sql,1);
                                        }

                                    }
                                    $SMS->Send();
                                    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                                }

                            }

                        }
                        // push send
                        if( preg_match("/push/",$cod['cod_send_type']) ) {

                        }

                    }

                }
                // end of sending email, sms, push..


                // update for arm_no
                $sql = " UPDATE {$g5['alarm_table']} SET
                            arm_cod_type = '".$cod['cod_type']."'
                            , arm_no = '".$arm_no."'
                        WHERE arm_idx = '".$arm_idx."'
                ";
                // echo $sql.'<br>---------<br>';
                sql_query($sql,1);

            }

        }
        $result_arr[$i]['dta_idx'] = $dta['dta_idx'];   // 고유번호 (최종 json 표현하는 값)





        // 일간 sum 합계 입력
        $sum_common = " mms_idx = '".$arr['mms_idx']."'
                        AND trm_idx_category = '".$arr['trm_idx_category']."'
                        AND dta_shf_no = '".$arr['dta_shf_no']."'
                        AND dta_group = '".$arr['dta_group']."'
                        AND dta_code = '".$arr['dta_code']."'
        ";
        $sql = "SELECT COUNT(dta_idx) AS dta_count_sum
                FROM {$g5['data_error_table']} 
                WHERE dta_status = 0
                    AND {$sum_common}
                    AND FROM_UNIXTIME(dta_dt,'%Y-%m-%d') = '".$arr['dta_date1']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='sum_calculate',  mta_value = '".addslashes($sql)."' ");
        $sum1 = sql_fetch($sql,1); // 일 합계 데이터값 추출 

        // 있으면 업데이트, 없으면 생성
        $sql_sum = "   SELECT dta_idx FROM {$g5['data_error_sum_table']} 
                        WHERE {$sum_common}
                            AND dta_date = '".$arr['dta_date1']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='sum_check', mta_value = '".addslashes($sql_sum)."' ");
		$sum = sql_fetch($sql_sum,1);
		// 정보 업데이트
		if($sum['dta_idx']) {
            $sql = "UPDATE {$g5['data_error_sum_table']} SET
                        dta_value = '".$sum1['dta_count_sum']."'
                    WHERE {$sum_common}
                        AND dta_date = '".$arr['dta_date1']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='update', mta_value = '".addslashes($sql)."' ");
            $result = sql_query($sql,1);
        }
        else {
            $sql = " INSERT INTO {$g5['data_error_sum_table']} SET
                        com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , mmg_idx = '".$g5['mms'][$arr['mms_idx']]['mmg_idx']."'
                        , trm_idx_category = '".$arr['trm_idx_category']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_code = '".$arr['dta_code']."'
                        , dta_date = '".$arr['dta_date1']."'
                        , dta_value = '".$sum1['dta_count_sum']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
            $result = sql_query($sql);
        }

    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>