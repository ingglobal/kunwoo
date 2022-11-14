<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 상태값 변경에 따른 생산량 일간 합계 업데이트, 이전상태에 대한 통계 및 현재상태에 대한 통계 둘 다 변경되어야 함
// itm_idx
if(!function_exists('update_item_sum_by_status')){
function update_item_sum_by_status($itm_idx) {
    global $g5;

    if(!$itm_idx) {
        return false;
    }
    $itm = get_table('item','itm_idx',$itm_idx);
    $oop = get_table('order_out_practice','oop_idx',$itm['oop_idx']);
    $orp = get_table('order_practice','orp_idx',$oop['orp_idx']);
    // print_r2($orp);
    // last two items form the last / This gets only one for one line, and two item for two line.
    $itm['itm_histories'] = explode("\n",trim($itm['itm_history']));
    // print_r2($itm['itm_histories']);
    if(trim($itm['itm_history'])) {
        $x=0;
        for($j=sizeof($itm['itm_histories'])-2;$j<sizeof($itm['itm_histories']);$j++) {
            $itm['itm_history_array'][$x] = $itm['itm_histories'][$j];
            $x++;
        }
    }
    // print_r2($itm['itm_history_array']);
    for($j=0;$j<sizeof($itm['itm_history_array']);$j++) {
        $itm['itm_history_items'] = explode("|",$itm['itm_history_array'][$j]);
        // print_r2($itm['itm_history_items']);
        // 통계 처리
        $ar['itm_date'] = $itm['itm_history_items'][1];
        $ar['mms_idx'] = $itm['mms_idx'];
        $ar['trm_idx_line'] = $orp['trm_idx_line'];
        $ar['itm_shift'] = $itm['itm_history_items'][2];
        $ar['bom_idx'] = $itm['bom_idx'];
        $ar['itm_status'] = $itm['itm_history_items'][0];
        $ar['com_idx'] = $itm['com_idx'];
        // print_r2($ar);
        update_item_sum($ar);
        unset($ar);
    }

    return true;
}
}
// 생산량 일간 합계 입력
// itm_date, trm_idx_line, itm_shift, bom_idx, itm_status
if(!function_exists('update_item_sum')){
function update_item_sum($arr) {
    global $g5;

    if(!$arr['itm_date']) {
        return false;
    }

    $sql_where = "itm_shift = '".$arr['itm_shift']."'
                AND mms_idx = '".$arr['mms_idx']."'
                AND trm_idx_line = '".$arr['trm_idx_line']."'
                AND itm_status = '".$arr['itm_status']."'
                AND itm_date = '".$arr['itm_date']."'
    ";

    // 합계 데이터값 추출 / 일별, 상태별, 구분별....
    $sql = "SELECT COUNT(itm_idx) AS itm_count
            FROM {$g5['item_table']} AS itm
                LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oop_idx = itm.oop_idx
                LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
            WHERE {$sql_where}
                AND itm.com_idx = '".$arr['com_idx']."'
                AND itm.bom_idx	= '".$arr['bom_idx']."'
    ";
    $sum = sql_fetch($sql,1);
    // echo $sql.'<br>';
    // print_r2($sum1);

    // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='test0', mta_value = '".json_encode($arr)."' ");
    // Record update or insert
    $sql = "SELECT itm_idx
            FROM {$g5['item_sum_table']}
            WHERE {$sql_where}
                AND com_idx = '".$arr['com_idx']."'
                AND bom_idx	= '".$arr['bom_idx']."'
    ";
    // echo $sql.'<br>';
	// sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
    $row = sql_fetch($sql,1);
    // 정보 업데이트
    if($row['itm_idx']) {
        $sql = "UPDATE {$g5['item_sum_table']} SET
                    itm_count = '".$sum['itm_count']."'
                WHERE {$sql_where}
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='update', mta_value = '".addslashes($sql)."' ");
        sql_query($sql,1);
    }
    else {
        // Get a bom info array for price.
        $bom = get_table('bom', 'bom_idx', $arr['bom_idx']);

        $sql = " INSERT INTO {$g5['item_sum_table']} SET
                    com_idx = '".$arr['com_idx']."'
                    , imp_idx = '".$arr['imp_idx']."'
                    , mms_idx = '".$arr['mms_idx']."'
                    , mmg_idx = '14'
                    , shf_idx = '".$arr['shf_idx']."'
                    , itm_shift = '".$arr['itm_shift']."'
                    , trm_idx_operation = '".$arr['trm_idx_operation']."'
                    , trm_idx_line = '".$arr['trm_idx_line']."'
                    , bom_idx = '".$arr['bom_idx']."'
                    , bom_part_no = '".$bom['bom_part_no']."'
                    , itm_price	= '".$bom['bom_price']."'
                    , itm_count = '".$sum['itm_count']."'
                    , itm_status = '".$arr['itm_status']."'
                    , itm_date = '".$arr['itm_date']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
        sql_query($sql,1);
        $row['itm_idx'] = sql_insert_id();
    }
    /*
    $sql = " SELECT itm.com_idx, itm.mms_idx, 14, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
        , COUNT(itm_idx) AS itm_count
        FROM {$g5['item_table']} AS itm
            LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oop_idx = itm.oop_idx
            LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
        WHERE itm_status NOT IN ('trash','delete')
            AND trm_idx_line = '{$arr['trm_idx_line']}'
            AND itm_date = '{$arr['itm_date']}'
            AND itm.bom_idx = '{$arr['bom_idx']}'
        GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status
        ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status
    ";
    $res = sql_query($sql,1);

    if($res->num_rows){
        for($i=0;$row1=sql_fetch_array($res);$i++){
            $sql = " UPDATE {$g5['item_sum_table']} SET
                        itm_count = '{$row1['itm_count']}'
                    WHERE itm_date = '{$row1['itm_date']}'
                        AND itm_shift = '{$row1['itm_shift']}'
                        AND trm_idx_line = '{$row1['trm_idx_line']}'
                        AND bom_idx = '{$row1['bom_idx']}'
                        AND itm_status = '{$row1['itm_status']}'
            ";
            sql_query($sql,1);
        }
    }
    */
    return $row['itm_idx'];
}
}


// 생산량 일간 합계 입력
// itm_date, trm_idx_line, itm_shift, bom_idx, itm_status
if(!function_exists('update_item_sum2')){
function update_item_sum2() {
    global $g5;

    //item_sum 테이블 초기화
    $truncate_sql = " TRUNCATE {$g5['item_sum_table']} ";
    sql_query($truncate_sql,1);

    $sqls = " INSERT INTO {$g5['item_sum_table']} (com_idx, imp_idx, mms_idx, mmg_idx, itm_date, trm_idx_line, bom_idx, bom_part_no, itm_price, itm_status, itm_count, itm_weight, itm_type)
           
           SELECT 
                itm.com_idx AS com_idx,itm.imp_idx AS imp_idx,itm.mms_idx AS mms_idx,31,itm_date AS mt_date, trm_idx_line AS trm_idx_line, oop.bom_idx AS bom_idx, bom_part_no AS bom_part_no, itm_price AS mt_price, itm_status AS mt_status,COUNT(itm_idx) AS mt_count,SUM(itm_weight) AS mt_sum,'product'
            FROM {$g5['item_table']} AS itm
                LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oop_idx = itm.oop_idx
                LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
            WHERE itm_status NOT IN ('trash','delete')
                AND itm_date != '0000-00-00'
            GROUP BY itm_date, itm.mms_idx, trm_idx_line, itm_shift, bom_idx, itm_status

            UNION

            SELECT 
                mtr.com_idx AS com_idx,mtr.imp_idx AS imp_idx,mtr.mms_idx AS mms_idx,31,mtr_input_date AS mt_date,trm_idx_location AS trm_idx_line,oop.bom_idx AS bom_idx,bom_part_no AS bom_part_no,mtr_price AS mt_price,mtr_status AS mt_status,COUNT(mtr_idx) AS mt_count,SUM(mtr_weight) AS mt_sum,'half'
            FROM {$g5['material_table']} AS mtr
                LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oop_idx = mtr.oop_idx
                LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
            WHERE mtr_status NOT IN ('trash','delete')
                AND mtr_input_date != '0000-00-00'
            GROUP BY mt_date, mms_idx, trm_idx_line, bom_idx, mt_status
            ORDER BY mt_date ASC, trm_idx_line, bom_idx, mt_status 
    ";
    sql_query($sqls,1);
}
}



// item 출하 처리 함수 (material도 함께 변경)
if(!function_exists('update_itm_delivery')){
function update_itm_delivery($arr) {
    global $g5;

    // 버튼상태값: 출력(print)/출하처리(out)/출하취소(cancel)
    $delivery_flag = ($arr['itm_status'] == 'delivery') ? 1 : 0;
	// sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".$arr['itm_status']."' ");


    $sql = "UPDATE {$g5['item_table']} SET
                itm_delivery = '{$delivery_flag}'
                , plt_idx = '".$arr['plt_idx']."'
                , itm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx = '".$arr['itm_idx']."'
    ";
	// sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
    // echo $sql.'<br>';
    sql_query($sql,1);

    // 연결된 자재의 모든 상태값을 변경
    $sql = "UPDATE {$g5['material_table']} SET
                mtr_delivery = '{$delivery_flag}'
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx = '".$arr['itm_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    return $arr['itm_idx'];
}
}

// item 상태 변경 함수 (material 상태도 함께 변경)
if(!function_exists('update_itm_status')){
function update_itm_status($arr) {
    global $g5;

    $sql = "UPDATE {$g5['item_table']} SET
                itm_history = CONCAT(itm_history,'\n".$arr['itm_status']."|".G5_TIME_YMDHIS."')
                , plt_idx = '".$arr['plt_idx']."'
                , itm_status = '".$arr['itm_status']."'
                , itm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx = '".$arr['itm_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // 연결된 자재의 모든 상태값을 변경
    $sql = "UPDATE {$g5['material_table']} SET
                mtr_status = '".$arr['itm_status']."'
                , mtr_history = CONCAT(mtr_history,'\n".$arr['itm_status']."|".G5_TIME_YMDHIS."')
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx = '".$arr['itm_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    return $arr['itm_idx'];
}
}

// item 상태 변경 함수 (material 상태도 함께 변경)
if(!function_exists('update_mtr_status')){
function update_mtr_status($arr) {
    global $g5;

    // 연결된 자재의 모든 상태값을 변경
    $sql = "UPDATE {$g5['material_table']} SET
                mtr_status = '".$arr['mtr_status']."'
                , mtr_history = CONCAT(mtr_history,'\n".$arr['mtr_status']."|".G5_TIME_YMDHIS."')
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE mtr_idx = '".$arr['mtr_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    return $arr['mtr_idx'];
}
}

// item 상태 변경 함수 (material 상태도 함께 변경)
if(!function_exists('update_mtr_multi_status')){
    function update_mtr_multi_status($arr) {
        global $g5;

        // 연결된 자재의 모든 상태값을 변경
        $sql = " UPDATE {$g5['material_table']} SET
                    mtr_status = '".$arr['mtr_status']."'
                    , mtr_history = CONCAT(mtr_history,'\n".$arr['mtr_status']."|".G5_TIME_YMDHIS."')
                    , mtr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE com_idx = '".$arr['com_idx']."'
                    AND bom_part_no = '".$arr['bom_part_no']."'
                ORDER BY mtr_idx
                LIMIT {$arr['count']}
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);

        return $arr['mtr_idx'];
    }
    }

// 빠레트 출하 취소 함수
if(!function_exists('pallet_item_init')){
function pallet_item_init($arr) {
    global $g5;

    // 연결된 자재의 출하 상태값을 변경
    $sql = "UPDATE {$g5['material_table']} SET
                mtr_delivery = '0'
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx IN (SELECT itm_idx FROM {$g5['item_table']} WHERE plt_idx = '".$arr['plt_idx']."' )
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // 제품 출하 정보 초기화
    $sql = "UPDATE {$g5['item_table']} SET
                itm_delivery = '0'
                , plt_idx = '0'
                , itm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE plt_idx = '".$arr['plt_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    return $arr['itm_idx'];
}
}

// item 상태 세팅 함수 (material 상태도 함께 변경)
// 상태값을 finish가 아닌 다른 값으로 바꾸려면 상태값을 배열로 넘겨주면 됨 $arr['itm_status'] = 'delivery';
if(!function_exists('pallet_item_reset')){
function pallet_item_reset($arr) {
    global $g5;

    // 상태값이 없으면 finish
    $arr['itm_status'] = $arr['itm_status'] ?: 'finish';

    // 연결된 자재의 모든 상태값을 변경
    $sql = "UPDATE {$g5['material_table']} SET
                mtr_status = '".$arr['itm_status']."'
                , mtr_history = CONCAT(mtr_history,'\n".$arr['itm_status']."|".G5_TIME_YMDHIS."')
                , mtr_update_dt = '".G5_TIME_YMDHIS."'
            WHERE itm_idx IN (SELECT itm_idx FROM {$g5['item_table']} WHERE plt_idx = '".$arr['plt_idx']."' )
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // 제품 정보 초기화
    $sql = "UPDATE {$g5['item_table']} SET
                itm_history = CONCAT(itm_history,'\n".$arr['itm_status']."|".G5_TIME_YMDHIS."')
                , plt_idx = '0'
                , itm_status = '".$arr['itm_status']."'
                , itm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE plt_idx = '".$arr['plt_idx']."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    return $arr['itm_idx'];
}
}


// 자재 재고 계산 함수
// oop_idx(출하-실행계획idx)만 있으면: 출하-실행계획(order_out_practice) 차원에서 재고계산
// orp_idx(실행계획idx)가 있으면: 실행계획(order_practice) 차원에서 재고계산 (다중 제품에 대해서 전체 계산)
if(!function_exists('material_stock_check')){
function material_stock_check($arr) {
    global $g5;

    if($arr['oop_idx']) {
        $sql_where = " oop_idx = '".$arr['oop_idx']."' ";
    }
    else if($arr['orp_idx']) {
        $sql_where = " orp_idx = '".$arr['orp_idx']."' ";
    }
    else {
        return false;
    }

    $sql = " SELECT *
                FROM {$g5['order_out_practice_table']} AS oop
                    LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = oop.bom_idx
                    LEFT JOIN {$g5['order_out_table']} AS oro ON oro.oro_idx = oop.oro_idx
                WHERE {$sql_where}
    ";
    // echo $sql.'<br>';
    $rs = sql_query($sql,1);
    for($i=0;$row=sql_fetch_array($rs);$i++) {
        //print_r3($rs);continue;
        // 생산제품 (팝오버 형태로 내용을 보여주기 위한 변수)
        $result['products'][$i]['bom_name'] = $row['bom_name'];
        $result['products'][$i]['bom_part_no'] = $row['bom_part_no'];
        $result['products'][$i]['oro_count'] = $row['oro_count'];
        $result['products'][$i]['oro_date_plan'] = $row['oro_date_plan'];
        $result['products'][$i]['com_idx_customer'] = $row['com_idx_customer'];

        // 01. BOM 구조 추출 (1개 생산시 소요자재수)
        $sql1 = "   SELECT bit.bom_idx, bom_name, bom_part_no, bom_status
                        , bit_idx, bom_idx_child, bit_reply, bit_count
                    FROM {$g5['bom_item_table']} AS bit
                        LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = bit.bom_idx_child
                    WHERE bit.bom_idx = '".$row['bom_idx']."'
                        AND bit_count > 0
                    ORDER BY bit.bit_num DESC, bit.bit_reply
        ";
        // echo $sql1.'<br>';
        $rs1 = sql_query($sql1,1);
        for ($j=0; $row1=sql_fetch_array($rs1); $j++) {
            // print_r2($row1);
            $row['mtr']['bom_idx'][] = $row1['bom_idx_child'];
            $row['mtr']['bom_part_no'][] = $row1['bom_part_no'];
            $row['mtr']['bit_count'][] = $row1['bit_count'];
            $row['mtr_count_each'] += $row1['bit_count']; // 필요 자재수 합계
        }
        // print_r2($row['mtr']);
        // echo $row['mtr_count_each'].'<br>'; // BOM 구조에 따른 필요자재 수량

        // 총 필요 자재수 = 단위당 자재수 * 지시수량
        $row['mtr_count_need'] = $row['mtr_count_each']*$row['oop_count'];
        // echo $row['mtr_count_need'].'<br>';

        // 02. 현재 재고 추출 (예측이므로 mtr_status=stock), 창고 단위+자재 단위로 보여주기 위해서 GROUP BY사용
        $sql_location = " AND trm_idx_location IN (52,53) "; // 창고위치 조건이 필요하면..
        $sql2 = "   SELECT mtr.bom_idx, mtr_name, bom_part_no, trm_idx_location
                        , COUNT(mtr_idx) AS cnt
                    FROM {$g5['material_table']} AS mtr
                        LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = mtr.bom_idx
                    WHERE mtr.bom_idx IN (".implode(",",$row['mtr']['bom_idx']).")
                        AND mtr_defect = '0'
                        AND mtr_status IN ('predict')
                        AND mtr.orp_idx = '".$row['orp_idx']."'
                        AND mtr.com_idx = '".$_SESSION['ss_com_idx']."'
                    GROUP BY mtr.bom_idx, trm_idx_location
        ";
        // echo $sql2.'<br>';
        $rs2 = sql_query($sql2,1);
        for ($j=0; $row2=sql_fetch_array($rs2); $j++) {
            // print_r2($row2);
            $row['mtr_stock'] += $row2['cnt']; // 할당 자재수 합계
            // 팝오버 형태로 내용을 보여주기 위한 변수
            $result['materials'][] = '<div class="div_stock">
                                        <span class="span_bom_name">'.$row2['mtr_name'].'</span>
                                        <span class="span_bom_part_no">'.$row2['bom_part_no'].'</span>
                                        <span class="span_location">'.$g5['location_name'][$row2['trm_idx_location']].'</span>
                                        <span class="span_bit_count"><b>'.number_format($row2['cnt']).'</b>개</span>
                                    </div>';
        }

        // 자재 = 현자재재고 - 총필요자재수
        $row['mtr_stock_predit'] = $row['mtr_stock'] - $row['mtr_count_need'];

        // 합계
        $result['stock_predict'] += $row['mtr_stock_predit'];

    }

    return $result;

}
}

// bom_item 정보 입력
if(!function_exists('update_bom_item')){
function update_bom_item($row) {
    global $g5;

    $list = $row; unset($row);

    $sql_common = " bom_idx = '".$list['bom_idx']."',
                    bom_idx_child = '".$list['bom_idx_child']."',
                    bit_count = '".$list['bit_count']."',
                    bit_num = '".$list['bit_num']."',
                    bit_reply = '".$list['bit_reply']."',
                    bit_update_dt = '".G5_TIME_YMDHIS."'
    ";

    $sql = "SELECT *
                FROM {$g5['bom_item_table']}
            WHERE bit_idx = '".$list['bit_idx']."'
    ";
    $bit = sql_fetch($sql,1);
    if(!$bit['bit_idx'] || !$list['bit_idx']) {
        $sql = " INSERT INTO {$g5['bom_item_table']} SET
                    {$sql_common}
                    , bit_reg_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
        $bit_idx = sql_insert_id();
    }
    else {
        $sql = "UPDATE {$g5['bom_item_table']} SET
                    {$sql_common}
                WHERE bit_idx = '".$bit['bit_idx']."'
        ";
        sql_query($sql,1);
        $bit_idx = $bit['bit_idx'];
    }
//	echo $sql.'<br>';

    return $bit_idx;
}
}

// 게시판 reply 생성 함수
// 초기값 정의
//$g5['bit']['num'] = array();
//$g5['bit']['reply'] = array();
//$g5['bit_num'] = 0;
if(!function_exists('get_num_reply')){
function get_num_reply($idx, $parent, $depth) {
    global $g5;

    // parent=0이면 num--
    if(!$parent)
        $g5['bit_num']--;

    // reply 코드 앞부분은 부모코드
    $reply_char1 = $g5['bit']['reply'][$parent];

    // 부모코드로 시작 & 한단계 높은(정규식 regexp="/^정규식.$/") 배열들 전부 추출
    // reply 코드 뒷부분은 같은 단계의 맨 끝값을 추출해서 나중에 +1 코드로 만들어야 함
    foreach($g5['bit']['reply'] as $key=>$val) if(preg_match('/^'.$reply_char1.'.$/', $val)) {
        //echo $key.'='.$val.'<br>';
        //echo $g5['bit']['num'][$key].'<>'.$g5['bit_num'].'<br>';
        // 같은 wr_num 그룹안에서만 찾아야 함
        if( $g5['bit']['num'][$key]==$g5['bit_num'] ) {
            $reply_last = $val;
        }
    }
    // 같은 단계값이 없으면 초기값, 있으면 마지막 한문자값+1
    if (!$reply_last)
        $reply_char2 = 'A';
    else
        $reply_char2 = chr(ord( substr($reply_last,-1) ) + 1);

    $g5['bit']['num'][$idx] = $g5['bit_num'];
    $g5['bit']['reply'][$idx] = ($depth) ? $reply_char1.$reply_char2 : '';

    return array($g5['bit']['num'][$idx], $g5['bit']['reply'][$idx]);

}
}


