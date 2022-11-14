<?php
include_once('./_common.php');

if(!$is_admin)
    alert('접근 권한이 없습니다.', G5_URL);

//print_r2($_POST);
// chk 배열 키-배열쌍 역으로 할당해 놓고 나중에 key값 입력
// checkbox 는 값이 안 넘어와서 늘 헷갈리네!!
// list.skin.php 단에 chkall 아래 한줄 추가: <input type="hidden" name="chk[]" value="<<< echo $list[$i]['wr_id'] >>>" id="chk_<<< echo $i; >>>">
foreach($chk as $key=>$value) {
	$chk1[$value] = $key;
}
//print_r2($chk1);

$count = count($_POST['chk_wr_id']);
for ($i=0; $i<$count; $i++) {

    // 정보 업데이트
    $sql = " UPDATE {$write_table} SET
                    wr_1 = '".$_POST['wr_1'][$chk1[$_POST['chk_wr_id'][$i]]]."'
                    , wr_2 = '".$_POST['wr_2'][$chk1[$_POST['chk_wr_id'][$i]]]."'
                    , wr_3 = '".$_POST['wr_3'][$chk1[$_POST['chk_wr_id'][$i]]]."'
                    , wr_4 = '".$_POST['wr_4'][$chk1[$_POST['chk_wr_id'][$i]]]."'
                WHERE wr_id = '".$_POST['chk_wr_id'][$i]."'
    ";
	//echo $sql.'<br>';
    //sql_query($sql,1);

}

// 캐시 삭제
delete_cache_latest($bo_table);


//exit;
goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;page='.$page.$qstr, false);
?>