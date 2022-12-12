<?php
/*
        for($i=0;$i<$getData[0]['number'];$i++){
            $date_plus = strtotime($start_date."+".($i*5)." second");
            $start_date = date('Y-m-d H:i:s',$date_plus);
            $date_minus = strtotime($start_date."-2 days");
            $start_dt = date('Y-m-d H:i:s',$date_minus);
            $sql_plus = $sql;
            $sql_plus .= "
                , mtr_input_date = '{$oop['orp_start_date']}'
                , mtr_reg_dt = '{$start_dt}'
                , mtr_update_dt = '{$start_dt}'
            ";
            sql_query($sql_plus,1);
        }
*/
$start_date = $bom1['orp_start_date'].' '.substr(G5_TIME_YMDHIS,-8);
for($j=0;$j<$getData[0]['number'];$j++){
    $date_plus = strtotime($start_date."+".($j*5)." second");
    $start_date = date('Y-m-d H:i:s',$date_plus);

    if($bom1['oop_onlythis_yn']
        || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '0_1'
        || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '1_1'
        || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '2_1'){
        $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_heat, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES 
        ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$getData[0]['heat']}', 'finish', '".substr($start_date,0,10)."', '".$start_date."', '".$start_date."' )
        ";
    }
    //press_type 규정에 반영하여 생성할것인가
    else {
        if($bom1['bom_press_type'] == '2_2'){
            $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_heat, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES 
            ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$getData[0]['heat']}', 'finish', '".substr($start_date,0,10)."', '".$start_date."', '".$start_date."' )

            , ( '{$bom2['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom2['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom2['bom_part_no']}', '".addslashes($bom2['bom_name'])."', '{$bom2['bom_weight']}', '{$getData[0]['heat']}', 'finish', '".substr($start_date,0,10)."', '".$start_date."', '".$start_date."' )
            ";
        }
        else {
            $tp_arr = explode('_',$bom1['bom_press_type']);
            $cp_num = $tp_arr[1]; //복제갯수
            $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_heat, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES ";
            $sql_loop = '';
            for($i=0;$i<$cp_num;$i++){
                $sql_loop .= (($i==0)?'':',')." ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$getData[0]['heat']}', 'finish', '".substr($start_date,0,10)."', '".$start_date."', '".$start_date."' ) ";
            }
            $sql = $sql.$sql_loop;
        }
    }
    sql_query($sql,1);

    $half_sql = " UPDATE {$g5['material_table']} SET mtr_status = 'finish'
        WHERE oop_idx = '{$getData[0]['oop_idx']}'
            AND mtr_type = 'half'
            AND mtr_status = 'stock'
        ORDER BY mtr_idx
        LIMIT 1
    ";
    sql_query($half_sql);
}