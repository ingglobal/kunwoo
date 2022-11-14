<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>
<table class="table01">
    <thead class="tbl_head">
    <tr>
        <th scope="col" style="width:30%">구분</th>
        <th scope="col">제목</th>
        <th scope="col" style="width:20%">정비일</th>
        <th scope="col" style="width:15%">점검기한</th>
    </tr>
    </thead>
    <tbody class="tbl_body">
    <?php
    for ($i=0; $i<$list_count; $i++) {
        // print_r2($list[$i]);
        // wr_9 serialized 추출
        $list[$i]['sried'] = get_serialized($list[$i]['wr_9']);
        // print_r2($list[$i]['sried']);

        echo '
        <tr class="'.$row['tr_class'].'">
            <td class="">'.$list[$i]['sried']['mms_name'].'</td><!-- 구분 -->
            <td class="">'.$list[$i]['subject'].'</td><!-- 제목 -->
            <td class="text_center">'.$list[$i]['wr_3'].'</td><!-- 정비일 -->
            <td class="text_center">'.$list[$i]['wr_4'].'일전부터</td><!-- 점검기한 -->
        </tr>
        ';
    
    }
    if ($i == 0)
        echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
    ?>
</tbody>
</table>
