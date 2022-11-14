<?php
$sub_menu = "915130";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// print_r2($_POST);

// 초기값 정의 (외부 함수들에서 사용)
$g5['bit']['num'] = array();
$g5['bit']['reply'] = array();
$g5['bit_num'] = 0;


$data = json_decode(stripslashes($_POST['serialized']),true);
// print_r2($data);
// exit;
function create_categories(&$arr, $parent_id=0) {
    global $g5;

    foreach($arr as $key => $item) {
		//id값이 셋팅되어 있지 않으면 빈값이므로 건너띈다.
        if(!array_key_exists('id',$item)) continue;
		
        $item['parent_id'] = $parent_id;
        $list = array();
        $list = $item;
        unset($list['children']);   // 서브까지 다 보이면 복잡해서 숨김
        $list['reply'] = get_num_reply($list['id'], $list['parent_id'], $list['depth']);
        $list['bit_num'] = $list['reply'][0];
        $list['bit_reply'] = $list['reply'][1];
        $list['bom_idx'] = $_POST['bom_idx'];   // 넘겨받은 bom_idx
        unset($list['reply']);
        $list['bit_idx'] = update_bom_item($list);
        $g5['bit_idxs'][] = $list['bit_idx'];   // 삭제를 위한 배열
        // print_r2($list);
        //print_r2($g5['bit']['num']);    // 공통 배열 변수
        //print_r2($g5['bit']['reply']);    // 공통 배열 변수

        // 하위가 있으면 재귀함수
        if(isset($item['children'])){
            create_categories($item['children'], $list['id']);
        }
    }
}
create_categories($data, 0);


// 리스트에서 사라진 항목 디비에서 삭제처리
if(is_array($g5['bit_idxs']))
    $sql_bit_idx = " AND bit_idx NOT IN (".implode(',',$g5['bit_idxs']).") ";

$sql = "DELETE FROM {$g5['bom_item_table']}
        WHERE bom_idx = '".$_POST['bom_idx']."'
            {$sql_bit_idx}
";
// echo $sql;
sql_query($sql,1);


// exit;
$qstr .= '&sca='.$sca.'&file_name='.$file_name; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./bom_list.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>
