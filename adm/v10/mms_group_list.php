<?php
$sub_menu = "990170";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

if(!$com_idx)
    alert('업체코드가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);


$g5['title'] = $com['com_name'].' MMS그룹 (트리보기)';
//include_once('./_top_menu_setting.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

//-- 카테고리 구조 추출 --//
$sql = "SELECT 
			mmg_idx
			, GROUP_CONCAT(name) AS mmg_name
			, mmg_type
			, mmg_memo
			, mmg_status
			, GROUP_CONCAT(cast(depth as char)) AS depth
			, GROUP_CONCAT(up_idxs) AS up_idxs
			, SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) AS up1st_idx
			, GROUP_CONCAT(up_names) AS up_names
			, GROUP_CONCAT(down_idxs) AS down_idxs
			, GROUP_CONCAT(down_names) AS down_names
			, leaf_node_yn
			, SUM(table_row_count) AS table_row_count
		FROM (	(
				SELECT mmg.mmg_idx
					, CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
					, mmg.mmg_type
					, mmg.mmg_memo
					, mmg.mmg_status
					, (COUNT(parent.mmg_idx) - 1) AS depth
					, GROUP_CONCAT(cast(parent.mmg_idx as char) ORDER BY parent.mmg_left) AS up_idxs
					, GROUP_CONCAT(parent.mmg_name ORDER BY parent.mmg_left SEPARATOR '|') AS up_names
					, NULL down_idxs
					, NULL down_names
					, (CASE WHEN mmg.mmg_right - mmg.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
					, 0 AS table_row_count
					, mmg.mmg_left
					, 1 sw
				FROM {$g5['mms_group_table']} AS mmg,
				        {$g5['mms_group_table']} AS parent
				WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
					AND mmg.com_idx = '".$com_idx."'
					AND parent.com_idx = '".$com_idx."'
					AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
                GROUP BY mmg.mmg_idx
				ORDER BY mmg.mmg_left
				)
			UNION ALL
				(
				SELECT parent.mmg_idx
					, NULL name
					, mmg.mmg_type
					, mmg.mmg_memo
					, mmg.mmg_status
					, NULL depth
					, NULL up_idxs
					, NULL up_names
					, GROUP_CONCAT(cast(mmg.mmg_idx as char) ORDER BY mmg.mmg_left) AS down_idxs
					, GROUP_CONCAT(mmg.mmg_name ORDER BY mmg.mmg_left SEPARATOR '|') AS down_names
					, (CASE WHEN parent.mmg_right - parent.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
					, SUM(mmg.mmg_count) AS table_row_count
					, parent.mmg_left
					, 2 sw
				FROM {$g5['mms_group_table']} AS mmg
						, {$g5['mms_group_table']} AS parent
				WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
					AND mmg.com_idx = '".$com_idx."'
					AND parent.com_idx = '".$com_idx."'
					AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
				GROUP BY parent.mmg_idx
				ORDER BY parent.mmg_left
				)
			) db_table
		GROUP BY mmg_idx
		ORDER BY mmg_left
";
// echo $sql;
$result = sql_query($sql,1);
$total_count = sql_num_rows($result);
?>


<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">그룹수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>개 </span></span>
    
    <div style="display:inline-block;float:right;">
        <span class="btn_ov01">
            <a href="./mms_group_chart.php?com_idx=<?=$com_idx?>"><span class="ov_txt">차트보기</span></a>
            <a href="./mms_group_list.php?com_idx=<?=$com_idx?>"><span class="ov_num">트리보기</span></a>
        </span>
    </div>
</div>

<form name="fcarlist" method="post" action="./mms_group_list_update.php" autocomplete="off">
<input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 목록</caption>
<thead>
<tr>
    <th scope="col" style="width:6%">단계설정</th>
    <th scope="col" style="width:15%">그룹명</th>
    <th scope="col" style="width:7%"><a href="javascript:" id="sub_toggle">닫기</a></th>
    <th scope="col" style="width:20%">설명</th>
    <th scope="col" style="width:10%">위치이동</th>
    <th scope="col" style="width:5%;white-space:nowrap;">고유코드</th>
	<th scope="col" style="width:6%">숨김</th>
    <th scope="col" style="width:6%">관리</th>
</tr>
</thead>
<tbody>
	<!-- 항목 추가를 위한 DOM (복제후 제거됨) -->
	<tr class="" style="display:none">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_mmg_name" style="padding-left:10px">
			<input type="hidden" name="mmg_depth[]" value="0">
			<input type="hidden" name="mmg_idx[]" value="">
			<input type="text" name="mmg_name[]" value="그룹명을 입력하세요" required class="frm_input full_input required" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><a href="#">열기</a></td>
	    <td class="td_mmg_memo"><!-- 설명 -->
	        <input type="text" name="mmg_memo[]" value="" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center">
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
		<td class="td_idx" style="text-align:center">
		<td class="td_use" style="text-align:center">
			<input type="hidden" name="mmg_status[]" value="ok">
			<input type="checkbox" name="mmg_use[]">
	    </td>
	    <td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
	<!-- //항목 추가를 위한 DOM (복제후 제거됨) -->
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	//print_r2($row);
	//-- 들여쓰기
	$row['indent'] = ($row['depth']) ? $row['depth']*50:10;
	
	//-- 하위 열기 닫기
	$row['sub_toggle'] = ($row['depth']==0) ? '<a href="#">닫기</a>':'-';
	
	$usechecked = ($row['mmg_status'] == 'ok') ? '':'checked';
	$status_txt = ($row['mmg_status'] == 'ok') ? 'ok':'hide';
    $bg = 'bg'.($i%2);
?>
	<tr class="<?php echo $bg; ?>">
	    <td class="td_depth" style="text-align:center">
	        <a href="#" alt="상위단계로">◀</a> | <a href="#" alt="하위단계로">▶</a>
	    </td>
	    <td class="td_mmg_name" style="padding-left:<?=$row['indent']?>px;text-align:left;">
			<input type="hidden" name="mmg_depth[]" value="<?=$row['depth']?>">
			<input type="hidden" name="mmg_idx[]" value="<?=$row['mmg_idx']?>">
			<input type="text" name="mmg_name[]" value="<?php echo get_text(trim($row['mmg_name'])); ?>" required class="frm_input full_input required" style="width:180px;">
	    </td>
		<td class="td_sub_category" style="text-align:center"><?=$row['sub_toggle']?></td>
	    <td class="td_mmg_memo"><!-- 설명 -->
	        <input type="text" name="mmg_memo[]" value="<?php echo get_text(trim($row['mmg_memo'])); ?>" class="full_input frm_input" style="width:100%;">
	    </td>
	    <td class="td_sort" style="text-align:center"><!-- 위치이동 -->
	        <a href="#">▲위</a> | <a href="#">아래▼</a>
	    </td>
	    <td class="td_idx" style="text-align:center"><!-- 코유코드 -->
			<?=$row['mmg_idx']?>
	    </td>
	    <td class="td_use" style="text-align:center"><!-- 숨김 -->
			<input type="hidden" name="mmg_status[]" value="<?=$status_txt?>">
	        <input type="checkbox" name="mmg_use[]" <?=$usechecked?>>
	    </td>
		<td class="td_del" style="text-align:center">
	        <a href="#">삭제</a>
	    </td>
	</tr>
<?php }
if ($i == 0) echo '<tr class="no-data"><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:insert_item()" id="btn_add_car" class="btn btn_02">항목추가</a>
    <a href="./company_list.php" id="btn_add_car" class="btn btn_03">업체목록</a>
	<input type="submit" name="act_button" value="확인" class="btn_submit btn">
</div>
</form>

<script>
//----------------------------------------------
$(function() {
    
	//-- DOM 복제 & 생성 & 초기화 --//
	list_dom01=$("#table01_list tbody");
	orig_dom01=list_dom01.find("tr").eq(0).clone();
	list_dom01.find("tr:eq(0)").remove();	// 복제한 후에 제거
	list01_nothing_display();

	//-- 정렬(Sortable) --//
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
			$(this).width($originals.eq(index).width());
		});
		return $helper;
	};

	$("#table01_list tbody").sortable({
		cancel: "input, textarea, a, i"
		, helper: fixHelperModified
		, items: "tr:not(.no-data)"
		, placeholder: "tr-placeholder"
		, connectWith: "#table01_list tr:not(.no-data)"
		, stop: function(event, ui) {
			//alert(ui.item.html());
			//-- 정렬 후 처리 / 맨 처음 항목이면 최상위 레벨이어야 함
			if($(this).find('tr').index(ui.item) == 0 && ui.item.find('input[name^=mmg_depth]').val() > 0) {
				ui.item.find('input[name^=mmg_depth]').val(0);
				ui.item.find('.td_mmg_name').css('padding-left','0px');
			}
			
			setTimeout(function(){ ui.item.removeAttr('style'); }, 10);
		}
	});
	
	//=====================================================카테고리 사용여부=========================== 
	$('input[type="checkbox"]').click(function(){
		if($(this).is(":checked")){
			$(this).siblings('input[type="hidden"]').val('hide');
			//alert($(this).siblings('input[type="hidden"]').val());
		}else{
			$(this).siblings('input[type="hidden"]').val('ok');
			//alert($(this).siblings('input[type="hidden"]').val());
		}
	});

	//-- 차종추가 경고창 초기 설정
	alert_flag = true; 


	//-- 단계이동 버튼 클릭 --//