// update bom_price_history
// bom_idx, bom_start_date, bom_price
if(!function_exists('bom_price_history')){
function bom_price_history($arr) {
	global $g5;

    // Update price table info. Update for same price and date, Insert for not existing.
    $sql = "SELECT * FROM {$g5['bom_price_table']}
        WHERE bom_idx = '".$arr['bom_idx']."'
            AND bop_start_date = '".$arr['bom_start_date']."'
    ";
    $bop = sql_fetch($sql,1);
    if($bop['bop_idx']) {
        $sql = "UPDATE {$g5['bom_price_table']} SET
                    bop_price = '".$arr['bom_price']."',
                    bop_start_date = '".$arr['bom_start_date']."',
                    bop_update_dt = '".G5_TIME_YMDHIS."'
                WHERE bop_idx = '".$bop['bop_idx']."'
        ";
        sql_query($sql,1);
    }
    else {
        $sql = " INSERT INTO {$g5['bom_price_table']} SET
                    bom_idx = '".$arr['bom_idx']."',
                    bop_price = '".$arr['bom_price']."',
                    bop_start_date = '".$arr['bom_start_date']."',
                    bop_reg_dt = '".G5_TIME_YMDHIS."',
                    bop_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
        $bop['bop_idx'] = sql_insert_id();
    }

    return $bop['bop_idx'];
}
}

// set the today's proper price according the date registered in the bom_price table.
if(!function_exists('set_bom_price')){
function set_bom_price($bom_idx) {
	global $g5;

    // get the latest price info and update the mms_item table info.
    $sql = "UPDATE {$g5['bom_table']} AS bom SET
                    bom_price = (
                        SELECT bop_price
                        FROM {$g5['bom_price_table']}
                        WHERE bom_idx = bom.bom_idx
                            AND bop_start_date <= '".G5_TIME_YMD."'
                        ORDER BY bop_start_date DESC
                        LIMIT 1
                    )
                WHERE bom_idx = '".$bom_idx."' AND bom_status NOT IN ('delete','trash')
    ";
    sql_query($sql,1);

}
}

// get the today's proper price according the date registered in the bom_price table.
if(!function_exists('get_bom_price')){
function get_bom_price($bom_idx) {
	global $g5;

    $sql = "SELECT bop_price
            FROM {$g5['bom_price_table']}
            WHERE bom_idx = '".$bom_idx."'
                AND bop_start_date <= '".G5_TIME_YMD."'
            ORDER BY bop_start_date DESC
            LIMIT 1
    ";
    $row = sql_fetch($sql,1);
    return (int)$row['bop_price'];

}
}


// 생산량 일간 합계 입력
// mms_idx, shift_no, item_no, dta_group, dta_defect, dta_defect_type, stat_date
if(!function_exists('update_output_sum')){
function update_output_sum($arr) {
    global $g5;

    $table_name = 'g5_1_data_output_'.$arr['mms_idx'];
    $mms = get_table_meta('mms', 'mms_idx', $arr['mms_idx']);

    // 일간 sum 합계 입력
    $sum_common = " dta_shf_no = '".$arr['shift_no']."'
                    AND dta_mmi_no = '".$arr['item_no']."'
                    AND dta_group = '".$arr['dta_group']."'
                    AND dta_defect = '".$arr['dta_defect']."'
                    AND dta_defect_type = '".$arr['dta_defect_type']."'
    ";

    $sql = "SELECT SUM(dta_value) AS dta_sum
            FROM {$table_name}
            WHERE {$sum_common}
                AND dta_date = '".$arr['stat_date']."'
    ";
    // echo $sql.'<br>';
    $sum1 = sql_fetch($sql,1); // 일 합계 데이터값 추출
    // print_r2($sum1);


    // 있으면 업데이트, 없으면 생성
    $sql_sum = "SELECT dta_idx
                FROM {$g5['data_output_sum_table']}
                WHERE {$sum_common}
                    AND mms_idx = '".$arr['mms_idx']."'
                    AND dta_date = '".$arr['stat_date']."'
    ";
    // echo $sql_sum.'<br>';
    $sum = sql_fetch($sql_sum,1);
    // 정보 업데이트
    if($sum['dta_idx']) {
        $sql = "UPDATE {$g5['data_output_sum_table']} SET
                    dta_value = '".$sum1['dta_sum']."'
                WHERE {$sum_common}
                    AND mms_idx = '".$arr['mms_idx']."'
                    AND dta_date = '".$arr['stat_date']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='update', mta_value = '".addslashes($sql)."' ");
        $result = sql_query($sql);
    }
    else {
        // Get a mms_item price which is the most nearest one from now back.
        $sql = "SELECT mms_idx, mmi_no, mip_price, mip_start_date
                FROM g5_1_mms_item_price AS mip
                    LEFT JOIN g5_1_mms_item AS mmi ON mmi.mmi_idx = mip.mmi_idx
                WHERE mmi_status NOT IN ('trash','delete')
                    AND mms_idx = '".$arr['mms_idx']."'
                    AND mmi_no = '".$arr['item_no']."'
                ORDER BY mip_start_date DESC
                LIMIT 1
        ";
        $mip1 = sql_fetch($sql,1);

        $sql = " INSERT INTO {$g5['data_output_sum_table']} SET
                    com_idx = '".$mms['com_idx']."'
                    , imp_idx = '".$mms['imp_idx']."'
                    , mms_idx = '".$arr['mms_idx']."'
                    , mmg_idx = '".$g5['mms'][$arr['mms_idx']]['mmg_idx']."'
                    , dta_shf_no = '".$arr['shift_no']."'
                    , dta_mmi_no = '".$arr['item_no']."'
                    , dta_mmi_no_price = '".$mip1['mip_price']."'
                    , dta_group = '".$arr['dta_group']."'
                    , dta_defect = '".$arr['dta_defect']."'
                    , dta_defect_type = '".$arr['dta_defect_type']."'
                    , dta_message = '".$arr['dta_message']."'
                    , dta_date = '".$arr['stat_date']."'
                    , dta_value = '".$sum1['dta_sum']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
        sql_query($sql,1);
    }

}
}


// 통계일자 추출 함수
// mms_idx, time(timestamp)
if(!function_exists('get_output_stat_date')){
function get_output_stat_date($arr) {
    global $g5;

    // 설정값들
    $arr['shf_dt'] = date("Y-m-d H:i:s",$arr['time']);  // 입력일시
    // echo $arr['shf_dt'].'<br>';
    $arr['shf_his'] = date("H:i:s",$arr['time']);  // 입력시간
    $arr['shf_his1'] = sprintf("%06d",preg_replace("/:/","",$arr['shf_his'])); 	// 숫자로만
    // $arr['shf_his1'] = '060000'; 	// 숫자로만

    // order by shf_period_type DESC 이므로 기간설정된 것들이 뒤에 와서 이전 정보를 덮어쓰게 됨
    $sql = "SELECT shf_idx, mms_idx, shf_period_type
                , shf_range_1, shf_range_2, shf_range_3
                , shf_target_1, shf_target_2, shf_target_3
                , shf_start_dt
                , shf_end_dt
            FROM {$g5['shift_table']}
            WHERE shf_status IN ('ok')
                AND mms_idx = '".$arr['mms_idx']."'
                AND shf_start_dt <= '".$arr['shf_dt']."'
                AND shf_end_dt >= '".$arr['shf_dt']."'
            ORDER BY shf_period_type DESC, shf_start_dt
    ";
    // echo $sql.'<br>';
    $rs = sql_query($sql,1);
    for($i=0;$row=sql_fetch_array($rs);$i++) {
        // print_r2($row);

        for($j=1;$j<=4;$j++) {
            $row['range'][$j] = $row['shf_range_'.$j];
            $row['target'][$j] = $row['shf_target_'.$j];

            // 교대 시작~종료 시간 분리 배열
            $row['shift'][$j] = explode("~",$row['range'][$j]);

            $row['shift_start_his'] = sprintf("%06d",preg_replace("/:/","",$row['shift'][1][0])); 	// 1교대 시작시간 숫자로만 (여러군데 비교해야 함)
            // echo $row['shift_start_his'].' 1교대 시작시간<br>';

            // print_r3($j.'교대: '.$row['shift'][$j][0].' ~ '.$row['shift'][$j][1]);                       // ------------------
            // 교대시간 값이 있는 경우만 추출
            if($row['shift'][$j][0] && $row['shift'][$j][1]) {

                // 1교대 전이면 240000을 더해줘야 함, 날짜 범위 조건에 맞추기 위함
                $arr['shf_his2'] = ($arr['shf_his1'] < $row['shift_start_his']) ? $arr['shf_his1'] + 240000
                                    : $arr['shf_his1'];
                // echo $row['shift_start_his'].'<br>';
                // echo $arr['shf_his2'].'<br>';

                $start_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$row['shift'][$j][0])); 	// 숫자로만
                $end_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$row['shift'][$j][1]));		// 숫자로만

                // 교대 범위 추출
                if( $arr['shf_his2'] >= $start_dt1[$j] && $arr['shf_his2'] <= $end_dt1[$j] )
                {
                    // 교대값
                    $shift['shift_no'] = $j;
                    // 통계일자(종료일이 24시가 넘어가면서 해당 날짜가 1교대 전일 경우는 전날자)
                    $shift['stat_date'] = ( $end_dt1[$j] >= 240000 && $arr['shf_his1'] < $row['shift_start_his'] ) ? date("Y-m-d",$arr['time']-86400)
                                            : date("Y-m-d",$arr['time']);
                }

            }
        }
    }
    // 값이 없는 경우 디폴트
    $shift['shift_no'] = ($shift['shift_no']) ?: 1;
    $shift['stat_date'] = ($shift['stat_date']) ?: date("Y-m-d",$arr['time']);  // 입력일시
    // print_r3($shift);

    return $shift;
}
}

// token 체크 판단
if(!function_exists('check_token1')){
function check_token1($token) {

    $str = true;
    $expire_date = 86400*30*6; // 약 6개월 정도

    // 기존 방법 체크, 12자리수 보다 적은 경우, ex) 1099de5drf09
    if( strlen($token) <= 12 ) {
        $to[] = substr($token,0,2);
        $to[] = substr($token,2,2);
        $to[] = substr($token,-2);
        $to[] = substr((string)((int)$to[0]+(int)$to[1]),-2);
        //print_r2($to);
        if($to[2]!=$to[3]) {
            $str = false;
        }
    }
    // 공개키 같은 경우 기간 제한 있음 ex) 2451RNC4xg161355065075
    else {
        $to[] = substr($token,0,2);
        $to[] = substr($token,2,2);
        $to[] = substr($token,-2);
        $to[] = substr((string)((int)$to[0]+(int)$to[1]),-2);
        $to[] = substr($token,10,-2);
        // print_r2($to);
        if($to[2]!=$to[3] || $to[4] < time()-$expire_date) {
            $str = false;
        }
    }
    return $str;
}
}



// make token 함수
if(!function_exists('make_token1')){
function make_token1() {
	// 토큰 생성
	$to[] = rand(10,99);
	$to[] = rand(10,99);
	$to[] = G5_SERVER_TIME;
	$to[] = sprintf("%02d",substr($to[0]+$to[1],-2));
	$token = $to[0].$to[1].random_str(6).$to[2].$to[3];
	//echo $token.'<br>';
    return $token;
}
}


