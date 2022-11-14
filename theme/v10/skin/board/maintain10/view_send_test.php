<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

// print_r2($write);

// 남은 기간 추출
$sql = " SELECT DATEDIFF( '".$write['wr_3']."', '".G5_TIME_YMD."') AS maintain_spare_time ";
$rs1 = sql_fetch($sql,1);
// echo $sql.'<br>';
// print_r2($rs1);
$rs1['maintain_spare_text'] = ($rs1['maintain_spare_time']<0) ? '일후' : '일전'; 
$write['maintain_spare_time'] = abs($rs1['maintain_spare_time']).$rs1['maintain_spare_text'];

$mms = get_table('mms','mms_idx',$write['wr_2']);
// print_r2($mms);
// print_r2($write);
// exit;


// towhom_info variable
$wr_7s = json_decode($write['wr_7'], true);
if(is_array($wr_7s)) {
    foreach($wr_7s as $k1 => $v1) {
        // echo $k1.'<br>';
        // print_r2($v1);
        for($i=0;$i<sizeof($v1);$i++) {
            $towhom_li[$i][$k1] = $v1[$i];
            // 폰번호반 따로 배열
            if($k1=='r_hp') {
                $towhom_hp[] = $v1[$i];
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
// $sms_contents = '문자발송 내용입니다.(SMS) 80포트로 통신을 하나?';
// $sms_contents = '문자발송 내용입니다.(LMS) 80포트로 통신을 하는가 싶기도 하고 그러네요. 어떤 포트로 하는가 모르겠네! 그렇지 않다면 보낼 필요가 없을 터이야';
$sms_contents = $write['wr_content'];


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
            for($i=0;$i<sizeof($towhom_li);$i++) {
                $strDest[]   = preg_replace("/[^0-9]/", "", $towhom_li[$i]['r_hp']);
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
        for($i=0;$i<sizeof($towhom_li);$i++) {
            $SMS->Add(preg_replace("/[^0-9]/", "", $towhom_li[$i]['r_hp']), $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
        }
        $SMS->Send();
        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
    }
}

// 메일발송
for($i=0;$i<sizeof($towhom_li);$i++) {

    $sw = preg_match("/[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*@[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*/", $towhom_li[$i]['r_email']);
    // 올바른 메일 주소만
    if ($sw == true)
    {
        // echo $towhom_li[$i]['r_email'].'<br>';

        $patterns = array ( '/{이름}/','/{제목}/'
                            ,'/{설비명}/','/{만료일}/'
                            ,'/{남은기간}/','/{내용}/'
                            ,'/{년월일}/','/{HOME_URL}/'
                        );
                        // print_r2($patterns);
        $replace = array (  $towhom_li[$i]['r_name'], $write['wr_subject']
                            ,$mms['mms_name'], $write['wr_3']
                            ,$write['maintain_spare_time'], conv_content($write['wr_content'],2)
                            ,G5_TIME_YMD, G5_URL
                        );
                        // print_r2($replace);

        $towhom['subject'] = preg_replace($patterns,$replace
                                        ,$g5['setting']['set_maintain_plan_subject']);
        $towhom['content'] = preg_replace($patterns,$replace
                                        ,$g5['setting']['set_maintain_plan_content']);
        // echo $towhom['subject'].'<br>';
        // echo $towhom['content'].'<br>';

        // 메일발송
        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $towhom_li[$i]['r_email'], $towhom['subject'], $towhom['content'], 1);

    }

}


// exit;
alert('메시지를 발송하였습니다.');
?>