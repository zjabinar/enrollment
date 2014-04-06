<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");

	// if logout do logout
	if( isset($_REQUEST["logout"]) ) auth_logout();
	
	// authentication
	if( isset($_POST["username"]) && isset($_POST["password"]) ) {
		auth_start( $_POST["username"],$_POST["password"] );
	}
	
	if( isset($_POST["office"]) ) {
		$_SESSION["office"] = $_POST["office"];
		// set cookie to remember which office user was using
		$uri = $_SERVER["REQUEST_URI"];
		$pos = 0;
		while( ($newpos=strpos($uri,"/",$pos+1))!=false ) $pos = $newpos;	// find last '/'
		if( $pos > 0 ) {
			$uri = substr( $uri,0,$pos+1 );
			setcookie( "office",$_POST["office"],time()+60*60*24*30,$uri );
		}
	} else if( isset($_COOKIE["office"]) ) {
		// retrieve cookie value for default office
		$_SESSION["office"] = $_COOKIE["office"];
	}

	if( isset($_REQUEST["sy_id"]) ) $_SESSION["sy_id"] = $_REQUEST["sy_id"];
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="base.css" />
<title>EVSU-OCC Enrollment System</title>
<style type="text/css">
<!--
input.menu { width:20em; text-align:left; padding-left:1em; padding-right:1em }
-->


</style>
<style type="text/css" media="screen">
<!--
@import url("p7tp/p7tp_01.css");
-->
</style>
<script type="text/javascript" src="p7tp/p7tpscripts.js"></script>
</head>

<body onLoad="P7_initTP(1,0)">
<?php
	print_heading();
?>
<?php
	$officearray[AUTH_REGISTRAR] = get_office_name(AUTH_REGISTRAR);
	$officearray[AUTH_ACCOUNTING] = get_office_name(AUTH_ACCOUNTING);
	$officearray[AUTH_CASHIER] = get_office_name(AUTH_CASHIER);
	$officearray[AUTH_SCHOLARSHIP] = get_office_name(AUTH_SCHOLARSHIP);
	$officearray[AUTH_GRADE] = get_office_name(AUTH_GRADE);
	$officearray[AUTH_HRMO] = get_office_name(AUTH_HRMO);
	$ar = get_department_array();
	foreach( $ar as $idx => $nm ) $officearray[$idx] = $nm;
	if( isset($_SESSION["office"]) && auth_test($_SESSION["office"]) ) {
		if( isset($_REQUEST["sy_id"]) ) {
			print_title( $officearray[$_SESSION["office"]], 'Main menu', lookup_schoolyear($_REQUEST["sy_id"]) );
		} else {
			print_title( $officearray[$_SESSION["office"]], '', '' );
		}
	} else {
		//echo '<table border="0" width="100%">';
		//echo '<tr><td><h1> <center>EVSU-OCC Enrollment System</center> </h1></td>';
		////echo '<td align="right" valign="bottom"><a href="../manual/" target="_blank">manual</a></td></tr>';
		//echo '</table>';
		//echo '<hr>';
	}

