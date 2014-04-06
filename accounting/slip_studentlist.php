<?php
	session_start();
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/section.inc");
	require_once("../include/student.inc");
	require_once("../include/enrol_student.inc");

//	$size_x = '7.2in';
	$size_x = '100%';
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> STUDENTS' OFFICIAL LIST </title>
<style type="text/css">
<!--
	body{ font-family:times; font-size:9pt }
	th{ font-family:'Courier New'; font-size:9pt; font-weight:normal }
	td{ font-family:'Courier New'; font-size:9pt }

	div.RP{ font-size:16pt; line-height:16pt; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }
	div.school{ font-size:20pt; line-height:20pt; font-weight:bold; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }
	div.addr{ font-size:16pt; line-height:16pt; font-style:italic; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }

	div.dep{ text-align:center; font-size:14pt; font-weight:bold; page-break-before:always; margin:0 auto 0 auto }
	div.semester{ text-align:center; font-size:12pt; margin:0 auto 0 auto }
	table.course{ text-align:center; border-style:double; border-width:medium; border-color:black; border-collapse:collapse; margin:0 auto 0 auto }
	td.course{ font-family:times; font-size:12pt; font-weight:bold }
	div.major{ text-align:center; font-size:11pt; margin:0 auto 0 auto }
	div.minor{ text-align:center; font-size:11pt; margin:0 auto 0 auto }
	div.year_level{ text-align:center; font-size:11pt; font-weight:bold }
	table.student_list{ border-style:groove; border-width:thin; border-color:black; border-collapse:collapse }
	th.student_list{ border-style:groove; border-width:thin; background-color:#d0d0d0 }
	td.student_list{ border-style:none groove; border-width:thin; padding-left:0.4mm; padding-right:0.4mm }
	#gender{ font-style:italic }
	#major{ font-style:italic; text-decoration:underline }
-->
</style>
</head>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id) . ' Semester';
	$course_id = $_REQUEST["course_id"];
	$obj_course = new model_course;
	$obj_course->connect();
	$obj_course->get_by_id($course_id);
	$course = $obj_course->get_fetch_assoc(0);
	$department_id = get_department_id_from_course_id($course_id);
	$year_level = $_REQUEST["year_level"];
	$section = $_REQUEST["section"];

	$datlist = new model_enrol_student;
	$datlist->connect();

	// Student Lists
	$datlist->get_list_officially_enrolled2($sy_id,0,$course_id,$year_level,$section);

	echo '<div class="dep">' . get_department_from_department_id($department_id) . '</div>' . "\n";
	echo '<div class="semester">' . $str_schoolyear . '</div>' . "\n";
	echo '<table class="course" align="center" style="width:' . $size_x . '"><tr><td class="course">' . $course["long_name"] . '(' . $course["short_name"] . ')</td></tr></table>' . "\n";
	if( strlen($course["major"])>0 ) echo '<div class="major"> Major : ' . $course["major"] . '</div>' . "\n";
	if( strlen($course["minor"])>0 ) echo '<div class="minor"> Minor : ' . $course["minor"] . '</div>' . "\n";
	echo '<div class="year_level">' . lookup_yearlevel($year_level) . ($section>0 ? lookup_section($section) : "") . '</div>' . "\n";

	echo '<table class="student_list" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0">';
	echo '<tr><th class="student_list">ID</th><th class="student_list">LAST NAME</th><th class="student_list">FIRST NAME</th><th class="student_list">M.I.</th><th class="student_list">DATE OF BIRTH</th><th class="student_list">HOME ADDRESS</th></tr>' . "\n";
	$gender_count = array();
	for( $i=0; $i<$datlist->get_numrows(); $i++ ) {
		$dat = $datlist->get_fetch_assoc($i);
		if( $dat["gender"]!=$gender ) {
			$gender = $dat["gender"];
			echo '<tr><td class="student_list" colspan="6" id="gender">' . ($gender=="M" ? "Male" : "Female") . ':</td></tr>' . "\n";
		}
		echo '<tr>';
		echo '<td class="student_list">' . mkstr_student_id($dat["student_id"]) . '</td>';
		echo '<td class="student_list" nowrap="nowrap">' . mkstr_neat( $dat["last_name"] ) . '</td>';
		echo '<td class="student_list" nowrap="nowrap">' . mkstr_neat( $dat["first_name"] ) . '</td>';
		echo '<td class="student_list">' . mkstr_neat( get_initial($dat["middle_name"]) ) . '</td>';
		echo '<td class="student_list">' . mkstr_date( $dat["date_of_birth"] ) . '</td>';
		echo '<td class="student_list">' . mkstr_neat( $dat["home_address"] ) . '</td>';
		echo '</tr>';
		$gender_count[$gender]++;
	}
	output_gender_count($gender_count);
	echo '</table>';

function output_gender_count( $gender_count )
{
	echo '<table border="0" align="center" cellspacing="0" cellpadding="0" style="margin:0 auto 1em auto">';
	echo '<tr><td id="gender"> Male </td><td>&nbsp;&nbsp;-&nbsp;&nbsp;</td><td style="text-align:right">' . intval($gender_count["M"]) . '</td></tr>';
	echo '<tr><td id="gender"> Female </td><td>&nbsp;&nbsp;-&nbsp;&nbsp;</td><td style="text-align:right; text-decoration:underline">' . intval($gender_count["F"]) . '</td></tr>';
	echo '<tr><td></td><td></td><td>' . intval($gender_count["M"]+$gender_count["F"]) . '</td></tr>';
	echo '</table>';
}

?>

</body>

</html>