if(!function_exists('random_str')){
function random_str($length) {
    $characters  = "0123456789";
    $characters .= "abcdefghijklmnopqrstuvwxyz";
    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $characters .= "_";

    $string_generated = "";
    $nmr_loops = $length;
    while ($nmr_loops--) {
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string_generated;
}
}


// 접근 체크
// permit = permit or deny
// flag = <, =, >=, <=
if(!function_exists('check_access')){
function check_access($mb_level,$flag='<',$permit='permit') {
	global $member;

    $msg = '접근이 불가능한 페이지입니다. 관리자에게 문의해 주세요.';
    // 접근가능 설정
    if($permit=='permit') {
        if($flag=='<' && $member['mb_level']>=$mb_level) {
            alert($msg);
        }
        else if($flag=='<=' && $member['mb_level']>$mb_level) {
            alert($msg);
        }
        else if($flag=='>' && $member['mb_level']<=$mb_level) {
            alert($msg);
        }
        else if($flag=='>=' && $member['mb_level']<$mb_level) {
            alert($msg);
        }
        else if($flag=='=' && $member['mb_level']!=$mb_level) {
            alert($msg);
        }
    }
    // 접근불가 설정 (디폴트)
    else {
        if($flag=='<' && $member['mb_level']<$mb_level) {
            alert($msg);
        }
        else if($flag=='<=' && $member['mb_level']<=$mb_level) {
            alert($msg);
        }
        else if($flag=='>' && $member['mb_level']>$mb_level) {
            alert($msg);
        }
        else if($flag=='>=' && $member['mb_level']>=$mb_level) {
            alert($msg);
        }
        else if($flag=='=' && $member['mb_level']==$mb_level) {
                    alert($msg);
        }
    }

    return true;

}
}


// number to hangle display
// $mny = 305142809;
// $mny = 2809;
// print_r2(num_to_han($mny)).'<br>';
if(!function_exists('num_to_han')){
function num_to_han($mny){
    $stlen = strlen($mny)-1;
	//숫자를 4단위로 한글 단위를 붙인다.
	$names = array("원","만원","억","조","경"); // 단위의 한글발음 (조 다음으로 계속 추가 가능)
    $nums = str_split($mny); // 숫자를 배열로 분리
    $nums = array_reverse($nums);
    $units = array();
    // 역으로 자리숫자마다 숫자 단위를 붙여서 배열로 만듦
    for($i=0,$m=count($nums);$i<$m;$i++){
	    $units[] = $names[floor($i/4)];
    }
    // print_r2($units);
    $cu = '';
    $str = '';
    $flag = floor($stlen/4)*4;
    // echo $flag.'<br>';
    // 4자리 단위로 flag 기준 범위만 돌면서 값을 생성
    for($i=$flag,$m=count($nums); $i<$m; $i++){
        $arr = $nums[$i];
        // echo $t.'<br>';
        // 단위가 바뀔 때만 단위값을 붙여줌
        if($cu != $units[$i]){
            $unit = $units[$i];
        }
        // 숫자를 역으로 돌면서 앞에다 숫자를 붙여줌
        $str = $arr.$str;
    }
    // 만단위 이상인 경우는 끝에 한자리만 더 추가
    if($flag>3) {
         $str .= '.'.$nums[$flag-1];
    }
    $str = $str ?: 0;
    // return($str);
    return(array($str,$unit));
}
}

// update the latest price info of mms_item table
// post: mmi_idx
if(!function_exists('update_mmi_price')){
function update_mmi_price($mmi_idx) {
	global $g5;

    // get the latest price info and update the mms_item table info.
    $sql = "SELECT mip_price, mip_start_date
            FROM {$g5['mms_item_price_table']}
            WHERE mmi_idx = '".$mmi_idx."'
            ORDER BY mip_start_date DESC LIMIT 1
    ";
    $mip = sql_fetch($sql,1);

    $sql = "UPDATE {$g5['mms_item_table']} SET
                mmi_price = '".$mip['mip_price']."',
                mmi_start_date = '".$mip['mip_start_date']."'
            WHERE mmi_idx = '".$mmi_idx."'
    ";
    sql_query($sql,1);

}
}


// Create table tr (단순한 한줄짜리 tr을 만들 경우에 사용)
// type(input(default), textarea)
// id(필드명), name(필드제목), value(값), value_type(number), required
// width(인풋박스폭), unit(인붓박스끝에단위), help(설명글), none(tr 숨김), colspan
// tr_class, th_class, td_class, tr_style, th_style, td_style, form_style
// -----------------------
// $ar['type'] = 'input';
// $ar['id'] = 'mmi_no';
// $ar['name'] = '기종번호';
// $ar['value'] = $mmi['mmi_no'];
// $ar['value_type'] = '';
// $ar['required'] = 'required';
// $ar['width'] = '';
// $ar['unit'] = '';
// $ar['help'] = 'PLC에서 설정한 생산번호입니다.(디폴트는 0입니다.)';
// $ar['colspan'] = '';
// $ar['th_class'] = '';
// $ar['td_class'] = '';
// $ar['th_style'] = '';
// $ar['td_style'] = '';
// $ar['form_style'] = '';
// $ar['form_script'] = '';
// echo create_tr_input($ar);
// unset($ar);
if(!function_exists('create_tr_input')){
function create_tr_input($arr) {

    if(!$arr['id']||!$arr['name'])
        return false;

    // tr 숨김
    $form_none = ($arr['none']) ? 'display:'.$arr['none'] : '';

    $arr['tr_class'] = $arr['tr_class'] ?: 'tr_'.$arr['id'];

    $ar['type'] = $arr['type'];
    $ar['id'] = $arr['id'];
    $ar['name'] = $arr['name'];
    $ar['value'] = $arr['value'];
    $ar['value_type'] = $arr['value_type'];
    $ar['required'] = $arr['required'];
    $ar['width'] = $arr['width'];
    $ar['unit'] = $arr['unit'];
    $ar['help'] = $arr['help'];
    $ar['colspan'] = $arr['colspan'];
    $ar['th_class'] = $arr['th_class'];
    $ar['td_class'] = $arr['td_class'];
    $ar['th_style'] = $arr['th_style'];
    $ar['td_style'] = $arr['td_style'];
    $ar['form_style'] = $arr['form_style'];
    $ar['form_script'] = $arr['form_script'];
    $td = create_td_input($ar);
    unset($ar);

    $str = '<tr class="'.$arr['tr_class'].'" style="'.$arr['tr_style'].';'.$form_none.';">
            '.$td.'
            </tr>';

    return $str;
}
}


// Create table tr
// type(input, textarea)
// id(필드명), name(필드제목), value(값), value_type(number), required
// width(인풋박스폭), unit(인붓박스끝에단위), help(설명글), colspan
// th_class, td_class, th_style, td_style, form_style, form_script
if(!function_exists('create_td_input')){
function create_td_input($arr) {

    if(!$arr['id']||!$arr['name'])
        return false;

    // 폭
    $form_width = ($arr['width']) ? 'width:'.$arr['width'] : '';
    // 단위
    $form_unit = ($arr['unit']) ? ' '.$arr['unit'] : '';
    // 설명
    $form_help = ($arr['help']) ? ' '.help($arr['help']) : '';
    // 한줄 두항목
    $form_span = ($arr['colspan']) ? ' colspan="'.$arr['colspan'].'"' : '';
    // value_type==number
    $arr['value'] = ($arr['value_type']=='number') ? number_format($arr['value']) : $arr['value'];

    // 각 class
    $arr['th_class'] = $arr['th_class'] ?: 'th_'.$arr['id'];
    $arr['td_class'] = $arr['td_class'] ?: 'td_'.$arr['id'];

    // textarea
    if($arr['type']=='textarea') {
        $item_form = '<textarea name="'.$arr['id'].'" id="'.$arr['id'].'" style="'.$arr['form_style'].';" '.$arr['form_script'].'>'.$arr['value'].'</textarea>';
    }
	// radio
    else if($arr['type']=='radio') {
		$arr['value'] = $arr['value'] ?: 0;	// default
		$arr_yes = array('1','Y'); 	// yes인 경우
		$arr['chk'] = (in_array((string)$arr['value'],$arr_yes)) ? 1 : 0;
		$arr['checked_'.$arr['chk']] = ' checked="checked"';
		$arr_yn = array('N','Y'); 	// Y,N 형태인 경우를 위해서 체크
		$arr['value_yes'] = (in_array((string)$arr['value'],$arr_yn)) ? 'Y': 1;
		$arr['value_no'] = (in_array((string)$arr['value'],$arr_yn)) ? 'N': 0;
		$item_form = '<input type="radio" name="'.$arr['id'].'" value="'.$arr['value_yes'].'" id="'.$arr['id'].'_yes" '.$arr['checked_1'].'>
						<label for="'.$arr['id'].'_yes">예</label>
						<input type="radio" name="'.$arr['id'].'" value="'.$arr['value_no'].'" id="'.$arr['id'].'_no" '.$arr['checked_0'].'>
						<label for="'.$arr['id'].'_no">아니오</label>';
    }
	// checkbox
    else if($arr['type']=='checkbox') {
		$arr['value'] = $arr['value'] ?: 0;	// default
		$arr_yes = array('1','Y'); // Y,N 형태인 경우를 위해서 체크
		$arr['checked'] = (in_array((string)$arr['value'],$arr_yes)) ? ' checked="checked"' : '';
		$arr_yn = array('N','Y'); 	// Y,N 형태인 경우를 위해서 체크
		$arr['value_yes'] = (in_array((string)$arr['value'],$arr_yn)) ? 'Y': 1;
		$arr['value_no'] = (in_array((string)$arr['value'],$arr_yn)) ? 'N': 0;
		if(!in_array((string)$arr['value'],$arr_yn)) {
			$arr['value'] = ($arr['value']) ? 1 : 0;
		}
		$item_form = '<input type="checkbox" name="'.$arr['id'].'" id="'.$arr['id'].'" '.$arr['checked'].'
						onClick="javascript:if(this.checked){this.form.'.$arr['id'].'_value.value=\''.$arr['value_yes'].'\'}else{this.form.'.$arr['id'].'_value.value=\''.$arr['value_no'].'\'}">
						<label for="'.$arr['id'].'">예</label>
					  <input type="hidden" name="'.$arr['id'].'" id="'.$arr['id'].'_value" value="'.$arr['value'].'">';
    }
    else if($arr['type']=='text') {
        $item_form = $arr['value'];
    }
    // 기본 디폴트는 INPUTBOX
    else {
        $item_form = '<input type="text" name="'.$arr['id'].'" id="'.$arr['id'].'" value="'.$arr['value'].'" '.$arr['required'].' '.$arr['readonly'].'
                        class="frm_input '.$arr['required'].' '.$arr['readonly'].'" style="'.$arr['form_style'].';'.$form_width.'" '.$arr['form_script'].'>'.$form_unit;
    }

    $str = '<th scope="row" class="'.$arr['th_class'].'" style="'.$arr['th_style'].';">'.$arr['name'].'</th>
            <td class="'.$arr['td_class'].'" style="'.$arr['td_style'].';" '.$form_span.'>
                '.$form_help.'
                '.$item_form.'
            </td>';

    return $str;
}
}


// Message send_type setting
// array: prefix, com_idx, value
if(!function_exists('set_send_type')){
function set_send_type($arr) {
	global $g5;

    // Get the company info.
    $com = get_table_meta('company','com_idx',$arr['com_idx']);

    $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_send_type']));
    foreach ($set_values as $set_value) {
        list($key, $value) = explode('=', $set_value);
        // 해당 업체 발송 설정을 먼저 체크해서 비활성 표현
        if(!preg_match("/".$key."/i",$com['com_send_type'])) {
            ${"disable_".$key} = ' disabled';
        }
        ${"checked_".$key} = (preg_match("/".$key."/i",$arr['value'])) ? 'checked':'';
        $str .= '<label for="set_send_type_'.$key.'" class="set_send_type" '.${"disable_".$key}.'>
                <input type="checkbox" id="set_send_type_'.$key.'"
                    name="'.$arr['prefix'].'_send_type[]" value="'.$key.'"
                     '.${"checked_".$key}.${"disable_".$key}.'>'.$value.'('.$key.')
            </label>';
    }

    return $str;
}
}


// Seconds to H:M:s 초를 시:분:초
// t = seconds, f = separator
if(!function_exists('sectohis')){
function sectohis($t,$f=':') {
    return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
}
}

// H:M:s to seconds 시:분:초를 초로
// t = seconds, f = separator
if(!function_exists('histosec')){
function histosec($t) {
    // $parsed = date_parse($t);
    // $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
    $seconds = strtotime("1970-01-01 $t UTC");
    return $seconds;
}
}

// mms_idx-column 정보 업데이트
// mms_idx, column
if(!function_exists('dashboard_column_update')){
function dashboard_column_update($arr) {
	global $g5,$member;

	if(!$arr['mms_idx'] || !$arr['column'])
		return false;

	$row1 = sql_fetch(" SELECT * FROM {$g5['member_dash_table']}
                        WHERE mb_id='".$member['mb_id']."'
						    AND com_idx='".$_SESSION['ss_com_idx']."'
						    AND mms_idx='{$arr['mms_idx']}'
						    AND mbd_type='column'
    ");

	// 있으면 UPDATE
	if($row1['mbd_idx']) {
		$sql = " UPDATE {$g5['member_dash_table']} SET mbd_value='{$arr['column']}' WHERE mbd_idx='".$row1['mbd_idx']."' ";
		sql_query($sql,1);
//		echo $sql.'<br>';
	}
	// 없으면 INSERT
	else {
		$sql = " INSERT INTO {$g5['member_dash_table']} SET
                    mb_id='".$member['mb_id']."'
					, com_idx='".$_SESSION['ss_com_idx']."'
					, mms_idx = '{$arr['mms_idx']}'
					, mbd_type='column'
					, mbd_value='{$arr['column']}'
					, mbd_status = 'ok'
	                , mbd_reg_dt='".G5_TIME_YMDHIS."'
        ";
		sql_query($sql,1);
//		echo $sql.'<br>';
		$row1['mbd_idx'] = sql_insert_id();
	}

    return $row1['mbd_idx'];
}
}

// 사용자단 hlep 함수가 없어서 추가함
if(!function_exists('help2')){
function help2($help="") {
    global $g5;
    $str  = '<span class="txt_help">'.str_replace("\n", "<br>", $help).'</span>';
    return $str;
}
}

// mms 설비 대표 이미지 추출
// [mms_idx], [img_width], [img_height]
if(!function_exists('get_mms_image')){
function get_mms_image($ar) {
    global $g5;

    // print_r2($ar);
    $ar['img_width'] = $ar['img_width'] ?: 100;
    $ar['img_height'] = $ar['img_height'] ?: 100;

    // mms_img 타입중에서 대표 이미지 한개만
    $sql = "SELECT * FROM {$g5['file_table']}
            WHERE fle_db_table = 'mms' AND fle_db_id = '".$ar['mms_idx']."'
                AND fle_type = 'mms_img'
                AND fle_sort = 0
            ORDER BY fle_idx DESC
            LIMIT 1
    ";
    // echo $sql.'<br>';
    $row1 = sql_fetch($sql,1);
    // print_r2($row1);
    if( $row1['fle_name'] && is_file(G5_PATH.$row1['fle_path'].'/'.$row1['fle_name']) ) {
        $img = $arr[$row1['fle_type']][$row1['fle_sort']]; // 변수명 좀 짧게
        $img['thumbnail'] = thumbnail($row1['fle_name'],
                        G5_PATH.$row1['fle_path'], G5_PATH.$row1['fle_path'],
                        $ar['img_width'], $ar['img_height'],
                        false, true, 'center', true, $um_value='85/3.4/15');	// is_create, is_crop, crop_mode
    }
    else {
        $arr['thumbnail'] = 'default.png';
        $row1['fle_path'] = '/data/mms_img';	// 디폴트 경로 결정해야 합니다.
    }
    $arr['src'] = G5_URL.$row1['fle_path'].'/'.$img['thumbnail'];
    $arr['img'] = '<img src="'.$arr['src'].'" width="'.$ar['img_width'].'" height="'.$ar['img_height'].'">';
    // print_r2($arr);

    return $arr;
}
}



// 배너출력
if(!function_exists('display_banner2')){
function display_banner2($position, $skin='')
{
    global $g5,$default;

    if (!$position) $position = '왼쪽';
    if (!$skin) $skin = 'boxbanner.skin.php';

    $skin_path = G5_THEME_PATH.'/skin/shop/'.$default['de_shop_skin'].'/'.$skin;
    $skin_url = G5_THEME_URL.'/skin/shop/'.$default['de_shop_skin'];
    if(G5_IS_MOBILE) {
        $skin_path = G5_THEME_PATH.'/mobile/skin/shop/'.$default['de_shop_skin'].'/'.$skin;
        $skin_url = G5_THEME_URL.'/mobile/skin/shop/'.$default['de_shop_skin'];
    }
//    echo $skin_url;

    if(file_exists($skin_path)) {
        // 접속기기
        $sql_device = " and ( bn_device = 'both' or bn_device = 'pc' ) ";
        if(G5_IS_MOBILE)
            $sql_device = " and ( bn_device = 'both' or bn_device = 'mobile' ) ";

        // 배너 출력
        $sql = " select * from {$g5['g5_shop_banner_table']} where '".G5_TIME_YMDHIS."' between bn_begin_time and bn_end_time $sql_device and bn_position = '$position' order by bn_order, bn_id desc ";
        $result = sql_query($sql);

        include $skin_path;
    } else {
        echo '<p>'.str_replace(G5_PATH.'/', '', $skin_path).'파일이 존재하지 않습니다.</p>';
    }
}
}


// 고객-업체 최신 정보를 얻는다.
if(!function_exists('get_company_member')){
function get_company_member($mb_id, $com_idx) {
    global $g5;
    $sql = "SELECT * FROM {$g5['company_member_table']}
            WHERE mb_id = TRIM('$mb_id')
                AND com_idx = '".$com_idx."'
                AND cmm_status IN ('ok')
            ORDER BY cmm_idx DESC LIMIT 1
    ";
    $row = sql_fetch($sql);
    $mb1 = get_table_meta('member','mb_id',$row['mb_id']);
    $row['cmm_name_rank'] = $mb1['mb_name'].' '.$g5['set_mb_ranks_value'][$row['cmm_title']]; // 홍길동 대리

    return $row;
}
}


// 계약 승인 요청 문자 발송
if(!function_exists('sms_contract_certify')) {
function sms_contract_certify($arr) {
    global $g5;
    //print_r2($arr);

    $to_number = $arr['mb']['mb_hp'];
    $from_number = $g5['board']['setting2_com_hp_callback'][$arr['od']['od_company']];

    $content = $g5['setting']['set_contract_sms_content'];
    $content = preg_replace("/{법인명}/", $g5['board']['setting2_name'][$arr['od']['od_company']], $content);
    $content = preg_replace("/{이름}/", $arr['mb']['mb_name'], $content);
    $content = preg_replace("/{업체명}/", $arr['com']['com_name'], $content);
    $content = preg_replace("/{회원아이디}/", $arr['com']['mb_id'], $content);
    $content = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $content);
    $content = preg_replace("/{CONTRACT_URL}/", G5_USER_URL.'/od1.php?'.$arr['od']['od_id'], $content);

    // 엔씨티 서버를 이용한 SMS 문자 발송
    //sms_nct(array("to_number"=>$to_number
    //                ,"from_number"=>$from_number
    //                ,"content"=>$content
    //));
    sms_woogle(array("to_number"=>$to_number
                    ,"from_number"=>$from_number
                    ,"content"=>$content
    ));

}
}


// 계약 확인 메일 발송, 함수 호출하기 전 /lib/mailer.lib.php 인클루드되어 있어야 함
if(!function_exists('email_contract_confirm')) {
function email_contract_confirm($arr) {
    global $g5;

    //오늘
    $today2 = date("Y년 m월 d일");

    $subject = $g5['setting']['set_contract_email_confirm_subject'];
    $subject = preg_replace("/{법인명}/", $g5['board']['setting2_name'][$arr['od']['od_company']], $subject);
    $subject = preg_replace("/{이름}/", $arr['mb']['mb_name'], $subject);
    $subject = preg_replace("/{업체명}/", $arr['com']['com_name'], $subject);
    $subject = preg_replace("/{회원아이디}/", $arr['com']['mb_id'], $subject);
    $subject = preg_replace("/{이메일}/", $arr['mb']['mb_email'], $subject);

    $content = $g5['setting']['set_contract_email_confirm'];
    $content = preg_replace("/{법인명}/", $g5['board']['setting2_name'][$arr['od']['od_company']], $content);
    $content = preg_replace("/{이름}/", $arr['mb']['mb_name'], $content);
    $content = preg_replace("/{업체명}/", $arr['com']['com_name'], $content);
    $content = preg_replace("/{회원아이디}/", $arr['com']['mb_id'], $content);
    $content = preg_replace("/{이메일}/", $arr['mb']['mb_email'], $content);
    $content = preg_replace("/{년월일}/", $today2, $content);
    $content = preg_replace("/{담당자}/", $arr['mb1']['mb_name'], $content);
    $content = preg_replace("/{담당자이메일}/", $arr['mb1']['mb_email'], $content);
    $content = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $content);
    $content = preg_replace("/{CONTRACT_URL}/", '<a href="'.G5_USER_URL.'/od1.php?'.$arr['od']['od_id'].'" target="_blank">계약확인 [클릭]</a>', $content);
    //$content = preg_replace("/{CONTRACT_URL}/", '<a href="'.G5_USER_URL.'/od1.php?'.$arr['od']['od_id'].'" target="_blank">계약확인&승인 '.G5_USER_URL.'/od1.php?'.$arr['od']['od_id'].'</a>', $content);
    //$content = $content . "<hr size=0><p><span style='font-size:9pt; font-familye:굴림'>▶ 더 이상 정보 수신을 원치 않으시면 [<a href='".G5_BBS_URL."/email_stop.php?mb_id={$mb_id}&amp;mb_md5={$mb_md5}' target='_blank'>수신거부</a>] 해 주십시오.</span></p>";

    //mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $arr['mb']['mb_email'], $subject, $content, 1);
    mailer($g5['board']['setting2_name'][$arr['od']['od_company']], $g5['board']['setting2_email'][$arr['od']['od_company']], $arr['mb']['mb_email'], $subject, $content, 1);
}
}

// 계약 승인 요청 메일 발송, 함수 호출하기 전 /lib/mailer.lib.php 인클루드되어 있어야 함
if(!function_exists('email_contract_certify')) {
function email_contract_certify($arr) {
    global $g5;
    //print_r2($arr);

    //오늘
    $today2 = date("Y년 m월 d일");

    $subject = $g5['setting']['set_contract_email_subject'];
    $subject = preg_replace("/{법인명}/", $g5['board']['setting2_name'][$arr['od']['od_company']], $subject);
    $subject = preg_replace("/{이름}/", $arr['mb']['mb_name'], $subject);
    $subject = preg_replace("/{업체명}/", $arr['com']['com_name'], $subject);
    $subject = preg_replace("/{회원아이디}/", $arr['com']['mb_id'], $subject);
    $subject = preg_replace("/{이메일}/", $arr['mb']['mb_email'], $subject);

    $content = $g5['setting']['set_contract_email_content'];
    $content = preg_replace("/{법인명}/", $g5['board']['setting2_name'][$arr['od']['od_company']], $content);
    $content = preg_replace("/{이름}/", $arr['mb']['mb_name'], $content);
    $content = preg_replace("/{업체명}/", $arr['com']['com_name'], $content);
    $content = preg_replace("/{회원아이디}/", $arr['com']['mb_id'], $content);
    $content = preg_replace("/{이메일}/", $arr['mb']['mb_email'], $content);
    $content = preg_replace("/{년월일}/", $today2, $content);
    $content = preg_replace("/{담당자}/", $arr['mb1']['mb_name'], $content);
    $content = preg_replace("/{담당자이메일}/", $arr['mb1']['mb_email'], $content);
    $content = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $content);
    $content = preg_replace("/{CONTRACT_URL}/", '<a href="'.G5_USER_URL.'/od1.php?'.$arr['od']['od_id'].'" target="_blank">계약확인&승인 [클릭]</a>', $content);

    //mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $arr['mb']['mb_email'], $subject, $content, 1);
    mailer($g5['board']['setting2_name'][$arr['od']['od_company']], $g5['board']['setting2_email'][$arr['od']['od_company']], $arr['mb']['mb_email'], $subject, $content, 1);
}
}


// 결제정보 상세 내용 추출
if(!function_exists('get_opa_pay_info')){
function get_opa_pay_info($row,$flag=0) {
    global $g5;

    $pay_info = '<span style="color:gray;">'.$g5['set_opa_types_value'][$row['opa_type']].'</span>';
    if( $row['opa_type'] == 'card' ) {
        $row['opa_card_installment'] = ($row['opa_card_installment']=='0') ? '일시불' : $row['opa_card_installment'].'개월';	// 할부기간
        // 카드번호는 일주일 지나면 안 보임
        $row['opa_card_no'] = (substr($row['opa_update_dt'],0,10) < date("Y-m-d", G5_SERVER_TIME-86400*6)) ? card_number_hidden($row['opa_card_no']) : $row['opa_card_no'];
        //$pay_info .= '<br>'.substr($row['opa_card_no'],0,8).'... ('.$row['opa_card_company'].', 유효기간: '.$row['opa_card_valid'].')';
        $pay_info .= '<br>'.$row['opa_card_no'].' ('.$row['opa_card_company'].', 유효기간: '.$row['opa_card_valid'].', '.$row['opa_card_installment'].')';
        $pay_info .= '<br>소유자:'.$row['opa_card_owner'];
    }
    else if( $row['opa_type'] == 'bank' ) {
        $pay_info .= '<br>'.cut_str($row['opa_bank_name'],32);
        $pay_info .= '<br>입금자: '.$row['opa_deposit_name'];
        $pay_info .= ($row['opa_doc_dt']!='0000-00-00 00:00:00')?'<br>'.$g5['set_doc_types_value'][$row['opa_doc_type']].' (<span style="font-size:0.8em;">'.$row['opa_doc_dt'].'</span>)' : '';
        $pay_info .= '<br>세금계산서발행방법: '.$g5['set_doc_issue_types_value'][$row['opa_doc_issue_type']];
    }
    else if( $row['opa_type'] == 'cms' ) {
        $pay_info .= '<br>CMS 출금일: '.$row['opa_cms_date'].' 일';
    }
    else if( $row['opa_type'] == 'customer' ) {
        $pay_info .= ($row['opa_customer_type']) ? ' <span style="color:gray;">('.$g5['set_settle_customer_type_value'][$row['opa_customer_type']].')</span>' : '';
        if($row['opa_customer_type']=='card') {
            $row['opa_card_installment'] = ($row['opa_card_installment']=='0') ? '일시불' : $row['opa_card_installment'].'개월';	// 할부기간
            // 카드번호는 일주일 지나면 안 보임
            $row['opa_card_no'] = (substr($row['opa_update_dt'],0,10) < date("Y-m-d", G5_SERVER_TIME-86400*6)) ? card_number_hidden($row['opa_card_no']) : $row['opa_card_no'];
            if($row['opa_card_valid']) {
                $pay_info .= '<br>'.$row['opa_card_no'].' ('.$row['opa_card_company'].', 유효기간: '.$row['opa_card_valid'].', '.$row['opa_card_installment'].')';
                $pay_info .= '<br>소유자:'.$row['opa_card_owner'];
            }
        }
        else if($row['opa_customer_type']=='bank') {
        }
    }
    else if( $row['opa_type'] == 'etc' ) {
        $pay_info .= ($row['opa_doc_dt']!='0000-00-00 00:00:00')?'<br>'.$g5['set_doc_types_value'][$row['opa_doc_type']].' (<span style="font-size:0.8em;">'.$row['opa_doc_dt'].'</span>)' : '';
    }
    // 취소금액이 있는 경우
    $pay_info .= ($row['opa_price_cancel']>0)?'<br>취소금액: '.number_format($row['opa_price_cancel']) : '';
    // 취소가 있는 경우
    $pay_info .= ($row['opa_pay_cancel_dt']!='0000-00-00 00:00:00')?'<br>취소일: '.$row['opa_pay_cancel_dt'] : '';

	return $pay_info;

}
}


