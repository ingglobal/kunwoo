<?php
// 회원검색은 mb_level=2,4 일반 회원을 검색하는 페이지입니다.
// 직원검색(영업자검색)은 employee_select.php 페이지에서 검색합니다.
// 호출 페이지들
// /adm/v10/company_form.php: (고객)업체 추가시 회원검색
// /adm/v10/employ_form.php: 사원등록시 회원검색
// /adm/v10/manager_form.php: 관리자등록시 회원검색
// /adm/v10/order_out_practice_form.php: 실행계획폼
include_once('./_common.php');

if($member['mb_level']<4)
    alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['member_table']} ";

// 디폴트 검색기준
$sql_where = " WHERE mb_leave_date = '' AND mb_level >= 4 ";

// 검색 조건
if($mb_where) {
    $sql_where .= " AND ".stripslashes($mb_where)." ";
}

// 업체 구분, 해당 업체 것만 추출
$sql_company_member = " AND mb_id IN ( SELECT mb_id
    FROM {$g5['company_member_table']}
    WHERE com_idx = '".$_SESSION['ss_com_idx']."' )
";

// 검색어
if($sch_word) {
    $sch_word   = clean_xss_tags($_GET['sch_word']);
    $sql_where .= " AND (mb_name LIKE '%$sch_word%' OR mb_id LIKE '%$sch_word%' OR mb_hp LIKE '%$sch_word%' OR mb_email LIKE '%$sch_word%') ";
}
// 정렬기준
$sql_order = " ORDER BY mb_datetime DESC ";


// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_where . $sql_company_member;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 운영권한이 없으면 검색했을 때만 결과가 나옴
if(false){//(!$member['mb_manager_yn']&&!$sch_word&&$page<2) {
    $rows = 0;
    $total_page = 0;
}

// 리스트 쿼리
$sql = "SELECT *
            " . $sql_common . $sql_where . $sql_company_member .  $sql_order . "
            LIMIT $from_record, $rows
";
//echo $sql.'<br>';
$result = sql_query($sql,1);

// counter display for manager
$total_count_display = ($member['mb_manager_account_yn']) ? ' ('.number_format($total_count).')' : '';


$g5['title'] = '회원 검색'.$total_count_display;
include_once('./_head.sub.php');


