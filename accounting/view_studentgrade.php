<?php
session_start();
require_once("../include/auth.inc");
require_once("../include/util.inc");
require_once("../include/semester.inc");
require_once("../include/course.inc");
require_once("../include/student.inc");
require_once("../include/teacher.inc");
require_once("../include/class.inc");
require_once("../include/enrol_student.inc");
require_once("../include/enrol_class.inc");
require_once("../include/grade.inc");
auth_check( $_SESSION["office"] );

function print_class_table( $list )
{
	$remarkarray = get_graderemark_array();
	echo "<table border=\"1\">";
	echo "<tr>";
	echo "<th>Subject</th><th>Code</th><th>Course</th><th>YearLevel</th><th>Teacher</th><th>Units</th><th>Mid Grade</th><th>Final Grade</th><th>Grade Remark</th>";
	echo "</tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$course_id = $dat["course_id"];
		if( ! isset($cache_course[$course_id]) ) {
			$cache_course[$course_id] = get_short_course_from_course_id($course_id);
		}
		printf( "<tr>" );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td>",
			mkstr_neat($dat["subject"]),
			mkstr_neat($dat["subject_code"]),
			mkstr_neat( $dat["major_ignore"] ? strtok($cache_course[$course_id],' ') : $cache_course[$course_id] ),
			lookup_yearlevel($dat["year_level"]),
			mkstr_neat( lookup_teacher_name($dat["teacher_id"]) ),
			mkstr_neat($dat["unit"]) . ($dat["exempt"]>0 ? "*" : "")
		);
		if( $dat['regist_flag'] & REGISTFLAG_GRADECONFIRM ) {
			printf( "<td class=\"grade\">%s</td> <td class=\"grade\">%s</td> <td>%s</td>\n",
				mkstr_neat( mkstr_grade($dat['grade_midterm']) ),
				mkstr_neat( mkstr_grade($dat['grade_final']) ),
				mkstr_neat( $remarkarray[$dat['grade_remark']] )
			);
		} else {
			echo "<td colspan=\"3\" align=\"center\">Not confirmed</td>\n";
		}
		printf( "</tr>" );
	}
	echo "</table>";
	echo '<div style="text-align:right">* asterisk in units means it is exempt from tuition</div>';
}

?>
<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Grade </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_REQUEST["sy_id"];
	if( isset($_POST["sy_id"]) ) $sy_id = $_REQUEST["sy_id"];
	$student_id = $_REQUEST["student_id"];

	echo "<form method=\"POST\">";
	echo '<input type="hidden" name="student_id" value="' . $student_id . '">';

	$tblstudentsenrolled = new model_enrol_student;
	$tblstudentsenrolled->connect();
	$tblstudentsenrolled->get_sy_list( $student_id );
	for( $i=0; $i<$tblstudentsenrolled->get_numrows(); $i++ ) {
		$dat = $tblstudentsenrolled->get_fetch_assoc($i);
		$sy_array[$dat['sy_id']] = lookup_schoolyear($dat['sy_id']);
	}

	echo '<div style="border-style:solid;border-width:thin">';
	$student_dat = print_studentinfo_simple( $student_id,$sy_id );
	$sy_id_st = $student_dat["sy_id"];
	echo '</div><br>';

	echo mkhtml_select('sy_id',$sy_array,$sy_id);
	echo '<input type="submit" value="go">';

	$list = new model_regist_class;
	$list->connect();
	$result = $list->get_class_list( $sy_id_st,$student_id );
	print_class_table( $list );
	
	echo "</form>";

	echo " <input type=\"button\" value=\"Close\" onclick=\"window.close()\">";
?>

</body>

</html>
