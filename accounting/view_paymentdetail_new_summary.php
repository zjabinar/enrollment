<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/payment.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> View Collection Details </title>
</head>

<body>

<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	print_title( get_office_name($_SESSION["office"]), "View Collection Details", $str_schoolyear );

	$sy_id_array = get_schoolyear_array();
	
	if( isset($_REQUEST["date_from"]) ) {
		$date_from = retrieve_date($_REQUEST["date_from"]);
	} else {
		$date_from = date("Y-m-j");
	}
	if( isset($_REQUEST["date_to"]) ) {
		$date_to = retrieve_date($_REQUEST["date_to"]);
		if( $date_to!=0 ) $date_to .= ' 23:59:59';
	} else {
		$date_to = date("Y-m-j") . ' 23:59:59';
	}
	$sy_id_from = isset($_REQUEST['sy_id_from']) ? $_REQUEST['sy_id_from'] : $sy_id;
	$sy_id_to = isset($_REQUEST['sy_id_to']) ? $_REQUEST['sy_id_to'] : $sy_id;

	echo '<form method="POST" name="mainform">';

	echo '<div class="prompt">Enter date</div>';
	echo '<table border="0">';
	echo '<tr>';
	echo '<td>Semster:</td>';
	echo '<td>From ' . mkhtml_select('sy_id_from',$sy_id_array,$sy_id_from) . '</td>';
	echo '<td>To ' . mkhtml_select('sy_id_to',$sy_id_array,$sy_id_to) . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Date:</td>';
	echo '<td>From <input type="text" name="date_from" value="' . mkstr_date($date_from) . '"></td>';
	echo '<td>To <input type="text" name="date_to" value="' . mkstr_date($date_to) . '"> (MM/DD/YYYY)</td>';
	echo '</tr>';
	//$collector_array[0] = ' - all - ';
	//$collector_array += get_collector_array( $sy_id );
	//echo '<tr><td>Collector:</td><td >' . mkhtml_select( "collector_id",$collector_array,$_REQUEST["collector_id"] ) . '</td>
	//<td>Fund:
//<select name="fund_id">
  //<option ';

	//if ($_REQUEST["fund_id"]=="STF"){ echo 'selected="selected"';} 

	//echo ' >STF</option>
//</select>
	// </td></tr>';
	
	echo '</table>';
	echo '<input type="submit" name="view" value="     View     ">';
	//echo '<br>';
	//echo '***************************';
	//echo $_REQUEST["fund_id"];
	//echo '***************************';

	if( isset($_REQUEST["view"]) ) {
		$result = false;
		$list = new model_payment;
		$list->connect();
		$list_detail = new model_payment;
		$list_detail->connect();
		$result = $list->get_stf_summary($sy_id_from,$sy_id_to,$date_from,$date_to);
                if( $result != false ) {
                        set_time_limit( 90 );
			$grand_total=0;
			//echo '<br>';
			//echo mkstr_peso(12763100);
			//echo '<br>';
			//echo mkstr_peso(21166200);
			//echo '<br>';
			//echo mkstr_peso(333939453);
			//echo '<br>';
                        echo "&nbsp;<table width=\"400px\"  border=\"1\" cellspacing=\"0\" cellpadding=\"3\" style=\"font-size:medium\">";
			echo '<tr><th colspan="2"><b>STF COLLECTION - SUMMARY</b></th></tr>';
			echo '<tr><th><b>FEES</b></th><th><b>AMOUNT</b></th></tr>';
                        $id = '';
                        for( $i=0; $i<$list->get_numrows(); $i++ ) {
                                $this_dat = $list->get_fetch_assoc($i);
                                        echo '<tr>';
                                        echo '<td>&nbsp;' . mkstr_neat($this_dat["title"]);
					if ($this_dat["feeelement_id"]==1 or $this_dat["feeelement_id"]==20) {
                        			echo "<table width=\"40%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"font-size:small\" align=\"right\">";
						$result_detail = $list_detail->get_stf_detail($sy_id_from,$sy_id_to,$date_from,$date_to,$this_dat["feeelement_id"]);
                        			for( $a=0; $a<$list_detail->get_numrows(); $a++ ) {
							$this_dat_detail = $list_detail->get_fetch_assoc($a);
							echo '<tr><td>&nbsp;' . mkstr_neat($this_dat_detail["short_name"]) . '</td><td align="right"> ' . mkstr_peso($this_dat_detail["Total"]).'</td></tr>';
						}
						echo '</table>';
					}		

					echo  '</td>';
                                        echo '<td class="peso">' . mkstr_peso($this_dat["Total"]) . '</td>';
					$grand_total = $grand_total + $this_dat["Total"];
                        }
			echo '<tr><td align="right"> <b>TOTAL</b></td><td align="right"><b>' . mkstr_peso($grand_total) . '</b></td></tr>';
			echo "</table>";
		}
		

	}

	echo "</form>";

	print_footer();

	echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
	echo "<input type=\"submit\" value=\"Go back\">";
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo "</form>";
?>

</body>

</html>

