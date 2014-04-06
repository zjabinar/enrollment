<?php
	session_start();
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/section.inc");
	require_once("../include/enrol_student.inc");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Summary of officially enrolled students </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	print_title( get_office_name($_SESSION["office"]), "Summary of officially enrolled students", $str_schoolyear );

	$datlist = new model_enrol_student;
	$datlist->connect();
	$datlist->get_summary($sy_id,$_SESSION["department_id"]);
	$datas = array();
	for( $i=0; $i<$datlist->get_numrows(); $i++ ) {
		$dat = $datlist->get_fetch_assoc($i);
		$department_id = $dat["department_id"];
		$course_id = $dat["course_id"];
		$year_level = $dat["year_level"];
		$section = $dat["section"];
		$gender = $dat["gender"];
		if( ! isset($datas[$department_id][$course_id][$year_level][$section]) ) {
			$rowspan_dep[$department_id]++;
			$rowspan_course[$course_id]++;
			$rowspan_yearlevel[$course_id][$year_level]++;
		}
		$datas[$department_id][$course_id][$year_level][$section][$gender] = $dat["total"];
	}

	echo '<table border="1">';
	echo '<tr><th>Department</th><th>Course</th><th>YearLevel</th><th>Section</th><th>M</th><th>F</th><th>Total</th><th>List</th></tr>';
	foreach( $datas as $dep_id=>$dep ) {
		$dep_total = 0;
		$dep_total_m = 0;
		$dep_total_f = 0;
		echo '<tr>';
		echo '<td rowspan="' . ($rowspan_dep[$dep_id]+1) . '">';
		echo get_department_from_department_id($dep_id);
		echo '</td>';
		$course_count = 0;
		foreach( $dep as $course_id=>$course ) {
			if( $course_count>0 ) echo '<tr>';
			$course_count++;
			echo '<td rowspan="' . $rowspan_course[$course_id] . '">';
			echo get_short_course_from_course_id($course_id);
			$yearlevel_count = 0;
			echo '</td>';
			foreach( $course as $year_level=>$yl ) {
				if( $yearlevel_count>0 ) echo '<tr>';
				$yearlevel_count++;
				echo '<td rowspan="' . $rowspan_yearlevel[$course_id][$year_level] . '">';
				echo lookup_yearlevel($year_level);
				$section_count = 0;
				echo '</td>';
				foreach( $yl as $section=>$sec ) {
					$subtotal = 0;
					foreach( $sec as $val ) $subtotal += $val;
					if( $section_count>0 ) echo '<tr>';
					$section_count++;
					echo '<td>' . mkstr_neat(lookup_section($section)) . '</td>';
					echo '<td>' . intval($sec["M"]) . '</td>';
					echo '<td>' . intval($sec["F"]) . '</td>';
					echo '<td>' . intval($subtotal) . '</td>';
					echo '<td><input type="button" value="List" onClick="' . "window.open('slip_studentlist.php?sy_id=$sy_id&course_id=$course_id&year_level=$year_level&section=$section','_blank')\">";
					echo '</tr>';
					$dep_total += $subtotal;
					$dep_total_m += $sec["M"];
					$dep_total_f += $sec["F"];
				}
			}
		}
		echo '<tr><td colspan="3" align="right">Total</td><td>' . intval($dep_total_m) . '</td><td>' . intval($dep_total_f) . '</td><td>' . intval($dep_total) . '</td></tr>';
		$total += $dep_total;
	}
	echo '</table>';
	echo 'Total ' . $total . ' students';

	echo '<br>';
	$department_id = $_SESSION["department_id"];
	echo "<input type=\"button\" value=\"Printable Official List" . ($department_id>0 ? " (" . get_department_from_department_id($department_id) . ")" : "") . "\" onClick=\"window.open('slip_studentsummary.php?sy_id=$sy_id&department_id=$department_id','_blank')\">";
	echo "<br><input type=\"button\" value=\"Units Summary List" . ($department_id>0 ? " (" . get_department_from_department_id($department_id) . ")" : "") . "\" onClick=\"window.open('slip_studentunits.php?sy_id=$sy_id&department_id=$department_id','_blank')\">";

	print_footer();

	echo '<form method="POST" action="index.php" id="goback">';
	echo '<input type="submit" value="Go back">';
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '</form>';
?>

</body>

</html>
