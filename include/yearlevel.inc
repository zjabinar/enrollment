<?php



function get_yearlevel_array()
{
	return array(
		"0" => "any",
		"1" => "1stYear",
		"2" => "2ndYear",
		"3" => "3rdYear",
		"4" => "4thYear",
		"5" => "5thYear"
	);
}



/**
 * lookup year level string (1stYear,2ndYear,...) from year_level.
 * if $include_any is false, any will not be returned when zero.
 */
function lookup_yearlevel( $year_level,$include_any=true )
{
	if( $include_any==false && $year_level==0 ) return '';
	$array = get_yearlevel_array();
	return $array[$year_level];
}



?>
