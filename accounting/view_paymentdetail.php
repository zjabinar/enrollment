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
	print_heading();
?>
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
		$list->get_list_of_feeelement($sy_id);
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			$feeelement_array[$dat["feeelement_id"]] = $dat["title"];
		}
		$result = $list->get_list_by_feeelement($sy_id_from,$sy_id_to,$date_from,$date_to);
		if( $result != false ) {
			set_time_limit( 90 );
			echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"0\" style=\"font-size:small\">";
			echo "<tr>";
			echo "<th>Date</th><th>ORNO</th><th>StudentID</th><th>StudentName</th><th>Total</th>";
			foreach( $feeelement_array as $title ) echo "<th>$title</th>";
			echo "</tr>";
			$id = '';
			for( $i=0; $i<$list->get_numrows()+1; $i++ ) {
				$this_dat = $list->get_fetch_assoc($i);
				$this_id = $this_dat["date"] . $this_dat["orno"] . $this_dat["student_id"];
				if( ($id!=$this_id || $i==$list->get_numrows()) && ($id!='') ) {
					echo '<tr>';
					echo '<td>' . mkstr_date($dat["date"]) . '</td>';
					echo '<td>' . mkstr_neat($dat["orno"]) . '</td>';
					echo '<td>' . mkstr_student_id($dat["student_id"]) . '</td>';
					echo '<td>' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</td>';
					echo '<td class="peso">' . mkstr_peso($dat["total"]) . '</td>';
					foreach( $feeelement_array as $feeelement_id=>$title ) {
						if( $dat[$feeelement_id]==0 ) {
							echo '<td align="center"> - </td>';
						} else {
							echo '<td class="peso">' . mkstr_peso($dat[$feeelement_id]) . '</td>';
							$total[$feeelement_id] += $dat[$feeelement_id];
						}
					}
					echo "</tr>\n";
					$total["total"] += $dat["total"];
				}
				if( $id!=$this_id ) {
					unset( $dat );
					$id = $this_id;
					$dat["date"] = $this_dat["date"];
					$dat["orno"] = $this_dat["orno"];
					$dat["student_id"] = $this_dat["student_id"];
					$dat["first_name"] = $this_dat["first_name"];
					$dat["middle_name"] = $this_dat["middle_name"];
					$dat["last_name"] = $this_dat["last_name"];
				}
				$dat[$this_dat["feeelement_id"]] = $this_dat["payment"];
				$dat["total"] += $this_dat["payment"];
			}
			echo '<tr>';
			echo '<td align="right">Total</td><th>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
			echo '<td class="peso">' . mkstr_peso($total["total"]) . '</td>';
			foreach( $feeelement_array as $feeelement_id=>$title ) {
				echo '<td class="peso">' . mkstr_peso($total[$feeelement_id]) . '</td>';
			}
			echo '</tr>';
			echo '</table>';
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

