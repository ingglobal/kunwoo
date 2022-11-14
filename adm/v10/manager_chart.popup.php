<?php
$sub_menu = "950300";
include_once('./_common.php');

auth_check($auth[$sub_menu],'r');

//print_r3($member);
//print_r2($g5); exit;

// 제외할 직원 아이디들 (임성호, 대표영엽자, 관리팀영업자)
$mb_excludes = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_employee_exclude_ids']));

// 관리자 레벨이 아니면 자기 조직 것만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
if (auth_check($auth[$sub_menu],'d',1)) {
	// 디폴트 그룹 접근 레벨
	$my_department_idxs = $member['mb_2'];

	// 팀장 이상은 자기 하부 전체
//	if ($member['mb_1'] >= 6) {
		// 팀장 이상이면서 상위 그룹 접근이 가능하다면..
		if ($member['mb_group_level'] == 2) {
				// 조직 검색이 있으면 조직 리스트만
				if ($_GET['ser_trm_idxs']) {
					$my_department_idxs = $_GET['ser_trm_idxs'];
				}
				else {
					$my_department_idxs = $g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]];
				}
		}
		// 아니면 내 조직만
		else
			$my_department_idxs = $g5['department_down_idxs'][$member['mb_2']];

		//echo $my_department_idxs.'<br>';
//	}
}
// 관리자인 경우
else {
	// 조직 검색
	if ($_GET['ser_trm_idxs'])
		$my_department_idxs = $_GET['ser_trm_idxs'];
}

if($my_department_idxs) {
	$sql_search = " AND term.trm_idx IN (".$my_department_idxs.") ";
	$sql_search_mb = " AND mb_2 IN (".$my_department_idxs.") ";
}

// 전체 회원 (제외 직원 반영)
$sql = " SELECT mb_id, mb_name, mb_1, mb_2, mb_3 AS mb_rank
				, (SELECT trm_name FROM g5_5_term WHERE trm_idx = mb.mb_2) AS term_name
				, mta2.mta_value AS mb_enter_date
			FROM g5_member AS mb
				LEFT JOIN {$g5['meta_table']} AS mta2 ON mta2.mta_db_table = 'member'  AND mta2.mta_db_id = mb.mb_id AND mta2.mta_key = 'mb_enter_date'
			WHERE mb_level IN (6,7,8) AND mb_2 != '' AND mb_leave_date = '' AND mb_id NOT IN ('".implode("','",$mb_excludes)."')
			{$sql_search_mb}
			ORDER BY convert(mb_2, decimal), convert(mb_1, decimal) DESC, convert(mb_3, decimal) DESC, mta2.mta_value
";
//print_r3($sql);
$result = sql_query($sql,1);
// 회원 변수 생성
for ($i=0; $row=sql_fetch_array($result); $i++) {
	// mb_1 구분 (1=파트타임, 4=팀원, 6=팀장...)
	
	$row['mb_rank'] = ($row['mb_rank'] != '10') ? $g5['set_mb_ranks_value'][$row['mb_rank']] : '' ;
	$row['mb_name'] = $row['mb_name'].$row['mb_rank'];
	$emp[$row['mb_2']][] = $row;	
//	$emp[$i]['mb_id'] = $row['mb_id'];
//	$emp[$i]['mb_name'] = $row['mb_name'];
//	$emp[$i]['trm_idx_department'] = $row['mb_1'];
//	$emp[$i]['term_name'] = $row['term_name'];
}
$total_count = $i+1;
//print_r2($emp);


