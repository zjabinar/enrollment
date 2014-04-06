<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc"); 
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/grade.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Grades </title>
</script>
</head>

<body>

<?php
	$sy_id = $_SESSION["sy_id"];
	$class_id = $_REQUEST["class_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$editable = true;	// only colleges can edit, confirm. only Grade Coordinator can unconfirm.
	}

	print_title( get_office_name($_SESSION["office"]), "Encode grades", $str_schoolyear );

	echo '<div style="border-style:solid">';
	$class_dat = print_classinfo_simple( $class_id );
	echo '</div>';
	echo '<br>';

	if( $class_id==0 ) {
		echo '<div class="error">Select a class</div>';
	} else if( isset($_REQUEST["update"]) || isset($_REQUEST['confirm']) ) {
		$result = true;
		$obj = new model_regist_class;
		$obj->connect( auth_get_writeable() );
		
		// get previous data
		$obj->get_student_list_official($sy_id,$class_id);
		for( $i=0; $i<$obj->get_numrows(); $i++ ) {
			$dat = $obj->get_fetch_assoc($i);
			$prev_dat[$dat['regist_id']] = $dat;
		}
		
		// update
		$count = 0;
		$result = $obj->begin_transaction();
		if( ($result) && count($_REQUEST["grades"])>0 ) {
			foreach( $_REQUEST["grades"] as $regist_id => $grade_ar ) {
				$ar = array();
				$ar['regist_id'] = $regist_id;
				if( isset($grade_ar[0]) ) {
					$grade_midterm = retrieve_grade($grade_ar[0]);
					if( $grade_midterm!=intval($prev_dat[$regist_id]['grade_midterm']) ) {
						$ar['grade_midterm'] = ($grade_midterm==null ? 'NULL' : $grade_midterm);
					}
				}
				if( isset($grade_ar[1]) ) {
					$grade_final = retrieve_grade($grade_ar[1]);
					if( $grade_final!=intval($prev_dat[$regist_id]['grade_final']) ) {
						$ar['grade_final'] = ($grade_final==null ? 'NULL' : $grade_final);
					}
				}
				if( isset($grade_ar[2]) ) {
					$grade_remark = $grade_ar[2];
					if( $grade_remark!=intval($prev_dat[$regist_id]['grade_remark']) ) {
						$ar['grade_remark'] = ($grade_remark==0 ? 'NULL' : $grade_remark);
					}
				}
				if( isset($ar['grade_midterm']) || isset($ar['grade_final']) || isset($ar['grade_remark']) || isset($ar['regist_flag']) ) {
					$result = $obj->update( $ar );
					if( ! $result ) break;
					$count++;
				}
			}
			if( $result ) {
				if( isset($_REQUEST['confirm']) ) {
					$result = $obj->confirm_grade( $class_id );
					$count++;
				}
			}
			if( $result ) $result = $obj->end_transaction();
			if( ! $result ) $obj->rollback();
		}
		if( $result ) {
			if( $count>0 ) {
				echo '<div class="message">Updated successfully</div>';
			} else {
				echo '<div class="warning">Nothing changed</div>';
			}
		} else {
			echo '<div class="error">' . $obj->get_errormsg() . '</div>';
		}
	} else if( isset($_REQUEST['unconfirm']) ) {
		$obj = new model_regist_class;
		$obj->connect( auth_get_writeable() );
		if( $obj->unconfirm_grade( $class_id ) ) {
			echo '<div class="message">Unconfirmed successfully</div>';
		} else {
			echo '<div class="error">' . $obj->get_errormsg() . '</div>';
		}
	} else {
		$graderemark_array[0] = '';
		$graderemark_array += get_graderemark_array();
	
		$list = new model_regist_class;
		$list->connect();
		$list->get_student_list_official($sy_id,$class_id);

		$count = 0;

		echo '<form method="POST" name="mainform">';
		echo "<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\">";
		echo "<tr>";
		echo "<th>StudentID</th><th>Name</th></th><th>Course</th><th>YearLevel</th><th>Gender</th><th>Mid Grade</th><th>Final Grade</th><th>Remark</th>";
		echo "</tr>";
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			if( ! isset($course_cache[$dat["course_id"]]) ) {
				list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
				$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
			}
			printf( "<tr>" );
			printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td>",
				mkstr_student_id($dat["student_id"]),
				mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
				mkstr_neat( $course_cache[$dat["course_id"]] ),
				lookup_yearlevel( $dat["year_level"] ),
				mkstr_neat($dat["gender"] )
			);
			if( $dat['regist_flag'] & REGISTFLAG_GRADECONFIRM ) {
				printf( "<td align=\"center\">%s</td> <td align=\"center\">%s</td> <td>%s</td>\n",
					mkstr_neat( mkstr_grade($dat["grade_midterm"]) ),
					mkstr_neat( mkstr_grade($dat["grade_final"]) ),
					mkstr_neat( $graderemark_array[$dat["grade_remark"]] )
				);
				if( ! $editable ) $count++;
			} else if( $editable ) {
				$midterm = '<input type="text" class="grade" size="5" name="grades[' . $dat["regist_id"] .'][0]" value="' . mkstr_grade($dat["grade_midterm"]) . '">';
				$final   = '<input type="text" class="grade" size="5" name="grades[' . $dat["regist_id"] .'][1]" value="' . mkstr_grade($dat["grade_final"]) . '">';
				$remark  = mkhtml_select('grades[' . $dat["regist_id"] . '][2]', $graderemark_array, $dat["grade_remark"] );
				printf( "<td align=\"center\">%s</td> <td align=\"center\">%s</td> <td>%s</td>\n",
					mkstr_neat( $midterm ), mkstr_neat( $final ), mkstr_neat( $remark )
				);
				$count ++;
			} else {
				echo '<td colspan="3" align="center">Unconfirmed</td>';
			}
			printf( "</tr>" );
		}
		echo "</table>";
		if( $editable ) {
			if( $count > 0 ) {
				echo '<input type="submit" name="update" value="Update">';
				echo '<input type="submit" name="confirm" value="Confirm" onClick="return window.confirm(\'After confirming you can not edit. OK?\')">';
			} else {
				echo 'Confirmed already. To make changes, have this unconfirmed by Grade Coordinator(Registrar).<br>';
			}
			echo '<br><input type="button" value="GradingSheet" onClick="' . "window.open('slip_gradesheet.php?sy_id=$sy_id&class_id=$class_id&per_page='+document.mainform.per_page.value,'_blank')" . '"><input type="text" name="per_page" value="0" size="2" style="text-align:right">students per page (0=all)';
		} else {
			if( $count > 0 ) {
				echo '<input type="submit" name="unconfirm" value="Unconfirm" onClick="return window.confirm(\'Unconfirm. OK?\')">';
				echo '<br><input type="button" value="GradingSheet" onClick="' . "window.open('slip_gradesheet.php?sy_id=$sy_id&class_id=$class_id&per_page='+document.mainform.per_page.value,'_blank')" . '"><input type="text" name="per_page" value="0" size="2" style="text-align:right">students per page (0=all)';
			}
		}
		print_hidden( array('class_id'=>$class_id) );
		echo '</form>';
	}

	print_footer();

	if( isset($_REQUEST["update"]) || isset($_REQUEST["confirm"]) || isset($_REQUEST["unconfirm"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		print_hidden( array('class_id'=>$class_id) );
		echo '</form>';
	} else {
		echo '<form action="encode_grade.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		print_hidden( array('teacher_id'=>$class_dat['teacher_id']) );
		echo '</form>';
	}
?>

</body>

</html>
