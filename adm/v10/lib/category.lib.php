<?php
/*

*/
class category_list {

	protected $value = "";
	protected $name = "";
	protected $list_flag = false;
	protected $list_id = "";
	protected $required = false;
	protected $readonly = false;
	protected $required_str = "";
	protected $readonly_str = "";
	protected $readonly_scr = "";
	protected $count = 0;
	protected $id = "";
	protected $id1 = "";
	protected $id2 = "";
	protected $id3 = "";
	protected $id4 = "";
	//초기생성자 함(회사idx, 값, 목록여부, [안에 들어갈 id])
	function __construct($value='',$list_flag=false,$list_id='') {
        $this->value = $value; //값
		$this->name = 'bct_id'; //name
		$this->name .= ($list_flag)?$this->name.'['.$list_id.']':''; //name 수
		$this->list_flag = $list_flag;// 목록인지 아닌지 여부
		$this->list_id = $list_id; //목록이면 name="xxx[들어갈idx]"
		$this->id = "bct_id"; //<input type="hidden" name="bct_id" />의 아이디 id
		$this->id1 = $this->id."_1";//첫번째 선택박스 id
		$this->id2 = $this->id."_2";//두번째 선택박스 id
		$this->id3 = $this->id."_3";//세번째 선택박스 id
		$this->id4 = $this->id."_4";//번째 선택박스 id
        $this->count++;
    }
	//값을 셋팅
	function set_value($value) {
		$this->value = $value;
	}
	//name명을 셋
	function set_name($name) {
		$this->name = $name;
		if($this->list_flag){
			$this->name .= '['.$this->list_id.']';
		}
	}
	//id를 셋팅
	function set_id($id) {
		$this->id = $id;
		if($id) {
			$this->id1 = $id.'_1';
			$this->id2 = $id.'_2';
			$this->id3 = $id.'_3';
			$this->id4 = $id.'_4';
		}
	}
	//필수입력인지 셋팅
	function set_required($rq){
		$this->required = $rq;
		if($this->required){
			$this->required_str = " required";
		}
	}
	//읽기 전용인지 셋팅
	function set_readonly($rd){
		$this->readonly = $rd;
		if($this->readonly){
			$this->readonly_str = " readonly";
			$this->readonly_scr = " oncFocus=\"this.initialSelect = this.selectedIndex;\" onChange=\"this.selectedIndex = this.initialSelect;\"";
		}
	}
	//실행함
	function run(){
        //global $g5, $config, $member, $default;
        global $g5;

		$cats = category_tree_array($this->value);
		/*
		[0] => 1c
		[1] => 1c10
		[2] => 1c103m
		[3] => 1c103m14
		*/
		$cats1 = array();
		$cats2 = array();
		$cats3 = array();
		$cats4 = array();

		for($i=0;$i<4;$i++){
			$csql = " SELECT bct_id,bct_desc FROM {$g5['bom_category_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bct_id REGEXP '^.{".(($i==0)?2:strlen($cats[$i]))."}$' ";
			$csql .= ($i == 0) ? "" : " AND bct_id LIKE '{$cats[$i-1]}%' ";
			//echo $csql;
			$cres = sql_query($csql,1);
			if($cres->num_rows){
				//${'cats'.($i+1)}
				for($j=0;$crow=sql_fetch_array($cres);$j++){
					${'cats'.($i+1)}[$crow['bct_id']] = $crow['bct_desc'];
				}
			}
		}
		// echo $this->bct_id."<br>";
		// print_r2($cats1);
		// echo $this->bct_id."<br>";
		// print_r2($cats2);
		// echo $this->bct_id."<br>";
		// print_r2($cats3);
		// echo $this->bct_id."<br>";
		// print_r2($cats4);

		$file = G5_USER_ADMIN_SKIN_PATH.'/category/category.skin.php';
		$call_url = G5_USER_ADMIN_SKIN_URL.'/category/ajax/category_call.php';
		$d = $this->id;
		if (!file_exists($file)) {
            return $file." 파일을 찾을 수 없습니다.";
        } else {
            ob_start();
			$this->com_idx;
			$this->value;
            include($file);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
	}
}