$sql = " SELECT
				trm_idx
				, GROUP_CONCAT(name) term_name
				, GROUP_CONCAT(cast(depth as char)) depth
				, GROUP_CONCAT(up_idxs) up_idxs
				, SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) up1st_idx
				, SUBSTRING_INDEX(up_idxs, ',', 1) uptop_idx
				, GROUP_CONCAT(up_names) up_names
				, GROUP_CONCAT(down_idxs) down_idxs
				, GROUP_CONCAT(down_names) down_names
				, leaf_node_yn leaf_node_yn
				, trm_left
				, SUM(mb_count) AS mb_count
				, SUM(mb_count1) AS mb_count1
				, SUM(mb_count2) AS mb_count2
			FROM (	(
					SELECT term.trm_idx
						, CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
						, (COUNT(parent.trm_idx) - 1) AS depth
						, GROUP_CONCAT(cast(parent.trm_idx as char) ORDER BY parent.trm_left) up_idxs
						, GROUP_CONCAT(parent.trm_name ORDER BY parent.trm_left SEPARATOR ' > ') up_names
						, NULL down_idxs
						, NULL down_names
						, (CASE WHEN term.trm_right - term.trm_left = 1 THEN 1 ELSE 0 END ) leaf_node_yn
						, term.trm_left
						, 0 mb_count
						, 0 mb_count1
						, 0 mb_count2
					FROM {$g5['term_table']} AS term,
							{$g5['term_table']} AS parent
					WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
						AND term.trm_taxonomy = 'department'
						AND parent.trm_taxonomy = 'department'
						AND term.trm_status = 'ok'
						AND parent.trm_status = 'ok'
						{$sql_search}
					GROUP BY term.trm_idx
					ORDER BY term.trm_left
					)
				UNION ALL
					(
					
					SELECT parent.trm_idx
						, NULL name
						, NULL depth
						, NULL up_idxs
						, NULL up_names
						, GROUP_CONCAT(cast(term.trm_idx as char) ORDER BY term.trm_left) AS down_idxs
						, GROUP_CONCAT(term.trm_name ORDER BY term.trm_left SEPARATOR ' > ') AS down_names
						, (CASE WHEN parent.trm_right - parent.trm_left = 1 THEN 1 ELSE 0 END ) leaf_node_yn
						, parent.trm_left
						, SUM(mb_count) AS mb_count
						, SUM(mb_count1) AS mb_count1
						, SUM(mb_count2) AS mb_count2
					FROM {$g5['term_table']} AS term, 
						{$g5['term_table']} AS parent,
						(
						SELECT 
							mb_2 AS trm_idx_department
							, COUNT( mb_no ) AS mb_count
							, SUM( CASE WHEN convert(mb_1, decimal) < 4 THEN 1 ELSE 0 END ) mb_count1
							, SUM( CASE WHEN convert(mb_1, decimal) >= 4 THEN 1 ELSE 0 END ) mb_count2
						FROM {$g5['member_table']}
						WHERE mb_level IN (6,7,8) AND mb_2 != '' AND mb_leave_date = '' AND mb_id NOT IN ('".implode("','",$mb_excludes)."')
						GROUP BY mb_2
						) ord_opa
					WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
						AND term.trm_taxonomy = 'department'
						AND parent.trm_taxonomy = 'department'
						AND term.trm_status = 'ok'
						AND parent.trm_status = 'ok'
						AND term.trm_idx = trm_idx_department
						{$sql_search}
					GROUP BY parent.trm_idx
					ORDER BY parent.trm_left
					
					) 
				) db_table
			GROUP BY trm_idx
			ORDER BY trm_left
";
$result = sql_query($sql,1);
//print_r3($sql);



$g5['title'] = '조직도';
include_once('./_head.sub.php');
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<style>
table {border-collapse: separate !important;}
table td{font-size:1em !important;}
</style>

<!-- 그래프 -->
<div style="overflow:;">
<div id="chart_div" style="width:4700px;height:676px;padding:10px 10px 40px;"></div>
</div>

<?php
// 표시 데이타 생성
for ($i=0; $row=sql_fetch_array($result); $i++) {
	// up_idxs 분리
	$row['up_idxs_array'] = explode(",",$row['up_idxs']);
	
	$list[$i]['value'] = $row['trm_idx'];		// 고유코드
	$list[$i]['term_name'] = $row['term_name'];	// 조직명
	$list[$i]['mb_count'] = $row['mb_count'];		// 팀원전체수
	$list[$i]['mb_count1'] = $row['mb_count1'];		// 비정규직수
	$list[$i]['mb_count1_text'] = ($row['mb_count1']!='0') ? '(파:'.$row['mb_count1'].')' : '';		// 비정규직 표현
	$list[$i]['mb_count2'] = $row['mb_count2'];		// 정규직수
	$list[$i]['leaf_node_yn'] = $row['leaf_node_yn'];	// 마지막노드여부
	$list[$i]['field'] = $row['term_name'].'<br>'.$row['mb_count'].'명'.$list[$i]['mb_count1_text'].' <a href="./manager_list.php?ser_trm_idxs='.$row['down_idxs'].'" style="font-size:0.8em;">List</a>';	// 항목표현내용
	$list[$i]['parent'] = ($row['depth']==0) ? '0' : $row['up_idxs_array'][($row['depth']-1)];	// 부모고유코드
	
	// 1단계 레벨 갯수
	if($row['depth']==0)
		$depth0++;
}
//echo $depth0;
$chart_width = ($depth0>2) ? 522*$depth0 : 1040;
//print_r2($list);
?>


