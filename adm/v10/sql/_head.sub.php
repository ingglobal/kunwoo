<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_SQL_URL.'/css/sql.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_SQL_URL.'/js/sql.js"></script>', 0);
?>
<div id="sql_head">
    <a class="<?=(($g5['file_name'] == 'index')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>">SQL홈</a>
    <a class="" href="<?=G5_USER_ADMIN_URL?>">관리자홈</a>
    <a class="<?=(($g5['file_name'] == 'reg_bom0')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/reg_bom0.php">BOM초기등록</a>
    <a class="<?=(($g5['file_name'] == 'reg_bom_item')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/reg_bom_item.php">BOM트리구성</a>
    <!--
    <a class="<?=(($g5['file_name'] == 'reg_workplace_uph')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/reg_workplace_uph.php">작업장상품UPH등록</a>
    <a class="<?=(($g5['file_name'] == 'upt_bom_model_texture')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/upt_bom_model_texture.php">BOM모델재질추가</a>
    <a class="<?=(($g5['file_name'] == 'upt_material_half_price')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/upt_material_half_price.php">원자재반제품가격</a>
    <a class="<?=(($g5['file_name'] == 'upt_item_price')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/upt_item_price.php">완제품가격수정</a>
    <a class="<?=(($g5['file_name'] == 'reg_company')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/reg_company.php">업체등록</a>
    <a class="<?=(($g5['file_name'] == 'mod_myisam')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/mod_myisam.php">MyISAM변경</a>
    <a class="<?=(($g5['file_name'] == 'add_companies_direct')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/add_companies_direct.php">기존업체DB등록(직접)</a>
    <a class="<?=(($g5['file_name'] == 'add_products_direct')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/add_products_direct.php">기존제품DB등록(직접)</a>
    <a class="<?=(($g5['file_name'] == 'add_material_direct')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/add_material_direct.php">기존원자재DB등록(직접)</a>
    <a class="<?=(($g5['file_name'] == 'add_item20_direct')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/add_item20_direct.php">기존절단재DB등록(직접)</a>
    <a class="<?=(($g5['file_name'] == 'add_item30_direct')?'focus':'')?>" href="<?=G5_USER_ADMIN_SQL_URL?>/add_item30_direct.php">기존완제품DB등록(직접)</a>
    -->
</div>
<div id="sql_container">