// 주문의 금액, 배송비 과세금액 등의 정보를 가져옴
if(!function_exists('get_order_info2')){
function get_order_info2($od_id)
{
    global $g5;

    // 주문정보
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);

    if(!$od['od_id'])
        return false;

    $info = array();

    // 장바구니 주문금액정보
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(cp_price) as coupon,
                    SUM( IF( ct_notax = 0, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as tax_mny,
                    SUM( IF( ct_notax = 1, ( IF(io_type = 1, (io_price * ct_qty), ( (ct_price + io_price) * ct_qty) ) - cp_price ), 0 ) ) as free_mny
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id'
                  and ct_status IN ( '주문', '결제완료', '준비', '배송', '광고중', '광고종료', '촬영대기', '촬영완료', '등록대기', '완료' )
	";
	//echo $sql.'<br>';
    $sum = sql_fetch($sql,1);

    $cart_price = $sum['price'];
    $cart_coupon = $sum['coupon'];

    // 배송비
    $send_cost = get_sendcost($od_id);

    $od_coupon = $od_send_coupon = 0;

    if($od['mb_id']) {
        // 주문할인 쿠폰
        $sql = " select a.cp_id, a.cp_type, a.cp_price, a.cp_trunc, a.cp_minimum, a.cp_maximum
                    from {$g5['g5_shop_coupon_table']} a right join {$g5['g5_shop_coupon_log_table']} b on ( a.cp_id = b.cp_id )
                    where b.od_id = '$od_id'
                      and b.mb_id = '{$od['mb_id']}'
                      and a.cp_method = '2' ";
        $cp = sql_fetch($sql);

        $tot_od_price = $cart_price - $cart_coupon;

        if($cp['cp_id']) {
            $dc = 0;

            if($cp['cp_minimum'] <= $tot_od_price) {
                if($cp['cp_type']) {
                    $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
                } else {
                    $dc = $cp['cp_price'];
                }

                if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                    $dc = $cp['cp_maximum'];

                if($tot_od_price < $dc)
                    $dc = $tot_od_price;

                $tot_od_price -= $dc;
                $od_coupon = $dc;
            }
        }

        // 배송쿠폰 할인
        $sql = " select a.cp_id, a.cp_type, a.cp_price, a.cp_trunc, a.cp_minimum, a.cp_maximum
                    from {$g5['g5_shop_coupon_table']} a right join {$g5['g5_shop_coupon_log_table']} b on ( a.cp_id = b.cp_id )
                    where b.od_id = '$od_id'
                      and b.mb_id = '{$od['mb_id']}'
                      and a.cp_method = '3' ";
        $cp = sql_fetch($sql);

        if($cp['cp_id']) {
            $dc = 0;
            if($cp['cp_minimum'] <= $tot_od_price) {
                if($cp['cp_type']) {
                    $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
                } else {
                    $dc = $cp['cp_price'];
                }

                if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                    $dc = $cp['cp_maximum'];

                if($dc > $send_cost)
                    $dc = $send_cost;

                $od_send_coupon = $dc;
            }
        }
    }

    // 과세, 비과세 금액정보
    $tax_mny = $sum['tax_mny'];
    $free_mny = $sum['free_mny'];

    if($od['od_tax_flag']) {
        $tot_tax_mny = ( $tax_mny + $send_cost + $od['od_send_cost2'] )
                       - ( $od_coupon + $od_send_coupon + $od['od_receipt_point'] );
        if($tot_tax_mny < 0) {
            $free_mny += $tot_tax_mny;
            $tot_tax_mny = 0;
        }
    } else {
        $tot_tax_mny = ( $tax_mny + $free_mny + $send_cost + $od['od_send_cost2'] )
                       - ( $od_coupon + $od_send_coupon + $od['od_receipt_point'] );
        $free_mny = 0;
    }

    $od_tax_mny = round($tot_tax_mny / 1.1);
    $od_vat_mny = $tot_tax_mny - $od_tax_mny;
    $od_free_mny = $free_mny;

    // 장바구니 취소금액 정보
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id'
                  and ct_status IN ( '취소', '반품', '품절' ) ";
    $sum = sql_fetch($sql);
    $cancel_price = $sum['price'];

    // 장바구니 주문 총상품금액 (주문금액 + 취소금액)
    $od_cart_price = $cart_price + $cancel_price;

	// 취소금액 재사용 금액이 있으면 미수금에 반영한다.
	$ct_refund_price_used = ct_refund_price_used($od_id);

    // 미수금액 (환불 금액은 따로 표현합니다. 여기서는 계산 제외!)
	$od_misu = $od_cart_price - $od['od_receipt_price'] - $ct_refund_price_used;

    // 결과처리
    $info['od_cart_price']      = $od_cart_price;
    $info['od_send_cost']       = $send_cost;
    $info['od_coupon']          = $od_coupon;
    $info['od_send_coupon']     = $od_send_coupon;
    $info['od_cart_coupon']     = $cart_coupon;
    $info['od_tax_mny']         = $od_tax_mny;
    $info['od_vat_mny']         = $od_vat_mny;
    $info['od_free_mny']        = $od_free_mny;
    $info['od_cancel_price']    = $cancel_price;
    $info['od_misu']            = $od_misu;

    return $info;
}
}


// 주문시 사이트생성
if(!function_exists('insert_site')){
function insert_site($ct_id) {
    global $g5;

    if(!$ct_id)
        return;

    // 사이트 정보가 존재하면 return
    $sql = " SELECT sit_idx FROM {$g5['site_table']} WHERE sit_status NOT IN ('trash') AND ct_id = '".$ct_id."' ";
    $sit = sql_fetch($sql,1);
    if($sit['sit_idx'])
        return;


    // 장바구니, 상품 정보
    $ct = get_table_meta('g5_shop_cart','ct_id',$ct_id,'shop_cart');
    $it = get_table_meta('g5_shop_item','it_id',$ct['it_id'],'shop_item');

    // 기본 2년후로 설정
    $yr2 = sql_fetch(" SELECT DATE_ADD(now(), INTERVAL +2 YEAR) AS year2 ");

    // 공통 퀴리문
    $sql_common = "	ct_id					= '".$ct['ct_id']."'
                    , com_idx               = '".$ct['com_idx']."'
                    , com_name            	= '".$ct['com_name']."'
                    , mb_id                 = '".$ct['mb_id']."'
                    , mb_id_saler           = '".$ct['mb_id_saler']."'
                    , trm_idx_department    = '".$ct['trm_idx_department']."'
                    , sit_name         	    = '".$ct['com_name']."'
                    , sit_type             	= '".$it['it_sit_type']."'
                    , sit_disk_max			= '".$g5['setting']['set_sit_disk']."'
                    , sit_traffic_max      	= '".$g5['setting']['set_sit_traffic']."'
                    , sit_memo      		= '".$ct['sit_memo']."'
                    , sit_start_date    	= '".G5_TIME_YMDHIS."'
                    , sit_expire_date    	= '".$yr2['year2']."'
                    , sit_work_status 		= 'pending'
                    , sit_status 			= 'pending'
    ";
    $sql = "INSERT INTO {$g5['site_table']} SET
                sit_reg_dt = '".G5_TIME_YMDHIS."'
                , sit_update_dt = '".G5_TIME_YMDHIS."'
            ,{$sql_common}
    ";
    //echo $sql.'<br>';
    sql_query($sql,1);
    $sit_idx = sql_insert_id();

    // 부모값 업데이트
    $sit_idx_parent = ($w=="t") ? $sit['sit_idx'] : $sit_idx;
    $sql = " UPDATE {$g5['site_table']} SET sit_idx_parent = '".$sit_idx_parent."' WHERE sit_idx = '".$sit_idx."' ";
    sql_query($sql,1);

    // 메타 추가정보 업데이트
    $ar['mta_db_table'] = 'site';
    $ar['mta_db_id'] = $sit_idx;
    $ar['mta_key'] = 'sit_setting_price';
    $ar['mta_value'] = $g5['setting']['set_setting_price'];
    meta_update($ar);
    unset($ar);

    $ar['mta_db_table'] = 'site';
    $ar['mta_db_id'] = $sit_idx;
    $ar['mta_key'] = 'sit_make_price';
    $ar['mta_value'] = $g5['setting']['set_make_price'];
    meta_update($ar);
    unset($ar);

    $ar['mta_db_table'] = 'site';
    $ar['mta_db_id'] = $sit_idx;
    $ar['mta_key'] = 'sit_day_price';
    $ar['mta_value'] = $g5['setting']['set_day_price'];
    meta_update($ar);
    unset($ar);

    return true;
}
}

// 취소 재반영 금액 총합계 추출
if(!function_exists('ct_refund_price_used')){
function ct_refund_price_used($od_id) {
    global $g5;

	if(!$od_id)
		return;

	$sql = "SELECT SUM(IF(ct_refund_use_yn = 1, ct_refund_price, 0)) AS ct_refund_price_used_total
			FROM {$g5['g5_shop_cart_table']}
			WHERE od_id = '".$od_id."'
				AND ct_status IN ( '취소', '반품', '품절' )
	";
	//echo $sql.'<br>';
	$ct1 = sql_fetch($sql,1);

	return $ct1['ct_refund_price_used_total'];

}
}


// 결제정보(order_payment) 테이블 업데이트
// od_receipt_time(최종결제승인일), od_receipt_price(결제금액 총합계)
if(!function_exists('order_price_update')){
function order_price_update($od_id, $is_update=0) {
    global $g5;

	$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} where od_id = '".$od_id."' ");

	$sql = "	SELECT sum(opa_price) AS opa_sum
					, max(opa_pay_dt) AS opa_receipt_time
				FROM {$g5['order_payment_table']}
				WHERE od_id = '".$od_id."'
					AND opa_status IN ('ok')
	";
	$opa1 = sql_fetch($sql,1);

	// 디비 업데이트
	if($is_update) {
		// 취소금액 재사용 금액이 있으면 미수금에 반영한다.
		//$ct_refund_price_used = ct_refund_price_used($od_id);

		// 미수금액
		$od_misu = ( $od['od_cart_price'] - $od['od_cancel_price'] )
				   - ( $opa1['opa_sum'] - $od['od_refund_price'] )
				   - $ct_refund_price_used;

		// 미수가 0이고 상태가 주문이었다면 결제완료로 변경
		if($od_misu == 0 && $od['od_status'] == '주문') {
			$od_status = '결제완료';
			$cart_status = true;
			$cart_status = false;	// 개별 처리하게 바꾸지 마세요.
		}
		else
			$od_status = $od['od_status'];

	   // 미수금 정보 다시 재반영
		$sql = "	UPDATE {$g5['g5_shop_order_table']} SET
						od_receipt_price 	= '".$opa1['opa_sum']."',
						od_misu 	= '$od_misu',
						od_status  	= '$od_status'
					WHERE od_id = '$od_id'
		";
		//echo $sql.'<br>';
		sql_query($sql,1);
		//echo $sql.'<br>';

	}

	return array(
		'od_receipt_price'=>$opa1['opa_sum']
		, 'od_receipt_time'=>$opa1['opa_receipt_time']
	);

}
}


// 회원 레이어 - 원본 함수는 super일 때만 회원정보수정, 포인트관리가 나와서 수정함
if(!function_exists('get_sideview2')){
function get_sideview2($mb_id, $name='', $email='', $homepage='', $memo_yn=0, $formmail_yn=0, $profile_yn=0)
{
    global $config;
    global $g5;
    global $bo_table, $sca, $is_admin, $member;

    $email_enc = new str_encrypt();
    $email = $email_enc->encrypt($email);
    $homepage = set_http(clean_xss_tags($homepage));

    $name     = get_text($name, 0, true);
    $email    = get_text($email);
    $homepage = get_text($homepage);

    $tmp_name = "";
    if ($mb_id) {
        //$tmp_name = "<a href=\"".G5_BBS_URL."/profile.php?mb_id=".$mb_id."\" class=\"sv_member\" title=\"$name 자기소개\" target=\"_blank\" onclick=\"return false;\">$name</a>";
        $tmp_name = '<a href="'.G5_BBS_URL.'/profile.php?mb_id='.$mb_id.'" class="sv_member" title="'.$name.' 자기소개" target="_blank" onclick="return false;">';

        if ($config['cf_use_member_icon']) {
            $mb_dir = substr($mb_id,0,2);
            $icon_file = G5_DATA_PATH.'/member/'.$mb_dir.'/'.$mb_id.'.gif';

            if (file_exists($icon_file)) {
                $width = $config['cf_member_icon_width'];
                $height = $config['cf_member_icon_height'];
                $icon_file_url = G5_DATA_URL.'/member/'.$mb_dir.'/'.$mb_id.'.gif';
                $tmp_name .= '<img src="'.$icon_file_url.'" width="'.$width.'" height="'.$height.'" alt="">';

                if ($config['cf_use_member_icon'] == 2) // 회원아이콘+이름
                    $tmp_name = $tmp_name.' '.$name;
            } else {
                  $tmp_name = $tmp_name." ".$name;
            }
        } else {
            $tmp_name = $tmp_name.' '.$name;
        }
        $tmp_name .= '</a>';

        $title_mb_id = '['.$mb_id.']';
    } else {
        if(!$bo_table)
            return $name;

        $tmp_name = '<a href="'.G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;sca='.$sca.'&amp;sfl=wr_name,1&amp;stx='.$name.'" title="'.$name.' 이름으로 검색" class="sv_guest" onclick="return false;">'.$name.'</a>';
        $title_mb_id = '[비회원]';
    }

    $str = "<span class=\"sv_wrap\">\n";
    $str .= $tmp_name."\n";

    $str2 = "<span class=\"sv\">\n";
    if($mb_id && $memo_yn)
        $str2 .= "<a href=\"".G5_BBS_URL."/memo_form.php?me_recv_mb_id=".$mb_id."\" onclick=\"win_memo(this.href); return false;\">쪽지보내기</a>\n";
    if($email && $formmail_yn)
        $str2 .= "<a href=\"".G5_BBS_URL."/formmail.php?mb_id=".$mb_id."&amp;name=".urlencode($name)."&amp;email=".$email."\" onclick=\"win_email(this.href); return false;\">메일보내기</a>\n";
    if($homepage)
        $str2 .= "<a href=\"".$homepage."\" target=\"_blank\">홈페이지</a>\n";
    if($mb_id && $profile_yn)
        $str2 .= "<a href=\"".G5_BBS_URL."/profile.php?mb_id=".$mb_id."\" onclick=\"win_profile(this.href); return false;\">자기소개</a>\n";
    if($bo_table) {
        if($mb_id)
            $str2 .= "<a href=\"".G5_BBS_URL."/board.php?bo_table=".$bo_table."&amp;sca=".$sca."&amp;sfl=mb_id,1&amp;stx=".$mb_id."\">아이디로 검색</a>\n";
        else
            $str2 .= "<a href=\"".G5_BBS_URL."/board.php?bo_table=".$bo_table."&amp;sca=".$sca."&amp;sfl=wr_name,1&amp;stx=".$name."\">이름으로 검색</a>\n";
    }
    if($mb_id)
        $str2 .= "<a href=\"".G5_BBS_URL."/new.php?mb_id=".$mb_id."\">전체게시물</a>\n";
    if($member['mb_level'] >= 8 && $mb_id) {
        $str2 .= "<a href=\"".G5_ADMIN_URL."/member_form.php?w=u&amp;mb_id=".$mb_id."\" target=\"_blank\">회원정보변경</a>\n";
        $str2 .= "<a href=\"".G5_ADMIN_URL."/point_list.php?sfl=mb_id&amp;stx=".$mb_id."\" target=\"_blank\">포인트내역</a>\n";
    }
    $str2 .= "</span>\n";
    $str .= $str2;
    $str .= "\n<noscript class=\"sv_nojs\">".$str2."</noscript>";

    $str .= "</span>";

    return $str;
}
}



if(!function_exists('print_item_options2')){
function print_item_options2($it_id, $cart_id)
{
    global $g5;

    $sql = " select ct_option, ct_qty, io_price
                from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$cart_id' order by io_type asc, ct_id asc ";
    $result = sql_query($sql);

    $str = '';
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0)
            $str .= '<ul>'.PHP_EOL;
        $price_plus = '';
        if($row['io_price'] >= 0)
            $price_plus = '+';

		// 옵션가격이 0 보다 큰 경우만 표현함
		if($row['io_price'] > 0)
			$price_display[$i] = ' ('.$price_plus.display_price($row['io_price']).')';

		$str .= '<li>'.get_text($row['ct_option']).' '.$row['ct_qty'].'개'.$price_display[$i].'</li>'.PHP_EOL;
    }

    if($i > 0)
        $str .= '</ul>';

    return $str;
}
}


// 조직코드가 변경된 경우 처리할 함수
if(!function_exists('department_change')){
function department_change($mb_id, $dept1, $dept2) {
    global $g5;

	// 전부다 값이 있어야 함
	if(!$mb_id||!$dept1||!$dept2)
		return;

	// 이전코드랑 값이 같으면 리턴
	if($dept1==$dept2)
		return;

//	// 업체 연결 코드 전부 업데이트
//	$sql = "	UPDATE {$g5['company_member_table']}
//					SET trm_idx_department = '".$dept2."'
//				WHERE mb_id_saler = '".$mb_id."'
//	";
//    sql_query($sql,1);
//
//	// 모든 게시판(gr_id=intra) 조직 코드(wr_7)를 수정
//	$sql = " SELECT bo_table FROM {$g5['board_table']} WHERE gr_id = 'intra' ";
//    $rs = sql_query($sql);
//    for($i=0;$row=sql_fetch_array($rs);$i++) {
//        //echo $row['bo_table'].'<br>';
//        $write_table = $g5['write_prefix'].$row['bo_table'];
//        $sql = "UPDATE ".$write_table." SET
//                    wr_7 = '".$dept2."'
//                WHERE wr_6 = '".$mb_id."' AND wr_7 = '".$dept1."'
//        ";
//        sql_query($sql,1);
//        //echo $sql.'<br>';
//    }
//
//	// 모든 신청항목 조직코드 수정(g5_shop_order)
//    $sql = "UPDATE {$g5['g5_shop_order_table']} SET
//                trm_idx_department = '".$dept2."'
//            WHERE mb_id_saler = '".$mb_id."' AND trm_idx_department = '".$dept1."'
//    ";
//    sql_query($sql,1);
//    //echo $sql.'<br>';
//
//	// 모든 신청상품 조직코드 수정(g5_shop_cart)
//    $sql = "UPDATE {$g5['g5_shop_cart_table']} SET
//                trm_idx_department = '".$dept2."'
//            WHERE mb_id_saler = '".$mb_id."' AND trm_idx_department = '".$dept1."'
//    ";
//    sql_query($sql,1);
//    //echo $sql.'<br>';
//
//	// 모든 매출의 조직코드 수정(g5_1_sales)
//    $sql = "UPDATE {$g5['sales_table']} SET
//                trm_idx_department = '".$dept2."'
//                , sls_department_name = '".$g5['department_name'][$dept2]."'
//            WHERE mb_id_saler = '".$mb_id."' AND trm_idx_department = '".$dept1."'
//    ";
//    sql_query($sql,1);
//    //echo $sql.'<br>';

	return true;
}
}


// 내 소속 조직을 SELECT 형식으로 얻음
if(!function_exists('get_dept_select')){
function get_dept_select($trm_idx=0,$sub_menu,$select_type='form')
{
    global $g5,$auth,$member,$department_form_options,$department_select_options;

    // form의 select 박스이면 <option value='20'></option>과 같은 특정 trm_idx 한개
    // 리스트 페이지의 조직 select에서는 <option value='1,43,20,35'></option>과 같은 trm_idx 여러개
    if(!$select_type)
        $select_type = 'select';

    // 삭제 권한이 있고 모든법인 접근 권한이 있는 경우는 전부
    if(!auth_check($auth[$sub_menu],'d',1) && $member['mb_firm_yn'] ) {
        return ${'department_'.$select_type.'_options'};
    }

    if(${'department_'.$select_type.'_options'}) {
        // 기본적으로는 나의 그룹 조직만 표현
        for($i=0; $i<sizeof($g5['department']); $i++) {
            if(preg_match("/".$g5['department_name'][$g5['department_uptop_idx'][$member['mb_2']]]."/", $g5['department'][$i]['up_names'])) {
                if($select_type=='form')
                    $str .= '<option value="'.$g5['department'][$i]['term_idx'].'"';
                else
                    $str .= '<option value="'.$g5['department'][$i]['down_idxs'].'"';
                if ($k1 == $selected)
                    $str .= ' selected="selected"';
                $str .= ">".$g5['department'][$i]['up_names']."</option>\n";
            }
        }
    }

    return $str;
}
}


