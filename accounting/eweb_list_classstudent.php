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
//auth_check( $_SESSION["office"] );

?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Subject List </title>
</head>

<body>

<?php
	$class_id = $_REQUEST["class_id"];

	//$sy_id = $_SESSION["sy_id"];
	$sy_id = $_REQUEST["sy_id"];
	echo lookup_schoolyear( $sy_id ) . '<br>';

	echo '<div style="border-style:solid;border-width:thin">';
	print_classinfo_simple( $class_id );
	echo '</div><br>';

	$list = new model_regist_class;
	$list->connect();
	$list->get_student_list($sy_id,$class_id);

	$total_reg = 0;
	$total_nreg = 0;

	echo "<table border=\"1\">";
	echo "<tr>";
	echo "<th>StudentID</th><th>Name</th></th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>reg/ireg</th><th>Officially</th>";
	echo "</tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		if( $dat["regist_flag"] & REGISTFLAG_REGULAR ) {
			$total_reg++;
		} else {
			$total_nreg++;
		}
		if( ! isset($course_cache[$dat["course_id"]]) ) {
			list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
			$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
		}
		printf( "<tr>" );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
			mkstr_student_id($dat["student_id"]),
			mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
			mkstr_neat( $course_cache[$dat["course_id"]] ),
			lookup_yearlevel( $dat["year_level"] ),
			mkstr_neat( lookup_section( $dat["section"] ) ),
			(($dat["regist_flag"] & REGISTFLAG_REGULAR) ? "R" : "NR"),
			(isset($dat["date_officially"]) ? mkstr_date_short($dat["date_officially"]) : "&nbsp;")
		);
		printf( "</tr>" );
	}
	echo "</table>";
	printf( "Total %d students (%d regular, %d irregular)<br>",
		$total_reg+$total_nreg, $total_reg, $total_nreg );

	echo " <input type=\"button\" value=\"Close\" onclick=\"window.close()\">";
?>

</body>

</html>
