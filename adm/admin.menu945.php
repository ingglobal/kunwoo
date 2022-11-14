<?php
if($is_admin){
	$menu["menu945"] = array (
		array('945000', '재고관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945100', '수불전표관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		// ,array('945110', '원자재재고관리', ''.G5_USER_ADMIN_URL.'/material_list.php', 'material_list')
		,array('945113', '반제품재고관리', ''.G5_USER_ADMIN_URL.'/half_list.php', 'half_list')
		,array('945115', '완제품재고관리', ''.G5_USER_ADMIN_URL.'/item_list.php', 'item_list')
		//,array('945118', '고객처재고관리', ''.G5_USER_ADMIN_URL.'/guest_item_list.php', 'guest_item_list')
		//,array('945125', '파렛트조회', ''.G5_USER_ADMIN_URL.'/pallet_list.php', 'pallet_list')
		//,array('945120', '자재선출처리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945130', '모델별 생산관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945140', '자재소요량산출', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945150', '발주서관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
	);

}
else{
	$menu["menu945"] = array (
		array('945000', '재고관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945100', '수불전표관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		// ,array('945110', '원자재재고관리', ''.G5_USER_ADMIN_URL.'/material_list.php', 'material_list')
		,array('945113', '반제품재고관리', ''.G5_USER_ADMIN_URL.'/half_list.php', 'half_list')
		,array('945115', '완제품재고관리', ''.G5_USER_ADMIN_URL.'/item_list.php', 'item_list')
		//,array('945118', '고객처재고관리', ''.G5_USER_ADMIN_URL.'/guest_item_list.php', 'guest_item_list')
		// ,array('945125', '파렛트조회', ''.G5_USER_ADMIN_URL.'/pallet_list.php', 'pallet_list')
		//,array('945120', '자재선출처리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945130', '모델별 생산관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945140', '자재소요량산출', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
		//,array('945150', '발주서관리', ''.G5_USER_ADMIN_URL.'/config_form.php', 'config_form')
	);
}