// 직책, 직급을 SELECT 형식으로 얻음
if(!function_exists('get_set_options_select')){
function get_set_options_select($set_variable, $start=0, $end=200, $selected="",$sub_menu)
{
    global $g5,$auth;
    // 삭제 권한이 있으면 전부
    if(!auth_check($auth[$sub_menu],'d',1)) {
        return $g5[$set_variable.'_value_options'];
    }

    if(is_array($g5[$set_variable.'_value'])) {
        foreach ($g5[$set_variable.'_value'] as $k1=>$v1) {
            if($k1 >= $start && $k1 <= $end) {
                $str .= '<option value="'.$k1.'"';
                if ($k1 == $selected)
                    $str .= ' selected="selected"';
                $str .= ">{$v1}</option>\n";
            }
        }
    }

    return $str;
}
}


// 추천인 아이디들이 정상적인지 체크
if(!function_exists('mb_recommend_check')){
function mb_recommend_check($mb_ids) {
    global $g5;

	// 전부다 값이 있어야 함
	if(!$mb_ids)
		return;

    // 배열값 분리
    $mb_id_arr = explode(',', preg_replace("/\s+/", "", $mb_ids));
    for($i=0;$i<sizeof($mb_id_arr);$i++) {
        if($mb_id_arr[$i]) {
            $mb = get_member($mb_id_arr[$i]);
            if(!$mb['mb_id'])
                $mb_id_errors[] = $mb_id_arr[$i];
        }
    }

    if(is_array($mb_id_errors))
        alert('존재하지 않는 회원 ('.implode(",",$mb_id_errors).')입니다. 아이디를 확인해 주세요.');

	return true;
}
}

