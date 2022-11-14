<?php
$sub_menu = "919100";
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_view/","",$g5['file_name']); // _form을 제외한 파일명
// $qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
if (!${$pre}[$pre.'_idx'])
    alert('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
$mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);
// print_r3($cod);


// towhom_info variable
$reports = json_decode($cod['cod_reports'], true);
if(is_array($reports)) {
    foreach($reports as $k1 => $v1) {
        // echo $k1.'<br>';
        // print_r2($v1);
        for($i=0;$i<sizeof($v1);$i++) {
            // set array, ex) $towhom_li[$i]['r_hp'], $towhom_li[$i]['r_email']
            $towhom_li[$i][$k1] = $v1[$i];
            // 폰번호반 따로 배열
            if($k1=='r_hp') {
                $towhom_hp[] = $v1[$i];
            }
        }
    }
}
// print_r2($towhom_li);
$send_number = preg_replace("/[^0-9]/", "", $sms5['cf_phone']); // 발신자번호
// 문자 내용
$sms_contents = '설비명:'.$mms['mms_name'].PHP_EOL;
$sms_contents .= '['.$cod['cod_code'].']'.PHP_EOL;
$sms_contents .= ($cod['cod_name']) ? '알람:'.$cod['cod_name'].PHP_EOL : '';
$sms_contents .= $cod['cod_memo'];


$g5['title'] = '알람/예지 테스트';
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];
?>
<style>
    .testing {padding:10px 0;color:red;font-size:1.5em;}
</style>

<div class="cod_title">
    <?=$mms['mms_name']?> <?=$cod['cod_name']?>
    <span class="cod_sub_title"><?=$cod['cod_reg_dt']?></span>
</div>
<div class="cod_setting">
    <div class="cod_code" style="display:<?=(!$cod['cod_code'])?'none':''?>"><b>코드</b><?=$cod['cod_code']?></div>
    <div class="cod_group"><b>코드그룹</b><?=$g5['set_cod_group_value'][$cod['cod_group']]?></div>
    <div class="cod_type"><b>코드타입</b><?=$g5['set_cod_type'][$cod['cod_type']]?></div>
        <?php
        if($cod['cod_type']=='r')
            $cod['cod_text'] = '기록만 수행 (알림 없음)';
        else if($cod['cod_type']=='a')
            $cod['cod_text'] = '발생 시 알림';
        else if($cod['cod_type']=='p')
            $cod['cod_text'] = $g5['set_cod_interval_value'][$cod['cod_interval']].' '.$cod['cod_count'].'회 발생 시 알림';
        else if($cod['cod_type']=='p2')
            $cod['cod_text'] = '발생 시 즉시 알림';
        ?>
    <div class="cod_text"><b>설정</b><?=$cod['cod_text']?></div>
</div>
<div class="cod_content">
    <?=conv_content($cod['cod_memo'],2)?>
    <div class="testing">테스트 실행중입니다. 잠시 기다리세요.</div>
    <span id="cont"></span>
</div>
<div class="cod_buttons">
    <button class="btn btn_02" onClick="javascript:history.back()"><i class="fa fa-list"></i> 뒤로</button>
</div>

<script>
$(function() {

});
</script>

<?php
include_once ('./_tail.php');
sleep(1);
?>


<?php
// 설정
if($cod['cod_type']=='a') {
    $cod['cod_count'] = $cod['cod_count'] ?: 3;
    $code_array = array(
        $cod['cod_code']
        ,$cod['cod_code']
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
        ,'M'.rand(1111,9999)
    );
}
else if($cod['cod_type']=='p') {
    $cod['cod_count'] = $cod['cod_count'] ?: 1;
    $code_array = array(
            $cod['cod_code']
            ,$cod['cod_code']
            ,$cod['cod_code']
            ,$cod['cod_code']
            ,$cod['cod_code']
            ,$cod['cod_code']
            ,$cod['cod_code']
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
        );
}
else if($cod['cod_type']=='p2') {
    $cod['cod_count'] = $cod['cod_count'] ?: 1;
    $code_array = array(
            $cod['cod_code']
            ,'M'.rand(1111,9999)
            ,'M'.rand(1111,9999)
        );
}

$countgap = 10; // 몇건씩 보낼지 설정
$maxscreen = 500; // 몇건씩 화면에 보여줄건지?
$sleepsec = 200;  // 천분의 몇초간 쉴지 설정

flush();
ob_flush();

$cnt = 0;
$idx = 0;
for ($j=0; $j<$cod['cod_count']*2; $j++) {
    $cnt++;

    // 코드 랜덤 생성
    $cod_code[$cnt] = $code_array[rand(0,sizeof($code_array)-1)];

    // 해당 건수가 발생하면..
    if ($cod_code[$cnt] == $cod['cod_code']) {
        $idx++;
        $cod_code_check[$cnt] = ' <i class="fa fa-check" style="color:red;"></i> '.$idx.'회 발생';

        // p2일 때는 같은 조건이 되면 발송
        if($cod['cod_type']=='p2') {
           $send_flag[$cnt] = 1;
        }
        // p일 때는 해당 횟수번째에 걸려야 함
        if($cod['cod_type']=='p' && $idx==$cod['cod_count']) {
            $send_flag[$cnt] = 1;
        }

        // 알림 발송
        if($send_flag[$cnt]) {
            $cod_sent[$cnt] = ' >>>>>>>>>>>>>> <i class="fa fa-envelope-o" style="color:darkorange;"></i> 알림 발송';

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
                                        ,'/{설비명}/','/{구분}/'
                                        ,'/{코드}/','/{내용}/'
                                        ,'/{년월일}/','/{HOME_URL}/'
                                    );
                                    // print_r2($patterns);
                    $replace = array (  $towhom_li[$i]['r_name'], $cod['cod_name']
                                        ,$mms['mms_name'], $g5['set_cod_group_value'][$cod['cod_group']]
                                        ,$cod['cod_code'], conv_content($cod['cod_memo'],2)
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
                    mailer($config['cf_admin_email_name'].'(발신전용)', $config['cf_admin_email'], $towhom_li[$i]['r_email'], $towhom['subject'], $towhom['content'], 1);

                }

            }


        }

    }
    else {
        $cod_sent[$cnt] = ' >>> 알림 없음';
    }

    echo "<script> document.all.cont.innerHTML += '$cnt. ".$cod_code[$cnt].$cod_code_check[$cnt].$cod_sent[$cnt]."<br>'; </script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    if ($cnt % $countgap == 0)
    {
        echo "<script> document.all.cont.innerHTML += '<br>'; document.body.scrollTop += 1000; </script>\n";
    }

    // 화면을 지운다... 부하를 줄임
    if ($cnt % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; document.body.scrollTop += 1000; </script>\n";

    usleep(500000);
}
?>
<script> document.all.cont.innerHTML += "<br><font color=crimson><b>[테스트 종료]</b></font><br><br>"; document.body.scrollTop += 1000; </script>
