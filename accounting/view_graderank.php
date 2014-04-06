<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/section.inc");
	require_once("../include/student.inc");
	require_once("../include/enrol_student.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> View officially enroled student </title>

<?php
	if( ! isset($_SESSION["department_id"]) ) {
?>
<script type="text/javascript">
<!--
function OnSelectDepartment( form_name,select1,select2 ) {
<?php
	$da = get_short_department_array();
	$ca = get_course_array(0,0,true);
	echo "\tcourse_array = new Array();\n";
	foreach( $da as $department_id=>$department_name ) {
		echo "\tcourse_array[$department_id] = new Array();\n";
		echo "\tcourse_array[$department_id][0] = '- all -';\n";
		foreach( $ca as $course_id=>$course_dat ) {
			if( $course_dat["department_id"]==$department_id ) {
				echo "\tcourse_array[$department_id][$course_id] = \"" . $course_dat["short_name"] . " " . $course_dat["major"] . " " . $course_dat["minor"] . "\";\n";
			}
		}
	}
?>
	department_id = document.forms[form_name].elements[select1].value;
	options = document.forms[form_name].elements[select2];
	options.length = 0;
	var counter = 0;
	for( i in course_array[department_id] ) {
		options.options[counter] = new Option(course_array[department_id][i],i);
		counter++;
	}
}
// -->
</script>
<?php
	}
?>

</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$department_id = $_SESSION["department_id"];
	} else {
		$department_id = $_REQUEST["department_id"];
	}

	print_title( get_office_name($_SESSION["office"]), "View students", $str_schoolyear );

	echo '<form method="POST" name="mainform">';

	if( ! isset($_SESSION["department_id"]) ) {
		$department_array = get_short_department_array();
		if( $department_id==0 ) $department_id = key($department_array);
		echo '<nobr>Department' . mkhtml_select( "department_id",$department_array,$department_id,"onChange=\"OnSelectDepartment('mainform','department_id','course_id');\"" ) . '</nobr>';
	}
	$course_array[0] = ' - all - ';
	if( $department_id>0 ) $course_array += get_course_array(0,$department_id);
	$yearlevel_array = get_yearlevel_array();
	$yearlevel_array[0] = ' - all - ';
	echo '<nobr>Course' . mkhtml_select( "course_id",$course_array,$_REQUEST["course_id"] ) . '</nobr>';
	echo '<nobr>Year level' . mkhtml_select( "year_level",$yearlevel_array,$_REQUEST["year_level"] ) . '</nobr>';
	$section_array = get_section_array();
	$section_array[0] = ' - all - ';
	echo '<nobr>Section' . mkhtml_select( "section",$section_array,$_REQUEST["section"] ) . '</nobr>';
	echo '<br>';
	echo '<input type="submit" name="view" value="view">';
	echo '<br>';

	if( isset($_REQUEST["view"]) ) {
		$result = false;
		$list = new model_enrol_student;
		$list->connect();
		$result = $list->get_graderanklist($sy_id,$department_id,$_REQUEST["course_id"],$_REQUEST["year_level"],$_REQUEST["section"]);
		# $result = $list->get_list_officially_enrolled($sy_id,$department_id,$_REQUEST["course_id"],$_REQUEST["year_level"],$_REQUEST["section"],retrieve_date($_REQUEST["date_from"]),retrieve_date($_REQUEST["date_to"]));
		if( $result != false ) {
			echo 'Total ' . $list->get_numrows() . ' students';
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Total units</th><th>Average Grade</th></tr>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					$course_cache[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
				}
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td> <td class=\"grade\">%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$course_cache[$dat["course_id"]],
					mkstr_neat( lookup_yearlevel($dat["year_level"]) ),
					mkstr_neat( lookup_section($dat["section"]) ),
					$dat["total_unit"],
					mkstr_gradeaverage($dat["average"])
				);
				printf( "</tr>" );
			}
			echo '</table>';
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo '<input type="button" value="View Details" onClick="' . "OnRadioOpen('mainform','student_id','view_studentinfo.php','','_blank')" . '">';
				echo '<input type="button" value="View Subject List" onClick="' . "OnRadioOpen('mainform','student_id','list_subject.php','sy_id=$sy_id','_blank')" . '">';
				echo '<input type="button" value="View Assessement Slip" onClick="' . "OnRadioOpen('mainform','student_id','assessment_slip.php','sy_id=$sy_id','_blank')" . '">';
				echo '<input type="button" value="View Grades" onClick="' . "OnRadioOpen('mainform','student_id','view_studentgrade.php','sy_id=$sy_id','_blank')" . '">';
			}
			$list->close();
		}
	}
	echo "</form>";

	print_footer();

	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"submit\" value=\"Go back\">";
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '<input type="hidden" name="department_id" value="' . $department_id . '">';
	echo "</form>";
?>

</body>

</html>

