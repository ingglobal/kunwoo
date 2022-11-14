<?php
$sub_menu = "990180";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


$g5['title'] = '로그인현황집계';
include_once('./_head.php');
$colspan = 6;

$sql_common = " from {$g5['login_table']} ";
// $sql_search = " where lo_datetime between '{$fr_date} 00:00:00' and '{$to_date} 23:59:59' ";
$sql_search = " where mb_id != '' AND mb_id != 'super' AND lo_location != 'EPCS' AND lo_url != '' AND lo_url REGEXP '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+'  ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            order by lo_datetime desc
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);
?>
<style>
.tbl_head01{font-size:1.3em;}
</style>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">IP</th>
        <th scope="col">회원ID</th>
        <th scope="col">접속자명</th>
        <th scope="col">로그인일시</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        $loiparr = explode('.',$row['lo_url']);
        $loip = '';
        for($k=0;$k<count($loiparr);$k++){
            if($k % 2 == 1){
                $loip .= '.'.$loiparr[$k];
            }else{
                $loip .= ($k == 0) ? str_replace($loiparr[$k],'***',$loiparr[$k]) : '.'.str_replace($loiparr[$k],'***',$loiparr[$k]); 
            }
        }

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_category"><?php echo $loip ?></td>
        <td class="td_category td_category3"><?php echo $row['mb_id'] ?></td>
        <td class="td_category td_category2"><?php echo $row['lo_location'] ?></td>
        <td class="td_datetime"><?php echo $row['lo_datetime'] ?></td>
    </tr>

    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없거나 관리자에 의해 삭제되었습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php
$qstr .= "&amp;page=";

$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr");
echo $pagelist;

include_once('./_tail.php');