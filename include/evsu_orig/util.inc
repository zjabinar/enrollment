<?php

require_once("auth.inc");



/////////////////////////////////
/// convert null string to \&nbsp; and others no change
/// in order to make table of html neat
////////////////////////////////
function mkstr_neat( $str )
{
	if( $str=="" ) {
		return "&nbsp;";
	} else {
		return $str;
	}
}



////////////////////////////////
/// lookup table of gender
////////////////////////////////
$lookup_gender = array(
	"M" => "Male",
	"F" => "Female"
);



define( "MKHTML_SELECT_NONE","-101" );
define( "MKHTML_SELECT_FIRST","-102" );
define( "MKHTML_SELECT_LAST","-103" );
////////////////////////////////
/// make select tag for array
////////////////////////////////
function mkhtml_select( $name, $array, $defaultval, $optionstr=null )
{
	if( $optionstr==null ) {
		$res = "<select name=\"${name}\">";
	} else {
		$res = "<select name=\"${name}\" $optionstr>";
	}
	$i=0;
	$max = count($array);
	if( is_array($array) && ($max>0) ) {
		foreach( $array as $id => $nm ) {
			$res .= "<option value=\"${id}\"";
			if( $defaultval==MKHTML_SELECT_NONE ) {
			} else if( $defaultval==MKHTML_SELECT_FIRST ) {
				if( $i==0 ) $res .= " selected";
			} else if( $defaultval==MKHTML_SELECT_LAST ) {
				if( $i==($max-1) ) $res .= " selected";
			} else if( strcmp($id,$defaultval)==0 ) {
				$res .= " selected";
			}
			$res .= ">${nm}</option>";
			$i++;
		}
	}
	$res .= "</select>";
	return $res;
}



////////////////////////////////
/// make peso value, which 1 peso equals to 100, to dotted string, which is 1.00
////////////////////////////////
function mkstr_peso( $amount )
{
	//return sprintf( "%d.%02d", $amount / 100, abs($amount % 100) );
	$str = sprintf( ".%02d", abs($amount % 100) );
	$amount = intval($amount/100);
	while( abs($amount) >= 1000 ) {
		$str = sprintf( ",%03d", abs($amount % 1000) ) . $str;
		$amount = intval($amount/1000);
	}
	$str = sprintf( "%d", $amount ) . $str;
	return $str;
}



////////////////////////////////
/// retrieve peso value from dotted string
////////////////////////////////
function retrieve_peso( $str )
{
	$str = ereg_replace( ",","",$str );	// remove comma
	//sscanf( $str, "%d.%d", &$n, &$m );
	sscanf( $str, "%d.%s", $n, $m );
	if( strlen($m)==1 ) $m = $m . '0';	// if .5, it is .50
	if( $n < 0 ) {
		return - (abs($n)*100 + abs($m));
	} else {
		return $n*100 + $m;
	}
}



////////////////////////////////
/// convert date string from database format (YYYY-MM-DD) to MM/DD/YYYY
////////////////////////////////
function mkstr_date( $date )
{
	if( $date=="" ) return "";
	list( $year,$month,$day ) = sscanf( $date, "%d-%d-%d" );
	return sprintf( "%02d/%02d/%04d", $month, $day, $year );
}



////////////////////////////////
/// convert date string from MM/DD/YYYY to database format (YYYY-MM-DD)
////////////////////////////////
function retrieve_date( $datestr )
{
	if( $datestr=="" ) return "";
	list( $month,$day,$year ) = sscanf( $datestr, "%d/%d/%d" );
	return sprintf( "%04d-%02d-%02d", $year, $month, $day );
}



/////////////////////////////////
/// convert to short date string from database format (YYYY-MM-DD) to MM/DD
/////////////////////////////////
function mkstr_date_short( $date )
{
	if( $date=="" ) return "";
	list( $year,$month,$day ) = sscanf( $date, "%d-%d-%d" );
	return sprintf( "%02d/%02d", $month, $day );
}



////////////////////////////////
/// make grade, which 1 equals to 0.1
////////////////////////////////
function mkstr_grade( $grade )
{
	if( $grade=='' ) return '';
	//return sprintf( "%d.%d", intval($grade/10),$grade%10 );
	return sprintf( "%4.2f", $grade/100.0 );
}
function mkstr_gradeaverage( $average )
{
	if( $average=='' ) return '';
	return sprintf( "%5.3f", $average/100 );
}



////////////////////////////////
/// retrieve grade from dotted string
////////////////////////////////
function retrieve_grade( $str )
{
	sscanf( $str, "%s.%s", $n, $m );
	if( strlen($m)==1 ) $m = $m . '0';	// if .5, it is .50
	$grade = $n*100 + $m;
	if( $grade==0 ) return null;
	return $grade;
}



