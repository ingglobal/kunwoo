<?php
//루트의 쓰기영역을 후회하는 후킹: 필요없는경우 스킨폴더에서 "write.change.skin.php"파일을 제거해라
if(is_file($board_skin_path.'/write.change.skin.php')){
    @include_once($board_skin_path.'/write.change.skin.php');
    return;
}

if(!$bo_table){
    $msg = "bo_table 값이 넘어오지 않았습니다.\\n\\nbbs_board.php?bo_table=code 와 같은 방식으로 넘겨 주세요.";
    alert($msg);
}

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.', G5_USER_ADMIN_URL);
}
if($board['gr_id'] != 'intra'){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\게시판그룹을 확인해 주세요.';
    alert($msg);
}
if($board['bo_1_subj'] != 'sub_menu'){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\n게시판관리에서 [sub_menu]지정이 되어 있는지 확인해 주세요.';
    alert($msg);
}

if(!$board['bo_1']){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\n게시판관리에서 [sub_menu]의 코드가 지정이 되어 있는지 확인해 주세요.';
    alert($msg);
}

$sub_menu = trim($board['bo_1']);
auth_check($auth[$sub_menu],"r");

$notice_array = explode(',', trim($board['bo_notice']));

if (!($w == '' || $w == 'u' || $w == 'r')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == 'u' || $w == 'r') {
    if ($write['wr_id']) {
        // 가변 변수로 $wr_1 .. $wr_10 까지 만든다.
        for ($i=1; $i<=10; $i++) {
            $vvar = "wr_".$i;
            $$vvar = $write['wr_'.$i];
        }
    } else {
        alert("데이터가 존재하지 않습니다.\\n삭제 되었을 가능성이 있습니다.", G5_URL);
    }
}



if ($w == '') {
    if ($wr_id) {
        alert('등록페이지에서는 \$wr_id 값을 사용하지 않습니다.', G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table);
    }

    if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('글을 쓸 권한이 없습니다.');
        }
    }

    $title_msg = '등록';
} else if ($w == 'u') {
    // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
    //if ($member['mb_level'] < $board['bo_write_level']) {
    if($member['mb_id'] && $write['mb_id'] === $member['mb_id']) {
        ;
    } else if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('데이터를 수정할 권한이 없습니다.');
        }
    }

    
    $title_msg = '수정';

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
        WHERE fle_db_table = 'board/{$bo_table}' AND fle_type = 'ref' AND fle_db_id = '".$write['wr_id']."' ORDER BY fle_reg_dt DESC ";
    //echo $sql;exit;
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $row['bo_f_ref'] = array();
    $row['bo_ref_fidxs'] = array();//게시판 파일번호(fle_idx) 목록이 담긴 배열
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'" style="color:blue;">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($row['bo_f_'.$row2['fle_type']],array('file'=>$file_down_del));
        @array_push($row['bo_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
    }

}


// 그룹접근 가능
if (!empty($group['gr_use_access'])) {
    if ($is_admin == 'super' || $group['gr_admin'] === $member['mb_id'] || $board['bo_admin'] === $member['mb_id']) {
        ; // 통과
    } else {
        // 그룹접근
        $sql = " select gr_id from {$g5['group_member_table']} where gr_id = '{$board['gr_id']}' and mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['gr_id'])
            alert('접근 권한이 없으므로 데이터 등록이 불가합니다.\\n\\n궁금하신 사항은 관리자에게 문의 바랍니다.');
    }
}



// 글자수 제한 설정값
if ($is_admin || $board['bo_use_dhtml_editor'])
{
    $write_min = $write_max = 0;
}
else
{
    $write_min = (int)$board['bo_write_min'];
    $write_max = (int)$board['bo_write_max'];
}

$g5['title'] = ((G5_IS_MOBILE && $board['bo_mobile_subject']) ? $board['bo_mobile_subject'] : $board['bo_subject']).' '.$title_msg;

$is_notice = false;
$notice_checked = '';
if ($is_admin && $w != 'r') {
    $is_notice = true;

    if ($w == 'u') {
        if (in_array((int)$wr_id, $notice_array)) {
			$notice_checked = 'checked';
		}
    }
}

$is_html = false;
if ($member['mb_level'] >= $board['bo_html_level'])
    $is_html = true;

$is_secret = $board['bo_use_secret'];

$is_mail = false;
if ($config['cf_email_use'] && $board['bo_use_email'])
    $is_mail = true;

$recv_email_checked = '';
if ($w == '' || strstr($write['wr_option'], 'mail'))
    $recv_email_checked = 'checked';

$is_name     = false;
$is_password = false;
$is_email    = false;
$is_homepage = false;
if ($is_guest || ($is_admin && $w == 'u' && $member['mb_id'] !== $write['mb_id'])) {
    $is_name = true;
    $is_password = true;
    $is_email = true;
    $is_homepage = true;
}

$is_category = false;
$category_option = '';
if ($board['bo_use_category']) {
    $ca_name = "";
    if (isset($write['ca_name']))
        $ca_name = $write['ca_name'];
    $category_option = get_category_option($bo_table, $ca_name);
    $is_category = true;
}

$is_link = false;
if ($member['mb_level'] >= $board['bo_link_level']) {
    $is_link = true;
}

$is_file = false;
if ($member['mb_level'] >= $board['bo_upload_level']) {
    //$is_file = true; //게시판 자체 파일을 사용할 경우 주석을 풀어라
}

$is_file_content = false;
if ($board['bo_use_file_content']) {
    $is_file_content = true;
}

$file_count = (int)$board['bo_upload_count'];

$name     = "";
$email    = "";
$homepage = "";
if ($w == "" || $w == "r") {
    if ($is_member) {
        $email = get_email_address($member['mb_email']);
        $homepage = get_text(stripslashes($member['mb_homepage']));
    }
}

$html_checked   = "";
$html_value     = "";
$secret_checked = "";

if ($w == 'u') {
    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
}

set_session('ss_bo_table', $_REQUEST['bo_table']);
set_session('ss_wr_id', $_REQUEST['wr_id']);

$upload_max_filesize = number_format($board['bo_upload_size']) . ' 바이트';

$width = $board['bo_table_width'];
if ($width <= 100)
    $width .= '%';
else
    $width .= 'px';


// 임시 저장된 글 수

include_once('./_head.php');
include_once('./bbs_common_head.php');
echo $g5['container_sub_title'];

$action_url = https_url(G5_ADMIN_DIR.'/'.G5_USER_ADMIN_DIR)."/bbs_write_update.php";

//별도의 메타테이블에 저장된 데이터를 합친다
$mrow = get_meta('board/'.$bo_table,$wr_id);//,$code64=1
if(isset($mrow) && (is_array($mrow) || is_object($mrow))){
    foreach($mrow as $mk => $mv){
        $write[$mk] = $mv;
    }
}

echo '<!-- skin : '.(G5_IS_MOBILE ? $board['bo_mobile_skin'] : $board['bo_skin']).' -->';
$cancel_url = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&'.$qstr;
echo $field_prop;
include_once ($board_skin_path.'/write.skin.php');
echo <<<HEREDOC
<script>
$('<input type="hidden" name="token" value="">').insertAfter('input[name="page"]');
</script>
HEREDOC;

include_once('./bbs_common_tail.php');
include_once ('./_tail.php');