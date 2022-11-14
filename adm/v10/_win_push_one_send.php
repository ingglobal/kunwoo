<?php
$sub_menu = '960120';
include_once('./_common.php');

$mb = get_table_meta('member','mb_id',$mb_id);
// print_r2($mb);

$g5['title'] = $mb['mb_name'].'님께 푸시발송';

include_once(G5_PATH.'/head.sub.php');

// echo $push_content.PHP_EOL;
// print_r2($_REQUEST);
// exit;

function sendMessage2() {
    global $g5;

    $headings = array(
        "en" => $_REQUEST["push_title"]
    );
    $content = array(
        "en" => $_REQUEST["push_content"]
    );
    $fields = array(
        'app_id' => $g5['setting']['set_onesignal_id'],
        'include_player_ids' => array($_REQUEST['push_key']),
        'data' => array(
            "url" => $_REQUEST['push_url']
        ),
        'headings' => $headings,
        'contents' => $content
    );
    $fields = json_encode($fields);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic '.$g5['setting']['set_onesignal_key']
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// $response = sendMessage2();
// $return["allresponses"] = $response;
// $return = json_encode($return);

// $data = json_decode($response, true);
// print_r2($data);
// $id = $data['id'];
// print_r2($id);

// print("\n\nJSON received:\n");
// print($return);
// print("\n");


?>
<style>
/* html,body{overflow:hidden;} */
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_submit btn_close" onclick="window.close()">닫기</a>
	<?php } ?>

	<h1><?php echo $g5['title']; ?></h1>
    <div class="local_desc01 local_desc" style="display:none;">
        <p>본 페이지는 담당자를 간단하게 관리하는 페이지입니다.(아이디, 비번 임의생성)</p>
    </div>
	<div id="com_sch_list" class="new_win" style="word-break:break-all;">

		<?php
        if($_REQUEST['push_key']) {
            $response = sendMessage2();
            $return["allresponses"] = $response;
            $return = json_encode($return);
    
            $data = json_decode($response, true);
            // print_r2($data);
            // $id = $data['id'];
            // print_r2($id);
    
            // print("\n\nJSON received:\n");
            // print($return);
            // echo PHP_EOL;
            if($data['id']) {
                echo '푸시 메시지 발송 성공!';
            }
        }
        else {
            echo '푸시키가 존재하지 않습니다.';
        }
		?>

	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(400,640)','onload':'parent.resizeTo(400,640)'});

</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>