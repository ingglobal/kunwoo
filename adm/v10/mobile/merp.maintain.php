<!-- ========================================================================================= -->
<!-- ========================================================================================= -->
<!-- ========================================================================================= -->
<!-- start of 정비 및 재고  -->
<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 정비 및 재고</i></div>
<div class="div_wrapper">
    <div class="div_left">

        <!-- ========================================================================================= -->
        <div class="div_title_02"><i class="fa fa-check" aria-hidden="true">정비 이력</i>
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=maintain" target="_parent" class="more">더보기</a>
        </div>
        <div class="div_info_body">
            <table class="table01">
                <thead class="tbl_head">
                <tr>
                    <th scope="col" style="width:30%">구분</th>
                    <th scope="col">제목</th>
                    <th scope="col" style="width:20%;">정비일</th>
                    <th scope="col" style="width:20%">비용</th>
                </tr>
                </thead>
                <tbody class="tbl_body">
                <?php
                $sql = "SELECT *
                        FROM g5_write_maintain
                        WHERE wr_is_comment = 0
                            AND wr_1 = '".$com['com_idx']."'
                            {$sql_mmses2}
                            AND wr_3 != ''
                            AND wr_3 >= '".$st_date." 00:00:00'
                            AND wr_3 <= '".$en_date." 23:59:59'
                        ORDER BY wr_num
                ";
                // echo $sql.'<br>';
                $rs = sql_query($sql,1);
                for ($i=0; $row=sql_fetch_array($rs); $i++) {
                    // print_r2($row);
                    // wr_9 serialized 추출
                    $row['sried'] = get_serialized($row['wr_9']);
                    // print_r2($row['sried']);

                    // 비용
                    $wr_maintain_price += $row['wr_6'];

                    echo '
                    <tr class="'.$row['tr_class'].'">
                        <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                        <td class="">'.$row['wr_subject'].'</td><!-- 제목 -->
                        <td class="text_center">'.$row['wr_3'].'</td><!-- 정비일 -->
                        <td class="text_center text_right pr_10">'.number_format($row['wr_6']).'</td><!-- 비용 -->
                    </tr>
                    ';
                }
                if ($i == 0)
                    echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                ?>
            </tbody>
            </table>
            <?php
            $maintain_price = num_to_han($wr_maintain_price);
            // print_r2($maintain_price);
            ?>
            <script>
            // 정비비용
            $('#sum_maintain').text('<?=number_format($maintain_price[0],1)?>');
            $('#sum_maintain').closest('li').find('.unit').text('<?=$maintain_price[1]?>');
            </script>
        </div><!-- .div_info_body -->

    
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
                    <th scope="col" style="width:20%">단가</th>
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
                ";
                // echo $sql.'<br>';
                $rs = sql_query($sql,1);
                for ($i=0; $row=sql_fetch_array($rs); $i++) {
                    // print_r2($row);
                    // wr_9 serialized 추출
                    $row['sried'] = get_serialized($row['wr_9']);
                    // print_r2($row['sried']);
                    // 재고총액
                    $wr_stock_price += $row['wr_3']*$row['wr_4'];

                    echo '
                    <tr class="'.$row['tr_class'].'">
                        <td class="">'.$row['sried']['mms_name'].'</td><!-- 구분 -->
                        <td class="">'.$row['wr_subject'].'</td><!-- 부품명 -->
                        <td class="text_center">'.number_format($row['wr_3']).'</td><!-- 단가 -->
                        <td class="text_center">'.$row['wr_4'].'</td><!-- 수량 -->
                    </tr>
                    ';
                }
                if ($i == 0)
                    echo '<tr class="tr_empty"><td class="td_empty" colspan="6">자료가 없습니다.</td></tr>';
                ?>
            </tbody>
            </table>
            <?php
            $stock_price = num_to_han($wr_stock_price);
            // print_r2($amount_price);
            ?>
            <script>
            // 재고총액
            $('#sum_stock').text('<?=number_format($stock_price[0],1)?>');
            $('#sum_stock').closest('li').find('.unit').text('<?=$stock_price[1]?>');
            </script>

        </div><!-- .div_info_body -->

    </div><!-- .div_r -->
</div><!-- .div_wrapper -->