?>


	
<?php		

	if( !isset($_SESSION["office"]) || !auth_test($_SESSION["office"]) ) {
	?> <!-- <div align="center" style="border-style:solid;border-width:thin;padding:5px; width:700px; jal">-->
	<table border="0" align="center" width="400"> 
		 <tr>
		  <td>
  <?php
		if( isset($_POST["username"]) ) {
			echo '<div class="error"> Username or password is incorrect</div>';
		} else {
			echo '<div class="prompt">Enter your username and password</div>';
		}
		echo "<form method=\"POST\" name=\"mainform\">";
		echo '<table>';
		echo '<tr><td>Office</td><td>';
		echo mkhtml_select( "office", $officearray, isset($_SESSION["office"]) ? $_SESSION["office"] : "" );
		echo '</td></tr>';
		echo "<tr><td>Username</td><td><input type=\"text\" name=\"username\" autocomplete=\"off\"></td></tr>";
		echo "<tr><td>Password</td><td><input type=\"password\" name=\"password\" autocomplete=\"off\"></td></tr>";
		echo "<tr><td></td><td>";
		echo '<script type="text/javascript">';
		echo "<!--\n";
		echo 'document.write("<input type=\"submit\" value=\"     login     \">");';
		echo 'document.mainform.username.focus();';
		echo "// -->\n";
		echo '</script>';
		echo '<noscript><div class="error">You have to enable JavaScript to use the system!</div></noscript>';
		echo "</td></tr>";
		echo "</table>";
		echo "</form>";
?>	  </td><td><img src="images/logo.png" width="144" height="150"></td>
	 </tr>
	</table>
	</div>


<?php
		//echo '<b>Updates</b><br>';
		//echo '<iframe src="updates.html" width="500" height="128"></iframe>';
	} else if( isset($_REQUEST["chg_office"]) ) {
		unset( $_SESSION["department_id"] );
		echo '<form method="POST">';
		echo '<div class="prompt">Select semester and office</div>';
		echo mkhtml_select( "sy_id",get_schoolyear_array(), $_SESSION["sy_id"]==0 ? MKHTML_SELECT_FIRST : $_SESSION["sy_id"] ) . '<br>';
		$ar = array();
		foreach( $officearray as $idx=>$val ) {
			if( auth_test($idx) ) $ar[$idx] = $val;
		}
		echo mkhtml_select( "office", $ar, isset($_SESSION["office"]) ? $_SESSION["office"] : "" ) . '<br>';
		echo '<input type="submit" value="Go" style="width:4em">';
		echo '</form>';
	} else if( ! isset($_REQUEST["sy_id"]) ) {
		echo '<form method="POST">';
		echo '<div class="prompt">Select semester</div>';
		echo mkhtml_select( "sy_id",get_schoolyear_array(), $_SESSION["sy_id"]==0 ? MKHTML_SELECT_FIRST : $_SESSION["sy_id"] ) . '<br>';
		echo '<input type="submit" value="Go" style="width:4em">';
		echo '</form>';
	} else if( $_SESSION["office"]==AUTH_REGISTRAR ) {
		echo '<table border="0">';
		echo '<tr><td valign="top">';
		echo 'Students<br>';
		echo '<form action="student.php" method="POST"><input type="submit" value="Student Registration" class="menu"></form>';
		echo '<form action="blockstudent.php" method="POST"><input type="submit" value="Block/Unblock Student" class="menu"></form>';
		echo '<form action="view_officialstudent.php" method="POST"><input type="submit" value="View officially enrolled students" class="menu"></form>';
		echo '<form action="view_studentsummary.php" method="POST"><input type="submit" value="View summary of officially enrolled students" class="menu"></form>';
		echo '</td><td style="padding-left:1em" valign="top">';
		echo 'Class schedules<br>';
		echo '<form action="room.php" method="POST"><input type="submit" value="Room Registration" class="menu"></form>';
		echo '<form action="view_scheduleteacher.php" method="POST"><input type="submit" value="View schedule of teachers" class="menu"></form>';
		echo '<form action="view_scheduleroom.php" method="POST"><input type="submit" value="View schedule of rooms" class="menu"></form>';
		echo '</td></tr>';
		echo '</table>';
	} else if( $_SESSION["office"]==AUTH_ACCOUNTING ) {
		echo '<table border="0">';
		echo '<tr><td valign="top">';
		echo 'Fees<br>';
		echo '<form action="syinfo.php" method="POST"><input type="submit" value="Edit date of enrollment" class="menu"></form>';
		echo '<form action="feerate.php" method="POST"><input type="submit" value="Edit Fee Rate" class="menu"></form>';
		echo '<form action="miscfee.php" method="POST"><input type="submit" value="Edit Miscellaneous Fee" class="menu"></form>';
		echo '<form action="optionalfee.php" method="POST"><input type="submit" value="Edit Optional Fee" class="menu"></form>';
		echo '<form action="gradfee.php" method="POST"><input type="submit" value="Edit Graduation Fee" class="menu"></form>';
		echo '<br>Classes<br>';
		echo '<form action="class.php" method="POST"><input type="submit" value="Check class details" class="menu"></form>';
		echo '</td><td valign="top" style="padding-left:1em">';
		echo 'Students<br>';
		echo '<form action="assessment.php" method="POST"><input type="submit" value="Check Assessment" class="menu"></form>';
		echo '<form action="enrol_drop.php" method="POST"><input type="submit" value="Officially drop a student" class="menu"></form>';
		echo '<form action="blockstudent.php" method="POST"><input type="submit" value="Block/Unblock Student" class="menu"></form>';
		echo '<form action="list_guarantor.php" method="POST"><input type="submit" value="List of guaranted students" class="menu"></form>';
		echo '<form action="view_balance.php" method="POST"><input type="submit" value="List of students\' balance" class="menu"></form>';
		echo '</td></tr>';
		echo '</table>';
	} else if( $_SESSION["office"]==AUTH_CASHIER ) {
		echo 'Students<br>';
		echo '<form action="assessment.php" method="POST"><input type="submit" value="Payment" class="menu"></form>';
		echo '<form action="blockstudent.php" method="POST"><input type="submit" value="Block/Unblock Student" class="menu"></form>';
		echo '<form action="list_guarantor.php" method="POST"><input type="submit" value="List of guaranted students" class="menu"></form>';
		echo '<br>Reports<br>';
		echo '<form action="collection.php" method="POST"><input type="submit" value="Summary of collection" class="menu"></form>';
		echo '<form action="view_orno.php" method="POST"><input type="submit" value="View Official Receipt" class="menu"></form>';
		echo '<form action="view_paymentdetail.php" method="POST"><input type="submit" value="View Collection Detail" class="menu"></form>';
		echo '<form action="view_paymentdetail_new.php" method="POST"><input type="submit" value="View Collection Detail - By Fund" class="menu"></form>';
		echo '<form action="view_paymentdetail_new_summary.php" method="POST"><input type="submit" value="View Collection Summary - STF Fund" class="menu"></form>';
	} else if( $_SESSION["office"]==AUTH_SCHOLARSHIP ) {
		echo '<form action="scholar_manage.php" method="POST"><input type="submit" value="Scholarship definition" class="menu"></form>';
		echo '<form action="scholar_assign.php" method="POST"><input type="submit" value="Scholarship assignment" class="menu"></form>';
		echo '<form action="scholar_list.php" method="POST"><input type="submit" value="List of scholars" class="menu"></form>';
		echo '<form action="blockstudent.php" method="POST"><input type="submit" value="Block/Unblock Student" class="menu"></form>';
	} else if( $_SESSION["office"]==AUTH_GRADE ) {
		echo '<form action="encode_grade.php" method="POST"><input type="submit" value="Grades encoding" class="menu"></form>';
		echo '<form action="view_graderank.php" method="POST"><input type="submit" value="View grade ranking" class="menu"></form>';
		echo '<form action="view_nogrades.php" method="POST"><input type="submit" value="Class with no grades" class="menu"></form>';
	} else if( $_SESSION["office"]==AUTH_HRMO ) {
		echo '<form action="teacher.php" method="POST"><input type="submit" value="Teacher Registration" class="menu"></form>';
		echo '<form action="view_scheduleteacher.php" method="POST"><input type="submit" value="View schedule of teachers" class="menu"></form>';
	} else {
		$department_id = $_SESSION["office"];
		$_SESSION["department_id"] = $department_id;

		echo '<table border="0">';
		echo '<tr><td valign="top">';
		echo 'Students<br>';
		echo '<form action="enrol_student.php" method="POST"><input type="submit" value="Enrollment" class="menu"></form>';
		echo '<form action="blockstudent.php" method="POST"><input type="submit" value="Block/Unblock Student" class="menu"></form>';
		echo '<form action="grad_student.php" method="POST"><input type="submit" value="Set Graduating Student" class="menu"></form>';
		echo '<form action="view_officialstudent.php" method="POST"><input type="submit" value="View officially enrolled students" class="menu"></form>';
		echo '<form action="view_studentsummary.php" method="POST"><input type="submit" value="View summary of officially enrolled students" class="menu"></form>';
		echo '<br>Grading<br>';
		echo '<form action="encode_grade.php" method="POST"><input type="submit" value="Grades encoding" class="menu"></form>';
		echo '<form action="view_graderank.php" method="POST"><input type="submit" value="View grade ranking" class="menu"></form>';
		echo '</td><td valign="top" style="padding-left:1em">';
		echo 'Class schedules<br>';
		echo '<form action="class.php" method="POST"><input type="submit" value="Class Registration" class="menu"></form>';
		echo '<form action="view_scheduleteacher.php" method="POST"><input type="submit" value="View schedule of teachers" class="menu"></form>';
		echo '<form action="view_scheduleroom.php" method="POST"><input type="submit" value="View schedule of rooms" class="menu"></form>';
		echo '</td></tr>';
		echo '</table>';
	}

	print_footer();

	if( isset($_REQUEST['office']) && isset($_REQUEST['sy_id']) ) {
		echo '<form method="POST" id="goback"><input type="submit" value="Go back"></form>';
	}
?>

</body>

</html>
