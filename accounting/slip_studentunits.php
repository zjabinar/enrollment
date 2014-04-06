<?php
	session_start();
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/enrol_student.inc");

//	$size_x = '7.2in';
	$size_x = '100%';
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Enrollment DATA BY UNITS </title>
<style type="text/css">
<!--
	body{ font-family:times; font-size:10pt }
	table{ border-collapse:collapse }
	th{ font-family:'Courier New'; font-size:10pt; font-weight:bold; background-color:#d0d0d0; border-style:solid; border-width:thin }
	td{ font-family:'Courier New'; font-size:10pt }
	td.course{ text-align:center; font-size:11pt; font-weight:bold; border-style:none; border-width:thin }
	td.unit{ text-align:center; border-style:none solid; border-width:thin }
	td.unit_first{ text-align:center; border-style:solid solid none solid; border-width:thin }
	td.courseyear{ border-style:none solid; border-width:thin }
	td.courseyear_first{ border-style:solid solid none solid; border-width:thin; padding-left:0.4mm; padding-right:0.4mm }
	td.total{ text-align:center; border-style:solid; border-width:thin; font-weight:bold; background-color:#d0d0d0 }
-->
</style>
</head>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id) . ' Semester';

	$course_array = get_course_array(0,0,true);

	// Summary page
	$datlist = new model_enrol_student;
	$datlist->connect();
	$datlist->get_unitsummary($sy_id,$_REQUEST["department_id"]);

	for( $n=0; $n<$datlist->get_numrows(); $n++ ) {
		$dat = $datlist->get_fetch_assoc($n);
		$course_name = $course_array[$dat["course_id"]]["short_name"];
		$course_long_name[$course_name] = $course_array[$dat["course_id"]]["long_name"];
		$year_level = $dat["year_level"];
		$units = $dat["total_units"];
		$data[$course_name][$year_level][$units]++;
	}

	echo '<div style="text-align:right; font-size:9pt; width:' . $size_x . '">' . date("M j, Y") . '</div>';
	echo '<div style="text-align:center; font-size:16pt; line-height:16pt; width:' . $size_x . '">';
	echo $g_schoolname . '<br>';
	echo '<b>Enrollment DATA BY UNITS</b><br>';
	if( ! empty($_REQUEST["department_id"]) ) echo get_department_from_department_id($_REQUEST["department_id"]) . '<br>';
	echo $str_schoolyear;
	echo '</div>';
	echo '<br>';

	echo '<table align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0">';
	echo '<tr><th>COURSE</th><th>UNITS ENROLLED</th><th>NO. OF STUDENTS ENROLLED</th><th>TOTAL NO. OF UNITS ENROLLED</th></tr>';
	foreach( $data as $course=>$course_dat ) {
		$total_units = 0;
		$total_stdt = 0;
		echo '<tr><td colspan="4" class="course"><br>' . strtoupper($course_long_name[$course]) . ' (' . $course . ')</td></tr>';
		foreach( $course_dat as $year_level=>$year_level_dat ) {
			$count_year_level = 0;
			foreach( $year_level_dat as $units=>$student_count ) {
				$style = '';
				if( $count_year_level==0 ) $style="_first";
				echo '<tr>';
				echo '<td class="courseyear' . $style . '">' . ($count_year_level==0 ? $course . ($year_level > 0 ? '-' . $year_level : '') : '&nbsp;') . '</td>';
				echo '<td class="unit' . $style . '">' . $units . '</td>';
				echo '<td class="unit' . $style . '">' . $student_count . '</td>';
				echo '<td class="unit' . $style . '">' . ($units * $student_count) . '</td>';
				echo '</tr>';
				$count_year_level++;
				$total_units += $units;
				$total_stdt += $student_count;
			}
		}
		echo '<tr>';
		echo '<td class="total">TOTAL</td>';
		echo '<td class="total">' . $total_units . '</td>';
		echo '<td class="total">' . $total_stdt . '</td>';
		echo '<td class="total">' . ($total_units * $total_stdt) . '</td>';
		echo '</tr>';
	}
	echo '</table>';

?>

</body>

</html>
