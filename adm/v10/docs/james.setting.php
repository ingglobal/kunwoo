// 설정 페이지 관련
// 네이티브 코어 업데이트 관련
// 정보를 저장하고 관리합니다.

# 네이티브 코어 버전 동기화 파일들입니다.
1. /config.php
    // 디비 테이블 생성시 myisam 으로 생성해야 합니다.
    define('G5_DB_ENGINE', 'MyISAM');
2. /plugin/jquery-ui/datepicker.php
    jquery-ui 충돌이 나요. 예전 꺼는 주석 처리하고 별도로 선언한 최근 jquery-ui를 사용하도록 합니다.
3. /adm/v10/_common.php
    대표님 휴대폰으로 통신하면서 아이피가 바뀌면 자동 로그아웃!! 로그아웃 안 되게 수정했습니다.
    



# 네이티브 코어 버전 동기화 파일들입니다.
1. /adm/ajax.token.php
    사용자단에서도 관리자단의 게시판을 쓰기 위해서 불가피하게 수정 필요함
2. 에러 주석 처리 (이건 이제 해결된 듯 하다.)
    Warning: mysqli_connect(): Headers and client library minor version mismatch. Headers:50560 Library:100144 in /home/ingiot/intra/lib/common.lib.php on line 1518
3. /bbs/board_head.php
    관리자단에서 모바일 게시판을 사용하려면 어쩔 수 없는 수정이 필요합니다.
4. /bbs/board_tail.php
    관리자단에서 모바일 게시판을 사용하려면 어쩔 수 없는 수정이 필요합니다.
5. /adm/admin.lib.php
6. /adm/v10/_common.php
    대표님 휴대폰으로 통신하면서 아이피가 바뀌면 자동 로그아웃!! 로그아웃 안 되게 수정했습니다.


