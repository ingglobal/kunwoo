<?php
include_once("./_common.php");
//echo $com_idx."<br>";
//echo $mb_id."<br>";
//echo $fle_idx;
//$g5['project_table'] 
//$g5['member_table']
//$g5['company_table']
//$g5['company_member_table']

$prj_arr = sql_fetch(" SELECT prj_name FROM {$g5['project_table']} WHERE prj_idx = '{$prj_idx}' ");
$fle_arr = sql_fetch(" SELECT * FROM {$g5['file_table']} WHERE fle_idx = '{$fle_idx}' ");
$com_sql = " SELECT com.com_name,com.com_biz_no,com.com_tel,cmm.cmm_title,mb.mb_name,mb.mb_email
                FROM {$g5['company_table']} AS com
                LEFT JOIN {$g5['company_member_table']} AS cmm ON cmm.com_idx = com.com_idx
                LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cmm.mb_id
            WHERE com.com_idx = '{$com_idx}'
                AND mb.mb_id = '{$mb_id}'
";
$com_arr = sql_fetch($com_sql);
$m = array();
$m['prj_idx'] = $prj_idx;
$m['prj_name'] = $prj_arr['prj_name'];
$m['fle_path'] = $fle_arr['fle_path'];
$m['fle_name'] = $fle_arr['fle_name'];
$m['fle_name_orig'] = $fle_arr['fle_name_orig'];
$m['com_name'] = $com_arr['com_name'];
$m['com_biz_no'] = $com_arr['com_biz_no'];
$m['com_tel'] = $com_arr['com_tel'];
$m['cmm_title'] = $com_arr['cmm_title'];
$m['to_name'] = $com_arr['mb_name'];
$m['to_email'] = $com_arr['mb_email'];
$m['from_name'] = $member['mb_name'];
$m['from_email'] = $member['mb_email'];
$m['subject'] = $prj_arr['prj_name'].' 견적서 ('.G5_TIME_YMDHIS.')';
$m['short_memo'] = $com_arr['mb_name'].' '.$g5['set_mb_ranks_value'][$com_arr['cmm_title']].'님';

if($m['from_email'] && $m['to_email']){
    include_once(G5_LIB_PATH.'/mailer.lib.php');
    $orl_no = 'quot-attachment-file-'.G5_SERVER_TIME;

    $file_down = (is_file(G5_PATH.$m['fle_path'].'/'.$m['fle_name'])) ? '<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$m['fle_path'].'/'.$m['fle_name']).'&file_name_orig='.$m['fle_name_orig'].'" title="'.$m['fle_name_orig'].'" target="_self"><i class="fa fa-file" aria-hidden="true"></i>&nbsp;'.$m['fle_name_orig'].'[다운로드]</a>':''.PHP_EOL;

    ob_start();
    include_once (G5_USER_ADMIN_PATH.'/quot_form_email_html.php');
    $content = ob_get_contents();
    ob_end_clean();

    /*
    $file_name = $m['fle_path'].'/'.$m['fle_name'];
    $files[] = attach_file($m['fle_name_orig'],$file_name);
    */
    mailer($default['de_admin_company_name'].'('.$m['from_name'].')', $m['from_email'], $m['to_email'], $m['subject'], $content, 1);	

    $sql = " INSERT {$g5['order_log_table']} 
                SET od_id = ''
                    ,orl_no = '{$orl_no}'
                    ,orl_com_name = '{$m['com_name']}'
                    ,orl_com_biz_no = '{$m['com_biz_no']}'
                    ,orl_com_manager = '{$m['to_name']}'
                    ,orl_com_tel = '{$m['com_tel']}'
                    ,orl_com_fax = ''
                    ,orl_com_email = '{$m['to_email']}'
                    ,orl_com_addr = ''
                    ,orl_sender_id = '{$member['mb_id']}'
                    ,orl_receiver_id = '{$mb_id}'
                    ,orl_type = 'quot-attachment-file'
                    ,orl_subject = '{$m['subject']}'
                    ,orl_memo = 'quot_form_prj_idx_{$prj_idx}'
                    ,orl_reg_dt = NOW()
    ";
    sql_query($sql,1);
    echo 'email_success';
}else{
    echo 'email_error';
}
?>