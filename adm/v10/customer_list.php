
<?php
$sub_menu = "915120";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


$g5['title'] = '거래처정보';
// include_once('./_top_menu_company.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['company_table']} AS com 
                LEFT JOIN {$g5['company_member_table']} AS cmm ON cmm.com_idx = com.com_idx AND cmm_status = 'ok'
                LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cmm.mb_id AND mb_level >= 4
"; 

//-- 업종 검색
$sql_com_type = ($ser_com_type) ? " AND com_type IN ('".$ser_com_type."') " : "";

$where = array();
$where[] = " com_level = '2' AND com_idx_par = ".$_SESSION['ss_com_idx']." AND com_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
// print_r2($_SESSION);
// 운영권한이 없으면 자기것만
if (false){ //(!$member['mb_manager_yn']) {
    // company_saler 교차 테이블에서 내 것만 추출
    $where[] = " com.com_idx IN ( SELECT com.com_idx
        FROM {$g5['company_table']} AS com
            LEFT JOIN {$g5['company_saler_table']} AS cms ON cms.com_idx = com.com_idx
        WHERE mb_id_saler = '".$member['mb_id']."'
        GROUP BY com.com_idx ) ";
    // 관련직원(영업자) 추가쿼리
    $sql_mb_firms = " AND mb_id = '".$member['mb_id']."' ";
}
//print_r3($member['mb_manager_yn']);
if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
            break;
		case ( $sfl == 'mb_id' || $sfl == 'com_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'mb_name' || $sfl == 'mb_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "com_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT com.com_idx, com_name, com_names, com_type, com_reg_dt, com_status
            ,com_tel, com_president, com_email, com_fax
            ,GROUP_CONCAT( CONCAT(
                'mb_id=', cmm.mb_id, '^'
                ,'cmm_title=', cmm.cmm_title, '^'
                ,'mb_name=', mb_name, '^'
                ,'mb_hp=', mb_hp
            ) ORDER BY cmm_reg_dt DESC ) AS com_namagers_info
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        GROUP BY com_idx
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 11;

// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
?>
<style>
    .b_default_company {color:#b01acc;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_value_options']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET['ser_com_type']?>').attr('selected','selected');</script>
<select name="sfl" id="sfl">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
    <!--option value="mb_name"<?php ;//echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option-->
    <!--option value="mb_hp"<?php ;//echo get_selected($_GET['sfl'], "mb_hp"); ?>>담당자휴대폰</option-->
    <option value="com_president"<?php echo get_selected($_GET['sfl'], "com_president"); ?>>대표자</option>
	<!--option value="com.com_idx"<?php ;//echo get_selected($_GET['sfl'], "com.com_idx"); ?>>업체고유번호</option-->
	<!--option value="cmm.mb_id"<?php //echo get_selected($_GET['sfl'], "cmm.mb_is"); ?>>담당자아이디</option-->
    <option value="com_status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>업체측 담당자를 관리하시려면 업체담당자 항목의 <i class="fa fa-edit"></i> 편집아이콘을 클릭하세요. 담당자는 여러명일 수 있고 이직을 하는 경우 다른 업체에 소속될 수도 있습니다. </p>
</div>

<form name="form01" id="form01" action="./customer_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_com_type" value="<?php echo $ser_com_type; ?>">
<input type="hidden" name="ser_trm_idx_salesarea" value="<?php echo $ser_trm_idx_salesarea; ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col">
			<label for="chkall" class="sound_only">업체 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col" class="td_left">번호</th>
		<th scope="col" class="td_left">업체명</th>
		<th scope="col">대표자명</th>
		<!-- <th scope="col">이메일</th> -->
		<?php if(false){ ?><th scope="col">업체담당자</th><?php } ?>
		<th scope="col" class="td_left">업종</th>
		<th scope="col" style="width:120px;">대표전화</th>
		<th scope="col" style="width:120px;">팩스</th>
		<th scope="col"><?php echo subject_sort_link('com_reg_dt','ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea) ?>등록일</a></th>
		<th scope="col"><?php echo subject_sort_link('com_status','ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea) ?>상태</a></th>
        <th scope="col" id="mb_list_mng">수정</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
		// 메타 분리
        if($row['com_namagers_info']) {
            $pieces = explode(',', $row['com_namagers_info']);
            for ($j1=0; $j1<sizeof($pieces); $j1++) {
                $sub_item = explode('^', $pieces[$j1]);
                for ($j2=0; $j2<sizeof($sub_item); $j2++) {
                    list($key, $value) = explode('=', $sub_item[$j2]);
//                    echo $key.'='.$value.'<br>';
                    $row['com_managers'][$j1][$key] = $value;
                }
            }
            unset($pieces);unset($sub_item);
        }
//		print_r2($row);
        
        // 담당자(들)
        if( is_array($row['com_managers']) ) {
            for ($j=0; $j<sizeof($row['com_managers']); $j++) {
//                echo $key.'='.$value.'<br>';
                $row['com_managers_text'] .= $row['com_managers'][$j]['mb_name'].' '.$g5['set_mb_ranks_value'][$row['com_managers'][$j]['cmm_title']]
                                                .' <span class="font_size_8">('.$row['com_managers'][$j]['mb_hp'].')</span><br>';
            }
        }

        // 직함까지 다 표현하려면 GROUP_CONCAT로 단순하게 합쳐버리면 안 됨
        $sql1 = "   SELECT mb_id, mb_name, mb_3
                    FROM {$g5['company_saler_table']} AS cms
                        LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cms.mb_id_saler
                    WHERE com_idx='".$row['com_idx']."' 
                        AND cms_status IN ('ok')
                        {$sql_mb_firms}
        ";
        $rs1 = sql_query($sql1,1);
        for($j=0;$row1=sql_fetch_array($rs1);$j++) {
            //print_r2($row1);
            $row['mb_name_salers'] .= $row1['mb_name'].' '.$g5['set_mb_ranks_value'][$row1['mb_3']].'<br>';
        }
        
		
		// 수정 및 발송 버튼
//		if($is_delete) {
			$s_mod = '<a href="./customer_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$row['com_idx'].'&amp;ser_com_type='.$ser_com_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" class="btn btn_03">수정</a>';
			//$s_pop = '<a href="javascript:company_popup(\'./company_order_list.popup.php?com_idx='.$row['com_idx'].'\',\''.$row['com_idx'].'\')">보기</a>';
//		}
		//$s_del = '<a href="./customer_form_update.php?'.$qstr.'&amp;w=d&amp;com_idx='.$row['com_idx'].'&amp;ser_com_type='.$ser_com_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        // default company class name
        $row['default_com_class'] = ($_SESSION['ss_com_idx']==$row['com_idx']&&$member['mb_manager_yn']) ? 'b_default_company' : '';
        
 
		// 삭제인 경우 그레이 표현
		if($row['com_status'] == 'trash')
			$row['com_status_trash_class']	= " tr_trash";

        $bg = 'bg'.($i%2);
    ?>

	<tr class="<?php echo $bg; ?> <?=$row['com_status_trash_class']?>" tr_id="<?php echo $row['com_idx'] ?>">
		<td class="td_chk">
			<input type="hidden" name="com_idx[<?php echo $i ?>]" value="<?php echo $row['com_idx'] ?>" id="com_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['com_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <td class="td_com_idx td_left font_size_8"><!-- 번호 -->
            <?php echo $row['com_idx'] ?>
        </td>
		<td class="td_com_name td_left"><!-- 업체명 -->
			<b class="<?=$row['default_com_class']?>"><?php echo get_text($row['com_name']); ?></b>
			<a style="display:none;" href="javascript:company_popup('./company_order_list.popup.php?com_idx=<?php echo $row['com_idx'];?>','<?php echo $row['com_idx'];?>')" style="float:right;"><i class="fa fa-window-restore"></i></a>
		</td>
		<td class="td_com_president"><!-- 대표자명 -->
			<?php echo get_text($row['com_president']); ?>
		</td>
		<!-- <td class="td_com_email font_size_8">
			<?php ;//echo cut_str($row['com_email'],21,'..'); ?>
		</td> -->
        <?php if(false){ ?>
		<td class="td_com_manager td_left"><!-- 담당자 -->
			<?php echo $row['com_managers_text']; ?>
            <div style="display:<?=($is_admin=='super')?:'no ne'?>"><a href="javascript:" com_idx="<?=$row['com_idx']?>" class="btn_manager"><i class="fa fa-edit"></i></a></div>
		</td>
        <?php } ?>
        <td class="td_com_type td_left font_size_8"><!-- 업종 -->
            <?php echo $g5['set_com_type_value'][$row['com_type']] ?>
        </td>
        <td class="td_com_tel"><!-- 대표전화 -->
            <span class="font_size_8"><?php echo $row['com_tel']; ?></span>
        </td>
        <td><span class="font_size_8"><?php echo $row['com_fax']; ?></span></td><!-- 팩스번호 -->
		<td class="td_com_reg_dt td_center font_size_8"><!-- 등록일 -->
			<?php echo substr($row['com_reg_dt'],0,10) ?>
		</td>
		<td headers="list_com_status" class="td_com_status"><!-- 상태 -->
            <?php echo $g5['set_com_status_value'][$row['com_status']] ?>
        </td>
        <td class="td_mngsmall">
            <?php echo $s_mod ?>
        </td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"w",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="./customer_form.php" id="bo_add" class="btn_01 btn">추가하기</a>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<script>
$(function(e) {
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            //stuff to do on mouse enter
            //console.log($(this).attr('od_id')+' mouseenter');
            //$(this).find('td').css('background','red');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
            
        },
        mouseleave: function () {
            //stuff to do on mouse leave
            //console.log($(this).attr('od_id')+' mouseleave');
            //$(this).find('td').css('background','unset');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./company_member_list.php?com_idx="+$(this).attr('com_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_company_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_company_board = window.open(this_href,'win_company_board','left=100,top=100,width=770,height=650');
        win_company_board.focus();
	});
	
});

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	
	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