// 업체-영업자 정보 업데이트
if(!function_exists('company_saler_update')){
function company_saler_update($arr) {
	global $g5,$member;

	if(!$arr['mb_id_saler'] || !$arr['com_idx'])
		return false;

	$mb1 = get_table_meta('member','mb_id',$arr['mb_id_saler']);
	$row1 = sql_fetch(" SELECT * FROM {$g5['company_saler_table']}
                        WHERE mb_id_saler='{$arr['mb_id_saler']}'
						  AND com_idx='{$arr['com_idx']}'
    ");

	$sql_common = " mb_id_saler = '{$arr['mb_id_saler']}'
					, com_idx = '{$arr['com_idx']}'
					, trm_idx_department = '".$mb1['mb_2']."'
					, cms_memo = '{$arr['cms_memo']}'
					, cms_status = '{$arr['cms_status']}'
					, cms_update_dt = '".G5_TIME_YMDHIS."'
	";

	// 있으면 UPDATE
	if($row1['cms_idx']) {
		$sql = " UPDATE {$g5['company_saler_table']} SET {$sql_common} WHERE cms_idx='".$row1['cms_idx']."' ";
		sql_query($sql,1);
//		echo $sql.'<br>';
	}
	// 없으면 INSERT
	else {
		$sql = " INSERT INTO {$g5['company_saler_table']} SET cms_reg_dt='".G5_TIME_YMDHIS."', {$sql_common} ";
		sql_query($sql,1);
//		echo $sql.'<br>';
		$row1['cms_idx'] = sql_insert_id();
	}

    return $row1['cms_idx'];
}
}


// 업체&=고객 업데이트
if(!function_exists('company_member_update')){
function company_member_update($arr)
{
	global $g5,$member;

	if(!$arr['mb_id_saler'] || !$arr['com_idx'])
		return false;

	$mb1 = sql_fetch("SELECT mb_2 FROM {$g5['member_table']} WHERE mb_id = '".$arr['mb_id_saler']."' ");

	$row1 = sql_fetch(" SELECT * FROM {$g5['company_member_table']} WHERE mb_id_saler='{$arr['mb_id_saler']}'
															AND com_idx='{$arr['com_idx']}' ");

	$sql_common = " mb_id_saler = '{$arr['mb_id_saler']}'
					, com_idx = '{$arr['com_idx']}'
					, trm_idx_department = '".$mb1['mb_2']."'
					, cmm_memo = '{$arr['cmm_memo']}'
					, cmm_status = '{$arr['cmm_status']}'
					, cmm_update_dt = '".G5_TIME_YMDHIS."'
	";

	// 있으면 UPDATE
	if($row1['cmm_idx']) {
		$sql = " UPDATE {$g5['company_member_table']} SET {$sql_common} WHERE cmm_idx='".$row1['cmm_idx']."' ";
		sql_query($sql,1);
//		echo $sql.'<br>';
	}
	// 없으면 INSERT
	else {
		$sql = " INSERT INTO {$g5['company_member_table']} SET cmm_reg_dt='".G5_TIME_YMDHIS."', {$sql_common} ";
		sql_query($sql,1);
//		echo $sql.'<br>';
		$row1['cmm_idx'] = sql_insert_id();
	}

    return $row1['cmm_idx'];
}
}

// 카드 번호 숨김 함수
if(!function_exists('card_number_hidden')){
	function card_number_hidden($card_no){

		$card_nos = explode("-",$card_no);
		// - 가 아니고 공백으로 구분되었다면
		if(!preg_match("/-/",$card_no)) {
			$card_nos = explode(" ",$card_no);
		}
		for($i=1;$i<sizeof($card_nos)-1;$i++) {
			//echo $card_nos[$i].'<br>';
			$card_no_middle .= str_repeat('*',strlen($card_nos[$i])).'-';
		}
		$card_no = $card_nos[0].'-'.$card_no_middle.$card_nos[sizeof($card_nos)-1];

		return $card_no;
	}
}

// 휴대폰 번호 숨김 함수
if(!function_exists('hp_hidden')){
	function hp_hidden($hp_no){

		$middle_hp = (strlen(preg_replace('/-/','',$hp_no)) >= 11) ? '****':'***';
		$new_hp = substr($hp_no,0,3).'-'.$middle_hp.'-'.substr($hp_no,-4);

		return $new_hp;
	}
}

// Email 숨김 함수
if(!function_exists('email_hidden')){
	function email_hidden($email){

		$mb_email_array = explode('@', $email);
		$new_email = substr($mb_email_array[0],0,-3).'***@'.$mb_email_array[1];
		return $new_email;
	}
}

/* NEW 데이터 암호화 함수 */
if(!function_exists('encryption')){
function encryption($plain_data,$key='intra'){
    $crypt_pass = "abcdefghij123456"; // 16자리
    $crypt_iv = "abcdefghij123456"; // 16자리
    $endata = openssl_encrypt($plain_data , "aes-128-cbc", $crypt_pass, true, $crypt_iv);
    $endata = base64_encode($endata);
	return $endata;
}
}

/* NEW 데이터 복호화 함수 */
if(!function_exists('decryption')){
function decryption($encrypted_data,$key='intra'){
    $crypt_pass = "abcdefghij123456"; // 16자리
    $crypt_iv = "abcdefghij123456"; // 16자리
    $data = base64_decode($encrypted_data);
    $endata = openssl_decrypt($data, "aes-128-cbc", $crypt_pass, true, $crypt_iv);
	return $endata;
}
}


// 장바구니 정보를 html 형태로 추출 (장바구니 상품 정보가 여러군데서 사용됨)
if(!function_exists('get_cart_html')){
function get_cart_html($ct_id) {
    global $g5;

    if(!$ct_id)
        return false;

    $cart = get_table_meta('g5_shop_cart','ct_id',$ct_id,'shop_cart');
    //print_r2($cart);
    if(!$cart['ct_id'])
        return false;

    // 상품가격 = (상품가격+옵션가격)*구매수량
    $cart['ct_price_total'] = ($cart['ct_price']+$cart['io_price'])*$cart['ct_qty'];

    // 상태값 색상은 클래스로 설정합니다.
    $cart['ct_status_class'] = (in_array($cart['ct_status'],$g5['set_workable_status_array'])) ? 'workable' : 'no_work';
    // 영업자
    $mb1 = get_saler($cart['mb_id_saler']);
    //print_r3($mb1);
    $cart['mb_name_saler_rank'] = $mb1['mb_name'].$g5['set_mb_ranks_value'][$mb1['mb_3']];
    // 업체정보
    if($cart['com_idx']) {
        $com1 = get_table_meta('company','com_idx',$cart['com_idx']);
        //print_r3($com1);
        $com1['com_manager_hp'] = ($com1['com_manager_hp']) ? '('.$com1['com_manager_hp'].')' : '';
        // 고객
        $mb = get_member($com1['mb_id']);
        $com1['com_president_hp'] = ($mb['mb_hp']) ? '('.$mb['mb_hp'].')' : '';
    }

    $html = ' <div id="cart_info">';
    $html .= '  <span><b>상품</b> '.$cart['it_name'].' ('.number_format($cart['ct_price_total']).'원)</span>';
    $html .= '  <span><b>결제</b> <span class="item_status_'.$cart['ct_status_class'].'">'.$cart['ct_status'].'</span></span>';
    $html .= '  <span><b>담당자</b> '.$cart['mb_name_saler_rank'].'('.$g5['department_name'][$mb1['mb_2']].')</span>';
    $html .= '  <span style="float:right;"><b>작업상태</b> <span class="item_status_'.$cart['ct_status_class'].'">'.$g5['set_ct_work_status_value'][$cart['ct_work_status']].'</span></span>';
    $html .= '</div>';
    $html .= ' <div id="company_info">';
    $html .= '  <span><b>업체명</b> '.$com1['com_name'].' ('.$com1['com_tel'].' '.$com1['com_email'].') <span class="com_idx">'.$com1['com_idx'].'</span></span>';
    $html .= '  <span><b>대표자</b> '.$com1['com_president'].' '.$com1['com_president_hp'].'</span>';
    $html .= '  <span><b>매니저</b> '.$com1['com_manager'].' '.$com1['com_manager_hp'].'</span>';
    $html .= '</div>';

    return $html;
}
}


// 사원 정보를 얻는다. (기본값을 변경해야 하는 부분이 있어서 별도 함수로 추가함)
if(!function_exists('get_saler')){
function get_saler($mb_id, $fields='*') {
    global $g5;
    $sql = " SELECT $fields FROM {$g5['member_table']} WHERE mb_id = TRIM('$mb_id') ";
    $row = sql_fetch($sql);
    if($fields=='*') {
        $row['mb_3'] = ($row['mb_3']) ? $row['mb_3'] : 10 ; // 직함이 없으면 기본 '사원'
        $row['mb_name_rank'] = $row['mb_name'].' '.$g5['set_mb_ranks_value'][$row['mb_3']]; // 홍길동 대리
        $row['trm_idx_department'] = $row['mb_2'];
        $row['trm_name_department'] = $g5['department_name'][$row['mb_2']];
    }

    // meta 값을 배열로 만들어서 원배열과 병합
    $row2 = get_meta('member',$mb_id);
	if(is_array($row2) && $fields=='*')
		$row = array_merge($row, $row2);

    return $row;
}
}

// 사원 정보를 얻는다. (외부 인트라인 경우 내부인트라에서 )
if(!function_exists('get_saler_idx')){
function get_saler_idx($mb_name, $mb_intra='', $mb_intra_id='') {
    global $g5;

    if(!$mb_name)
        return false;

    $sql = " SELECT mb_2, mb_9 FROM {$g5['member_table']} WHERE mb_name = TRIM('$mb_name') ";
    $rs = sql_query($sql,1);
    // 한명 이상인 경우는 mb_9 keys 값을 분리해서 해당 회원을 찾아야 함
    if(sql_num_rows($rs) > 1) {
        for($i=0;$row=sql_fetch_array($rs);$i++) {
            // mb9에 기존 인트라 정보 저장됨 (:mb_intra=31:,:mb_intra31_id=jamesjoa:,)
            $row['keys'] = get_keys($row['mb_9']);
            if($row['keys']['mb_intra']==$mb_intra && $row['keys']['mb_intra'.$mb_intra.'_id']==$mb_intra_id)
            $trm_idx = $row['mb_2'];
        }
    }
    else {
        $mb = sql_fetch($sql);
        $trm_idx = $mb['mb_2'];
    }

    return $trm_idx;
}
}

// 업체 정보를 얻는다.
if(!function_exists('get_company')){
function get_company($com_idx, $fields='*') {
    global $g5;
    $com_idx = preg_replace("/[^0-9a-z_]+/i", "", $com_idx);
    $sql = " SELECT $fields FROM {$g5['company_table']} WHERE com_idx = TRIM('$com_idx') ";
    $row = sql_fetch($sql);
    if($fields=='*') {
        // meta 값을 배열로 만들어서 원배열과 병합
        $row2 = get_meta('member',$mb_id);
        if(is_array($row2) && $fields=='*')
            $row = array_merge($row, $row2);
    }

    return $row;
}
}


// 게시판 변수설정들을 불려온다. wr_7 serialized 풀어서 배열로 가지고 옴
if(!function_exists('get_board')){
function get_board($bo_table) {
    global $g5;

    $sql = " SELECT * FROM ".$g5['board_table']." WHERE bo_table = '$bo_table' ";
    $board = sql_fetch($sql,1);
    $unser = unserialize($board['bo_7']);
    if( is_array($unser) ) {
        foreach ($unser as $k1=>$v1) {
            $board[$k1] = stripslashes64($v1);
        }
    }
    return $board;
}
}


// ct_history를 배열로 반환하는 함수
if(!function_exists('get_ct_history')){
function get_ct_history($text)
{
    $a = array();
    $b = explode("\n",$text);
    for($i=0;$i<sizeof($b);$i++) {
        list($ct_status,$mb_id,$ct_date,$ct_ip) = explode('|', trim($b[$i]));
        //echo $ct_status.' | '.$mb_id.' | '.$ct_date.' | '.$ct_ip.'<br>';
        // 상태값이 한글이 아니면 무시
        if(preg_match("/^[가-힝]/",$ct_status) && $ct_date) {
            $a[] = trim($b[$i]);
        }
    }
    return $a;
}
}


// 팀 idxs 추출 함수, 접근 범위에 따라 idxs 추출이 달라짐
// 매개변수 level (1=직속상위까지, 2=그룹전체까지, 9=전체조직)
if(!function_exists('get_dept_idxs')){
function get_dept_idxs($level=0) {
    global $g5,$member;

    // 수퍼인 경우 모든 조직코드 리턴, $level=10인 경우는 조직 코드조건 필요없음 -> 전부
    if($member['mb_allauth_yn']) {
        $trm = sql_fetch(" SELECT GROUP_CONCAT(trm_idx) AS trm_idxs FROM {$g5['term_table']} WHERE trm_taxonomy = 'department' ");  // 삭제 포함 모든 조직코드값
        return false;
    }
    else if($member['mb_level']>=6) {
        //print_r3($member['mb_2']);
        //print_r3($g5['department_up1_idx'][$member['mb_2']].'(바로상위idx)의 down_idxs = '.$g5['department_down_idxs'][$g5['department_up1_idx'][$member['mb_2']]]);
        //print_r3($g5['department_uptop_idx'][$member['mb_2']].'(그룹최상위idx)의 down_idxs = '.$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]]);
        //print_r3($g5['department_uptop_idx'][$member['mb_2']].'(최상위 삭제조직idx)의 idxs = '.$g5['department_trash_idxs'][$g5['department_uptop_idx'][$member['mb_2']]]);
        //print_r3($member);
        // 개별 설정이 있는 경우는 개별 설정이 우선함
        if($member['mb_group_level']) {
            // 직속상위까지만
            if($member['mb_group_level']==1) {
                $trm_idx = $g5['department_up1_idx'][$member['mb_2']];
            }
            // 그룹전체까지
            else if($member['mb_group_level']==2) {
                $trm_idx = $g5['department_uptop_idx'][$member['mb_2']];
            }
        }
        // 개별 설정이 없는 경우는 접근범위(매개변수)에 따라 설정
        else if($level) {
            // 직속상위까지만
            if($level==1) {
                $trm_idx = $g5['department_up1_idx'][$member['mb_2']];
            }
            // 그룹전체까지
            else if($level==2) {
                $trm_idx = $g5['department_uptop_idx'][$member['mb_2']];
            }
        }
        // 디폴트는 내 조직만
        else {
            $trm_idx = $member['mb_2'];
        }

        // 삭제조직코드도 포함해서 리턴
        return $g5['department_down_idxs'][$trm_idx].$g5['department_trash_idxs'][$member['mb_2']];
    }
    else {
        return false;
    }
}
}



// 수당 분배 함수
if(!function_exists('order_share')){
function order_share($od_id)
{
	global $g5,$config;

	if(!$od_id)
		return 0;

	// 주문 추출
    $row = get_table_meta('g5_shop_order','od_id',$od_id,'shop_order');

	// 상품목록, shop_admin/ajax.orderitem.php 참조 -----------------------------------------------------
	// 옵션이 있는 경우 각각 레코드가 따로 등록되므로 장바구니 보여줄 때는 it_id 단위로 GROUP BY 해서 묶은 다음
	// 다시 분리해서 보여줘야 옵션까지 제대로 보여줄 수 있다.
	$sql2 = "	SELECT * FROM {$g5['g5_shop_cart_table']} WHERE od_id = '".$row['od_id']."' GROUP BY it_id ORDER BY ct_id ";
	$rs2 = sql_query($sql2);
	for($i=0; $row2=sql_fetch_array($rs2); $i++) {
		//print_r2($row2);

		$sql_common .= ", sls_it_name = '".$row2['it_name']."' ";	// 상품명

		// 상품의 옵션정보
		$sql3 = " SELECT ct_id, it_id, ct_price, ct_qty, ct_option, ct_status, ct_history, cp_price, ct_send_cost, io_type, io_price, ct_select_time
					FROM {$g5['g5_shop_cart_table']}
					WHERE od_id = '".$row['od_id']."'
						AND it_id = '{$row2['it_id']}'
                        AND ct_status NOT IN ('".implode("','",$g5['set_exclude_ct_status_array'])."')
					ORDER BY io_type asc, ct_id asc
		";
		$rs3 = sql_query($sql3);
		for($k=0; $opt=sql_fetch_array($rs3); $k++) {
			//print_r2($opt);

			// 추가옵션상품(io_type=1), 선택(필수)옵션(io_type=0)
			// 수당분배는 선택(필수)옵션에 대해서만 집행한다.
			if($opt['io_type'])
				continue;	// 추가옵션상품은 통과 (매출 기록 안함)
			else {
				$opt_price = $opt['ct_price'] + $opt['io_price'];	// 선택(필수)옵션 상품은 단가=상품가격+옵션가격
				$sql_common .= ", sls_ct_price = '".$opt_price."' ";	// 단가
				$sql_common .= ", sls_ct_option = '".$opt['ct_option']."' ";	// 옵션명
				$opt['sub_total'] = $opt_price * $opt['ct_qty'];	// 상품 sub_total
			}

			$sql_common .= ", sls_ct_status = '".$opt['ct_status']."' ";	// 장바구니 상태
			$sql_common .= ", sls_ct_qty = '".$opt['ct_qty']."' ";	// 수량

			// 히스토리가 없으면 매출 입력을 위해서 값을 만들어 두어야 한다. (현재의 ct_status, ct_select_time값을 히스토리로 인식)
            // 신용카드 같은 경우는 history가 없는 경우들이 있다.
			if(!$opt['ct_history']) {
				$opt['ct_history'] = $opt['ct_status'].'|super|'.$opt['ct_select_time'].'|127.0.0.1';	// 항목구조 "입금|super|2019-01-22 20:08:25|210.217.10.24"
			}

			// 히스토리에 따른 매출 입력 (히스토리값을 기반으로 매출 입력)
			$opt['ct_historys'] = explode("\n",$opt['ct_history']);	// 항목을 배열로 분리
			for($x=0; $x<sizeof($opt['ct_historys']); $x++) {
				// 빈줄을 건너뛰고 값이 있는 라인만
				if($opt['ct_historys'][$x]) {
					$opt['ct_history_each'] = explode("|",$opt['ct_historys'][$x]);	// 항목을 배열로 2차 분리, 항목구조 "입금|super|2019-01-22 11:52:10|210.217.10.24"
					$ar['ct_id'] = $opt['ct_id'];
					$ar['it_id'] = $opt['it_id'];
					$ar['sls_action'] = $opt['sls_action'];	// 액션내용
					//$ar['ct_sub_total'] = (in_array($opt['ct_history_each'][0], $g5['set_sales_status_array'])) ? $opt['sub_total'] : -$opt['sub_total'];  // 상품금액소계(sub_total)
					$ar['ct_sub_total'] = $opt['sub_total'];  // 상품금액 (-정산이 있어서 함수 내부에서 계산해야 함)
					$ar['sls_ct_qty'] = $opt['ct_qty'];	// 수량
					$ar['sls_ct_status'] = $opt['ct_history_each'][0];	// 상태값
					$ar['sls_sales_dt'] = $opt['ct_history_each'][2];	// 매출일시
					$ar['order_join_member_yn'] = 1;	// 추천회원매출수당 지급 여부
					$ar['check_type'] = 'order';	// 매출적용 타입 order = 총기록 합계가 0 또는 음수(-)일때 매출기록, 총기록 합계가 양수(+)일 때 취소기록 등..
					$sls_idx = sales_update2($ar);
					//print_r2($ar);
					unset($ar);
				}
			}
            //exit;

		}
	}
}
}


// 통계 정보 업데이트 함수 (sales)
// ct_id, it_id, ct_sub_total, sls_ct_qty, sls_ct_status, sls_sales_dt, check_type, order_join_member_yn
if(!function_exists('sales_update2')){
function sales_update2($arr)
{
	global $g5,$config,$member;

	if(!$arr['ct_id']||!$arr['ct_sub_total'])
		return false;
	//print_r2($arr);

	// 관련 테이블 정보 추출
    $ct = get_table_meta('g5_shop_cart','ct_id',$arr['ct_id'],'shop_cart');
	if(!$ct['ct_id'])
		return false;
    //print_r2($ct);
    $od = get_table_meta('g5_shop_order','od_id',$ct['od_id'],'shop_order');
    $it = get_table_meta('g5_shop_item','it_id',$ct['it_id'],'shop_item');
    $mb = get_table_meta('member','mb_id',$ct['mb_id_saler']);

	// 각 테이블 정보 serialize
	$ar = array('mb_id','it_name','it_sc_type','it_sc_method','it_sc_price','it_sc_minimum','it_sc_qty','ct_status','ct_history','ct_price','ct_point','cp_price','ct_point_use','ct_stock_use','ct_option','ct_qty','ct_notax','io_id','io_type','io_price','ct_time','ct_send_cost','ct_direct','ct_select','ct_select_time');
	foreach($ct as $key => $value ) { if(in_array($key,$ar)) { $a[$key] = addslashes64($value); } }
	$ct_values = serialize($a);
	unset($ar);unset($a);

	$ar = array('od_id','mb_id','od_name','od_cart_count','od_cart_price','od_cart_coupon','od_send_cost','od_send_cost2','od_send_coupon','od_receipt_price','od_cancel_price','od_receipt_point','od_refund_price','od_bank_account','od_receipt_time','od_coupon','od_misu','od_status','od_settle_case','od_time','mb_id_saler');
	foreach($od as $key => $value ) { if(in_array($key,$ar)) { $a[$key] = addslashes64($value); } }
	$od_values = serialize($a);
	unset($ar);unset($a);

	// +,- 구분 (추가인 경우와 삭감인 경우를 위한 구분값) (입금,준비,배송,완료 상태값이면 +정산, 아니면 -정산)
    if(in_array($arr['sls_ct_status'], $g5['set_sales_status_array'])) {
        $arr['sls_action'] = '+정산(매출)';
        $arr['sls_plus_yn'] = 1;
        $arr['times'] = 1;
    }
    else {
        $arr['sls_action'] = '-정산(삭감)';
        $arr['sls_plus_yn'] = 0;
        $arr['times'] = -1;
    }


    // 공급가(부가세, 면세 적용)
    $arr['sls_price_supply'] = ($it['it_notax']) ? $arr['ct_sub_total'] : share_rate_money($arr['ct_sub_total'],1);  // 두번째 변수 (세금을 제외해 주세요.)
    //echo $arr['sls_price_supply'].'<br>';

    // 상품원가 (특정 금액이 있거나 아니면 비율로 설정됨)
    $it['it_price_cost'] = ($it['it_price_cost']) ? $it['it_price_cost']*$arr['sls_ct_qty'] : share_rate_money($arr['sls_price_supply']*$it['it_price_cost_rate']/100);

    // 수당지급 기준금액 (원가빼는 상품이 있고 아닌 상품이 존재함), 비율인 경우만 해당사항 있음(확정금액인 경우는 상관없음)
    $arr['sls_price_share'] = ($it['it_share_type']) ? $arr['sls_price_supply']-$it['it_price_cost'] : $arr['sls_price_supply'];
    //echo $arr['sls_price_supply'].'<br>';

    // 매출액계산 (부가세 및 원가 반영한 매출계산)
    // 1. 부가세별도 매출인 경우는 부가세 뺀 매출
    if($mb['mb_sales_notax'])
        $arr['sls_price'] = $arr['sls_price_supply'];   // 공급가
    else
        $arr['sls_price'] = $arr['ct_sub_total'];   // 상품가격(소계)그대로
    // 2. 원가공제후 매출 반영하는 직원인 경우 원가를 빼고 매출 잡음
    if($mb['mb_sales_cost_yn'])
        $arr['sls_price'] -= (int)$it['it_price_cost'];

    // 매출0 상품은 무조건 매출을 0으로!!
    if($it['it_sales_zero']) {
        $arr['sls_price'] = 0;
    }

    // 신규 매출
    $od['od_new'] = ($od['od_order_type']=='new') ? 1:0;

    // sql_common 공통
	$sql_common = "
		, sls_company = '".$ct['ct_company']."'
		, com_idx = '".$ct['com_idx']."'
		, sls_com_name = '".addslashes($ct['com_name'])."'
		, od_id = '".$od['od_id']."'
		, ct_id = '".$ct['ct_id']."'
		, it_id = '".$ct['it_id']."'
		, sls_od_id_values = '".$od_values."'
		, sls_ct_id_values = '".$ct_values."'
		, sls_it_name = '".$ct['it_name']."'
		, sls_ct_option = '".$ct['ct_option']."'
		, sls_ct_status = '".$arr['sls_ct_status']."'
		, sls_sales_dt = '".$arr['sls_sales_dt']."'
		, sls_price_type = '".$it['it_price_type']."'
		, sls_type1 = '".$it['it_sls_type1']."'
		, sls_type2 = '".$it['it_sls_type2']."'
		, sls_price_supply = '".$arr['times']*$arr['sls_price_supply']."'
		, sls_price = '".$arr['times']*$arr['sls_price']."'
		, sls_price_cost = '".$arr['times']*$it['it_price_cost']."'
		, sls_new = '".$od['od_new']."'
		, sls_action = '".$arr['sls_action']."'
	";

	// 적용타입이 order => 관련 내용의 총기록 합계가 0 또는 음수(-)일때 매출기록, 총기록 합계가 양수(+)일 때 취소기록 등..
	if($arr['check_type']=='order') {

		// 상품 매출에 대한 분배 할당이 있는 사원들 전부 할당
		// ORDER BY sra_type 정렬 순서 중요: 개인분배를 먼저 하고 team 분배를 해야 함 (중복 체크!)
		$sql2 = " SELECT *
					FROM {$g5['share_rate_table']} AS sra
						LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = sra.mb_id_saler
						LEFT JOIN {$g5['g5_shop_item_table']} AS it ON it.it_id = sra.it_id
					WHERE sra.it_id = '".$ct['it_id']."' AND sra_status IN ('ok')
						AND ( sra_type LIKE 'order_%' OR sra_type LIKE 'dev_%' )
					ORDER BY sra_type
		";
        //echo $sql2.'<br>';
		$rs2 = sql_query($sql2,1);
		for($j=0;$row2=sql_fetch_array($rs2);$j++) {
			//print_r2($row2);
			// 시작일~종료일 사이에 있는 사람들에게만 분배하기 {
			if($row2['sra_start_date']<=G5_TIME_YMD && $row2['sra_end_date']>=G5_TIME_YMD) {
                //echo $row2['sra_start_date'].'~'.$row2['sra_end_date'].', G5_TIME_YMD='.G5_TIME_YMD.'<br>';

				// sra_values
				$ar = array('mb_id_saler','trm_idx_department','it_id','sra_type','sra_name','sra_price_type','sra_price','sra_start_date','sra_end_date','sra_status');
				foreach($row2 as $key => $value ) { if(in_array($key,$ar)) { $a[$key] = addslashes64($value); } }
				$sra_values = serialize($a);
				unset($ar);unset($a);

				// sql_common 설정
				$row2['sql_common'] = $sql_common."
					, sra_idx = '".$row2['sra_idx']."'
					, sra_type = '".$row2['sra_type']."'
					, sls_sra_values = '".$sra_values."'
				";

				// 분배금액 확정 (비율 및 확정금액)
				if($row2['sra_price_type']=='rate') {
					$row2['sra_price_share'] = share_rate_money($arr['sls_price_share']*$row2['sra_price']/100);	// 절삭, 절상 적용
                }
				else
					$row2['sra_price_share'] = $row2['sra_price']*$arr['sls_ct_qty'];

				// 개인, 팀개별 구분
				$row2['sra_team_yn'] = ( preg_match("/_team/",$row2['sra_type']) ) ? 1 : 0;

				// 개발수당 먼저 분배 (개인별, 팀별 중복 배분 가능, 개인별로 받고 팀별로도 받을 수 있음)
				if(preg_match("/dev_/",$row2['sra_type'])) {

					// 개인 수당인 경우, 개인에게 할당
					if( $row2['sra_type']=='dev_member' ) {

						// 매출 수당 입력(추가 또는 삭감 둘 다), // mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sql_common
						$ar['mb_id'] = $row2['mb_id_saler'];
						$ar['sra_plus_yn'] = $arr['sls_plus_yn'];
						$ar['sra_price_share'] = $arr['times']*$row2['sra_price_share'];
						$ar['ct_id'] = $ct['ct_id'];
						$ar['it_id'] = $ct['it_id'];
						$ar['sra_type'] = $row2['sra_type'];
						$ar['sls_action'] = $arr['sls_action'];
						$ar['sls_ct_status'] = $arr['sls_ct_status'];   // 상태조건비교 때문에 변수 넘김
						$ar['sls_sales_dt'] = $arr['sls_sales_dt'];
						$ar['sql_common'] = $row2['sql_common'];
						$sls_idx = put_emp_share($ar);	// 추가 또는 삭감하는 함수
						unset($ar);

					}
					// 팀개별 수당인 경우, 모든 팀원들에게 할당 (for문)
					else if( $row2['sra_type']=='dev_team' && $g5['department_down_idxs'][$row2['trm_idx_department']] ) {

						// 소속팀원 전체 (탈퇴 회원은 제외)
						$sql3 = " SELECT *
									FROM {$g5['member_table']}
									WHERE mb_leave_date = ''
										AND mb_2 IN (".$g5['department_down_idxs'][$row2['trm_idx_department']].")
									ORDER BY mb_3
						";
						$rs3 = sql_query($sql3,1);
						for($k=0;$row3=sql_fetch_array($rs3);$k++) {

							// 매출 수당 입력(추가 또는 삭감 둘 다), // mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sql_common
							$ar['mb_id'] = $row3['mb_id'];
							$ar['sra_plus_yn'] = $arr['sls_plus_yn'];
							$ar['sra_price_share'] = $arr['times']*$row2['sra_price_share'];
							$ar['ct_id'] = $ct['ct_id'];
							$ar['it_id'] = $ct['it_id'];
							$ar['sra_type'] = $row2['sra_type'];
							$ar['sls_action'] = $arr['sls_action'];
                            $ar['sls_ct_status'] = $arr['sls_ct_status'];   // 상태조건비교 때문에 변수 넘김
                            $ar['sls_sales_dt'] = $arr['sls_sales_dt'];
							$ar['sql_common'] = $row2['sql_common'];
							//echo $row3['mb_name'].' / '.$row2['sra_type'].'<br>';
							$sls_idx = put_emp_share($ar);	// 추가 또는 삭감하는 함수
							unset($ar);

						}

					}

				}
				// 매출수당 분배 (개인별, 팀별 중복 배분 불가, 개인별로 받았으면 팀별로는 받을 수 없음) {
				else if(preg_match("/order_/",$row2['sra_type'])) {
					// $od 메타확장값에서 mb_id_saler(영업자) 아이디 추출해서 해당 영업자에게 수당 할당
					if( $od['mb_id_saler'] ) {

                        // 개인 수당인 경우, 개인에게 할당, 개인 수당으로 지급하는 거라면 팀수당은 중복 지급 불가
                        if( $row2['sra_type']=='order_member' ) {
                            // 개인 수당인 경우, 개인에게 할당
                            // 매출 수당 입력(추가 또는 삭감 둘 다), // mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sql_common
                            if($row2['mb_id_saler'] == $od['mb_id_saler']) {
                                $ar['mb_id'] = $od['mb_id_saler'];
                                $ar['sra_plus_yn'] = $arr['sls_plus_yn'];
                                $ar['sra_price_share'] = $arr['times']*$row2['sra_price_share'];
                                $ar['ct_id'] = $ct['ct_id'];
                                $ar['it_id'] = $ct['it_id'];
                                $ar['sra_type'] = $row2['sra_type'];
                                $ar['sls_action'] = $arr['sls_action'];
                                $ar['sls_ct_status'] = $arr['sls_ct_status'];   // 상태조건비교 때문에 변수 넘김
                                $ar['sls_sales_dt'] = $arr['sls_sales_dt'];
                                $ar['sql_common'] = $row2['sql_common'];
                                $sls_idx = put_emp_share($ar);	// 추가 또는 삭감하는 함수
                                unset($ar);
                                $order_member_ids[] = $od['mb_id_saler']; // 중복지급 방지를 위한 배열값 (지급한 회원 아이디들)
                            }
                        }
                        // 팀개별 수당인 경우 영업자가 속한 팀할당(상위조직포함) 수당으로 받음, 개인 수당으로 지급된 거라면 중복 지급 불가, 퇴사자는 제외 {
                        else if( $row2['sra_type']=='order_team' && $od['mb_id_saler'] ) {
                            // 이미 수당 지급한 사람은 제외
                            if($order_member_ids) {
                                if( in_array($od['mb_id_saler'],$order_member_ids) )
                                    $no_emp_share = 1;
                            }

                            // 중복이 아닌 경우만 입력 {
                            if( !$no_emp_share ) {
                                // mb_id_saler가 설정된 조직에 속해 있으면(하위 조직에) 수당 부여
                                $sql3 = " SELECT mb_id
                                            FROM {$g5['member_table']}
                                            WHERE mb_leave_date = ''
                                                AND mb_id = '".$od['mb_id_saler']."'
                                                AND mb_2 IN (".$g5['department_down_idxs'][$row2['trm_idx_department']].")
                                ";
                                $mb3 = sql_fetch($sql3,1);
                                //echo $sql3.'<br>';
                                // 해당 팀에 소속된 영업자가 맞다면 수당 지급 {
                                if($mb3['mb_id']) {

                                    // 매출 수당 입력(추가 또는 삭감 둘 다), // mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sql_common
                                    $ar['mb_id'] = $od['mb_id_saler'];
                                    $ar['sra_plus_yn'] = $arr['sls_plus_yn'];
                                    $ar['sra_price_share'] = $arr['times']*$row2['sra_price_share'];
                                    $ar['ct_id'] = $ct['ct_id'];
                                    $ar['it_id'] = $ct['it_id'];
                                    $ar['sra_type'] = $row2['sra_type'];
                                    $ar['sls_action'] = $arr['sls_action'];
                                    $ar['sls_ct_status'] = $arr['sls_ct_status'];   // 상태조건비교 때문에 변수 넘김
                                    $ar['sls_sales_dt'] = $arr['sls_sales_dt'];
                                    $ar['sql_common'] = $row2['sql_common'];
                                    $sls_idx = put_emp_share($ar);	// 추가 또는 삭감하는 함수
                                    unset($ar);
                                }
                                // }해당 팀에 소속된 영업자가 맞다면 수당 지급
                            }
                            // }중복이 아닌 경우만 입력
                        }
                        // } 팀개별 수당인 경우 영업자가 속한 팀할당(상위조직포함) 수당으로 받음, 개인 수당으로 지급된 거라면 중복 지급 불가, 퇴사자는 제외
					}
                    // }$od 메타확장값에서 mb_id_saler(영업자) 아이디 추출해서 해당 영업자에게 수당 할당
				}
				// }매출수당 분배 (개인별, 팀별 중복 배분 불가, 개인별로 받았으면 팀별로는 받을 수 없음)
			}
			// }시작일~종료일 사이에 있는 사람들에게만 분배하기
		}

		// 추천회원매출 수당 지급: 회원가입 시 추천인이 있는 경우 그 회원에게 수당을 지급한다.
		// 쉼표로 구분 (여러명일 수도 있음)
		if($arr['order_join_member_yn']) {

			// 상품 매출에 대한 분배 금액 추출
			$sql2 = " SELECT *
						FROM {$g5['share_rate_table']} AS sra
						WHERE sra.it_id = '".$ct['it_id']."' AND sra_status IN ('ok')
							AND sra_type = 'order_join_member'
						ORDER BY sra_idx DESC LIMIT 1
			";
			//echo $sql2.'<br>';
			$row2 = sql_fetch($sql2);
			//print_r2($row2);
			// 시작일~종료일 사이에 있을 때만 분배하기 {
			if($row2['sra_idx'] && ($row2['sra_start_date']<=G5_TIME_YMD || $row2['sra_end_date']>=G5_TIME_YMD)) {

				// sra_values
				$ar = array('mb_id_saler','trm_idx_department','it_id','sra_type','sra_name','sra_price_type','sra_price','sra_start_date','sra_end_date','sra_status');
				foreach($row2 as $key => $value ) { if(in_array($key,$ar)) { $a[$key] = addslashes64($value); } }
				$sra_values = serialize($a);
				unset($ar);unset($a);

				// sql_common 설정
				$row2['sql_common'] = $sql_common."
					, sra_idx = '".$row2['sra_idx']."'
					, sra_type = '".$row2['sra_type']."'
					, sls_sra_values = '".$sra_values."'
				";

				// 분배금액 확정 (비율 및 확정금액)
				if($row2['sra_price_type']=='rate') {
					$row2['sra_price_share'] = share_rate_money($arr['sls_price_share']*$row2['sra_price']/100);	// 절삭, 절상 적용
                }
				else
					$row2['sra_price_share'] = $row2['sra_price'];


                // 주문내역 추출을 위한 초기화 설정( mb_recommend 쉼표 설정 등)
                order_init();

				// 구매회원 정보 추출
				$mb1 = get_table_meta('member','mb_id',$od['mb_id']);
				$mb_recommends = explode(',', preg_replace("/\s+/", "", $mb1['mb_recommend']));
				for($i=0;$i<sizeof($mb_recommends);$i++) {
					if($mb_recommends[$i]) {

						// 매출 수당 입력(추가 또는 삭감 둘 다), // mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sql_common
						$ar['mb_id'] = $mb_recommends[$i];
						$ar['sra_plus_yn'] = $arr['sls_plus_yn'];
						$ar['sra_price_share'] = $arr['times']*$row2['sra_price_share'];
						$ar['ct_id'] = $ct['ct_id'];
						$ar['it_id'] = $ct['it_id'];
						$ar['sra_type'] = $row2['sra_type'];
						$ar['sls_action'] = $arr['sls_action'];
                        $ar['sls_ct_status'] = $arr['sls_ct_status'];   // 상태조건비교 때문에 변수 넘김
                        $ar['sls_sales_dt'] = $arr['sls_sales_dt'];
						$ar['sql_common'] = $row2['sql_common'];
						$sls_idx = put_emp_share($ar);	// 추가 또는 삭감하는 함수
						unset($ar);

					}
				}

			}
			// }시작일~종료일 사이에 있을 때만 분배하기

		}

	} // }적용타입이 order => 관련 내용의 총기록 합계가 0 또는 음수(-)일때 매출기록, 총기록 합계가 양수(+)일 때 취소기록 등..
	// 기본 디폴트 상품매출에 대한 수당, 중복 허용 안함 // ct_id, it_id, sls_action, sls_price, sls_sales_dt, check_type
	else {
        $sql = "	SELECT COUNT(*) AS cnt
					FROM {$g5['sales_table']}
					WHERE ct_id = '".$arr['ct_id']."'
						AND it_id = '".$arr['it_id']."'
						AND sls_action = '".$arr['sls_action']."'
		";
        $row = sql_fetch($sql);
		// 중복이 있으면 리턴
        if ($row['cnt'])
            return -1;
		else {
			// 아직은 정의된 바 없음

		}

	}

	return $sls_idx;
}
}

// 직원에게 수당 할당 (추가 및 삭감 둘 다)
// mb_id, sra_plus_yn, sra_price_share, ct_id, it_id, sra_type, sls_ct_status, sls_sales_dt, sls_action, sql_common
if(!function_exists('put_emp_share')){
function put_emp_share($arr)
{
    global $g5;

	if(!is_array($arr))
		return false;
	//print_r2($arr);
//    echo '--------------------------------------------------------------<br>';

	// 직원 정보 추출
    $mb2 = get_table_meta('member','mb_id',$arr['mb_id']);

	// sql_common 추가
	$arr['sql_common'] .= "
		, mb_id_saler = '".$arr['mb_id']."'
		, mb_name_saler = '".$mb2['mb_name']."'
		, trm_idx_department = '".$mb2['mb_2']."'
		, sls_department_name = '".$g5['department_name'][$mb2['mb_2']]."'
		, sls_emp_rank = '".$mb2['mb_3']."'
		, sls_emp_enter_date = '".$mb2['mb_enter_date']."'
		, sls_share = '".$arr['sra_price_share']."'
		, sls_status = 'ok'
	";

    // 입력조건 두가지: -가 들어올 때는 바로 (앞전의 row가 +가 있을 때)만 입력, +가 들어올 때는 (row가 없거나 앞전의 row가 -였을 때)만 입력
    // 상기 두 가지 조건 외의 나머지는 다 수정만 함
	// 맨 마지막 라인 추출 (비교 기준값)
    $sql = "	SELECT sls_idx, trm_idx_department, sls_ct_status
                FROM {$g5['sales_table']}
                WHERE sls_status IN ('ok')
                    AND sra_type = '".$arr['sra_type']."'
                    AND ct_id = '".$arr['ct_id']."'
                    AND it_id = '".$arr['it_id']."'
                    AND mb_id_saler = '".$arr['mb_id']."'
                ORDER BY sls_idx DESC LIMIT 1
	";
	$sls1 = sql_fetch($sql,1);
    //echo $sql.'<br>==========<br>';
    // +가 들어왔을 때 입력처리: (row가 없거나 앞전의 row가 -였을 때)만 입력
    if( $arr['sra_plus_yn'] ) {
        // 이전값이 존재하지 않을 때는 입력
        if( !$sls1['sls_idx'] ) {
            $set_insert = 1;
        }
        // 이전값이 존재한다면 - 조건일 때만 입력
        else if( !in_array($sls1['sls_ct_status'], $g5['set_sales_status_array']) ) {
            $set_insert = 1;
        }
    }
    // -가 들어왔을 때 입력처리: (앞전의 row가 +가 있을 때)만 입력
    if( !$arr['sra_plus_yn'] ) {
        if( in_array($sls1['sls_ct_status'], $g5['set_sales_status_array']) ) {
            $set_insert = 1;
        }
    }
    //echo $arr['sls_ct_status'].', insert='.$set_insert.'<br>';    // 상태값 & 입력모드


	// 기존 값이 존재하는 지 체크(같은상태값, 같은 적용일시)해서 업데이트 or 입력
	$sql = "	SELECT sls_idx, trm_idx_department
                FROM {$g5['sales_table']}
                WHERE sls_status IN ('ok')
                    AND sra_type = '".$arr['sra_type']."'
                    AND ct_id = '".$arr['ct_id']."'
                    AND it_id = '".$arr['it_id']."'
                    AND mb_id_saler = '".$arr['mb_id']."'
                    AND sls_ct_status = '".$arr['sls_ct_status']."'
                    AND sls_sales_dt = '".$arr['sls_sales_dt']."'
                    AND sls_action = '".$arr['sls_action']."'
	";
	$sls1 = sql_fetch($sql,1);
    //echo $sql.'<br>========= <br>';
	// 존재하면 업데이트
    if($sls1['sls_idx']) {
        //echo 'exists!!<br>';
        // 매출수당(팀)이 중복될 때 이전 매출이 상위조직이었다면 내 걸로 업데이트, 기존 매출이 하위조직 매출이면 패쓰(통과)
        // 더 구체적인 하위조직 매출을 기록으로 가지고 있어야 함
        if($arr['sra_type']=='order_team') {
            $down_idx_array = explode(",",$g5['department_down_idxs'][$mb2['mb_2']]);
            $down_idx_array = array_diff( $down_idx_array, array($mb2['mb_2']) );   // 현재 조직의 매출이 수정이 안 되서 추가
            //print_r2($down_idx_array);

            for($i=0;$i<sizeof($down_idx_array)-1;$i++) {
                if($down_idx_array[$i] == $mb2['mb_2']) {
                    $down_idx_dup_yn = 1;
                    break;
                }
            }
        }
        //echo 'down_idx_dup_yn -> '.$down_idx_dup_yn.'<br>';

        // 하위 매출이 아닌 경우는 통과하고 나머지 경우는 업데이트
        // 기본 디폴트는 down_idx_dup_yn값이 없으므로 업데이트한다.
        $sql_interval = ($g5['setting']['set_od_registry_status']) ?: '-1 MONTH';
        if(!$down_idx_dup_yn) {
            $sql = " UPDATE {$g5['sales_table']} SET
                        sls_update_dt = '".G5_TIME_YMDHIS."'
                        ".$arr['sql_common']."
                    WHERE sls_idx = '".$sls1['sls_idx']."'
                        AND sls_sales_dt >= date_add(now(), INTERVAL ".$sql_interval.")
            ";
            echo $sql.' << updated -------------- <br>';
            sql_query($sql,1);
            $sls_idx = $sls1['sls_idx'];
//            echo $sls_idx.' << updated -------------- <br>';
        }
    }
    // 값을 입력할 때는 조건에 맞을 때만 입력해야 함 (위에서 추출한 조건)
    else {

        // 입력조건에 맞으면 정보 입력
        if($set_insert) {
            $sql = " INSERT INTO {$g5['sales_table']} SET
                        sls_reg_dt = '".G5_TIME_YMDHIS."'
                        , sls_update_dt = '".G5_TIME_YMDHIS."'
                        ".$arr['sql_common']."
            ";
//            echo $sql.' << inserted -------------- <br>';
            sql_query($sql,1);
            $sls_idx = sql_insert_id();
//            echo $sls_idx.' << inserted -------------- <br>';
        }


    }

	return $sls_idx;

}
}


// 월매출에 대한 수당 분배 함수 (sales)
if(!function_exists('sales_update3')){
function sales_update3($arr, $ym='')
{
	global $g5,$config,$member;

	if(!$arr['sls_action'])
		return false;

	// 달정보가 없으면 기본적으로는 이번달 매출 기반
	$ym = ($ym) ? $ym : date("Y-m",G5_SERVER_TIME);
	$st_date = $ym.'-01';
	$en_date = $ym.'-31';

	// sql_common 디폴트
	$sql_common = " , sls_sales_dt = '".$st_date." 00:00:00' ";

	// 매출에 대한 분배 할당이 있는 사원들만 추출
	$sql2 = " SELECT *
				FROM {$g5['share_rate_table']} AS sra
					LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = sra.mb_id_saler
				WHERE sra_status IN ('ok')
					AND sra_type LIKE 'monthly_%'
				ORDER BY sra_idx DESC
	";
	$rs2 = sql_query($sql2,1);
	for($j=0;$row2=sql_fetch_array($rs2);$j++) {
		//print_r2($row2);
		// 시작일~종료일 사이에 있는 사람들에게만 분배하기 {
		if($row2['sra_start_date']<=G5_TIME_YMD || $row2['sra_end_date']>=G5_TIME_YMD) {

			// sra_values
			$ar = array('mb_id_saler','trm_idx_department','it_id','sra_type','sra_name','sra_price_type','sra_price','sra_start_date','sra_end_date','sra_status');
			foreach($row2 as $key => $value ) { if(in_array($key,$ar)) { $a[$key] = addslashes64($value); } }
			$sra_values = serialize($a);
			unset($ar);unset($a);

			// 직원 정보 추출
			$mb2 = get_member2($row2['mb_id_saler']);
			// sql_common 추가
			$sql_common .= "
				, sra_idx = '".$row2['sra_idx']."'
				, sra_type = '".$row2['sra_type']."'
				, sls_sra_values = '".$sra_values."'
				, sls_action = '+정산, ".$g5['set_sra_type'][$row2['sra_type']]."'
				, mb_id_saler = '".$row2['mb_id_saler']."'
				, mb_name_saler = '".$mb2['mb_name']."'
				, trm_idx_department = '".$mb2['mb_2']."'
				, sls_department_name = '".$mb2['department_name']."'
				, sls_emp_rank = '".$mb2['mb_3']."'
				, sls_emp_enter_date = '".$mb2['mb_enter_date']."'
			";

			// 해당월의 매출 추출
			// monthly_sales=월영업매출수당, 영업자 발생 매출에 대한 % 수당 (영업팀장, 본부장등..)
			if(preg_match("/_sales/",$row2['sra_type'])) {
				// 나의 하위 조직 매출 전체
				$sls2 = sql_fetch("	SELECT SUM(sls_price) AS sls_price_sum
										FROM {$g5['sales_table']}
										WHERE sls_status IN ('ok')
											AND sls_sales_dt LIKE '".$ym."%'
											AND trm_idx_department IN (".$g5['department_down_idxs'][$mb2['mb_2']].")
				");
				$arr['sls_price'] = $sls2['sls_price_sum'];
			}
			// monthly_order=월주문매출수당, 전체 주문 매출에 대한 % 수당
			else {
				// g5_shop_order 매출 전체
				$od1 = sql_fetch("	SELECT SUM(od_cart_price + od_send_cost + od_send_cost2) AS order_price_sum
										FROM {$g5['g5_shop_order_table']}
										WHERE od_time LIKE '".$ym."%'
				");
				$arr['sls_price'] = $od1['order_price_sum'];
			}
			// sql_common 추가
			$sql_common .= " , sls_price = '".$row2['sra_price']."' ";

			// 분배금액 확정 (비율 및 확정금액)
			if($row2['sra_price_type']=='rate') {
                $row2['sra_price_share'] = share_rate_money($arr['sls_price_share']*$row2['sra_price']/100);	// 절삭, 절상 적용
            }
			else
				$row2['sra_price_share'] = 0;	// 월매출 수당은 확정금액이 없다. 무조건 비율이어야 한다.


			// 해당월 기존에 지급했던 금액이 있으면 수정(update) 아니면 입력(insert)
			$sql = "	SELECT sls_idx
						FROM {$g5['sales_table']}
						WHERE sls_status IN ('ok')
							AND sra_type = '".$row2['sra_type']."'
							AND sls_sales_dt LIKE '".$ym."%'
							AND mb_id_saler = '".$row2['mb_id_saler']."'
			";
			$sls1 = sql_fetch($sql,1);
			// 수정
			if( $sls1['sls_idx'] ) {
				$sql = " UPDATE {$g5['sales_table']} SET
							sls_share = '".$row2['sra_price_share']."'
							, sls_update_dt = '".G5_TIME_YMDHIS."'
							{$sql_common}
						WHERE sls_idx = '".$sls1['sls_idx']."'
				";
				sql_query($sql,1);
				$sls_idx = sql_insert_id();
			}
			// 입력
			else {
				$sql = " INSERT INTO {$g5['sales_table']} SET
							sls_share = '".$row2['sra_price_share']."'
							, sls_status = 'ok'
							, sls_reg_dt = '".G5_TIME_YMDHIS."'
							, sls_update_dt = '".G5_TIME_YMDHIS."'
							{$sql_common}
				";
				sql_query($sql,1);
				$sls_idx = sql_insert_id();
			}

		}
		// }시작일~종료일 사이에 있는 사람들에게만 분배하기
	}

	return $sls_idx;
}
}


// 주문내역 추출을 위한 초기화 설정( mb_recommend 쉼표 설정 등), 사용된 위치는 아래와 같습니다.
// 테마단 /emp/emp_order_list.php
// 관리자단 v10/order_list.php, 관리자단 v10/cart_list.php,
if(!function_exists('order_init')){
function order_init()
{
    global $g5;

    // 추천인이 있는 필드 끝에 쉼표(,)를 전부 붙여주라.
    $sql = " UPDATE {$g5['member_table']} SET mb_recommend = CONCAT(mb_recommend,',') WHERE substring(mb_recommend,-1) != ',' AND mb_recommend != '' ";
    sql_query($sql,1);

    // ct_history 값이 없는 것이 있으면 초기값 입력
    //$sql = " UPDATE {$g5['g5_shop_cart_table']} SET ct_history = CONCAT(ct_status,'|',mb_id,'|',ct_select_time,'|127.0.0.1') WHERE ct_history = '' ";
    //sql_query($sql,1);
    // ct_history 줄바꿈부터 시작하는 값이 있으면 전부 업데이트
    $sql = " UPDATE {$g5['g5_shop_cart_table']} SET ct_history = SUBSTRING(ct_history,2,LENGTH(ct_history)) WHERE ct_history REGEXP '^\n' ";
    sql_query($sql,1);

    return true;
}
}


// GROUP_CONCAT( CONCAT....  mb_id=home^mb_name=홍길동.....,mb_id=home2^mb_name=홍길동2..... 형태의 값을
// 2차 배열로 분리해서 반환하는 함수
if(!function_exists('get_group_info')){
function get_group_info($arr)
{
    global $g5;

	if(!is_array($arr))
		return false;

	$pieces = explode(',', $arr);
	for ($i=0; $i<sizeof($pieces); $i++) {
		$sub_item = explode('^', $pieces[$i]);
		for ($j=0; $j<sizeof($sub_item); $j++) {
			list($key, $value) = explode('=', $sub_item[$j]);
			$row[$i][$key] = $value;
		}
	}

    return $row;
}
}


// 비율금액 절삭 or 반올림
// $taxflag (세금제외설정) 금액/1.1 처리를 한 후 floor 계산 (floor 고질적인 문제 때문에 DB 처리함)
if(!function_exists('share_rate_money')){
function share_rate_money($mny,$taxflag=0,$flag='floor')
{
    global $g5;

	// 절삭(floor)인 경우, 디폴트는 절삭!
	if($flag=='floor') {
		// 16335 -> 16330 으로 절삭 (절사)
		// floor( 값 / 10 ) * 10;
        if($taxflag) {
            // floor 함수 에러 때문에 디비에서 1.1 나누어줘야 함
            $mny3 = sql_fetch(" SELECT ".$mny."/1.1 AS mny3 ",1);
            $mny = $mny3['mny3'];
        }
		$mny2 = floor($mny/$g5['setting']['set_rate_unit'])*$g5['setting']['set_rate_unit'];
	}
	// 반올림
	else {
		$digit = ($g5['setting']['set_rate_unit']>1) ? strlen($g5['setting']['set_rate_unit']/10) : 0;	// 10단위반올림=-1, 100단위반올림=-2...
		//echo $digit;
		$mny2 = round($mny,-$digit);
	}

	return $mny2;
}
}


// 분배조건 같은 값이 있는지 체크
if(!function_exists('check_share_rate_dup')){
function check_share_rate_dup($arr)
{
    global $g5;

	$arr['sra_idx'] = (!$arr['sra_idx']) ? 0 : $arr['sra_idx'];

	// 수정인 경우는 자기는 제외하고 조건검색해야 함
	if( $arr['flag'] == 'modify' )
		$sql_sra_idx = " AND sra_idx != '".$arr['sra_idx']."' ";

	// 팀인 경우와 개인인 경우
    if( preg_match("/_team/",$arr['sra_type']) ) {
		$sql_team_member = " AND trm_idx_department = '".$arr['trm_idx_department']."' ";
    }
    else {
		$sql_team_member = " AND mb_id_saler = '".$arr['mb_id_saler']."' ";
    }

	// 같은타입이 중복되는 기간에 있으면 안 된다.
	$sql = "	SELECT sra_idx
				FROM {$g5['share_rate_table']}
				WHERE sra_status NOT IN ('trash') {$sql_sra_idx}
					{$sql_team_member}
					AND it_id = '".$arr['it_id']."'
					AND sra_type = '".$arr['sra_type']."'
					AND ( sra_start_date BETWEEN '".$arr['sra_start_date']."' AND '".$arr['sra_end_date']."'
							OR sra_end_date BETWEEN '".$arr['sra_start_date']."' AND '".$arr['sra_end_date']."' )
	";
	//echo $sql; exit;
	$sra = sql_fetch($sql,1);
    if ($sra['sra_idx'])
        return true;	// 존재한다.
	else
        return false;	// 존재 안 한다.
}
}


// 영업자 정보 추출
// 대체함수로 갈아타세요. get_table_meta('member','mb_id','super')	// 4번째 매개변수는 테이블명과 같아서 생략!
// 단 trm_idx_department, department_name, mb_position 배열값이 필요하다면 이 함수를 사용할 것
if(!function_exists('get_member2')){
function get_member2($db_id)
{
    global $g5;

	if(!$db_id)
		return false;
	$db_id = trim($db_id);

    $row = sql_fetch(" select * from {$g5['member_table']} where mb_id = '".$db_id."' ");
    $row2 = get_meta('member',$db_id);
	if(is_array($row2))
		$row = array_merge($row, $row2);	// meta 값을 배열로 만들어서 원배열과 병합

	$row['trm_idx_department'] = $row['mb_2'];	// 조직코드
	$row['department_name'] = $g5['department_name'][$row['mb_2']];	// 조직명
	$row['mb_position'] = $row['mb_1'];	// mb_position 변수 재설정 (mb_1 이 그 역할을 대신함)

    return $row;
}
}


// erp 서버 연결
if(!function_exists('erp_server_connect')){
function erp_server_connect()
{
	global $g5,$connect_db_pdo;

	// 기존 디비 연결 해제
	$link = $g5['connect_db'];
	if( function_exists('mysqli_query') )
		$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	else
		$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	$smsDbHost="119.207.79.78";
	$smsDbUser="root";
	$smsDbPass="nccjelql@138";
	$smsDbName="nccj_erp";
	try {
		$connect_db_pdo = new PDO('mysql:host='.$smsDbHost.';dbname='.$smsDbName, $smsDbUser, $smsDbPass);
		$g5['pdo_yn'] = 1;
	}
	catch( PDOException $Exception ) {
		$connect_db_pdo  = @mysql_connect($smsDbHost,$smsDbUser,$smsDbPass) or die("DB connent Error... Check your database setting");
		mysql_select_db($smsDbName,$connect_db_pdo);
		$g5['pdo_yn'] = 0;
	}

}
}

// erp 서버 연결 종료
if(!function_exists('erp_server_close')){
function erp_server_close()
{
	global $g5,$connect_db_pdo,$connect_db;

	// 종료
	if($g5['pdo_yn'])
		$connect_db_pdo = null;
	else
		mysql_close($connect_db_pdo);


	// 영카트 디비 재연결
    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// NCT 문자 발송 서버 연결
if(!function_exists('sms_server_connect')){
function sms_server_connect()
{
	global $g5,$connect_db_sms;

	// 기존 디비 연결 해제
	$link = $g5['connect_db'];
	if( function_exists('mysqli_query') )
		$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	else
		$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	$smsDbHost="119.207.79.54";
	$smsDbUser="lguplus";
	$smsDbPass="nccj0901";
	$smsDbName="lguplus";
//	$connect_db_sms  = @mysql_connect($smsDbHost,$smsDbUser,$smsDbPass) or die("DB connent Error... Check your database setting");
//	mysql_select_db($smsDbName,$connect_db_sms);

//	$connect_db_sms = new PDO('mysql:host='.$smsDbHost.';dbname='.$smsDbName.';charset=utf8', $smsDbUser, $smsDbPass);
	try {
		$connect_db_sms = new PDO('mysql:host='.$smsDbHost.';dbname='.$smsDbName, $smsDbUser, $smsDbPass);
		$g5['pdo_yn'] = 1;
	}
	catch( PDOException $Exception ) {
		$connect_db_sms  = @mysql_connect($smsDbHost,$smsDbUser,$smsDbPass) or die("DB connent Error... Check your database setting");
		mysql_select_db($smsDbName,$connect_db_sms);
		$g5['pdo_yn'] = 0;
	}

}
}

// NCT 문자 발송 서버 연결 종료
if(!function_exists('sms_server_close')){
function sms_server_close()
{
	global $g5,$connect_db_sms,$connect_db;

	// 종료
	if($g5['pdo_yn'])
		$connect_db_sms = null;
	else
		mysql_close($connect_db_sms);


	// 영카트 디비 재연결
    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// 문자 발송 함수 (NCT SMS 서버를 통한 메시지 발송)
// $to_number, $from_number, $content;
if(!function_exists('sms_nct')){
function sms_nct($sms_array)
{
	global $g5,$member,$connect_db_sms;
    //print_r2($sms_array);

	sms_server_connect();	// 디비 연결

	$to_number = hyphen_hp_number($sms_array['to_number']);
	$from_number = hyphen_hp_number($sms_array['from_number']);
	$content = iconv('utf-8','euc-kr',$sms_array['content']);

	$sql = "INSERT INTO SC_TRAN SET
			TR_SENDDATE = now()
			,TR_PHONE = '".$to_number."'
			,TR_CALLBACK = '".$from_number."'
			,TR_MSG = '".$content."'
			,TR_ETC1 = 'kafain'
			,TR_ETC2 = '".$_SERVER['REMOTE_ADDR']."'
			,TR_ETC3 = '".$member['mb_id']."'
	";
	//mysql_query($sql);
	if($g5['pdo_yn'])
		$connect_db_sms->query($sql);
	else
		mysql_query($sql);

	sms_server_close();	// 디비 종료
}
}

// 문자 발송 함수 (NCT LMS 서버를 통한 메시지 발송)
if(!function_exists('lms_nct')){
function lms_nct($sms_array)
{
	global $g5,$member,$connect_db_sms;

	sms_server_connect();	// 디비 연결

	$subject = iconv('utf-8','EUC-KR',$sms_array['subject']);
	$to_number = hyphen_hp_number($sms_array['to_number']);
	$from_number = hyphen_hp_number($sms_array['from_number']);
	$content = iconv('utf-8','EUC-KR//IGNORE',$sms_array['content']);
	//$content = iconv('utf-8','cp949',$sms_array['content']);
	//echo $sms_array['content'].'<br>';
	//echo $content.'<br>';

	$file_name = $sms_array['file_name'];

	$now_id = ($member['mb_id']) ? $member['mb_id'] : 'kafain_cron';

	$sql = "INSERT INTO MMS_MSG SET
			SUBJECT = '".$subject."'
			,PHONE = '".$to_number."'
			,CALLBACK = '".$from_number."'
			,REQDATE = now()
			,MSG = '".$content."'
			,FILE_PATH1 = '/usr/local/lguplus/mmsfile'
			,SENTDATE = now()
			,TYPE = '0'
			,ID = '".$now_id."'
			,POST = 'kafain'
			,ETC1 = '".$_SERVER['REMOTE_ADDR']."'
			,ETC2 = '".$file_name."'
	";
	//mysql_query($sql);
	if($g5['pdo_yn'])
		$connect_db_sms->query($sql);
	else
		mysql_query($sql);

	sms_server_close();	// 디비 종료
}
}


// 우글 문자 발송 서버 연결
if(!function_exists('sms_woogle_server_connect')){
function sms_woogle_server_connect()
{
	global $g5,$connect_db;

	// 기존 디비 연결 해제
	$link = $g5['connect_db'];
	if( function_exists('mysqli_query') )
		$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	else
		$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	$smsDbHost="116.125.120.87";
	$smsDbUser="woogle";
	$smsDbPass="pw@woogle";
	$smsDbName="woogle_lguplus";

	// 우글 디비 연결
    $connect_db = sql_connect($smsDbHost,$smsDbUser,$smsDbPass) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db($smsDbName, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);
}
}

// 우글 문자 발송 서버 연결 종료
if(!function_exists('sms_woogle_server_close')){
function sms_woogle_server_close()
{
	global $g5,$connect_db;

	// 종료
    mysqli_close($connect_db);


	// 영카트 디비 재연결
    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// 문자 발송 함수 (우글 SMS 서버를 통한 메시지 발송)
// $to_number, $from_number, $content;
if(!function_exists('sms_woogle')){
function sms_woogle($sms_array)
{
	global $g5,$member;
    //print_r2($sms_array);

	sms_woogle_server_connect();	// 디비 연결

	$to_number = hyphen_hp_number($sms_array['to_number']);
	$from_number = hyphen_hp_number($sms_array['from_number']);
	$content = iconv('utf-8','euc-kr',$sms_array['content']);
	$content = $sms_array['content'];

	$sql = "INSERT INTO SC_TRAN SET
			TR_SENDDATE = now()
			,TR_PHONE = '".$to_number."'
			,TR_CALLBACK = '".$from_number."'
			,TR_MSG = '".$content."'
			,TR_ETC1 = 'woogle'
			,TR_ETC2 = '".$_SERVER['REMOTE_ADDR']."'
			,TR_ETC3 = '".$member['mb_id']."'
	";
	//mysql_query($sql);
    sql_query($sql);

	sms_woogle_server_close();	// 디비 종료
}
}

// 문자 발송 함수 (우글 LMS 서버를 통한 메시지 발송)
if(!function_exists('lms_woogle')){
function lms_woogle($sms_array)
{
	global $g5,$member,$connect_db;

	sms_woogle_server_connect();	// 디비 연결

	$subject = iconv('utf-8','EUC-KR',$sms_array['subject']);
	$to_number = hyphen_hp_number($sms_array['to_number']);
	$from_number = hyphen_hp_number($sms_array['from_number']);
	$content = iconv('utf-8','EUC-KR//IGNORE',$sms_array['content']);
	//$content = iconv('utf-8','cp949',$sms_array['content']);
	//echo $sms_array['content'].'<br>';
	//echo $content.'<br>';

	$file_name = $sms_array['file_name'];

	$now_id = ($member['mb_id']) ? $member['mb_id'] : 'woogle';

	$sql = "INSERT INTO MMS_MSG SET
			SUBJECT = '".$subject."'
			,PHONE = '".$to_number."'
			,CALLBACK = '".$from_number."'
			,REQDATE = now()
			,MSG = '".$content."'
			,FILE_PATH1 = '/usr/local/lguplus/mmsfile'
			,SENTDATE = now()
			,TYPE = '0'
			,ID = '".$now_id."'
			,POST = 'woogle'
			,ETC1 = '".$_SERVER['REMOTE_ADDR']."'
			,ETC2 = '".$file_name."'
	";
	//mysql_query($sql);
	sql_query($sql);

	sms_woogle_server_close();	// 디비 종료
}
}


// 인트라3 서버 연결
if(!function_exists('intra3_server_connect')){
function intra3_server_connect()
{
	global $g5,$connect_db;

	// 기존 디비 연결 해제
	$link = $g5['connect_db'];
	if( function_exists('mysqli_query') )
		$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	else
		$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	$smsDbHost="119.207.79.38";
	$smsDbUser="intra";
	$smsDbPass="nccjelql@38";
	$smsDbName="ypage_www";
    $connect_db = sql_connect($smsDbHost, $smsDbUser, $smsDbPass, $smsDbName) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db($smsDbName, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// 인트라3 서버 연결 종료
if(!function_exists('intra3_server_close')){
function intra3_server_close()
{
	global $g5,$connect_db;

	// 종료
    mysqli_close($connect_db);

	// 영카트 디비 재연결
    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// 인트라31(복제사이트) 서버 연결
if(!function_exists('intra31_server_connect')){
function intra31_server_connect()
{
	global $g5,$connect_db;

	// 기존 디비 연결 해제
	$link = $g5['connect_db'];
	if( function_exists('mysqli_query') )
		$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	else
		$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	$smsDbHost="119.205.221.33";
	$smsDbUser="ypage";
	$smsDbPass="db@ypage";
	$smsDbName="ypage_www";
    $connect_db = sql_connect($smsDbHost, $smsDbUser, $smsDbPass, $smsDbName) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db($smsDbName, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}

// 인트라31 서버 연결 종료
if(!function_exists('intra31_server_close')){
function intra31_server_close()
{
	global $g5,$connect_db;

	// 종료
    mysqli_close($connect_db);

	// 영카트 디비 재연결
    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    $g5['connect_db'] = $connect_db;
    sql_set_charset('utf8', $connect_db);

}
}



// 사이트 - 상품 정보 업데이트
// 관련 변수: sit_idx, it_id, sti_type(theme=테마,plugin=플러그인..)
if(!function_exists('site_item_update')){
function site_item_update($stis)
{
	global $g5,$config;

	// sit_idx & sti_type 중복 체크 (상품이 변경되는 상황을 체크하기 위해서 두개 값만 추출하여 검사)
	$sti = sql_fetch(" SELECT * FROM {$g5['site_item_table']}
						WHERE sit_idx='{$stis['sit_idx']}'
							AND sti_type='{$stis['sti_type']}'
							AND sti_status='ok'
	");

	$sql_common = " sit_idx = '".$stis['sit_idx']."'
					, it_id = '".$stis['it_id']."'
					, sti_type = '".$stis['sti_type']."'
					, sti_more = '".$stis['sti_more']."'
					, sti_memo = '".$stis['sti_memo']."'
	";

	// 있으면 UPDATE
	if($sti['sti_idx']) {
		// 기존 설정 유지인 경우 다른 정보 업데이트
		if($sti['it_id'] == $stis['it_id']) {
			$sql = " UPDATE {$g5['site_item_table']} SET {$sql_common} WHERE sti_idx='".$sti['sti_idx']."' ";
			sql_query($sql,1);
			$sti_idx = $sti['sti_idx'];

		}
		// 상품설정이 바뀐 경우는 기존 정보를 과거로 돌리고 새정보 입력
		else {
			$sti['sti_update_dt'] = G5_TIME_YMDHIS;
			$sql = " UPDATE {$g5['site_item_table']} SET sti_status='history', sti_update_dt='".G5_TIME_YMDHIS."' WHERE sti_idx='".$sti['sti_idx']."' ";
			sql_query($sql,1);

			$sql = " INSERT INTO {$g5['site_item_table']} SET sti_status='ok', sti_reg_dt='".G5_TIME_YMDHIS."', sti_update_dt='".G5_TIME_YMDHIS."', {$sql_common} ";
			sql_query($sql,1);
			$sti_idx = sql_insert_id();
		}

	}
	// 없으면 INSERT
	else {
		$sql = " INSERT INTO {$g5['site_item_table']} SET sti_status='ok', sti_reg_dt='".G5_TIME_YMDHIS."', sti_update_dt='".G5_TIME_YMDHIS."', {$sql_common} ";
		sql_query($sql,1);
		$sti_idx = sql_insert_id();
	}

	return $sti_idx;
}
}

// 작업상태에 따른 border 색상
if(!function_exists('get_sit_work_border')){
function get_sit_work_border($status,$box_no)
{
	global $g5;

    $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_sit_work_status_line']));
    if( is_array($set_values) ) {
        for($i=0;$i<sizeof($set_values);$i++) {
            $set_value = explode('|', $set_values[$i]);
            for($j=0;$j<sizeof($set_value);$j++) {
                //print_r3($set_value[$j]).'<br>';
                //$g5['set_sit_work_status_line_colors'][$i][] = $set_value[$j];
                if($status==$set_value[$j] && $box_no==$i)
                    $style = 'border:solid 1px #fd6565;';
            }
        }
    }

	return $style;
}
}

// 날짜가 유효한 값인지 체크
if(!function_exists('check_date')){
function check_date($date)
{
    if($date=='0000-00-00'||$date=='0000-00-00 00:00:00'||$date=='') {
        return false;
    }
    else {
        return true;
    }
}
}


// 사이트 정보 삭제 (order에서삭제, cart에서 삭제)
// delete_sites(array('ct_id'=>$ct_id)); delete_sites(array('od_id'=>$od_id));
if(!function_exists('delete_sites')){
function delete_sites($arr)
{
    global $g5;

	if(!is_array($arr))
		return false;
	//print_r2($arr);

    // 주문쪽에서 삭제하는 경우는 해당 cart 돌면서 체크
    if($arr['od_id']) {
        $sql_search = " od_id = '".$arr['od_id']."' ";
    }
    // 장바구니쪽에서 삭제하는 경우는 해당 cart 정보 돌면서 체크하고 삭제
    else if($arr['ct_id']) {
        $sql_search = " ct_id = '".$arr['ct_id']."' ";
    }

    $sql = " SELECT ct_id FROM {$g5['g5_shop_cart_table']} WHERE ".$sql_search;
    $result = sql_query($sql,1);
    //echo $sql.'<br>';
    for($i=0; $row=sql_fetch_array($result); $i++) {
        delete_site($row['ct_id']);
    }

	return $sls_idx;

}
}

// 사이트 정보 삭제 (site에서삭제)
if(!function_exists('delete_site')){
function delete_site($ct_id,$del=0)
{
    global $g5,$member;

    if(!$ct_id)
        return;

    // site 에 해당 정보가 존재하면 삭제
    $sql = " SELECT sit_idx FROM {$g5['site_table']} WHERE ct_id = '".$ct_id."' ";
    $sit = sql_fetch($sql,1);
    if($sit['sit_idx']) {

        if($del) {
            $sql = " DELETE FROM {$g5['site_table']} WHERE sit_idx = '".$sit['sit_idx']."' ";
            sql_query($sql,1);
            $sql = " DELETE FROM {$g5['meta_table']} WHERE mta_db_table = 'site' AND mta_db_id = '".$sit['sit_idx']."' ";
            sql_query($sql,1);
        }
        else {
            $sql = "UPDATE {$g5['site_table']} SET
                        sit_status = 'trash'
                        , sit_memo = CONCAT(sit_memo,'\n".G5_TIME_YMDHIS." 삭제 by ".$member['mb_name']."')
                    WHERE sit_idx = '".$sit['sit_idx']."'
            ";
            //echo $sql.'<br>';
            sql_query($sql,1);
        }

    }

	return $ct_id;

}
}

// 장바구니 연결 도메인들 추출 함수
if(!function_exists('site_domains')){
function site_domains($com_idx,$ct_id)
{
    global $g5;

    if(!$com_idx||!$ct_id)
        return;

    $sql3 = "   SELECT *
                    , ( SELECT GROUP_CONCAT(std_subdomain) FROM {$g5['site_domain_table']} WHERE dmn_idx = dmn.dmn_idx AND std_status NOT IN ('trash') ) AS sit_sub_domains
                    , ( SELECT GROUP_CONCAT(std_status) FROM {$g5['site_domain_table']} WHERE dmn_idx = dmn.dmn_idx AND std_status NOT IN ('trash') ) AS sit_std_status
                FROM {$g5['domain_table']} AS dmn
                WHERE com_idx = '".$com_idx."'
                    AND ct_id = '".$ct_id."'
                    AND dmn_status NOT IN ('trash')
                ORDER BY dmn_reg_dt
    ";
    $rs3 = sql_query($sql3,1);
    //echo $sql3.'<br>';
    for($j=0;$row3 = sql_fetch_array($rs3);$j++) {
        // 2차 도메인들이 있는 경우
        if($row3['sit_sub_domains']) {
            $row3['sit_sub_domains_array'] = explode(",",$row3['sit_sub_domains']);
            $row3['sit_std_status_array'] = explode(",",$row3['sit_std_status']);
            for($k=0;$k<sizeof($row3['sit_sub_domains_array']);$k++) {
                $row['sit_domain'][$j][$k] = '<a href="//'.$row3['sit_sub_domains_array'][$k].'.'.$row3['dmn_domain'].'" target="_blank"><i class="fa fa-link"></i> '
                                                .$row3['sit_sub_domains_array'][$k].'.'.$row3['dmn_domain'].'</a> ['.$g5['set_std_status_value'][$row3['sit_std_status_array'][$k]].']';
                $row['sit_domain_text'] .= $row['sit_domain'][$j][$k].'<br>';
                $row['sit_domains'][] .= $row['sit_domain'][$j][$k];
            }
        }
        // 도메인만 등록된 경우는 실서버연결 대기 상태!
        else {
            //print_r2($row3);
            $row['dmn_status_text'][$j] = '<span style="color:darkorange;">'.$g5['set_dmn_status_value'][$row3['dmn_status']].'</span>';
            // 도메인 상태 ok && 제작 상태값이 '완료' && 완료예정일이 지난 경우
            if($row3['dmn_status']=='ok'&&$row['sit_work_status']=='ok'&&$row['sit_schedule_date']<=G5_TIME_YMD) {
                $row['dmn_status_text'][$j] = '<a href="'.G5_BBS_URL.'/write.php?bo_table=dns1&dmn_idx='.$row3['dmn_idx'].'" target="_blank"><span style="color:blue;">실섭연결신청</span></a>';
            }
            $row3['dmn_domain'] = (!$row3['dmn_domain']) ? $row3['dmn_domain1'] : $row3['dmn_domain'];

            $row['sit_domain'][$j] = $row3['dmn_domain'].' ['.$row['dmn_status_text'][$j].']';
            $row['sit_domain_text'] .= $row['sit_domain'][$j].'<br>';
            $row['sit_domains'][] .= $row3['dmn_domain'];
        }
    }

	return array("sit_domain_count"=>$j,"sit_domain_text"=>$row['sit_domain_text'],"sit_domains"=>implode(",",$row['sit_domains']));
}
}

// 업체명 변경
if(!function_exists('change_com_names')){
function change_com_names($com_idx,$new_com_name)
{
    global $g5;

    if(!$com_idx||!$new_com_name)
        return;

	$com = get_table_meta('company','com_idx',$com_idx);

    // I intended to change keys info of order_table. cart_table, sales.
    // But it could break data integrity and have to change all data each time changing.
    // So trying to join tables.

    // Change all board info if needed.
    echo 55;


    return true;
}
}

// 생간시간구간/생산반영일 반환하는 함수
if(!function_exists('item_shif_date_return')){
function item_shif_date_return($date){
    global $g5;
    //입력되는 날짜의 정보
    $today_dt = $date;
    $today_date = explode(" ",$date);
    $today = $today_date[0];//입력날짜 예) 2021-12-11
    $todaytime = strtotime($today_dt);

    //아래 시간 범위에서는 전날 실적이므로 전날의 날짜를 반환해야 한다.
    $gstart_dt = $today." 00:00:00";
    $gstarttime = strtotime($gstart_dt);
    $gend_dt = $today." 05:09:59";
    $gendtime = strtotime($gend_dt);

    $shift = 0;
    $return_date = $today;
    //print_r2($g5['set_itm_shift_value']);
    foreach($g5['set_itm_shift_value'] as $k=>$v){
        $times = explode("-",$v);
        $start_dt = $today." ".$times[0];
        $end_dt = $today." ".$times[1];
        $endtime = strtotime($end_dt);
        $starttime = strtotime($start_dt);
        if($todaytime >= $starttime && $todaytime <= $endtime) $shift = (int)substr($k,0,2);
        if($todaytime >= $gstarttime && $todaytime <= $gendtime) $return_date = get_dayAddDate($today,-1);
    }

    $arr = array('shift'=>$shift,'workday'=>$return_date);
    return $arr;
}
}

// 생간시간구간/생산반영일 반환하는 함수(한국수지에만 해당)
if(!function_exists('item_shif_date_return2')){
function item_shif_date_return2($date){
    global $g5;
    //입력되는 날짜의 정보
    $today_dt = $date;
    $today_date = explode(" ",$date);
    $today = $today_date[0];//입력날짜 예) 2021-12-11
    $todaytime = strtotime($today_dt);

    //아래 시간 범위에서는 전날 실적이므로 전날의 날짜를 반환해야 한다.
    $gstart_dt = $today." 00:00:00";
    $gstarttime = strtotime($gstart_dt);
    $gend_dt = $today." 05:09:59";
    $gendtime = strtotime($gend_dt);

    $shift = 0;
    $return_date = $today;
    //print_r2($g5['set_itm_shift3_value']);
    foreach($g5['set_itm_shift3_value'] as $k=>$v){
        $times = explode("-",$v);
        $start_dt = $today." ".$times[0];
        $end_dt = $today." ".$times[1];
        $endtime = strtotime($end_dt);
        $starttime = strtotime($start_dt);
        if($todaytime >= $starttime && $todaytime <= $endtime) $shift = (int)substr($k,0,2);
        // if($todaytime >= $gstarttime && $todaytime <= $gendtime) $return_date = get_dayAddDate($today,-1);
    }

    // $arr = array('shift'=>$shift,'workday'=>$return_date);
    // return $arr;
    return $shift;
}
}
?>