//	$('.td_depth a').live('click',function(e) {
	$(document).on('click','.td_depth a',function(e) {
		e.preventDefault();
		//-- 맨 처음 항목은 무조건 최상위 단계이어야 함
		if($(this).parents('tbody:first').find('tr').index($(this).parents('tr:first')) == 0 && $(this).parent().find('a').index($(this)) == 1) {
			alert('맨 처음 항목은 최상위 레벨이어야 합니다. \n\n단계 2로 이동할 수 없습니다.');
			return false;
		}
		
		//-- depth 값 업데이트
		var indent_sign_value = ($(this).parent().find('a').index($(this)) == 0)? -1:1;
		var new_depth = parseInt($(this).parents('tr:first').find('input[name^=mmg_depth]').val()) + indent_sign_value;
		if(new_depth < 0) new_depth = 0;
		$(this).parents('tr:first').find('input[name^=mmg_depth]').val(new_depth);
		
		//-- 들여쓰기 적용
		var indent_value = (new_depth) ? new_depth * 50:10;
		$(this).parents('tr:first').find('.td_mmg_name').css('padding-left',indent_value+'px');
		
		//update_notice();	//-- [일괄수정] 버튼 활성화
	});


	//-- 위치이동 버튼 클릭 --//
//	$('.td_sort a').live('click',function(e) {
	$(document).on('click','.td_sort a',function(e) {
		e.preventDefault();

		var target_tr = $(this).parents('tr:first').clone().hide();
		var flag_up_down = ($(this).parent().find('a').index($(this)) == 0)? 'up':'down';
		var tr_loc = $(this).parents('tbody:first').find('tr').index($(this).parents('tr:first'));
		

		if(flag_up_down == "up" && tr_loc == 0) {
			alert('맨 처음 항목입니다. 더 이상 올라갈 때가 없지 않나요?');
			return false;
		}
		else if(flag_up_down == "down" && tr_loc == $(this).parents('tbody:first').find('tr').length - 1) {
			alert('마지막 항목입니다. 보면 알잖아요~');
			return false;
		}

		$(this).parents('tr:first').stop(true,true).fadeOut('fast',function(){
			$(this).remove();

			if(flag_up_down == "up") {
				target_tr.insertBefore($('#table01_list tbody tr').eq(parseInt(tr_loc)-1)).stop(true,true).fadeIn('fast').removeAttr('style');
			}
			else {
				target_tr.insertAfter($('#table01_list tbody tr').eq(tr_loc)).stop(true,true).fadeIn('fast').removeAttr('style');
			}

		});

		//update_notice();	//-- Submit 버튼 활성화
	});


	//-- 삭제 버튼 클릭시 --//
