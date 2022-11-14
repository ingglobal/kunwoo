<?php
$sub_menu = "990170";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu],"w");

if(!$com_idx)
    alert('업체코드가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);


//-- depth 설정 및 공백 체크
$prev_depth = 0;
for($i=0;$i<sizeof($mmg_depth);$i++) {
	if($i==0 && $mmg_depth[$i] > 0) {
		alert('맨 처음 항목은 최상위 레벨이어야 합니다. 단계 설정을 확인해 주세요.');
	}
	if($mmg_depth[$i] - $prev_depth > 1) {
		alert(trim($mmg_name[$i]) . " : 단계 설정에 문제가 있습니다. \\n\\n순서대로 하위 단계를 설정해 주세요.");
	}
	if(trim($mmg_name[$i]) == "") {
		alert('그룹명이 공백인 항목이 있습니다. \\n\\n확인하시고 다시 진행해 주세요.');
	}
	$prev_depth = $mmg_depth[$i]; 
}
//print_r2($mmg_status);
//exit;

//-- 먼저 left, right 값 초기화
$sql = " UPDATE {$g5['mms_group_table']} SET mmg_left = '0', mmg_right = '0' WHERE com_idx = '".$com_idx."' ";
sql_query($sql,1);


$depth_array = array();
$idx_array = array();	// 부모 idx를 입력하기 위한 정의
$prev_depth = 0;
for($i=0;$i<sizeof($mmg_name);$i++) {
    
	//-- leaf node(마지막노드) 체크 / $depth_array[$mmg_depth[$i]] = 1
	$depth_array[$mmg_depth[$i]]++;	// 형제 갯수를 체크
	if($mmg_depth[$i] < $prev_depth) {
		//echo $prev_depth - $mmg_depth[$i]."만큼 작아졌네~".$prev_depth."<br>";
		for($j=$mmg_depth[$i]+1;$j <= $prev_depth;$j++) {
			//echo $j.'<br>';
			$depth_array[$j] = 0;
		}
	}

	//echo $mmg_name[$i].'->'.$mmg_depth[$i].":::";
	//echo 'depth_array['.$mmg_depth[$i].']카운트 -> '.$depth_array[$mmg_depth[$i]].' | ';
	//echo "INSERT INTO {$g5['mms_group_table']} (mmg_idx,mmg_idx_parent,mmg_name,com_idx,mmg_memo,mmg_left,mmg_right,mmg_status,mmg_reg_dt) 
	//				VALUES ('$mmg_idx[$i]','".$idx_array[$mmg_depth[$i]-1]."','ko_KR','$mmg_name[$i]','".$com_idx."','$mmg_memo[$i]','$i', 1, 2, '".$mmg_status[$i]."', now())
	//				ON DUPLICATE KEY UPDATE mmg_idx_parent = '".$idx_array[$mmg_depth[$i]-1]."', mmg_name = '$mmg_name[$i]', mmg_memo = '$mmg_memo[$i]', mmg_left = 1, mmg_right = 2 ";
	//echo "<br><br>";
	//continue;
	//echo $mmg_status[$i];
	
	//-- 맨 처음 항목 입력 left=1, right=2 설정
	if($i == 0) {
		$sql = "INSERT INTO {$g5['mms_group_table']} (mmg_idx,mmg_idx_parent,mmg_name,mmg_type,com_idx,mmg_memo,mmg_left,mmg_right,mmg_status,mmg_reg_dt)
					VALUES ('$mmg_idx[$i]','".$idx_array[$mmg_depth[$i]-1]."','$mmg_name[$i]','$mmg_type[$i]','".$com_idx."','$mmg_memo[$i]', 1, 2, '".$mmg_status[$i]."', now())
					ON DUPLICATE KEY UPDATE mmg_idx_parent = '".$idx_array[$mmg_depth[$i]-1]."'
                                            , mmg_name = '$mmg_name[$i]'
                                            , mmg_type = '$mmg_type[$i]'
                                            , mmg_memo = '".$mmg_memo[$i]."'
                                            , mmg_status = '".$mmg_status[$i]."'
                                            , mmg_left = 1
                                            , mmg_right = 2
		";
		sql_query($sql,1);
		echo $sql.'<br><br>';
	}
	else {

		//-- leaf_node 이면 부모 idx를 참고해서 left, right 생성
		if($depth_array[$mmg_depth[$i]] == 1) {
			//echo '부모idx -> '.$idx_array[$mmg_depth[$i]-1];

			sql_query("SELECT @myLeft := mmg_left FROM {$g5['mms_group_table']} WHERE mmg_idx = '".$idx_array[$mmg_depth[$i]-1]."' ");
			sql_query("UPDATE {$g5['mms_group_table']} SET mmg_right = mmg_right + 2 WHERE mmg_right > @myLeft AND com_idx = '".$com_idx."' ");
			sql_query("UPDATE {$g5['mms_group_table']} SET mmg_left = mmg_left + 2 WHERE mmg_left > @myLeft AND com_idx = '".$com_idx."' ");
			$sql = "INSERT INTO {$g5['mms_group_table']} (mmg_idx, mmg_idx_parent, mmg_name, mmg_type, com_idx, mmg_memo, mmg_left, mmg_right, mmg_status, mmg_reg_dt) 
						VALUES ('$mmg_idx[$i]','".$idx_array[$mmg_depth[$i]-1]."','$mmg_name[$i]','$mmg_type[$i]','".$com_idx."','".$mmg_memo[$i]."', @myLeft + 1,@myLeft + 2, '".$mmg_status[$i]."', now())
						ON DUPLICATE KEY UPDATE mmg_idx_parent = '".$idx_array[$mmg_depth[$i]-1]."'
							, mmg_name = '$mmg_name[$i]'
							, mmg_type = '$mmg_type[$i]'
							, mmg_memo = '".$mmg_memo[$i]."'
							, mmg_status = '".$mmg_status[$i]."'
							, mmg_left = @myLeft + 1
							, mmg_right = @myLeft + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
		//-- leaf_node가 아니면 동 레벨 idx 참조해서 left, right 생성
		else {
			//echo '친구idx -> '.$idx_array[$mmg_depth[$i]];
			//$sql = "SELECT @myRight := mmg_right FROM {$g5['mms_group_table']} WHERE mmg_idx = '".$idx_array[$mmg_depth[$i]]."' ";
			//echo $sql.'<br><br>';
	
			sql_query("SELECT @myRight := mmg_right FROM {$g5['mms_group_table']} WHERE mmg_idx = '".$idx_array[$mmg_depth[$i]]."' ");
			sql_query("UPDATE {$g5['mms_group_table']} SET mmg_right = mmg_right + 2 WHERE mmg_right > @myRight AND com_idx = '".$com_idx."' ");
			sql_query("UPDATE {$g5['mms_group_table']} SET mmg_left = mmg_left + 2 WHERE mmg_left > @myRight AND com_idx = '".$com_idx."' ");
			$sql = "INSERT INTO {$g5['mms_group_table']} (mmg_idx, mmg_idx_parent, mmg_name, mmg_type, com_idx, mmg_memo, mmg_left, mmg_right, mmg_status, mmg_reg_dt) 
						VALUES ('$mmg_idx[$i]','".$idx_array[$mmg_depth[$i]-1]."','$mmg_name[$i]','$mmg_type[$i]','".$com_idx."','".$mmg_memo[$i]."', @myRight + 1,@myRight + 2, '".$mmg_status[$i]."', now())
						ON DUPLICATE KEY UPDATE mmg_idx_parent = '".$idx_array[$mmg_depth[$i]-1]."'
							, mmg_name = '$mmg_name[$i]'
							, mmg_type = '$mmg_type[$i]'
							, mmg_memo = '".$mmg_memo[$i]."'
							, mmg_status = '".$mmg_status[$i]."'
							, mmg_left = @myRight + 1
							, mmg_right = @myRight + 2
			";
			sql_query($sql,1);
			echo $sql.'<br><br>';
		}
	}
	
	//echo "<br><br>";
	$prev_depth = $mmg_depth[$i]; 
	$idx_array[$mmg_depth[$i]] = $mmg_idx[$i];	//-- left, right 기준 값 저장
	$idx_array[$mmg_depth[$i]] = sql_insert_id();	//-- left, right 기준 값 저장
}


//exit;
// 앞에서 넘어온 파일명으로 다시 돌려보낸다.
goto_url("./".$file_name.".php?com_idx=".$com_idx);
?>
