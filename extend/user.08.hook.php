<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 후킹 부분을 정의합니다


// // common 후킹
// add_event('common_header','u_common_header',10);
// function u_common_header(){
//     global $board,$board_skin_path,$board_skin_url;

//     // 관리자단 게시판 스킨 설정
//     $fr_adm = preg_match("/\/adm\/v10/",$_SERVER['HTTP_REFERER']);
//     if (defined('G5_IS_ADMIN') || $fr_adm) {
//         // 관리자 스킨
//         $unser = unserialize(stripslashes($board['bo_7']));
//         if( is_array($unser) ) {
//             foreach ($unser as $k1=>$v1) {
//                 $board[$k1] = htmlspecialchars($v1, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
//             }    
//         }
//         // print_r3($board);
//         // 모바일은 없음
//         if($board['set_skin_adm']) {
//             $board_skin_path    = get_skin_path('board', 'theme/'.$board['set_skin_adm']);
//             $board_skin_url     = get_skin_url('board', 'theme/'.$board['set_skin_adm']);
//         }
//     }    
// }



add_event('member_login_check','u_member_login_check',10);
function u_member_login_check(){
    global $g5, $mb;
    
    // $_SESSION['ss_com_idx'] setting
    // for a manager without mb_4, then assign default_com_idx
    $com_idx = ($mb['mb_level']>=6 && !$mb['mb_4']) ? $g5['setting']['set_com_idx'] : $mb['mb_4'];
    
    $c_sql = sql_fetch(" SELECT com_kosmolog_key FROM {$g5['company_table']} WHERE com_idx = '$com_idx' ");
    $com_kosmolog_key = $c_sql['com_kosmolog_key'];
    set_session('ss_com_idx', $com_idx);
    set_session('ss_com_kosmolog_key',$com_kosmolog_key);

    // login log recording
    $tmp_sql = "INSERT INTO {$g5['login_table']} ( lo_ip, mb_id, lo_datetime, lo_location, lo_url )
                VALUES ( '".G5_SERVER_TIME."', '{$mb['mb_id']}', '".G5_TIME_YMDHIS."', '".$mb['mb_name']."',  '".$_SERVER['REMOTE_ADDR']."' )
    ";
    sql_query($tmp_sql, FALSE);

    // BOM 가격 정보를 날짜를 기반으로 갱신
    $sql_bom = "UPDATE {$g5['bom_table']} AS bom SET
                    bom_price = (
                        SELECT bop_price
                        FROM {$g5['bom_price_table']}
                        WHERE bom_idx = bom.bom_idx
                            AND bop_start_date <= '".G5_TIME_YMD."'
                        ORDER BY bop_start_date DESC
                        LIMIT 1
                    )
                WHERE bom_status NOT IN ('delete','trash')    
    ";
    sql_query($sql_bom, FALSE);
}

// Modify for converting PC mode automatically when mobile logout. It should be stayed in Mobile mode.
add_event('member_logout','u_member_logout',10);
function u_member_logout(){
    if(G5_IS_MOBILE) {
        goto_url(G5_URL.'?device=mobile');
    }
}


// 모바일 회원가입 시 기본 설정 추가, 앱 승인 때문에 필요함
add_event('register_form_update_after','u_register_form_update_after',10);
function u_register_form_update_after(){
    global $g5,$mb_id;
    // 모바일일 때만
    if(G5_IS_MOBILE) {
        $mb = get_member($mb_id);

        // 회원권한을 4로, 업체 소속은 7(ING Global)
        $sql = "UPDATE {$g5['member_table']} SET mb_level = 3, mb_4 = 7 WHERE mb_id = '".$mb_id."' ";
        sql_query($sql,1);

        // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
        // 이게 왜 회원가입단계에서는 없지? 원래 있어야 하는 건디!!
        set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));

        // 메뉴 접근 권한을 열어준다.
        $set_values = explode("\n", $g5['setting']['set_mobile_auth']);
        foreach ($set_values as $set_value) {
            list($key, $value) = explode('=', trim($set_value));
            if($key&&$value) {
                // echo $key.' / '.$value.'<br>';

                $au1 = sql_fetch(" SELECT * FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' AND au_menu = '".$key."' ",1);
                // 존재하면 업데이트
                if($au1['au_menu']) {
                    $sql = "UPDATE {$g5['auth_table']} SET
                                au_auth = '".$value."'
                            WHERE mb_id = '".$mb_id."' AND au_menu = '".$key."'
                    ";
                    //echo $sql.'<br>';
                    sql_query($sql,1);
                }
                // 없으면 생성
                else {
                    $sql = "INSERT INTO {$g5['auth_table']} SET
                                mb_id = '".$mb_id."'
                                , au_menu = '".$key."'
                                , au_auth = '".$value."'
                    ";
                    //echo $sql.'<br>';
                    sql_query($sql,1);
                }

            }
        }
        unset($set_values);unset($set_value);

    }
}




?>