<!-- ========================================================================================= -->
<!-- ========================================================================================= -->
<!-- ========================================================================================= -->
<!-- start of 정비 및 재고  -->
<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 정비 및 재고</i></div>
<div class="div_wrapper">
    <div class="div_left">

        <!-- ========================================================================================= -->
        <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">계획(예방) 정비</i>
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=plan" target="_parent" class="more">더보기</a>
        </div>
        <div class="div_info_body">
            <?php
            // 점검기한을 D-10 형태로 표현해야 해서 변경
            // echo latest10('theme/kpi10', 'plan', 10, 23);
            ?>
            <table class="table01">
                <thead class="tbl_head">
                <tr>
                    <th scope="col" style="width:30%">구분</th>
                    <th scope="col">제목</th>
                    <th scope="col" style="width:20%;">정비일</th>
                    <th scope="col" style="width:20%">점검기한</th>
                </tr>
                </thead>
                <tbody class="tbl_body">
                <?php
                $sql = "SELECT *
                        FROM g5_write_plan
                        WHERE wr_is_comment = 0
                            AND wr_1 = '".$com['com_idx']."'
                            {$sql_mmses2}
                        ORDER BY wr_num
                        LIMIT 5
                ";
                // echo $sql.'<br>';
                $rs = sql_query($sql,1);
                for ($i=0; $row=sql_fetch_array($rs); $i++) {
                    // print_r2($row);
                    // wr_9 serialized 추출
                    $row['sried'] = get_serialized($row['wr_9']);
                    // print_r2($row['sried']);

                    // 점검기한 계산
                    $row['date_diff'] = date_diff(new DateTime($row['wr_3']), new DateTime(date("Y-m-d",G5_SERVER_TIME)));
                    $row['wr_date_diff'] = $row['date_diff']->days;
                    $row['wr_date_diff_prefix'] = $row['date_diff']->invert ? 'D-' : 'D+';
                    // Color awarness from one month before. 
                    $row['wr_date_diff_color'] = ($row['date_diff']->invert && $row['date_diff']->days<30) ? ' style="color:darkorange;"' : '';
                    // $row['wr_date_diff'] = (($row['date_diff']->invert)*-1)*$row['date_diff']->days;
                    // print_r2($row['date_diff']);

                    echo '
                    <tr class="'.$row['tr_class'].'">
                        <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                        <td class="">'.$row['wr_subject'].'</td><!-- 제목 -->
                        <td class="text_center">'.$row['wr_3'].'</td><!-- 정비일 -->
                        <td class="text_center" '.$row['wr_date_diff_color'].'>'.$row['wr_date_diff_prefix'].$row['wr_date_diff'].'</td><!-- 점검기한 -->
                    </tr>
                    ';
                }
                if ($i == 0)
                    echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                ?>
            </tbody>
            </table>

            <?php
            // 오늘 ~ +10일
            $tmp_write_table = $g5['write_prefix'].'plan'; // 게시판 테이블 전체이름
            $sql = "SELECT COUNT(wr_id) AS plan_cnt
                    FROM {$tmp_write_table}
                    WHERE wr_is_comment = 0
                        AND wr_1 = '".$com['com_idx']."'
                        {$sql_mmses2}
                        AND wr_3 != ''
                        AND wr_3 >= '".G5_TIME_YMD."'
                        AND wr_3 < DATE_ADD('".G5_TIME_YMDHIS."' , INTERVAL +10 DAY)
                    ORDER BY wr_num
            ";
            // echo $sql.'<br>';
            $plan = sql_fetch($sql,1);
            // echo $plan['plan_cnt'].'<br>';
            ?>
        </div><!-- .div_info_body -->
        <script>
        // 게획정비
        $('#sum_plan').text('<?=number_format($plan['plan_cnt'])?>');
        </script>

    
    </div><!-- .div_left -->
    <div class="div_right">

        <!-- ========================================================================================= -->
        <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">설비별 재고</i>
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=parts" target="_parent" class="more">더보기</a>
        </div>
        <div class="div_info_body">
            <?php 
            // latest에서 불러오면 cache때문에 시차가 생김
            // echo latest10('theme/kpi20', 'parts', 10, 23,0);
            ?>
            <table class="table01">
                <thead class="tbl_head">
                <tr>
                    <th scope="col" style="width:30%">구분</th>
                    <th scope="col">부품명</th>
                    <th scope="col" style="width:20%">수량</th>
                </tr>
                </thead>
                <tbody class="tbl_body">
                <?php
                $sql = "SELECT *
                        FROM g5_write_parts
                        WHERE wr_is_comment = 0
                            AND wr_1 = '".$com['com_idx']."'
                            {$sql_mmses2}
                        ORDER BY wr_num
                        LIMIT 5
                ";
                // echo $sql.'<br>';
                $rs = sql_query($sql,1);
                for ($i=0; $row=sql_fetch_array($rs); $i++) {
                    // print_r2($row);
                    // wr_9 serialized 추출
                    $row['sried'] = get_serialized($row['wr_9']);
                    // print_r2($row['sried']);
                    echo '
                    <tr class="'.$row['tr_class'].'">
                        <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                        <td class="">'.$row['wr_subject'].'</td><!-- 부품명 -->
                        <td class="text_center">'.$row['wr_4'].'</td><!-- 수량 -->
                    </tr>
                    ';
                }
                if ($i == 0)
                    echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                ?>
            </tbody>
            </table>

        </div><!-- .div_info_body -->

    </div><!-- .div_r -->
</div><!-- .div_wrapper -->