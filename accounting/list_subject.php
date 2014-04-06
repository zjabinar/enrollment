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
require_once("../include/feeelement.inc");
auth_check( $_SESSION["office"] );

function print_class_table( $list )
{
	echo "<table border=\"1\">";
	echo "<tr>";
	echo "<th>Subject</th><th>SubjectCode</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Teacher</th><th>Units</th><th>Options</th><th>Dep.</th>";
	echo "</tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$course_id = $dat["course_id"];
		if( ! isset($cache_course[$course_id]) ) {
			$cache_course[$course_id] = get_short_course_from_course_id($course_id);
		}
		printf( "<tr>" );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td><small>%s</small></td> <td>%s</td>\n",
			mkstr_neat($dat["subject"]),
			mkstr_neat($dat["subject_code"]),
			mkstr_neat( $dat["major_ignore"] ? strtok($cache_course[$course_id],' ') : $cache_course[$course_id] ),
			lookup_yearlevel($dat["year_level"]),
			mkstr_neat( lookup_section_flag($dat["section_flag"]) ),
			mkstr_neat( lookup_teacher_name($dat["teacher_id"]) ),
			mkstr_neat($dat["unit"]) . ($dat["exempt"]>0 ? "*" : ""),
			mkstr_neat( get_classflag_string( $dat["flag"] ) . ($dat['feeelement_id']>0 ? '<br><nobr>' . lookup_feeelement_title($dat['feeelement_id']) . '(P' . mkstr_peso($dat['fee_amount']) . ')</nobr>' : '' ) ),
			$dat["department_name"]
		);
		printf( "</tr>" );
	}
	echo "</table>";
	echo '<div style="text-align:right">* asterisk in units means it is exempt from tuition</div>';
}

$modelist = array(
	'Enrolled list',
	'Added list',
	'Dropped list',
	'Changed list'
);

function print_tab( $mode )
{
	global $modelist;
	foreach( $modelist as $idx ) {
		if( $idx==$mode ) {
			printf( '<div style="border-style:inset;float:left"><input type="button" value="%s"></div>', $idx );
		} else {
			printf( '<div style="border-style:outset;float:left;font-weight:lighter"><input type="submit" name="mode" value="%s"></div>', $idx );
		}
	}
	echo '<div style="clear:left"></div>';
	//echo "<br>";
}

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
	$sy_id = $_REQUEST["sy_id"];

	echo lookup_schoolyear( $sy_id ) . '<br>';

	echo "<form action=\"list_subject.php\">";

	if( ! isset($_REQUEST["mode"]) ) $_REQUEST["mode"] = $modelist[0];
	$student_id = $_REQUEST["student_id"];
	echo '<div style="border-style:solid;border-width:thin">';
	$student_dat = print_studentinfo_simple( $student_id,$sy_id );
	$sy_id_st = $student_dat["sy_id"];
	echo '</div><br>';
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '<input type="hidden" name="student_id" value="' . $student_id . '">';
	if( $_REQUEST["mode"]==$modelist[0] ) {
		$list = new model_regist_class;
		$list->connect();
		$result = $list->get_class_list( $sy_id_st,$student_id );
		print_tab( $_REQUEST["mode"] );
		if( $result!=false ) {
			print_class_table( $list );
		}
	} else if( $_REQUEST["mode"]==$modelist[1] ) {
		$list = new model_add_class;
		$list->connect();
		$result = $list->get_class_list( $sy_id_st,$student_id );
		print_tab( $_REQUEST["mode"] );
		if( $result!=false ) {
			print_class_table( $list );
		}
	} else if( $_REQUEST["mode"]==$modelist[2] ) {
		$list = new model_drop_class;
		$list->connect();
		$result = $list->get_class_list( $sy_id_st,$student_id );
		print_tab( $_REQUEST["mode"] );
		if( $result!=false ) {
			print_class_table( $list );
		}
	} else if( $_REQUEST["mode"]==$modelist[3] ) {
		$list = new model_change_class;
		$list->connect();
			$result = $list->get_class_list( $sy_id_st,$student_id );
		print_tab( $_REQUEST["mode"] );
		if( $result!=false ) {
			print_class_table( $list );
		}
	}
	
	echo "</form>";

	echo " <input type=\"button\" value=\"Close\" onclick=\"window.close()\">";
?>

</body>

</html>
