<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/class.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/semester.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/enrol_student.inc");
	require_once("../include/scholarship.inc");
	require_once("../include/guarantor.inc");
	require_once("../include/blockstudent.inc");
	auth_check( $_SESSION["office"] );

$modelist = array(
	'Not enrolled list',
	'Enrolled list'
);

function print_tab( $mode )
{
	global $modelist;
	foreach( $modelist as $n=>$str ) {
		if( $n==$mode ) {
			printf( '<div style="border-style:inset;float:left"><input type="button" value="%s"></div>', $str );
		} else {
			printf( '<div style="border-style:outset;float:left;font-weight:lighter"><input type="submit" name="mode_list_chg[%d]" value="%s"></div>', $n, $str );
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
<title> Enrol Student </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$str_schoolyear = lookup_schoolyear($_SESSION["sy_id"]);
	$str_department = get_department_from_department_id($_SESSION["department_id"]);

	print_title( "$str_department", "Enrol Student", $str_schoolyear );

//	echo '<div class="prompt">For ';
//	echo lookup_schoolyear($_SESSION["sy_id"]) . '<br>';
//	echo get_department_from_department_id($_SESSION["department_id"]);
//	echo '</div>';

	echo '<form method="POST" name="mainform">';

	$sy_id = $_SESSION["sy_id"];
	$department_id=$_SESSION["department_id"];

	$mode = $_REQUEST['mode_list'];
	if( isset($_REQUEST['mode_list_chg']) ) {
		foreach( $_REQUEST['mode_list_chg'] as $n=>$str ) $mode = $n;
	}
	print_hidden( array('mode_list'=>$mode) );

	$list = new model_enrol_student;

	if( isset($_REQUEST["enroll"]) ) {
		print_hidden( array('search_str'=>$_REQUEST['search_str']) );
		if( $_REQUEST["student_id"]=='' ) {
			echo '<div class="error">Student not selected!</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$blockdat = lookup_blockstudent($_REQUEST["student_id"]);
			if( $blockdat!=null ) {
				print_studentinfo_simple( $_REQUEST["student_id"] );
				print_blockstudent_info($blockdat);
			} else {
				echo '<div class="prompt">Enrol following student?</div>';
				print_studentinfo_simple( $_REQUEST["student_id"] );
				$obj = new model_student;
				$obj->connect();
				$obj->get_by_id( $_REQUEST["student_id"] );
				$dat = $obj->get_fetch_assoc(0);
				$year_level = get_next_year_level( $_REQUEST['student_id'],$sy_id,$dat['enter_sy'] );
				$section_array[0] = ' - none - ';
				$section_array += get_section_array();
				echo '<table border="0">';
				echo '<tr><td>Enrol date</td><td><input type="text" name="date" value="' . date("m/j/Y") . '">(MM/DD/YYYY)</td></tr>';
				echo '<tr><td>Year level</td><td>' . mkhtml_select( "year_level",get_yearlevel_array(),$year_level ) . '</td></tr>';
				echo '<tr><td>Section</td><td>' . mkhtml_select( "section",$section_array, 0 ) . '</td></tr>';
				echo '<tr><td colspan="2"><input type="checkbox" name="whole" value="1">Enrol for whole year</td></tr>';
				echo '<tr><td colspan="2"><input type="checkbox" name="outside" value="1">Enrol for outside campus</td></tr>';
				echo '</table>';
				echo '<input type="submit" name="exec" value="Enroll">';
				echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
				echo '<input type="hidden" name="feebase_sy" value="' . $dat["feebase_sy"] . '">';
				echo '<input type="hidden" name="course_id" value="' . $dat["course_id"] . '">';
				echo '<input type="hidden" name="enroll">';
			}
		} else if( isset($_REQUEST["whole"]) && get_semester_from_schoolyear($sy_id)!=1 ) {
			echo '<div class="error">Enrolling to whole semester should be done in 1st semester.</div>';
		} else {
			$list->connect( auth_get_writeable() );
			$date = retrieve_date($_REQUEST["date"]);
			$array = array(
				"sy_id" => $sy_id,
				"sy_id_end" => $sy_id,
				"student_id" => $_REQUEST["student_id"],
				"date" => $date,
				"feebase_sy" => $_REQUEST["feebase_sy"],
				"course_id" => $_REQUEST["course_id"],
				"year_level" => $_REQUEST["year_level"],
				"section" => $_REQUEST["section"],
			);
			if( isset($_REQUEST["whole"]) ) {
				$array["sy_id_end"] = get_schoolyear_end($sy_id);
			}
			if( isset($_REQUEST["outside"]) ) {
				$array["campus_flag"] = CAMPUSFLAG_OUTSIDE;
			}
			$result = $list->begin_transaction();
			if( $result ) $result = $list->add( $array );
			if( $result ) $result = $list->update_official_enrol( $sy_id,$_REQUEST["student_id"] );
			if( $result ) $result = $list->end_transaction();
			if( $result==false ) {
				$list->rollback();
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Enrolled successfully</div>';
				echo '<input type="button" value="Proceed to Class Enrollment" onClick="' . "window.open('enrol_class.php?student_id=${_REQUEST['student_id']}&mode_list=$mode&search_str=${_REQUEST['search_str']}','_self')\"><br>";
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		print_hidden( array('search_str'=>$_REQUEST['search_str']) );
		if( $_REQUEST["student_id"]=='' ) {
			echo '<div class="error">Student not selected!</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			print_studentinfo_simple( $_REQUEST["student_id"] );
			echo '<div class="prompt">Edit following data.</div>';
			$list->connect();
			$list->get_by_id( $sy_id,$_REQUEST["student_id"] );
			$dat = $list->get_fetch_assoc(0);
			if( $dat["sy_id"]!=$sy_id ) {
				echo '<div class="error">The student was enrolled in ' . lookup_schoolyear($dat["sy_id"]) . '. Edit in enrolled semester.</div>';
			} else {
				$section_array[0] = ' - none - ';
				$section_array += get_section_array();
				echo '<table border="0">';
				echo '<tr><td>Enrol date</td><td><input type="text" name="date" value="' . mkstr_date($dat["date"]) . '">(MM/DD/YYYY)</td></tr>';
				echo '<tr><td>Year level</td><td>' . mkhtml_select( "year_level",get_yearlevel_array(),$dat["year_level"] ) . '</td></tr>';
				echo '<tr><td>Section</td><td>' . mkhtml_select( "section",$section_array, $dat["section"] ) . '</td></tr>';
				echo '<tr><td colspan="2"><input type="checkbox" name="whole" value="1"' . ($dat["sy_id_end"]!=$dat["sy_id"] ? " checked" : "") . '>Enrol for whole year</td></tr>';
				echo '<tr><td colspan="2"><input type="checkbox" name="outside" value="1"' . ($dat["campus_flag"]&CAMPUSFLAG_OUTSIDE ? " checked" : "") . '>Enrol for outside campus</td></tr>';
				echo '</table>';
				echo '<input type="submit" name="exec" value="Update">';
				echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
				echo '<input type="hidden" name="enroll_id" value="' . $dat["enroll_id"] . '">';
				echo '<input type="hidden" name="edit">';
			}
		} else {
			$list->connect( auth_get_writeable() );
			$array = array(
				"enroll_id" => $_REQUEST["enroll_id"],
				"date" => retrieve_date($_REQUEST["date"]),
				"year_level" => $_REQUEST["year_level"],
				"section" => $_REQUEST["section"],
				"campus_flag" => (isset($_REQUEST["outside"]) ? CAMPUSFLAG_OUTSIDE : 'NULL'),
				"sy_id_end" => $sy_id,
			);
			if( isset($_REQUEST["whole"]) ) {
				$array["sy_id_end"] = get_schoolyear_end($sy_id);
			}
			$result = $list->begin_transaction();
			if( $result ) $result = $list->update( $array );
			if( $result ) $result = $list->update_official_enrol( $sy_id,$_REQUEST["student_id"] );
			if( $result ) $result = $list->end_transaction();
			if( $result==false ) {
				$list->rollback();
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["cancel"]) ) {
		print_hidden( array('search_str'=>$_REQUEST['search_str']) );
		if( $_REQUEST["student_id"]=='' ) {
			echo '<div class="error">Student not selected!</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$list->connect();
			$list->get_by_id( $sy_id,$_REQUEST["student_id"] );
			$dat = $list->get_fetch_assoc(0);
			if( $dat["sy_id"]!=$sy_id ) {
				echo '<div class="error">The student was enrolled in ' . lookup_schoolyear($dat["sy_id"]) . '. Edit in enrolled semester.</div>';
			} else if( check_officially_enrolled($sy_id,$_REQUEST["student_id"],true) ) {
				echo '<div class="error">The student is officially enrolled!</div>';
			} else {
				echo '<div class="prompt">Cancel Enrollment of following student?</div>';
				echo '<div class="warning">(All his enrolled classes will also be canceled!)</div>';
				print_studentinfo_simple( $_REQUEST["student_id"],$sy_id );
				echo '<input type="submit" name="exec" value="Cancel">';
				echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
				echo '<input type="hidden" name="cancel">';
			}
		} else {
			$error = false;
			$ar = array( 'sy_id'=>$sy_id, 'student_id'=>$_REQUEST["student_id"] );
			
			for( $i=0; ($i<5 && $error==false); $i++ ) {
				switch( $i ) {
					case 0:	$obj = new model_add_class;	break;
					case 1:	$obj = new model_drop_class;	break;
					case 2:	$obj = new model_change_class;	break;
					case 3:	$obj = new model_regist_class;	break;
					case 4:	$obj = new model_enrol_student;	break;
				}
				$obj->connect( auth_get_writeable() );
				if( $obj->del_cond( $ar )==false ) $error = true;
			}
			if( $error==true ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Cancelled successfully</div>';
			}
		}
	} else {
		echo '<div class="prompt">Enter Student ID or Last name to search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"" . $_REQUEST["search_str"] . "\">";
		echo " <input type=\"submit\" value=\"Search\"><br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';
		
		$result = false;
		if( $mode==0 ) {		// list of not enrolled student
			if( is_numeric($_REQUEST["search_str"]) ) {
				$list->connect();
				$result = $list->search_not_enrolled_by_id( $sy_id,$_REQUEST["search_str"],$_SESSION["department_id"] );
			} else if( isset($_REQUEST["search_str"]) ) {
				$list->connect();
				$result = $list->search_not_enrolled_by_lastname( $sy_id,$_REQUEST["search_str"],$_SESSION["department_id"] );
			}
			if( $result != false ) {
				print_tab( $mode );

				echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
				echo "<table border=\"1\">";
				echo "<tr>";
				echo "<th>&nbsp;</th>";
				echo "<th>StudentID</th><th>Name</th><th>Course</th>";
				echo "</tr>";
				for( $i=0; $i<$list->get_numrows(); $i++ ) {
					$dat = $list->get_fetch_assoc($i);
					if( ! isset($course_cache[$dat["course_id"]]) ) {
						list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
						$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
					}
					printf( "<tr>" );
					printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"%s></td>", $dat["student_id"], ($i==0 && $list->get_numrows()==1) ? " checked" : "" );
					printf( "<td>%s</td> <td>%s</td> <td>%s</td>\n",
						mkstr_student_id($dat["student_id"]),
						mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
						mkstr_neat( $course_cache[$dat["course_id"]] )
					);
					printf( "</tr>" );
				}
				echo "</table>";
				echo '</div>';
				echo '<input type="submit" name="enroll" value="Enroll">';
				$list->close();
			}
		} else if( $mode==1 ) {		// list of enrolled students
			$list->connect();
			if( is_numeric($_REQUEST["search_str"]) ) {
				$result = $list->search_enrolled_by_id( $sy_id,$_REQUEST["search_str"],$_SESSION["department_id"] );
			} else if( isset($_REQUEST["search_str"]) ) {
				$result = $list->search_enrolled_by_lastname( $sy_id,$_REQUEST["search_str"],$_SESSION["department_id"] );
			}
			if( $result != false ) {
				print_tab( $mode );

				echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
				echo "<table border=\"1\">";
				echo "<tr>";
				echo "<th>&nbsp;</th>";
				echo "<th>StudentID</th><th>Name</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Encoded</th><th>Official</th><th>Dropped</th>";
				echo "</tr>";
				for( $i=0; $i<$list->get_numrows(); $i++ ) {
					$dat = $list->get_fetch_assoc($i);
					if( ! isset($course_cache[$dat["course_id"]]) ) {
						list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
						$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
					}
					printf( "<tr>" );
					printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"%s></td>", $dat["student_id"], ($i==0 && $list->get_numrows()==1) ? " checked" : "" );
					printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
						mkstr_student_id($dat["student_id"]),
						mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
						mkstr_neat( $course_cache[$dat["course_id"]] ),
						lookup_yearlevel( $dat["year_level"] ),
						mkstr_neat( lookup_section( $dat["section"] ) ),
						mkstr_date( $dat["date"] ),
						mkstr_neat( mkstr_date( $dat["date_officially"] ) ),
						mkstr_neat( mkstr_date( $dat["date_dropped"] ) )
					);
					printf( "</tr>" );
				}
				echo "</table>";
				echo '</div>';
				echo '<input type="submit" name="cancel" value="Cancel Enroll">';
				echo '<input type="submit" name="edit" value="Edit Date/YearLevel/Section">';
				echo '<br><input type="button" value="Edit Class Enrollment" onClick="' . "OnRadioOpen('mainform','student_id','enrol_class.php','mode_list=$mode&search_str=${_REQUEST['search_str']}','_self')" . '">';
				$list->close();
			}
		}
	}

	echo "</form>";

	print_footer();

	if( isset($_REQUEST["enroll"]) || isset($_REQUEST["cancel"]) || isset($_REQUEST["edit"]) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		echo " <input type=\"hidden\" name=\"sy_id\" value=\"${sy_id}\">";
		echo " <input type=\"hidden\" name=\"department_id\" value=\"${department_id}\">";
		print_hidden( $_REQUEST,array('mode_list','search_str') );
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
