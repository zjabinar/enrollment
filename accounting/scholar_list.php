<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/section.inc");
	require_once("../include/student.inc");
	require_once("../include/scholarship.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> List of scholars  </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	print_title( get_office_name($_SESSION["office"]), "List of scholars", $str_schoolyear );

	echo '<form method="POST" name="mainform">';

	$scholartype_id = $_REQUEST["scholartype_id"];
	
	$scholartype_array[0] = ' - all -';
	$scholartype_array += get_scholartype_array($sy_id);
	echo mkhtml_select( 'scholartype_id',$scholartype_array,$scholartype_id );
	echo '<input type="submit" value="view"><br>';

	if( isset($_REQUEST["scholartype_id"]) ) {
		$result = false;
		$list = new model_scholarship;
		$list->connect();
		$result = $list->get_list($sy_id,$scholartype_id);
		if( $result != false ) {
			echo 'Total ' . $list->get_numrows() . ' students';
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>ScholarType</th></tr>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					$course_cache[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
				}
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$course_cache[$dat["course_id"]],
					$scholartype_array[$dat["scholartype_id"]]	
				);
				printf( "</tr>" );
			}
			echo '</table>';
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo '<input type="button" value="View Details" onClick="' . "OnRadioOpen('mainform','student_id','view_studentinfo.php','sy_id=$sy_id','_blank')" . '">';
				echo '<input type="button" value="View Subject List" onClick="' . "OnRadioOpen('mainform','student_id','list_subject.php','sy_id=$sy_id','_blank')" . '">';
				echo '<input type="button" value="View Assessement Slip" onClick="' . "OnRadioOpen('mainform','student_id','assessment_slip.php','sy_id=$sy_id','_blank')" . '">';
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

