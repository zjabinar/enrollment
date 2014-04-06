<?php
	session_start();
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");

$val = array(
	"student_id"		=> "StudentID",
	"first_name"		=> "First Name",
	"middle_name"		=> "Middle Name",
	"last_name"			=> "Last Name",
	"civil_status"		=> "Civil Status",
	"date_of_birth"		=> "Date of birth",
	"place_of_birth"	=> "Place of birth",
	"present_address"	=> "Present Address",
	"p_first_name"		=> "Parent(Guardian) First Name",
	"p_middle_name"		=> "Parent(Guardian) Middle Name",
	"p_last_name"		=> "Parent(Guardian) Last Name",
	"p_relation"		=> "Parent(Guardian) relation",
	"parent_address"	=> "Parent Address",
	"course_id"			=> "Course",
	"enter_sy"			=> "Entered semester",
	"graduate_sy"		=> "Graduated semester",
	"home_address"		=> "Home Address",
	"gender"			=> "Gender",
	"elem_school"		=> "Elementary school",
	"elem_grad_year"	=> "Year graduated elementary school",
	"second_school"		=> "Secondary school",
	"second_grad_year"	=> "Year graduated secondary school",
	"course_completed"	=> "Course Completed",
	"last_school"		=> "Last school attended",
	"last_school_year"	=> "Last school attended year"
);

function print_edit_info($student)
{
	global $lookup_gender;
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx == "student_id" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_student_id($student[$idx]) . "</td>";
		} else if( $idx == "course_id" ) {
			echo "<td> $name </td>";
			echo "<td>" . get_course_from_course_id($student[$idx]) . "</td>";
		} else if( $idx == "gender" ) {
			echo "<td> $name </td>";
			echo "<td>" . mkstr_neat($lookup_gender[$student[$idx]]) . "</td>";
		} else if( $idx == "date_of_birth" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_neat(mkstr_date($student[$idx])) . "</td>";
		} else if( $idx == "enter_sy" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . lookup_schoolyear($student[$idx]) . "</td>";
		} else if( $idx == "graduate_sy" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_neat(lookup_schoolyear($student[$idx])) . "</td>";
		} else if( $idx == "civil_status" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_neat(lookup_civilstatus($student[$idx])) . "</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_neat($student[$idx]) . "</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Student Information </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$student = new model_student;
	$student->connect();
	$student->get_by_id($_REQUEST["student_id"]);
	$dat = $student->get_fetch_assoc(0);

	print_edit_info( $dat );
?>

	<input type="button" value="Close" onClick="window.close()">

</body>

</html>