//////////////////////////////////
/// print html titles
//////////////////////////////////
function print_title( $title, $subtitle, $schoolyear_str=null )
{
	echo '<form action="index.php" method="POST">';
	echo '<table border="0" celppading="0" cellspacing="0" width="100%">';
	echo '<tr>';
	echo '<td><h1 style="font-size:medium" style="padding:0;margin:0">' . $title . '</h1></td>';
//	if( $schoolyear_str==null && isset($_SESSION['sy_id']) ) $schoolyear_str = lookup_schoolyear($_SESSION["sy_id"]);
	echo '<td align="right" style="font-size:small;padding:0;margin:0"><b>' . mkstr_neat($schoolyear_str) . '</b> <input type="submit" name="chg_office" value="ChangeOffice"></td>';
	echo '</tr><tr>';
	echo '<td><h2 style="font-size:small" style="padding:0;margin:0">' . $subtitle . '</h2></td>';
	echo '<td align="right" style="padding:0;margin:0"><a href="../manual/" target="_blank">manual</a> <a href="../option/option.html" target="_blank">options</a> <a href="userpage.php">' . auth_get_username() . '</a><input type="submit" name="logout" value="logout"></td>';
	echo '</tr>';
	echo '</table>';
	echo '<hr>';
	echo '</form>';
}


//////////////////////////////////
/// print program heading
//////////////////////////////////
function print_heading( )
{
		echo '<table border="0" width="100%">';
		echo '<tr><td><h2> <center> EVSU-OCC Enrollment System</center> </h2></td>';
		//echo '<td align="right" valign="bottom"><a href="../manual/" target="_blank">manual</a></td></tr>';
		echo '</table>';
		//echo '<hr>';
}


////////////////////////////////
/// print html footer
////////////////////////////////
function print_footer()
{
	echo '<hr>';
	//echo '<div style="font-size:small;font-style:italic;float:right">EVSU-OCC Enrollment System v1.00</div>';
}



////////////////////////////////
/// print hidden tags
///   ar_src : array that contains the source
///   ar_idx : (optional) array that contains that indexies that should be printed
////////////////////////////////
function print_hidden( $ar_src,$ar_idx=null )
{
	foreach( $ar_src as $idx=>$val ) {
		if( ($ar_idx==null) || in_array($idx,$ar_idx) ) {
			if( is_array($val)>0 ) {
				foreach( $val as $i=>$j ) {
					echo '<input type="hidden" name="' . $idx . '[' . $i . ']" value="' . $j . '">';
				}
			} else {
				echo '<input type="hidden" name="' . $idx . '" value="' . $val . '">';
			}
		}
	}
}



///////////////////////////////
/// make the string capitalize
///////////////////////////////
function mkstr_capitalize( $str )
{
//	setlocale( LC_CTYPE,"es_utf8" );
//	return strtoupper($str);
	$str = strtoupper( $str );
	$from = 'ñ';
	$to = 'Ñ';
	return str_replace( $from,$to,$str );
}


///////////////////////////////
/// get initial from name
///////////////////////////////
function get_initial( $name )
{
	if( strlen($name)==0 ) return '';
	return substr($name,0,1) . '.';
}



/**
 *  Split First name and Extension (Jr. II. III. IV, etc..)
 *  Returns array of (first_name, extension).
 */
function split_firstname_extension( $first_name )
{
	$extension_array = array( 'JR','JR.','SR','SR.','II','III','IV','V','VI' );
	$ar = explode( " ", $first_name );
	$n = count($ar);
	if( $n > 1 ) {
		if( in_array( strtoupper(trim($ar[$n-1])), $extension_array ) ) {
			$extension_name = $ar[$n-1];
			unset( $ar[$n-1] );
			$ar[$n-2] = trim($ar[$n-2],' ,');	// some people put comma before Jr.
			$first_name = implode(' ',$ar);
			return array($first_name,$extension_name);
		}
	}
	return array($first_name,'');
}



/**
 *  Make arrange name in First name, Middle Initial, Last name order
 */
function mkstr_name_fml( $first_name, $middle_name, $last_name )
{
	/* first, split first name and extension */
	list( $first,$ext ) = split_firstname_extension( $first_name );
	if( $ext=='' ) {
		return $first . ' ' . get_initial($middle_name) . ' ' . $last_name;
	} else {
		return $first . ' ' . get_initial($middle_name) . ' ' . $last_name . ', ' . $ext;
	}
}



/**
 *  Make arrange name in Last name, First name, Middle Initial order
 */
function mkstr_name_lfm( $first_name, $middle_name, $last_name )
{
	/* first, split first name and extension */
	list( $first,$ext ) = split_firstname_extension( $first_name );
	if( $ext=='' ) {
		return $last_name . ', ' . $first . ' ' . get_initial($middle_name);
	} else {
		return $last_name . ', ' . $first . ' ' . get_initial($middle_name) . ', ' . $ext;
	}
}



/**
 *  Make arrange name in first name initial, middle name initial, last name order
 */
function mkstr_name_fimil( $first_name, $middle_name, $last_name )
{
	/* first, split first name and extension */
	list( $first,$ext ) = split_firstname_extension( $first_name );
	if( $ext=='' ) {
		return get_initial($first) . get_initial($middle_name) . ' ' . $last_name;
	} else {
		return get_initial($first) . get_initial($middle_name) . ' ' . $last_name . ', ' . $ext;
	}
}



/**
 * Calculates age
 *   $birthday and $date (optional) should be given by unix time
 */
function calculate_age( $birthday, $now=null )
{
	if( $now==null ) {
		$now = getdate();
	} else {
		$now = getdate( $now );
	}
	$birthday = getdate( $birthday );

	$age = $now['year'] - $birthday['year'];
	if( $birthday['mon'] > $now['mon'] ) {
		$age--;
	} else if( ($bithday['mon']==$now['mon']) && ($birthday['mday'] > $now['mday']) ) {
		$age--;
	}
	
	return $age;
}



?>