<script>
//-- $(document).ready 페이지로드 후 js실행 --//
$(document).ready(function(){

	google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        // For each orgchart box, provide the name, manager, and tooltip to show.
		data.addRows([
			[{v:'0', f:'대표총괄법인<br>총 <?=$total_count?>명'},'', '']
			<?
			for($i=0;$i<sizeof($list);$i++) {
				// 팀원들 
				for($j=0;$j<sizeof($emp[$list[$i]['value']]);$j++) {
					//print_r2($emp[$list[$i]['value']]);
					//echo $emp[$list[$i]['value']][$j]['mb_id'];
					
					// Lefa 노드이면 팀원들 차례대로 배치
					if($list[$i]['leaf_node_yn']) {
						// 조직코드가 바뀌면 부모코드 변경
						if($emp[$list[$i]['value']][$j]['mb_2'] != $emp_last_mb_2) {
							$emp_parent = $list[$i]['value'];
						}
						// 조직코드가 안 바뀌면 부모코드는 이전 회원 아이디
						else {
							$emp_parent = $emp_last_parent;
						}

						// 팀장이면 팀이름 아래 붙이고
						$emps[$i] .= ", [{v:'".$emp[$list[$i]['value']][$j]['mb_id']."', f:'".$emp[$list[$i]['value']][$j]['mb_name']."'},'".$emp_parent."', '']\n";

						$emp_last_parent = $emp[$list[$i]['value']][$j]['mb_id'];	// 이전 직원 아이디 (부모 코드를 찾기 위해서 계속 저장)
						$emp_last_mb_2 = $emp[$list[$i]['value']][$j]['mb_2'];	// 이전 조직코드 (조직코드가 바뀌는 시점 체크)
					}
					// Lefa 노드가 아니면 팀장 보다 상위 관리자이므로 조직이름안에 포함시켜야 함
					else {
						$list[$i]['field_extra'][] = $emp[$list[$i]['value']][$j]['mb_name'];
					}
				}
				if(is_array($list[$i]['field_extra']))
					$list[$i]['field_extras'] = implode(",",$list[$i]['field_extra']);
				
				//if($i != 0) echo ",";
				//echo "['".$list[$i]['item_name']."',  ".$list[$i]['sum'].", '".$list[$i]['item_name']."(".$list[$i]['item_count']."건): ".number_format($list[$i]['sum'])."']";
				echo ", [{v:'".$list[$i]['value']."', f:'".$list[$i]['field']."<br>".$list[$i]['field_extras']."'},'".$list[$i]['parent']."', '']\n";
				echo $emps[$i];
			}
			?>
		]);
//        // For each orgchart box, provide the name, manager, and tooltip to show.
//        data.addRows([
//          [{v:'noranmu', f:'임성호 대표<br>컬설팅사업부'},'', ''],
//          [{v:'lenon', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//          [{v:'lenon1', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//          [{v:'lenon2', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//          [{v:'lenon3', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//          [{v:'lenon4', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//          [{v:'james', f:'손지식 실장'},'noranmu', ''],
//          [{v:'makebanana', f:'김효진 실장'},'noranmu', ''],
//          [{v:'bgkim', f:'김병관 팀장'},'james', ''],
//          [{v:'tomasjoa', f:'임채완 팀장'},'james', ''],
//          [{v:'jame', f:'유승경 팀장'},'makebanana', ''],
//          [{v:'yeon', f:'연정은 주임'},'tomasjoa', ''],
//          [{v:'emp1', f:'직원1'},'yeon', ''],
//          [{v:'emp2', f:'직원2'},'emp1', ''],
//          [{v:'emp3', f:'직원3'},'emp2', ''],
//          [{v:'emp4', f:'직원4'},'emp3', ''],
//          [{v:'emp5', f:'직원5'},'yeon', ''],
//          [{v:'emp6', f:'직원6'},'yeon', ''],
//          [{v:'emp7', f:'직원7'},'yeon', ''],
//          [{v:'hone', f:'홍현태 주임'},'tomasjoa', '']
//        ]);

        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml:true});
      }
	  
	// 조직도 div 폭 재설정
	//alert( $('#chart_div').css('width') );
	$('#chart_div').css('width','<?=$chart_width?>px');
	

	  
	$("#st_date,#en_date").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: "yy-mm-dd",
		showButtonPanel: true,
		yearRange: "c-99:c+99",
		//maxDate: "+0d"
	});	 

	$( "#fsearch" ).submit(function(e) {
		if($('input[name=st_date]').val() > $('input[name=en_date]').val()) {
			alert('시작일이 종료일보다 큰 값이면 안 됩니다.');
			e.preventDefault();
		}
	});
	
	// 차트 크게 보기
	$(document).on('click','#employee_cart_popup',function(e){
		e.preventDefault();
		window.open("./employee_chart.popup.php", "employee_chart_popup",'left=50,top=50,width=1000,height=800');
	});

	// 공개차트 보기
	$(document).on('click','#employee_cart_popup2',function(e){
		e.preventDefault();
		window.open("<?=G5_USER_URL?>/c/employee_chart.php", "employee_chart_popup2",'left=1,top=1,width=1240,height=800');
	});

});
//-- //$(document).ready 페이지로드 후 js실행 --//

</script>

<?php
include_once('./_tail.sub.php');
?>
