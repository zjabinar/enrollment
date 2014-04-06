<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc");
	require_once("../include/util.inc");
	require_once("../include/student.inc");
	require_once("../include/payment.inc");
	require_once("../include/feeelement.inc");
	require_once("../include/semester.inc");
	auth_check( $_SESSION["office"] );

function get_amount_in_words( $amount )
{
	if( $amount < 0 ) {
		$words = 'minus ';
		$amount = - $amount;
	}

	$peso = intval($amount/100);
	$cent = $amount % 100;

	$thousands = intval($peso/1000);
	if( $thousands>0 ) {
		$words .= get_words_3figs( $thousands ) . ' thousand';
	}
	if( ($peso%1000) ) {
		if( strlen($words)>0 ) $words .= ' ';
		$words .= get_words_3figs( $peso%1000 );
	}
	$words .= ' peso' . ($peso>1 ? 's' : '');

	$words .= ' and ' . get_words_3figs($cent) . ' cents';

	return $words;
}

function get_words_3figs( $n )
{
	$str_ones = array( '','one','two','three','four','five','six','seven','eight','nine' );
	$str_tens = array( '','','twenty','thirty','fourty','fifty','sixty','seventy','eighty','ninety' );
	$str_teens = array( 'ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen' );
	$hundreds = intval($n / 100) % 10;
	$tens = intval($n / 10) % 10;
	$ones = $n % 10;
	if( $n==0 ) return 'zero';
	if( $hundreds > 0 ) {
		$str = $str_ones[$hundreds] . ' hundred';
	}
	if( $tens > 1 ) {
		if( $hundreds > 0 ) $str .= ' and ';
		$str .= $str_tens[$tens] . ' ' . $str_ones[$ones];
	} else if( $tens==1 ) {
		if( $hundreds > 0 ) $str .= ' and ';
		$str .= $str_teens[$ones];
	} else {
		if( ($hundreds > 0) && ($ones>0) ) $str .= ' and ';
		$str .= $str_ones[$ones];
	}
	return $str;
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Official Receipt </title>
<style type="text/css">
<!--
	body{font-size:4mm}
	div.peso{font-family:courier}
-->
</style>
</head>

<body>

<?php

	


	//$agency = $g_schoolname;
	$agency = $g_schoolname_short;
	
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$student_id = $_REQUEST["student_id"];
	$date = $_REQUEST["date"];
	$orno = $_REQUEST["orno"];

	$student = new model_student;
	$student->connect();
	$student->get_by_id($student_id);
	$dat = $student->get_fetch_assoc(0);

	$obj_payment = new model_payment;
	$obj_payment->connect();
	$obj_payment->get_list_group_by_category($sy_id,$student_id,$date,$orno);
	
	$total_payment = 0;
	$feecategory_array = get_feecategory_array();
	for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
		$ar = $obj_payment->get_fetch_assoc($i);
		$paymentlist[$i]["detail"] = $feecategory_array[$ar["feecategory_id"]];
		$paymentlist[$i]["payment"] = $ar["payment"];
		$total_payment += $ar["payment"];
	}
	$payment_extrainfo = new model_payment_extrainfo;
	$payment_extrainfo->connect();
	$payment_extrainfo->get_by_id( $orno );
	if( $payment_extrainfo->get_numrows()>0 ) {
		$ar = $payment_extrainfo->get_fetch_assoc(0);
		$payor = $ar["payor"];
	} else {
		$payor = mkstr_name_fml($dat["first_name"],$dat["middle_name"],$dat["last_name"]);
	}

	$orig_x = 30 + $_COOKIE["opt_receipt_x"];
	$orig_y = -18 + $_COOKIE["opt_receipt_y"];

	//echo '<div style="position:absolute; top:' . ($orig_y+60) . 'mm;left:' . ($orig_x+50) . 'mm;">' . $orno . '</div>';
	
	echo '<div style="position:absolute; top:' . ($orig_y+92) . 'mm;left:' . ($orig_x+81) . 'mm;">' . mkstr_date($date) . '</div>';
	//echo '<div style="position:absolute; top:' . ($orig_y+67) . 'mm;left:' . ($orig_x+55) . 'mm;">' . mkstr_date($date) . '</div>';
	
	//echo '<div style="position:absolute; top:' . ($orig_y+75) . 'mm;left:' . ($orig_x+25) . 'mm;">' . $agency . '</div>';
	
	echo '<div style="position:absolute; top:' . ($orig_y+92) . 'mm;left:' . ($orig_x+12) . 'mm;">' . $payor . " (" . mkstr_student_id($student_id) . ")" .'</div>';
	//echo '<div style="position:absolute; top:' . ($orig_y+82) . 'mm;left:' . ($orig_x+75) . 'mm;">' . mkstr_student_id($student_id) . '</div>';
	
	$y = $orig_y + 104;
	foreach( $paymentlist as $i=>$val ) {
		echo '<div style="position:absolute; top:' . $y . 'mm; left:' . ($orig_x+10) . 'mm;">' . $val["detail"] . '</div>';
		echo '<div style="position:absolute; top:' . $y . 'mm; left:' . ($orig_x+70) . 'mm; width:30mm; text-align:right" class="peso">' . mkstr_peso($val["payment"]) . '</div>';
		$y += 5;
		//echo '<div style="position:absolute; top:' . $y . 'mm; left:' . ($orig_x+10) . 'mm;">' . $val["detail"] . '</div>';
		//echo '<div style="position:absolute; top:' . $y . 'mm; left:' . ($orig_x+60) . 'mm; width:30mm; text-align:right" class="peso">' . mkstr_peso($val["payment"]) . '</div>';
		//$y += 5;
	}
	
	echo '<div style="position:absolute; top:' . ($orig_y+181) . 'mm;left:' . ($orig_x+70) . 'mm; width:30mm; text-align:right" class="peso">' . mkstr_peso($total_payment) . '</div>';
	//echo '<div style="position:absolute; top:' . ($orig_y+143) . 'mm;left:' . ($orig_x+60) . 'mm; width:30mm; text-align:right" class="peso">' . mkstr_peso($total_payment) . '</div>';
	
	//echo '<div style="position:absolute; top:' . ($orig_y+156) . 'mm;left:' . ($orig_x+10) . 'mm; width:80mm">' . get_amount_in_words($total_payment) . '</div>';

	//echo '<div style="position:absolute; top:' . ($orig_y+190) . 'mm;left:' . ($orig_x+75) . 'mm;">' . mkstr_capitalize(auth_get_fullname()) . '</div>';

	echo '<div style="position:absolute; top:' . ($orig_y+186) . 'mm;left:' . ($orig_x+10) . 'mm; width:80mm">' . get_amount_in_words($total_payment) . '</div>';

	echo '<div style="position:absolute; top:' . ($orig_y+220) . 'mm;left:' . ($orig_x+75) . 'mm;">' . mkstr_capitalize(auth_get_fullname()) . '</div>';


//School Year and Semester
	//echo '<div style="position:absolute; top:' . ($orig_y+181) . 'mm;left:' . ($orig_x+10) . 'mm;">' . $str_schoolyear. '</div>';
?>

</body>

</html>
