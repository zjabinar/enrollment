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
	require_once("../include/assessment.inc");
	require_once("../include/payment.inc");
	auth_check( $_SESSION["office"] );

define( 'MAX_COUNT', 50 );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> View balances </title>

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

	print_title( get_office_name($_SESSION["office"]), "View balances", $str_schoolyear );

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
	$officially = true;
	if( isset($_REQUEST["view"]) || isset($_REQUEST["prev"]) || isset($_REQUEST["next"]) ) {
		if( ! isset($_REQUEST["officially"]) ) $officially = false;
	}
	echo '<input type="checkbox" name="officially" value="1"' . ($officially ? " checked" : "") . '>Officially enrolled students only<br>';
	echo '<input type="submit" name="view" value="view">';
	echo '<br>';

	if( (isset($_REQUEST["view"])) || (isset($_REQUEST["prev"])) || (isset($_REQUEST["next"])) ) {
		$result = false;
		$list = new model_enrol_student;
		$list->connect();
		if( $officially ) {
			$result = $list->get_list_officially_enrolled($sy_id,$department_id,$_REQUEST["course_id"],$_REQUEST["year_level"],$_REQUEST["section"],0,0,true);
		} else {
			$result = $list->get_list_enrolled($sy_id,$department_id,$_REQUEST["course_id"],$_REQUEST["year_level"],$_REQUEST["section"]);
		}
		if( $result != false ) {
			$obj_payment = new model_payment;
			$obj_payment->connect();
			if( isset($_REQUEST["prev"]) ) {
				$start = $_REQUEST["start"] - MAX_COUNT;
				if( $start < 0 ) $start = 0;
			} else if( isset($_REQUEST["next"]) ) {
				$start = $_REQUEST["start"] + MAX_COUNT;
			}
			$count = min( MAX_COUNT, $list->get_numrows()-$start );
			echo '<div align="right">';
			echo ($start+1) . '-' . ($start+$count) . ' of ' . $list->get_numrows();
			echo '<input type="hidden" name="start" value="' . $start . '">';
			if( $start>0 ) echo '<input type="submit" name="prev" value="prev">';
			if( $start+$count < $list->get_numrows() ) echo '<input type="submit" name="next" value="next">';
			echo '</div>';
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Officially</th><th>Total</th><th>Balance</th></tr>";
			echo "</tr>";
			for( $i=$start; $i<$start+$count; $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					$course_cache[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
				}
				$assessment = calc_assessment( $sy_id,$dat["student_id"] );
				$total_assessment = 0;
				foreach( $assessment as $val ) $total_assessment += $val["amount"];
				$balance = $total_assessment - $obj_payment->get_payment_of($sy_id,$dat["student_id"]);
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td class=\"peso\">%s</td> <td class=\"peso\"" . ($balance<0 ? " style=\"color:red\"" : "") . ">%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$course_cache[$dat["course_id"]],
					mkstr_neat( lookup_yearlevel($dat["year_level"]) ),
					mkstr_neat( lookup_section($dat["section"]) ),
					mkstr_neat( mkstr_date_short($dat["date_officially"]) ),
					mkstr_peso( $total_assessment ),
					mkstr_peso( $balance )
				);
				printf( "</tr>" );
			}
			echo '</table>';
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo '<input type="button" value="View Details" onClick="' . "OnRadioOpen('mainform','student_id','view_studentinfo.php','','_blank')" . '">';
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
	echo '<input type="hidden" name="department_id" value="' . $department_id . '">';
	echo "</form>";
?>

</body>

</html>

