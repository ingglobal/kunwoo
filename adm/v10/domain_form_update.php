<?php
$sub_menu = '950600';
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');
//check_admin_token();

// 넘겨줄 변수가 추가로 있어서 qstr 추가 (한글이 있으면 encoding)
$qstr = $qstr."&amp;sfl_date=$sfl_date&amp;st_date=$st_date&amp;en_date=$en_date&amp;ser_trm_idxs=$ser_trm_idxs";

//지번 분리작업.
$dmn_zip1 = substr($_POST['dmn_zip'], 0, 3);
$dmn_zip2 = substr($_POST['dmn_zip'], 3);

// 소유자 추가 정보 (serialize)
// 주민등록번호+사업자등록번호+주소+영문주소
$dmn_owner_infos['dmn_biz_no'] = $dmn_biz_no;
$dmn_owner_infos['dmn_zip1'] = $dmn_zip1;
$dmn_owner_infos['dmn_zip2'] = $dmn_zip2;
$dmn_owner_infos['dmn_addr1'] = $dmn_addr1;
$dmn_owner_infos['dmn_addr2'] = $dmn_addr2;
$dmn_owner_infos['dmn_addr3'] = $dmn_addr3;
$dmn_owner_infos['dmn_address_eng'] = $_POST['dmn_address_eng'];
$dmn_owner_info = serialize($dmn_owner_infos);


// 이전 도메인 등록기관 (serialize)
$dmn_registerer_infos['dmn_registerer_company'] = $dmn_registerer_company;
$dmn_registerer_infos['dmn_registerer_id'] = $dmn_registerer_id;
$dmn_registerer_infos['dmn_registerer_pw'] = $dmn_registerer_pw;
$dmn_registerer_info = serialize($dmn_registerer_infos);

// 만료일
$dmn_expire_date = (!$dmn_expire_date) ? '0000-00-00' : $dmn_expire_date;


$sql_common = "	ct_id					= '{$_POST['ct_id']}'
					, com_idx                = '{$_POST['com_idx']}'
					, mb_id                   = '{$_POST['mb_id']}'
					, mb_id_saler            = '{$_POST['mb_id_saler']}'             
					, dmn_domain          	= '{$_POST['dmn_domain']}'           
					, dmn_domain1         = '{$_POST['dmn_domain1']}'           
					, dmn_domain2         = '{$_POST['dmn_domain2']}'           
					, dmn_punicode        = '{$_POST['dmn_punicode']}'         
					, dmn_registerer       	= '{$_POST['dmn_registerer']}'         
					, dmn_registerer_old  	= '".$dmn_registerer_info."'
					, dmn_name             = '{$_POST['dmn_name']}'              
					, dmn_name_eng       = '{$_POST['dmn_name_eng']}'        
					, dmn_tel       			= '{$_POST['dmn_tel']}'
					, dmn_owner_info      = '".$dmn_owner_info."'
					, dmn_apply_name     = '{$_POST['dmn_apply_name']}'      
					, dmn_apply_tel        	= '{$_POST['dmn_apply_tel']}'          
					, dmn_price             	= '{$_POST['dmn_price']}'               
					, dmn_dns1              = '{$_POST['dmn_dns1']}'               
					, dmn_dns1_ip          = '{$_POST['dmn_dns1_ip']}'           
					, dmn_dns2              = '{$_POST['dmn_dns2']}'               
					, dmn_dns2_ip          = '{$_POST['dmn_dns2_ip']}'           
					, dmn_expire_date     	= '".$dmn_expire_date."'
					, dmn_memo            = '{$_POST['dmn_memo']}'             
					, dmn_reject_memo    = '{$_POST['dmn_reject_memo']}'
					, dmn_admin_memo   = '{$_POST['dmn_admin_memo']}'    
					, dmn_status            	= '{$_POST['dmn_status']}'             
";

// 도메인 정보 추출
if ($w != '') {
	$dmn = sql_fetch(" SELECT * FROM {$g5['domain_table']} WHERE dmn_idx = '$dmn_idx' ");
}
	
// 생성
if ($w == '') {

    // 업체 정보 생성
    $sql = " INSERT INTO {$g5['domain_table']} SET
				dmn_reg_dt = '".G5_TIME_YMDHIS."'
				, dmn_update_dt = '".G5_TIME_YMDHIS."'
				,{$sql_common} 
	";
    sql_query($sql,1);
	$dmn_idx = sql_insert_id();

}
// 수정
else if ($w == 'u') {

    $sql = " UPDATE {$g5['domain_table']} SET
				dmn_update_dt = '".G5_TIME_YMDHIS."'
				,{$sql_common} 
				WHERE dmn_idx = '".$dmn_idx."'
	";
    sql_query($sql,1);

}
else if ($w=="d") {

	if (!$dmn['dmn_idx']) {
		alert('존재하지 않는 업체자료입니다.');
	} else {
		// 자료 삭제
		$sql = " UPDATE {$g5['domain_table']} SET dmn_status = 'trash' WHERE dmn_idx = $dmn_idx ";
		sql_query($sql,1);
	}
	
	goto_url('./domain_list.php?'.$qstr, false);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


goto_url('./domain_form.php?'.$qstr.'&amp;w=u&amp;dmn_idx='.$dmn_idx, false);
?>