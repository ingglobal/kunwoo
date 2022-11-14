<?php
$ids = ['0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
$cds = array();
for($i=0;$i<count($ids);$i++){
    if($i == 0) continue;
    for($j=0;$j<count($ids);$j++){
        //echo $ids[$i].$ids[$j]."<br>";
        array_push($cds,$ids[$i].$ids[$j]);
    }
}
?>