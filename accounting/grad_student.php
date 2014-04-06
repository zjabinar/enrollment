<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/semester.inc");
	require_once("../include/enrol_student.inc");
	auth_check( $_SESSION["office"] );

$modelist = array(
	'Not graduating this sem',
	'Graduating this sem'
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
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Graduate Student </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$department_id = $_SESSION["department_id"];
	$str_schoolyear = lookup_schoolyear($_SESSION["sy_id"]);
	$str_department = get_department_from_department_id($department_id);

	print_title( $str_department, "Graduate Student", $str_schoolyear );

//	echo '<div class="prompt">For ';
//	echo lookup_schoolyear($_SESSION["sy_id"]) . '<br>';
//	echo get_department_from_department_id($_SESSION["department_id"]);
//	echo '</div>';

	echo "<form method=\"POST\">";

	echo '<div class="prompt">Select</div>';
	$course_array[0] = ' - all - ';
	$course_array += get_course_array(0,$department_id);
	$yearlevel_array = get_yearlevel_array();
	$yearlevel_array[0] = ' - all - ';
	echo '<nobr>Course' . mkhtml_select( "course_id",$course_array,$_REQUEST["course_id"] ) . '</nobr>';
	echo '<nobr>Year level' . mkhtml_select( "year_level",$yearlevel_array,$_REQUEST["year_level"] ) . '</nobr>';
	$section_array = get_section_array();
	$section_array[0] = ' - all - ';
	echo '<nobr>Section' . mkhtml_select( "section",$section_array,$_REQUEST["section"] ) . '</nobr>';
	echo '<input type="submit" name="search" value="search">';
	echo '<br>';

	$list = new model_enrol_student;

	$mode = $modelist[0];
	if( isset($_REQUEST["mode2"]) ) $mode = $_REQUEST["mode2"];
	if( isset($_REQUEST["mode"]) ) $mode = $_REQUEST["mode"];
	echo '<input type="hidden" name="mode2" value="' . $mode . '">';

	$result = false;
	if( isset($_REQUEST["graduate"]) ) {
		if( count($_REQUEST["ids"])==0 ) {
			echo '<div class="error">Select students!</div>';
		} else {
			$error = false;
			$student = new model_student;
			$student->connect( auth_get_writeable() );
			foreach( $_REQUEST["ids"] as $idx => $id ) {
				$student->get_by_id( $id );
				$dat = $student->get_fetch_assoc(0);
				$dat["graduate_sy"] = $sy_id;
				if( $student->update( $dat )==false ) {
					$error = true;
					break;
				}
			}
			if( $error==true ) {
				echo '<div class="error">' . $student->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Graduated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["cancel"]) ) {
		if( count($_REQUEST["ids"])==0 ) {
			echo '<div class="error">Select students!</div>';
		} else {
			$error = false;
			$student = new model_student;
			$student->connect( auth_get_writeable() );
			foreach( $_REQUEST["ids"] as $idx => $id ) {
				$student->get_by_id( $id );
				$dat = $student->get_fetch_assoc(0);
				$dat["graduate_sy"] = "0";
				if( $student->update( $dat )==false ) {
					$error = true;
					break;
				}
			}
			if( $error==true ) {
				echo '<div class="error">' . $student->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Cancelled successfully</div>';
			}
		}
	} else if( $mode==$modelist[0] ) {
		$result = false;
		if( isset($_REQUEST['course_id']) ) {
			$list->connect();
			$result = $list->search_not_graduates( $sy_id,$department_id,$_REQUEST['course_id'],$_REQUEST['year_level'],$_REQUEST['section'] );
		}
		if( $result != false ) {
			echo '<br>';
			print_tab( $mode );

			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>YearLevel</th><th>Section</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				printf( "<tr>" );
				printf( "<td><input type=\"checkbox\" name=\"ids[]\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$course_array[$dat["course_id"]],
					mkstr_neat( lookup_yearlevel($dat["year_level"]) ),
					mkstr_neat( lookup_section($dat["section"]) )
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			echo "<input type=\"submit\" name=\"graduate\" value=\"Move to graduating list\">";
			$list->close();
		}
	} else if( $mode==$modelist[1] ) {
		$result = false;
		if( isset($_REQUEST['course_id']) ) {
			$list->connect();
			$result = $list->search_graduates( $sy_id,$department_id,$_REQUEST['course_id'],$_REQUEST['year_level'],$_REQUEST['section'] );
		}
		if( $result != false ) {
			echo '<br>';
			print_tab( $mode );

			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>YearLevel</th><th>Section</th><th>Graduated Semester</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				printf( "<tr>" );
				printf( "<td><input type=\"checkbox\" name=\"ids[]\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					$course_array[$dat["course_id"]],
					mkstr_neat( lookup_yearlevel($dat["year_level"]) ),
					mkstr_neat( lookup_section($dat["section"]) ),
					lookup_schoolyear( $dat["graduate_sy"] )
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			echo "<input type=\"submit\" name=\"cancel\" value=\"Remove from graduating list\">";
			$list->close();
		}
	}

	echo "</form>";

	print_footer();

	if( isset($_REQUEST["graduate"]) || isset($_REQUEST["cancel"]) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		print_hidden( $_REQUEST,array('search','course_id','year_level','section') );
		echo '<input type="hidden" name="mode2" value="' . $mode . '">';
		echo " <input type=\"hidden\" name=\"sy_id\" value=\"${sy_id}\">";
		echo " <input type=\"hidden\" name=\"department_id\" value=\"${department_id}\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		echo "</form>";
	} else {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo " <input type=\"hidden\" name=\"sy_id\" value=\"${sy_id}\">";
		echo " <input type=\"hidden\" name=\"department_id\" value=\"${department_id}\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		echo "</form>";
	}
?>

</body>

</html>