$qstr1 = 'frm='.$frm.'&tar1='.$tar1.'&tar2='.$tar2.'&tar3='.$tar3.'&file_name='.$file_name.'&mb_level='.$mb_level.'&com_idx='.$com_idx.'&mb_where='.stripslashes($mb_where).'&sch_word='.urlencode($sch_word);
$qstr2 = 'frm='.$frm.'&tar1='.$tar1.'&tar2='.$tar2.'&tar3='.$tar3.'&file_name='.$file_name.'&mb_level='.$mb_level.'&com_idx='.$com_idx.'&mb_where='.stripslashes($mb_where);
?>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <div class="local_desc01 local_desc">
        <p><b>검색항목</b>: 회원명(대표자명), 아이디, 휴대폰, 이메일</p>
        <p><span style="color:red;">기존 등록 업체</span>를 선택을 할 때는 <span style="color:red;">업체명 오른편의 [선택]</span>을 클릭하세요.</p>
    </div>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="tar1" value="<?php echo $_GET['tar1']; ?>">
    <input type="hidden" name="tar2" value="<?php echo $_GET['tar2']; ?>">
    <input type="hidden" name="tar3" value="<?php echo $_GET['tar3']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_GET['file_name']; ?>"><!-- 파일명 -->
    <input type="hidden" name="mb_level" value="<?php echo $mb_level; ?>"><!-- 회원권한 -->
    <input type="hidden" name="mb_where" value="<?php echo stripslashes($mb_where); ?>"><!-- 추가조건 -->
    <input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>"><!-- 업체코드 -->
    <input type="hidden" name="d" value="<?php echo $d; ?>"><!-- 부모창 권한 -->

    <div id="scp_list_find">
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20">
        <input type="submit" value="검색" class="btn btn_01">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?<?php echo $qstr2?>" class="btn btn_02">검색취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">회원명</th>
            <th scope="col">선택</th>
            <th scope="col">휴대폰/이메일</th>
            <th scope="col">업체명</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            
            // 추가 정보 추출 (업체정보, 최근1개만(limit 1) )
            $sql1 = "   SELECT com_name
                        FROM {$g5['company_member_table']} AS cmm
                            LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = cmm.com_idx
                        WHERE mb_id = '".$row['mb_id']."'
                        ORDER BY cmm_reg_dt DESC
                        LIMIT 1
            ";
            // echo $sql1.'<br>';
            $com = sql_fetch($sql1,1);

            // 업체인 경우
            if($com['com_idx']) {
                // 최근업체정보 - 업체메일, 업체측담당자명, 업체측담당자휴대폰, 대표자, 업체전화, 우편번호, 주소1, 주소2, 주소3, 지번
                $mb_values = ",'".$com['com_email']."','".trim($com['com_manager'])."','".$com['com_manager_hp']."','".$com['com_president']."','".$com['com_tel']."','".$com['com_zip1']."','".$com['com_zip2']."','".$com['com_addr1']."','".$com['com_addr2']."','".$com['com_addr3']."','".$com['com_addr_jibeon']."'";

                // 업체명(최근 포함 여러업체가 있는 경우는 다 리스팅)
                $sql2 = " SELECT * FROM {$g5['company_table']} WHERE mb_id = '".$row['mb_id']."' ";
                $rs2 = sql_query($sql2,1);
                for($j=0; $row2=sql_fetch_array($rs2); $j++) {
                    $row['com_names'] .= $row2['com_name'].' <a href="javascript:" class="btns btn_02 btn_company" com_idx="'.$row2['com_idx'].'" mb_id_saler="'.$member['mb_id'].'">선택</a>'.'<br>';
                }
            }
            // 일반회원인 경우
            else {
                // 메일, 업체측담당자명, 업체측담당자휴대폰, 대표자, 업체전화, 우편번호, 주소1, 주소2, 주소3, 지번
                $mb_values = ",'".$row['mb_email']."','".trim($row['mb_name'])."','".$row['mb_hp']."','".$row['mb_name']."','".$row['mb_hp']."','".$row['mb_zip1']."','".$row['mb_zip2']."','".$row['mb_addr1']."','".$row['mb_addr2']."','".$row['mb_addr3']."','".$row['mb_addr_jibeon']."'";
            }
            
            // 이메일 숨김
            $row['mb_email1'] = explode("@",$row['mb_email']);
            $row['mb_email2'] = explode(".",$row['mb_email1'][1]);
            $row['mb_email_disp'] = ($row['mb_email']) ? '***'.substr($row['mb_email1'][0],-3).'@'.$row['mb_email2'][0] : '';
        ?>
        <tr>
            <td class="td_left"><?php echo $row['mb_name']; ?><br><span style="color:#818181;font-size:0.8em;"><?php echo $row['mb_id']; ?></span></td>
            <td class="td_mng td_mng_s"><button type="button" class="btn btn_02" onclick="put_value('<?php echo trim($row['mb_id']); ?>'
                ,'<?php echo trim($row['mb_name']);?>'
                ,'<?php echo $row['mb_2']; ?>','<?php echo trim($row['mb_nick']);?>'<?php echo $mb_values;?>);">선택</button>
            <td class="scp_target_code">****<?php echo substr($row['mb_hp'],-4); ?><br><?php echo $row['mb_email_disp']; ?></td><!-- 휴대폰/이메일 -->
            <td class="scp_target_code"><?php echo $com['com_name']; ?></td><!-- 업체명 -->
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="5" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : 5, $page, $total_page, '?'.$qstr1.'&amp;page='); ?>

    <div class="btn_confirm01 btn_confirm win_btn">
        <button type="button" onclick="window.close();" class="btn btn-secondary">닫기</button>
    </div>
</div>

