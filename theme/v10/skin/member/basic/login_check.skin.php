<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

// 자신만의 코드를 넣어주세요.

// 중복 로그인 방지 시작 ----- {
$mb_id = $_POST['mb_id'];
if($mb_id!='super') {
    $mb = get_member($mb_id);
    $session_dir = G5_DATA_PATH."/session";
    $d = dir($session_dir);
    while (false != ($entry = $d->read())) {
        $temp = file($session_dir . '/' . $entry);
        if (preg_match("`ss_mb_id\|[^;]*\"" . $mb['mb_id'] . "\";`", $temp[0])) {
            //세션디렉토리 설정
            $session_dir = G5_DATA_PATH."/session";
            $d = dir($session_dir);
            while (false !== ($entry = $d->read())) {
                if (substr($entry, 0, 1) != '.' && $entry != 'index.php'){
                    $temp = file($session_dir . '/' . $entry);
                    if (preg_match("`ss_mb_id\|[^;]*\"" . $mb['mb_id'] . "\";`", $temp[0])) {
                        unlink($session_dir . '/' . $entry);
                    }
                }
            }
    
            alert("회원님의 아이디(".$mb['mb_id'].")는 이미 접속중입니다.\\n보안상 이유로 해당 계정의 모든 접속을 종료합니다.\\n\\n다시 로그인해 주시기 바랍니다.", G5_URL);
        }
    }
}
// ----- } 중복 로그인 방지 끝
?>
