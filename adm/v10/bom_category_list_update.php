<?php
$sub_menu = '915125';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

if ($_POST['act_button2'] == "일괄수정") {
    $post_bct_id_count = (isset($_POST['bct_id']) && is_array($_POST['bct_id'])) ? count($_POST['bct_id']) : 0;

    for ($i=0; $i<$post_bct_id_count; $i++)
    {
        $sql = " update {$g5['bom_category_table']}
                    set bct_name    = '".$_POST['bct_name'][$i]."',
                        bct_desc    = '".$_POST['bct_desc'][$i]."',
                        bct_order   = '".sql_real_escape_string(strip_tags($_POST['bct_order'][$i]))."'
                where bct_id = '".sql_real_escape_string($_POST['bct_id'][$i])."'
                    AND com_idx = '".$_SESSION['ss_com_idx']."'
        ";
        sql_query($sql,1);
    }
}
if ($_POST['act_button'] == "분류환경변수설정반영") {
    $ids = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
    $idarr = array();
    for($i=0;$i<count($ids);$i++){
        if($i == 0) continue;
        for($j=0;$j<count($ids);$j++){
            //echo $ids[$i].$ids[$j]."<br>";
            array_push($idarr,$ids[$i].$ids[$j]);
        }
    }

    $cat1_vals = explode("\n", $g5['setting']['set_cat_1']);
    $cat1_vals = array_values(array_filter(array_map('trim',$cat1_vals)));
    $cat2_vals = explode("\n", $g5['setting']['set_cat_2']);
    $cat2_vals = array_values(array_filter(array_map('trim',$cat2_vals)));
    $cat3_vals = explode("\n", $g5['setting']['set_cat_3']);
    $cat3_vals = array_values(array_filter(array_map('trim',$cat3_vals)));
    $cat4_vals = explode("\n", $g5['setting']['set_cat_4']);
    $cat4_vals = array_values(array_filter(array_map('trim',$cat4_vals)));

    //기존의 해당업체의 레코드를 전부 삭제한다.
    $all_del_sql = " DELETE FROM {$g5['bom_category_table']} WHERE com_idx = '".$_SESSION['ss_com_idx']."' ";
    sql_query($all_del_sql,1);

    if(count($cat1_vals)){
        for($i=0;$i<count($cat1_vals);$i++){
            $cd1 = $idarr[$i];
            //echo $cd1."<br>";
            list($key,$value) = explode('=',$cat1_vals[$i]);
            $key = trim($key);
            $value = trim($value);
            
            $ist_sql = " INSERT INTO {$g5['bom_category_table']} SET
                bct_id = '{$cd1}'
                ,com_idx = '{$_SESSION['ss_com_idx']}'
                ,bct_name = '{$key}'
                ,bct_desc = '{$value}'
                ,bct_order = '0'
                ,bct_reg_dt = '".G5_TIME_YMDHIS."'
                ,bct_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($ist_sql,1);

            if(count($cat2_vals)){
                for($j=0;$j<count($cat2_vals);$j++){
                    $cd2 = $cd1.$idarr[$j];
                    //echo $cd2."<br>";
                    list($key,$value) = explode('=',$cat2_vals[$j]);
                    $key = trim($key);
                    $value = trim($value);

                    $ist_sql = " INSERT INTO {$g5['bom_category_table']} SET
                        bct_id = '{$cd2}'
                        ,com_idx = '{$_SESSION['ss_com_idx']}'
                        ,bct_name = '{$key}'
                        ,bct_desc = '{$value}'
                        ,bct_order = '0'
                        ,bct_reg_dt = '".G5_TIME_YMDHIS."'
                        ,bct_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($ist_sql,1);
                    
                    if(count($cat3_vals)){
                        for($k=0;$k<count($cat3_vals);$k++){
                            $cd3 = $cd2.$idarr[$k];
                            //echo $cd3."<br>";
                            list($key,$value) = explode('=',$cat3_vals[$k]);
                            $key = trim($key);
                            $value = trim($value);
                            //echo $cd3.'-'.$key.'-'.$value."<br>";

                            $ist_sql = " INSERT INTO {$g5['bom_category_table']} SET
                                bct_id = '{$cd3}'
                                ,com_idx = '{$_SESSION['ss_com_idx']}'
                                ,bct_name = '{$key}'
                                ,bct_desc = '{$value}'
                                ,bct_order = '0'
                                ,bct_reg_dt = '".G5_TIME_YMDHIS."'
                                ,bct_update_dt = '".G5_TIME_YMDHIS."'
                            ";
                            sql_query($ist_sql,1);
                            
                            if(count($cat4_vals)){
                                for($l=0;$l<count($cat4_vals);$l++){
                                    $cd4 = $cd3.$idarr[$l];
                                    //echo $cd4."<br>";
                                    list($key,$value) = explode('=',$cat4_vals[$l]);
                                    $key = trim($key);
                                    $value = trim($value);
                                    
                                    $ist_sql = " INSERT INTO {$g5['bom_category_table']} SET
                                        bct_id = '{$cd4}'
                                        ,com_idx = '{$_SESSION['ss_com_idx']}'
                                        ,bct_name = '{$key}'
                                        ,bct_desc = '{$value}'
                                        ,bct_order = '0'
                                        ,bct_reg_dt = '".G5_TIME_YMDHIS."'
                                        ,bct_update_dt = '".G5_TIME_YMDHIS."'
                                    ";
                                    sql_query($ist_sql,1);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
//exit;
goto_url("./bom_category_list.php?$qstr");