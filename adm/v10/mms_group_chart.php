<?php
$sub_menu = "990170";
include_once('./_common.php');

auth_check($auth[$sub_menu],'r');

if(!$com_idx)
    alert('업체코드가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);


$g5['title'] = $com['com_name'].' MMS그룹 (차트보기)';
//include_once('./_top_menu_setting.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

// 나중에 필요한 추가 조건이 있으면..
if($my_department_idxs) {
	$sql_search = " AND mmg.mmg_idx IN (".$my_department_idxs.") ";
}

// MMS 업체별 추출 (자바스크립트 부분에 넣어야 함)
$sql = "SELECT *
        FROM {$g5['mms_table']}
        WHERE com_idx = '".$com_idx."'
        ORDER BY mms_sort, mms_idx
";
//print_r3($sql);
$result = sql_query($sql,1);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$row['mms_name_model'] = $row['mms_name'].'<br>'.$row['mms_model'];
	$mms[$row['mmg_idx']][] = $row;	
}
$total_mms = $i;
//print_r2($mms);

// 각 mmg 별 구조만 생성 (mmg별 합계 카운터도 함께)
$sql = " SELECT
				mmg_idx
				, GROUP_CONCAT(name) AS mmg_name
				, GROUP_CONCAT(cast(depth as char)) AS depth
				, GROUP_CONCAT(up_idxs) AS up_idxs
				, SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) AS up1st_idx
				, SUBSTRING_INDEX(up_idxs, ',', 1) AS uptop_idx
				, GROUP_CONCAT(up_names) AS up_names
				, GROUP_CONCAT(down_idxs) AS down_idxs
				, GROUP_CONCAT(down_names) AS down_names
				, leaf_node_yn
				, mmg_left
				, SUM(mms_count) AS mms_count
			FROM (	(
					SELECT mmg.mmg_idx
						, CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
						, (COUNT(parent.mmg_idx) - 1) AS depth
						, GROUP_CONCAT(cast(parent.mmg_idx as char) ORDER BY parent.mmg_left) AS up_idxs
						, GROUP_CONCAT(parent.mmg_name ORDER BY parent.mmg_left SEPARATOR ' > ') AS up_names
						, NULL down_idxs
						, NULL down_names
						, (CASE WHEN mmg.mmg_right - mmg.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
						, mmg.mmg_left
						, 0 AS mms_count
					FROM {$g5['mms_group_table']} AS mmg,
							{$g5['mms_group_table']} AS parent
					WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
						AND mmg.com_idx = '".$com_idx."'
						AND parent.com_idx = '".$com_idx."'
						AND mmg.mmg_status NOT IN ('trash','delete')
						AND parent.mmg_status NOT IN ('trash','delete')
						{$sql_search}
					GROUP BY mmg.mmg_idx
					ORDER BY mmg.mmg_left
					)
				UNION ALL
					(
					SELECT parent.mmg_idx
						, NULL AS name
						, NULL AS depth
						, NULL AS up_idxs
						, NULL AS up_names
						, GROUP_CONCAT(cast(mmg.mmg_idx as char) ORDER BY mmg.mmg_left) AS down_idxs
						, GROUP_CONCAT(mmg.mmg_name ORDER BY mmg.mmg_left SEPARATOR ' > ') AS down_names
						, (CASE WHEN parent.mmg_right - parent.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
						, parent.mmg_left
						, SUM(mms_count) AS mms_count
					FROM {$g5['mms_group_table']} AS mmg, 
						{$g5['mms_group_table']} AS parent,
						(
						SELECT 
							mmg_idx AS mms_mmg_idx
							, COUNT( mms_idx ) AS mms_count
						FROM {$g5['mms_table']}
						WHERE com_idx = '".$com_idx."' AND mms_status NOT IN ('trash','delete')
						GROUP BY mmg_idx
						) db_mms
					WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
						AND mmg.com_idx = '".$com_idx."'
						AND parent.com_idx = '".$com_idx."'
						AND mmg.mmg_status NOT IN ('trash','delete')
						AND parent.mmg_status NOT IN ('trash','delete')
						AND mmg.mmg_idx = mms_mmg_idx
						{$sql_search}
					GROUP BY parent.mmg_idx
					ORDER BY parent.mmg_left
					) 
				) db_table
			GROUP BY mmg_idx
			ORDER BY mmg_left
";
$result = sql_query($sql,1);
// print_r3($sql);
$total_count = sql_num_rows($result);
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<style>
table {border-collapse: separate !important;}
table td{font-size:1em !important;line-height: 125% !important;padding: 6px 0 3px !important;}
</style>


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

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>MMS 설비(들)은 맨 마지막 그룹(노드) 하단에만 나타납니다. (MMS 설비는 노란색 박스, 그룹은 파란색 박스로 표현됩니다.)</p>
    <p>[보기]를 클릭하사면 업체의 모든 MMS 리스트 페이지를 확인할 수 있습니다.</p>
</div>

<!-- 그래프 -->
<div style="overflow:auto;">
<div id="chart_div" style="width:4700px;height:676px;padding:10px 10px 40px;"></div>
</div>


<div class="btn_fixed_top">
    <a href="./company_list.php" id="btn_add_car" class="btn btn_03">업체목록</a>
</div>


<?php
// 표시 데이타 생성
for ($i=0; $row=sql_fetch_array($result); $i++) {
	// up_idxs 분리
	$row['up_idxs_array'] = explode(",",$row['up_idxs']);
	
	$list[$i]['value'] = $row['mmg_idx'];		// 그룹고유코드
	$list[$i]['mmg_name'] = $row['mmg_name'];	// 그룹명
	$list[$i]['mms_count'] = $row['mms_count'];		// MMS수
	$list[$i]['mms_count_text'] = ($row['mms_count']) ? ' <a href="./mms_list.php" style="font-size:0.8em;">[보기]</a>' : '';
	$list[$i]['leaf_node_yn'] = $row['leaf_node_yn'];	// 마지막노드여부
	$list[$i]['field'] = $row['mmg_name'].'<br>MMS '.$row['mms_count'].'개'.$list[$i]['mms_count_text'];	// 항목표현내용
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
//       For each orgchart box, provide the name, manager, and tooltip to show.
        data.addRows([
		[{v:'0', f: '<?=$com['com_name']?><br><a href="./mms_list.php">MMS(총): <?=$total_mms?>개</a>'},'', '']
		<?php
		for($i=0;$i<sizeof($list);$i++) {
			// print_r2($mms[$list[$i]['value']]);
			// MMS들
			for($j=0;$j<sizeof($mms[$list[$i]['value']]);$j++) {
				//print_r2($mms[$list[$i]['value']]);
				//echo $mms[$list[$i]['value']][$j]['mms_idx'];
				
				// Leaf 노드일 때만 MMS 차례대로 배치
				if($list[$i]['leaf_node_yn']) {
					// 조직코드가 바뀌면 부모코드 변경
					if($mms[$list[$i]['value']][$j]['mmg_idx'] != $mms_last_mmg_idx) {
						$mms_parent = $list[$i]['value'];
					}
					// 조직코드가 안 바뀌면 부모코드는 이전 mms_idx
					else {
						$mms_parent = $mms_last_parent;
					}
					// MMS 박스 표현 (설비명/모델)
					$mmss_name[$i] = '<span class="span_mms">'.$mms[$list[$i]['value']][$j]['mms_name'].'<br>('.$mms[$list[$i]['value']][$j]['mms_model'].')</span>';
					$mmss[$i] .= ", [{v:'".$mmss_name[$i]."', f:'".$mms[$list[$i]['value']][$j]['mb_name']."'},'".$mms_parent."', '']\n";

                    $mms_last_parent = $mms[$list[$i]['value']][$j]['mms_idx'];	  // 이전 mms_idx (부모 코드를 찾기 위해서 계속 저장)
					$mms_last_mmg_idx = $mms[$list[$i]['value']][$j]['mmg_idx'];  // 이전 조직코드 (조직코드가 바뀌는 시점 체크)
				}
				// Leaf 노드가 아니면 팀장 보다 상위 관리자이므로 조직이름안에 포함시켜야 함
				else {
					$list[$i]['field_extra'][] = $mms[$list[$i]['value']][$j]['mb_name'];
				}
			}
			if(is_array($list[$i]['field_extra']))
				$list[$i]['field_extras'] = implode(",",$list[$i]['field_extra']);
			
			//if($i != 0) echo ",";
			//echo "['".$list[$i]['item_name']."',  ".$list[$i]['sum'].", '".$list[$i]['item_name']."(".$list[$i]['item_count']."건): ".number_format($list[$i]['sum'])."']";
			echo ", [{v:'".$list[$i]['value']."', f:'".$list[$i]['field']."<br>".$list[$i]['field_extras']."'},'".$list[$i]['parent']."', '']\n";
			echo $mmss[$i];
		}
		?>
        ]);
//      // For each orgchart box, provide the name, manager, and tooltip to show.
//      data.addRows([
//       [{v:'noranmu', f:'대표법인<br>컬설팅사업부'},'', ''],
//       [{v:'lenon', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//       [{v:'lenon1', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//       [{v:'lenon2', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//       [{v:'lenon3', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//       [{v:'lenon4', f:'김윤중 실장<br>전산팀'},'noranmu', ''],
//       [{v:'james', f:'손지식 실장'},'noranmu', ''],
//       [{v:'makebanana', f:'김효진 실장'},'noranmu', ''],
//       [{v:'bgkim', f:'김병관 팀장'},'james', ''],
//       [{v:'tomasjoa', f:'임채완 팀장'},'james', ''],
//       [{v:'jame', f:'유승경 팀장'},'makebanana', ''],
//       [{v:'yeon', f:'연정은 주임'},'tomasjoa', ''],
//       [{v:'emp1', f:'직원1'},'yeon', ''],
//       [{v:'emp2', f:'직원2'},'emp1', ''],
//       [{v:'emp3', f:'직원3'},'emp2', ''],
//       [{v:'emp4', f:'직원4'},'emp3', ''],
//       [{v:'emp5', f:'직원5'},'yeon', ''],
//       [{v:'emp6', f:'직원6'},'yeon', ''],
//       [{v:'emp7', f:'직원7'},'yeon', ''],
//       [{v:'hone', f:'홍현태 주임'},'tomasjoa', '']
//      ]);
        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
//      data.setRowProperty(1, 'style', 'background-color:red;background-image:none');

        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml:true});
    }
    
    //
    setTimeout(function(e){
//        $('.span_mms').closest('td').css('border','solid 1px red');
        $('.span_mms').closest('td').addClass('google-visualization-orgchart-nodesel');
    },400);
	  
	// 조직도 div 폭 재설정
	//alert( $('#chart_div').css('width') );
	$('#chart_div').css('width','<?=$chart_width?>px');
	

	// 차트 크게 보기
	$(document).on('click','#chart_popup',function(e){
		e.preventDefault();
		window.open("./mms_group_chart.popup.php", "mms_group_chart_popup",'left=50,top=50,width=1000,height=800');
	});

});
//-- //$(document).ready 페이지로드 후 js실행 --//
</script>

<?php
include_once ('./_tail.php');
?>
