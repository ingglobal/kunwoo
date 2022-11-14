<?php
if(!write_current_columns_flag($write_change_fields)){
	$sub_arr = write_subtract_fields($write_change_fields);
	$add_arr = write_addition_fields($write_change_fields);
	//제거해야할 필드가 있으면
	if(count($sub_arr)){
		foreach($sub_arr as $sv) sql_query(" ALTER TABLE {$write_table} DROP {$sv} ",1);
	}
	//추가해야할 필드가 있으면
	if(count($add_arr)){
		foreach($add_arr as $av) sql_query(" ALTER TABLE {$write_table} ADD COLUMN {$av} {$write_change_fields[$av]['type']} ",1);
	}
}
