<?php
// 호출페이지들
// /adm/v10/dashboard_setting.php: 설비그룹
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

// 권한이 없는 경우는 자기것만 리스트

// com_idx가 있는 경우
if(!$com_idx)
	alert_close('업체정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$_SESSION['ss_com_idx']);


// 각 mmg 별 구조만 생성 (mmg별 합계 카운터도 함께)
$sql = " SELECT
				mmg_idx
				, GROUP_CONCAT(name) AS mmg_name
				, GROUP_CONCAT(cast(depth as char)) AS depth
				, GROUP_CONCAT(up_idxs) AS up_idxs
				, SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) AS up1st_idx
                , SUBSTRING_INDEX(SUBSTRING_INDEX(up_names, ' > ', GROUP_CONCAT(cast(depth as char))),' > ',-1) AS up1st_name
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
					GROUP BY parent.mmg_idx
					ORDER BY parent.mmg_left
					) 
				) db_table
			GROUP BY mmg_idx
			ORDER BY mmg_left
";
$result = sql_query($sql,1);
// echo $sql.'<br>';
$total_count = sql_num_rows($result);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // 부모 - 자식 배열 생성
    if($row['up1st_name']) {
        $list[] = "['".$row['up1st_name']."', '".trim($row['mmg_name'])."']";
    }
    // 상위 이름이 없으면 업체 바로 아래 최상위 노드
    else {
        $list[] = "['".$com['com_name']."', '".trim($row['mmg_name'])."']";
    }
	$mmg_name[$row['mmg_idx']] = trim($row['mmg_name']);	// 그룹명 (아래 설비쪽을 위한 상위 노드명 선정의)
}
// print_r2($list);
// print_r2($mmg_name);



// 설비(mms) 생성
if($i>0) {
    $sql = "SELECT * 
            FROM {$g5['mms_table']} 
            WHERE mms_status NOT IN ('trash','delete') 
                AND com_idx = '".$com_idx."'
            ORDER BY mms_idx
    ";
    $result = sql_query($sql,1);
    // echo $sql.'<br>';
    $total_count = sql_num_rows($result);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 부모 - 자식 배열 생성 (mmg_idx가 있을 때만)
        if($row['mmg_idx']) {
            $list[] = "['".$mmg_name[$row['mmg_idx']]."', '".$row['mms_name']."']";
        }
    }
    // print_r2($list);
}
else {
    $list[] = "['".$com['com_name']."', '그룹 없음']";
}





$g5['title'] = $com['com_name'].' 배치도';
include_once('./_head.sub.php');
add_javascript('<script src="'.G5_ADMIN_URL.'/admin.js"></script>', 10);
?>
<style>
.btn_fixed_top {position:absolute;top: 12px;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.src.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/modules/networkgraph.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/themes/high-contrast-dark.js"></script>
<div id="sch_target_frm" class="new_win scp_new_win">
    <h1 id="g5_title"><?php echo $g5['title'];?></h1>

    <div id="netchart" style="width:100%; height:500px;"></div>

</div>

<script>
// Add the nodes option through an event call. We want to start with the parent
// item and apply separate colors to each child element, then the same color to
// grandchildren.
Highcharts.addEvent(
    Highcharts.Series,
    'afterSetOptions',
    function (e) {
        var colors = Highcharts.getOptions().colors,
            i = 0,
            nodes = {};
        if (
            this instanceof Highcharts.seriesTypes.networkgraph &&
            e.options.id === 'mms-tree'
        ) {
            e.options.data.forEach(function (link) {

                if (link[0] === '<?=$com['com_name']?>') {
                    nodes['<?=$com['com_name']?>'] = {
                        id: '<?=$com['com_name']?>',
                        marker: {
                            radius: 20
                        }
                    };
                    nodes[link[1]] = {
                        id: link[1],
                        marker: {
                            radius: 10
                        },
                        color: colors[i++]
                    };
                } else if (nodes[link[0]] && nodes[link[0]].color) {
                    nodes[link[1]] = {
                        id: link[1],
                        color: nodes[link[0]].color
                    };
                }
            });

            e.options.nodes = Object.keys(nodes).map(function (id) {
                return nodes[id];
            });
        }
    }
);

Highcharts.chart('netchart', {
    chart: {
        type: 'networkgraph',
        height: '100%'
    },
    title: {
        text: ''
    },
    plotOptions: {
        networkgraph: {
            keys: ['from', 'to'],
            layoutAlgorithm: {
                enableSimulation: true,
                friction: -0.9
            }
        }
    },
    series: [{
        dataLabels: {
            enabled: true,
            linkFormat: ''
            , style: {
                fontSize: '1.1em'
            }
        },
        id: 'mms-tree',
        data: [
            <?=implode(",",$list);?>
            // ['삼흥열처리', '밀양공장'],
            // ['삼흥열처리', '경주공장'],
            // ['삼흥열처리', 'LA공장'],
            // ['삼흥열처리', 'Italic'],
            // ['삼흥열처리', 'Hellenic'],
            // ['삼흥열처리', 'Anatolian'],
            // ['삼흥열처리', 'Tocharian'],
            // ['삼흥열처리', 'Indo-Iranian'],
            // ['Indo-Iranian', 'Indic'],
            // ['Indo-Iranian', 'Dardic'],
            // ['Indo-Iranian', 'Iranian'],
            // ['Italic', 'Osco-Umbrian'],
            // ['Italic', 'Latino-Faliscan'],
            // ['LA공장', 'Brythonic'],
            // ['LA공장', 'Goidelic'],
            // ['경주공장', 'North Germanic'],
            // ['경주공장', 'West Germanic'],
            // ['경주공장', 'East Germanic'],
            // ['밀양공장', 'Baltic'],
            // ['밀양공장', 'Slavic'],
        ]
    }]
});

setTimeout(function(e){
    $('.highcharts-credits').remove();
},10);
</script>

<script>
$(function() {
    $("#btn_company").click(function() {
        var href = $(this).attr("href");
        winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
        winCompany.focus();
        return false;
    });
});
</script>


<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <!-- <a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>" id="btn_company" class="btn btn_03">업체검색</a> -->
        <a href="javascript:opener.location.href='./mms_group_list.php?com_idx=<?=$com_idx?>';window.close();" class="btn btn_03">그룹설정</a>
    <?php } ?>
    <a href="javascript:window.close();" class="btn btn_02">창닫기</a>
</div>

<?php
include_once('./_tail.sub.php');
?>