<script>
function put_value(val1,val2,val3,val4,val_email,val_manager,val_hp,val_president,val_tel,val_zip1,val_zip2,val_addr1,val_addr2,val_addr3,val_addr_jibeon) {
    <?php
    // 고객정보 추가(수정)페이지(file_name=company_form)인 경우는 회원을 추가한다.
    if($file_name=='company_form') {
        ?>
        window.opener.document.getElementById('mb_id').value = val1;
        window.opener.document.getElementById('mb_name').value = val2;
		window.opener.document.getElementById('com_president').value = val_president;
		window.opener.document.getElementById('com_tel').value = val_tel;
		window.opener.document.getElementById('com_email').value = val_email;
		window.opener.document.getElementById('com_manager').value = val_manager;
		window.opener.document.getElementById('com_manager_hp').value = val_hp;
		window.opener.document.getElementById('com_zip').value = val_zip1+val_zip2;
		window.opener.document.getElementById('com_addr1').value = val_addr1;
		window.opener.document.getElementById('com_addr2').value = val_addr2;
		window.opener.document.getElementById('com_addr3').value = val_addr3;
		window.opener.document.getElementById('com_addr_jibeon').value = val_addr_jibeon;
        <?php
    }
    // 관리자 추가인 경우
    else if($file_name=='manager_form') {
        ?>
        window.opener.document.getElementById('reg_mb_id').value = val1;
        window.opener.document.getElementById('mb_name').value = val2;
        window.opener.document.getElementById('reg_mb_nick').value = val4;
		window.opener.document.getElementById('reg_mb_email').value = val_email;
		window.opener.document.getElementById('reg_mb_hp').value = val_hp;
        <?php
    }
    // 사원 추가인 경우
    else if($file_name=='employee_form') {
        ?>
        window.opener.document.getElementById('reg_mb_id').value = val1;
        window.opener.document.getElementById('mb_name').value = val2;
        window.opener.document.getElementById('reg_mb_nick').value = val4;
		window.opener.document.getElementById('reg_mb_email').value = val_email;
		window.opener.document.getElementById('reg_mb_hp').value = val_hp;
		window.opener.document.getElementById('reg_mb_tel').value = val_tel;
        $("#mb_password", window.opener.document).remove();
        <?php
    }
    // 출하실행계획 폼
    else if($file_name=='order_out_practice_form') {
        ?>
        $("input[name=mb_id]", opener.document).val( val1 ).attr('required',true);
        $("input[name=mb_name]", opener.document).val( val2 ).attr('required',true).addClass('required');

        //생산계획ID 해제
        $("input[name=orp_idx]", opener.document).val("").attr('required',false);
        $("input[name=line_name]", opener.document).val("").attr('required',false).removeClass('required');
        <?php
    }
    //실행계획/출하목록 폼
    else if($file_name=='order_practice_form' || $file_name=='order_out_list') {
        ?>
        $("input[name=mb_id]", opener.document).val( val1 );
        $("input[name=mb_name]", opener.document).val( val2 );
        <?php
    }
    // 디폴트
    else {
        // 타켓폼 변수가 존재하면..
        if($frm) {
        ?>
            // 폼이 존재하면
            if(window.opener.document.<?=$frm?> != undefined) {
                var f = window.opener.document.<?=$frm?>;
                f.<?=$tar1?>.value = val1;
                <?php if($tar2) { ?>f.<?=$tar2?>.value = val2; <?php } ?>
                <?php if($tar3) { ?>f.<?=$tar3?>.value = val3; <?php } ?>
            }
        <?php
        }
        // 타켓폼 변수가 없으면 아이디로 검색
        else {
        ?>
            if( $( '#<?=$tar1?>', opener.document ).length ) {
                $( '#<?=$tar1?>', opener.document ).val( val1 );
            }
            // 두번째 변수는 input 타입이 아닐 수도 있음
            <?php if($tar2) { ?>
            if( window.opener.document.getElementById('<?=$tar2?>').tagName == 'INPUT' || window.opener.document.getElementById('<?=$tar2?>').tagName == 'SELECT' )
                window.opener.document.getElementById('<?=$tar2?>').value = val2;
            else
                window.opener.document.getElementById('<?=$tar2?>').innerHTML = val2;
            <?php } ?>
            // 세번째 변수도 input 타입이 아닐 수도 있음
            <?php if($tar3) { ?>
            if( window.opener.document.getElementById('<?=$tar3?>').tagName == 'INPUT' || window.opener.document.getElementById('<?=$tar3?>').tagName == 'SELECT' )
                window.opener.document.getElementById('<?=$tar3?>').value = val2;
            else
                window.opener.document.getElementById('<?=$tar3?>').innerHTML = val2;
            <?php } ?>
        <?php
        }
    }
    ?>
    window.close();
}

// in case of clicking btn_company through ajax, Insert company_member table & operer window change;
$(document).on('click','.btn_company',function(e){
    e.preventDefault();
    var com_idx = $(this).attr('com_idx');
    var mb_id_saler = $(this).attr('mb_id_saler');
    //-- 디버깅 Ajax --//
    $.ajax({
        url:g5_user_admin_url+'/ajax/company.json.php',
        data:{"aj":"s1","com_idx":com_idx,"mb_id_saler":mb_id_saler},
        dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
    //$.getJSON(g5_user_admin_url+'/ajax/company.json.php',{"aj":"sales","cmm_idx":cmm_idx,"cmm_status":cmm_status},function(res) {
        //alert(res.sql);
            if(res.result == true) {
                alert(res.msg);
            }
            else {
                alert(res.msg);
            }
            
            // reloation opener window
            opener.location = "./company_form.php?w=u&com_idx="+com_idx;
            window.close();
            
        }, error:this_ajax_error	//<-- 디버깅 Ajax --//
    });
});
</script>

<?php
include_once('./_tail.sub.php');
?>