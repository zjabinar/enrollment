<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/payment.inc");
	require_once("../include/feeelement.inc");
	auth_check( $_SESSION["office"] );
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Summary of collection </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	print_title( get_office_name($_SESSION["office"]), "Summary of collection" , $str_schoolyear );

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
	
	echo '<form method="POST">';

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
	$collector_array[0] = ' - all - ';
	$collector_array += get_collector_array( $sy_id );
	echo '<tr><td>Collector:</td><td colspan="2">' . mkhtml_select( "collector_id",$collector_array,$_REQUEST["collector_id"] ) . '</td></tr>';
	echo '</table>';
	echo '<input type="submit" value="View">';

	if( isset($_REQUEST["date_from"]) ) {
		$list = new model_payment;
		$list->connect();
		$list->get_summary_by_feeelement($sy_id_from,$sy_id_to,$date_from,$date_to,$_REQUEST["collector_id"]);

		$total = 0;
		foreach( get_feecategory_array() as $category_id=>$category ) {
			$dat_ar[$category_id] = array();
			$subtotal = 0;
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( $dat["feecategory_id"]==$category_id ) {
					$dat_ar[$category_id][$dat["feeelement_id"]] = $dat;
					$subtotal += $dat["payment"];
				}
			}
			if( count($dat_ar[$category_id])>0 ) {
				$dat_ar[$category_id]["total"] = $subtotal;
				$total += $subtotal;
			}
		}

		echo '<table border="1">';
		echo '<tr><th>Title</th><th>Amount</th></tr>';
		foreach( get_feecategory_array() as $category_id=>$category ) {
			if( count($dat_ar[$category_id])>0 ) {
				echo '<tr><td colspan="2"><b>' . $category . '</b></td></tr>';
				foreach( $dat_ar[$category_id] as $id=>$dat ) {
					if( $id!="total" ) {
						echo '<tr><td>&nbsp;&nbsp;' . $dat["title"] . '</td><td class="peso">' . mkstr_peso($dat["payment"]) . '</td></tr>';
					}
				}
				echo '<tr><td align="right">Subtotal</td><td class="peso">' . mkstr_peso($dat_ar[$category_id]["total"]) . '</td></tr>';
			}
		}
		echo '<tr><td><b>Total</b></td><td class="peso"><b>' . mkstr_peso($total) . '</b></td></tr>';
		echo '</table>';
	}

	echo '</form>';

	print_footer();

	echo '<form action="index.php" method="POST" id="goback">';
	echo ' <input type="submit" value="Go back">';
	echo ' <input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '</form>';

?>

</body>

</html>
