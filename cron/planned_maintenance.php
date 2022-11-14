#!/opt/php/bin/php -q
<?php
include_once('/home/icmms/www/common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');


// get the current time
//echo G5_TIME_YMD.PHP_EOL;
$time1 = date("H",G5_SERVER_TIME); // current time.
//echo $time1.PHP_EOL;
//exit;

// get the list of cnadidates of planned maintenance.
$tmp_write_table = $g5['write_prefix'].'plan';
$sql = " SELECT * FROM {$tmp_write_table} WHERE wr_is_comment = 0 ORDER BY wr_num ";
$result = sql_query($sql,1);
$arr = array();
for ($i=0; $row = sql_fetch_array($result); $i++) {
    $row['seried'] = get_serialized($row['wr_9']);
    if( is_array($row['seried']) ) {
        foreach($row['seried'] as $k1=>$v1) {
//            echo $k1.' | '.$v1.'<br>'.PHP_EOL;
            $row[$k1] = $v1;
        }
    }
//    var_dump($row);
//    echo 'date:'.$row['wr_3'].' / from '.$row['wr_4'].' days early / days:'.$row['wr_5'].' / hour:'.$row['wr_6'].'<br>'.PHP_EOL;
    $row['day_start'] = new DateTime(G5_TIME_YMD);
    $row['day_end'] = new DateTime($row['wr_3']);
    $row['diff'] = date_diff($row['day_start'], $row['day_end']);
//    var_dump($row['diff']).'<br>'.PHP_EOL;
    $row['diff_days'] = ($row['diff']->format("%R%a")<0) ? $row['diff']->format("%R%a") : substr($row['diff']->format("%R%a"),1);
//    echo $row['diff_days'].'<br>'.PHP_EOL;
    // due date calculation. first=첫날만,every=매일, e2=2일마다, e3=3일마다, e7=7일마다, last=마지막날만
    if($row['wr_4'] >= $row['diff_days'] && $row['diff_days'] > -1) {
        if($row['wr_4'] == $row['diff_days']) {
//            echo 'First day.<br>'.PHP_EOL;
            $row['due_date'] = 1;
        }
        else if($row['diff_days'] == 0) {
//            echo 'Last day.<br>'.PHP_EOL;
            $row['due_date'] = 1;
        }
        else {
//            echo $row['wr_5'].'<br>'.PHP_EOL;
            // if number is included, you have to calcuate periodically.
            if( preg_match( "/[0-9]$/i", $row['wr_5'] ) ) {
                $row['due_day'] = ($row['diff_days']%substr($row['wr_5'],1));
                if($row['due_day']==0) {
//                    echo $row['diff_days'].'--- due.<br>'.PHP_EOL;
                    $row['due_date'] = 1;
                }
            }
            // everyday
            else {
//                echo 'Everyday. <br>'.PHP_EOL;
                $row['due_date'] = 1;
            }
        }
        
    }
    $row['due_time'] = ( $time1 == $row['wr_6'] ) ? 1 : 0;
//    echo $row['due_date'] .'/'. $row['due_time'].PHP_EOL;
//    echo '----------------------------------------------------------<br>'.PHP_EOL;
    
    // if due_date && due_time are all set, make array for sending message.
    if($row['due_date'] && $row['due_time']) {
        $arr[] = $row;
    }
    
}
//var_dump($arr);
//exit;


for($i=0;$i<sizeof($arr);$i++) {


    $com = get_table('company','com_idx',$arr[$i]['wr_1']);
    $mms = get_table('mms','mms_idx',$arr[$i]['wr_2']);
//    var_dump($com);
//    var_dump($mms);
//    var_dump($arr[$i]);
//    continue;
//    exit;

    // 남은 기간 추출
    $arr[$i]['maintain_spare_time'] = $arr[$i]['diff_days'].'일';

    // towhom_info variable
    $wr_7s = json_decode($arr[$i]['wr_7'], true);
    if(is_array($wr_7s)) {
        foreach($wr_7s as $k1 => $v1) {
//            echo $k1.'<br>'.PHP_EOL;
            // print_r2($v1);
            for($j=0;$j<sizeof($v1);$j++) {
                $towhom_li[$j][$k1] = $v1[$j];
                // 폰번호반 따로 배열
                if($k1=='r_hp') {
                    $towhom_hp[] = $v1[$j];
                }
            }
        }
    }
    $towhom_hp = array_filter($towhom_hp);  // 빈배열 제거
    // print_r2($towhom_li);
    // print_r2($towhom_hp);
    // echo count($towhom_hp);
    // exit;

    // $receive_number = preg_replace("/[^0-9]/", "", $towhom_li[1]['r_hp']);  // 수신자번호
    $send_number = preg_replace("/[^0-9]/", "", $sms5['cf_phone']); // 발신자번호
    // 문자 내용
    $sms_contents = '제목:'.$arr[$i]['wr_subject'].PHP_EOL
        .'설비명:'.$mms['mms_name'].PHP_EOL
        .$arr[$i]['wr_content'];

    
    // sms send, only if company sms setting is possible.
    if( preg_match("/sms/",$arr[$i]['wr_send_type']) && preg_match("/sms/",$com['com_send_type']) ) {
    
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
                    for($j=0;$j<sizeof($towhom_li);$j++) {
                        $strDest[]   = preg_replace("/[^0-9]/", "", $towhom_li[$j]['r_hp']);
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
                for($j=0;$j<sizeof($towhom_li);$j++) {
                    $SMS->Add(preg_replace("/[^0-9]/", "", $towhom_li[$j]['r_hp']), $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
                }
                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        }
    }

    
    // email send, it should also check if company condition.
//    echo $arr[$i]['wr_send_type'].PHP_EOL;
    if( preg_match("/email/",$arr[$i]['wr_send_type']) && preg_match("/email/",$com['com_send_type']) ) {
    
        // 메일발송
        for($j=0;$j<sizeof($towhom_li);$j++) {

            $sw = preg_match("/[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*@[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*/", $towhom_li[$j]['r_email']);
            // 올바른 메일 주소만
            if ($sw == true)
            {
                // echo $towhom_li[$j]['r_email'].'<br>';

                $patterns = array ( '/{이름}/','/{제목}/'
                                    ,'/{설비명}/','/{만료일}/'
                                    ,'/{남은기간}/','/{내용}/'
                                    ,'/{년월일}/','/{HOME_URL}/'
                                );
                                // print_r2($patterns);
                $replace = array (  $towhom_li[$j]['r_name'], $arr[$i]['wr_subject']
                                    ,$mms['mms_name'], $arr[$i]['wr_3']
                                    ,$arr[$i]['maintain_spare_time'], conv_content($arr[$i]['wr_content'],2)
                                    ,G5_TIME_YMD, "http://bogwang.epcs.co.kr"
                                );
                                // print_r2($replace);

                $towhom['subject'] = preg_replace($patterns,$replace
                                                ,$g5['setting']['set_maintain_plan_subject']);
                $towhom['content'] = preg_replace($patterns,$replace
                                                ,$g5['setting']['set_maintain_plan_content']);
                // echo $towhom['subject'].'<br>';
                // echo $towhom['content'].'<br>';

                // 메일발송
                mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $towhom_li[$j]['r_email'], $towhom['subject'], $towhom['content'], 1);

            }

        }
    }
    
}

exit;
?>