//	$('.td_del a').live('click',function(e) {
	$(document).on('click','.td_del a',function(e) {
		e.preventDefault();
		
		//-- 추가된 항목은 바로 삭제, 기 등록된 조직은 관련 작업 진행
		if(confirm('하위 카테고리 전체 및 소속 항목들이 전부 삭제됩니다. \n\n후회할 수도 있을 텐데~~ 정말 삭제하시겠습니까?')) {
			if($(this).parents('tr:first').find('input[name^=mmg_idx]').val()) {

				//-- 삭제 함수 호출
				mmg_delete($(this).parents('tr:first').find('input[name^=mmg_idx]').val(),0);

			}
			else {
				$(this).parents('tr:first').remove();
			}
			
			//update_notice();	//-- Submit 버튼 활성화
		}
	});


	//-- 닫기 열기
	$('#sub_toggle').click(function() {
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		$('#table01_list tbody tr').find('input[name^=mmg_depth]').each(function() {
			if($(this).val() > 0) {
				if(this_text == "닫기")
					$(this).closest('tr').hide();
				else 
					$(this).closest('tr').show();
			}
			else {
				if(this_text == "닫기") {
					$(this).closest('tr').find('.td_sub_category a').text('열기');
				}
				else 
					$(this).closest('tr').find('.td_sub_category a').text('닫기');
			}
		});
	});


	//-- 서브 부분만 열고 닫기
