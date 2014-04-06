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
<title> View Official Receipts </title>

<script type="text/JavaScript">
function OnReceipt( id )
{
	var id;
	if( document.mainform.id.length > 0 ) {
		for (i = 0; i < document.mainform.id.length; i++) {
			if (document.mainform.id[i].checked) {
				id = document.mainform.id[i].value;
			}
		}
	} else {
		id = document.mainform.id.value;
	}
	var dat = id.split(",");
	window.open('receipt.php?sy_id='+dat[0]+'&student_id='+dat[1]+'&date='+dat[2]+'&orno='+dat[3],'_blank');
}
</script>

</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	$sy_id_array = get_schoolyear_array();
	
	print_title( get_office_name($_SESSION["office"]), "View Official Receipts", $str_schoolyear );

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
	$collector_array[0] = ' - all - ';
	$collector_array += get_collector_array( $sy_id );
	echo '<tr><td>Collector:</td><td colspan="2">' . mkhtml_select( "collector_id",$collector_array,$_REQUEST["collector_id"] ) . '</td></tr>';
	echo '</table>';
	echo '<input type="submit" name="view" value="view">';
	echo '<br>';

	if( isset($_REQUEST["view"]) ) {
		$result = false;
		$list = new model_payment;
		$list->connect();
		$result = $list->get_list_of_date_orno($sy_id_from,$sy_id_to,$date_from,$date_to,$_REQUEST["collector_id"]);
		if( $result != false ) {
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo '<th>&nbsp;</th>';
			echo "<th>Date</th><th>ORNO</th><th>Amount</th><th>StudentID</th><th>StudentName</th><th>Collector</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"id\" value=\"%s,%s,%s,%s\"></td>", $sy_id,$dat["student_id"],$dat["date"],$dat["orno"] );
				printf( "<td>%s</td> <td>%s</td> <td class=\"peso\">%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_date($dat["date"]),
					mkstr_neat($dat["orno"]),
					mkstr_peso($dat["payment"]),
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$dat["fullname"]
				);
				printf( "</tr>" );
			}
			echo '</table>';
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo '<input type="button" value="View Receipt" onClick="' . "OnReceipt()" . '">';
			}
			$list->close();
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

