<?php



function get_section_array()
{
	return array(
		0=>'', 1=>'A', 2=>'B', 3=>'C', 4=>'D', 5=>'E', 6=>'F', 7=>'G', 8=>'H', 9=>'I', 10=>'J'
	); 
}



function lookup_section( $section )
{
	$array = get_section_array();
	return $array[$section];
}



function lookup_section_flag( $section_flag )
{
	$array = get_section_array();
	foreach( $array as $idx=>$val ) {
		if( $idx>0 ) {
			if( $section_flag & (0x01<<($idx-1)) ) {
				$str .= $val;
			}
		}
	}
	return $str;
}



?>