//	$('.td_sub_category a').live('click',function(e) {
	$(document).on('click','.td_sub_category a',function(e) {
		e.preventDefault();
		var this_text = $(this).text();

		if(this_text == "닫기")
			$(this).text('열기');
		else
			$(this).text('닫기');

		
		var this_depth = $(this).closest('tr').find('input[name^=mmg_depth]').val();
		var this_sub_flag = false;
		
		$(this).closest('tr').nextAll('tr').each(function() {
			if($(this).find('input[name^=mmg_depth]').val() > this_depth && this_sub_flag == false) {

				if(this_text == "닫기")
					$(this).hide();
				else
					$(this).show();
			}
			else 
				this_sub_flag = true;
		});
	});



});
//----------------------------------------------


//-- 01 No data 처리 --//
function list01_nothing_display() {
	if(list_dom01.find("tr:not(.no-data)").length == 0)
		list_dom01.find('.no-data').show();
	else 
		list_dom01.find('.no-data').hide();
}
//-- //01 No data 처리 --//


//-- 테이블 항목 추가
function insert_item() {
	//-- DOM 복제
	sDom = orig_dom01.clone();

	//-- DOM 입력
	//sDom.insertBefore($('#table01_list tbody tr').eq(0)).show();
	//$('#table01_list tbody tr').eq(0).find('input[name^=mmg_name]').select().focus();
	$('#table01_list tbody').append(sDom.show());
	$('#table01_list tbody tr:last').find('input[name^=mmg_name]').select().focus();

	list01_nothing_display();
	
	if(alert_flag == true) {
		alert('입력항목을 작성한 후 하단의 [일괄수정] 버튼을 클릭하여 적용해 주시면 됩니다.');
		alert_flag = false;
	}
}


//-- 항목 삭제 함수 --//
function mmg_delete(this_mmg_idx, fn_delte) {
	
	//-- 디버깅 Ajax --//
	$.ajax({
		url:'./ajax/mmg_delete.php',
		type:'get',
		data:{"com_idx":"<?=$com_idx?>", "mmg_idx":this_mmg_idx,"delete":fn_delte},
		dataType:'json',
		timeout:3000, 
		beforeSend:function(){},
		success:function(data){
			//alert(data.sql);
	//-- 디버깅 Ajax --//

	//$.getJSON('../ajax/mmg_delete.php',{"com_idx":"<?=$com_idx?>", "mmg_idx":this_mmg_idx,"delete":fn_delte},function(data){
			//alert(data.sql);

			//-- 페이지 새로고침 --//
//			setTimeout(function(e){
				self.location.reload();
//			},3000);

	//-- 디버깅 Ajax --//
		},
		error:function(xmlRequest) {
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
		} 
	//-- 디버깅 Ajax --//

	});	
}
</script>

<?php
include_once ('./_tail.php');
?>
