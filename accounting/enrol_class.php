<?php
session_start();
require_once("../include/auth.inc");
require_once("../include/util.inc");
require_once("../include/semester.inc");
require_once("../include/course.inc");
require_once("../include/student.inc");
require_once("../include/teacher.inc");
require_once("../include/class.inc");
require_once("../include/classschedule.inc");
require_once("../include/enrol_student.inc");
require_once("../include/enrol_class.inc");
require_once("../include/syinfo.inc");
auth_check( $_SESSION["office"] );

function print_class_table( $list )
{
	// check if regist_flag exists or not
	$regist = 0;
	if( ($list->get_numrows()>0) &&
		(array_key_exists("regist_flag",$list->get_fetch_assoc(0))) ) $regist = 1;

	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
	echo "<table border=\"1\">";
	echo "<tr>";
	echo "<th>&nbsp;</th>";
	echo "<th>Code</th><th>Subject</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Teacher</th><th>Units</th></th><th>Dep.</th><th>StudentList</th>";
	if( $regist ) echo "<th>R/NR</th>";
	echo "<th>Schedule</th>";
	echo "</tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		if( ! isset($cache_course[$dat["course_id"]]) ) {
			$cache_course[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
		}
		$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
		$schedule_str = '';
		for( $j=0; $j<count($schedule_ar); $j++ ) {
			$schedule_str .= $schedule_ar[$j][1] . ' ' . $schedule_ar[$j][2] . ' ' . $schedule_ar[$j][0] . '<br>';
		}
		printf( "<tr>" );
		printf( "<td><input type=\"checkbox\" name=\"class_ids[]\" value=\"%s\"></td>", $dat["class_id"] );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
			mkstr_neat($dat["subject_code"]),
			mkstr_neat($dat["subject"]),
			mkstr_neat( $dat['major_ignore'] ? strtok($cache_course[$dat["course_id"]],' ') : $cache_course[$dat["course_id"]] ),
			lookup_yearlevel($dat["year_level"]),
			mkstr_neat( lookup_section_flag($dat["section_flag"]) ),
			mkstr_neat( lookup_teacher_name( $dat["teacher_id"] ) ),
			mkstr_neat($dat["unit"]),
			mkstr_neat($dat["department_name"]),
			'<input type="button" value="check" onClick="' . "window.open('list_classstudent.php?class_id=" . $dat["class_id"]. "','_blank')" . '">'
		);
		if( $regist ) echo ($dat["regist_flag"] & REGISTFLAG_REGULAR) ? "<td>R</td>" : "<td>NR</td>";
		echo '<td><small>' . mkstr_neat($schedule_str) . '</small></td>';
		printf( "</tr>" );
	}
	echo "</table>";
	echo '</div>';
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Enroll Class </title>
<style>
th {margin:0; padding:0}
td {margin:0; padding:0}
</style>
</head>

<body onLoad="optionOnLoad(320)" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$department_id = $_SESSION["department_id"];

	$str_schoolyear = lookup_schoolyear($sy_id);
	$str_department = get_department_from_department_id($department_id);
	print_title( $str_department, "Enrol class", $str_schoolyear );

//	echo lookup_schoolyear( $sy_id ) . '<br>';
//	echo get_department_from_department_id( $department_id );

	echo "<form method=\"POST\" name=\"mainform\">";
	if( ! isset($_REQUEST["mode"]) ) $_REQUEST["mode"] = $modelist[0];
	echo '<input type="hidden" name="mode" value="' . $_REQUEST["mode"] . '">';

	if( !isset($_REQUEST["enroll_add"]) && !isset($_REQUEST["enroll_del"]) && !isset($_REQUEST["chg_add"]) && !isset($_REQUEST["chg_drop"]) && !isset($_REQUEST["chg_chg"]) &&
		!isset($_REQUEST["del_add"]) && !isset($_REQUEST["del_drop"]) && !isset($_REQUEST["del_chg"]) ) {
		$student_id = $_REQUEST["student_id"];
		echo '<div style="border-style:solid;border-width:thin">';
		$enrol_student_dat = print_studentinfo_simple( $student_id,$sy_id,true );
		echo '</div><br>';
		echo '<input type="hidden" name="student_id" value="' . $student_id . '">';

		$enrol_period = true;
		$obj_syinfo = new model_syinfo;
		$obj_syinfo->connect();
		if( $obj_syinfo->get_lastday_of_changing( $sy_id,$department_id ) ) {
			$syinfo = $obj_syinfo->get_fetch_assoc(0);
			if( strtotime($syinfo['lastday_of_changing']) < strtotime(date('Y-m-j')) ) {
				$enrol_period = false;
			}
		}

		if( $enrol_student_dat["sy_id"]!=$sy_id ) {
			echo '<div class="error">The student was enrolled in ' . lookup_schoolyear($enrol_student_dat["sy_id"]) . '. Edit in the enrolled semester.</div>';
		} else if( $_REQUEST["mode"]==$modelist[0] ) {
			$list = new model_regist_class;
			$list->connect();
			$result = $list->get_class_list( $sy_id,$student_id );
			print_tab( $_REQUEST["mode"] );
			if( $result!=false ) {
				print_class_table( $list );
				if( $enrol_period ) {
					echo "Enrollment(no charge)";
					echo "<input type=\"submit\" name=\"enroll_add\" value=\"Add\">";
					echo "<input type=\"submit\" name=\"enroll_del\" value=\"Delete\">";
					echo "<br>";
					echo "Changing(charge)";
					echo "<input type=\"submit\" name=\"chg_add\" value=\"Add\">";
					echo "<input type=\"submit\" name=\"chg_drop\" value=\"Drop\">";
					echo "<input type=\"submit\" name=\"chg_chg\" value=\"Change\">";
					echo "(Do not use these during Enrollment period!)";
				} else {
					echo "Changing period is finished";
				}
				echo "<br>";
				echo '<br><input type="button" value="Enrollment Form" onClick="' . "window.open(GetRadioValue('mainform','formtype')+'?sy_id=$sy_id&student_id=$student_id&cp='+document.mainform.copy.value)" . '">';
				echo ' <input type="radio" name="formtype" value="slip_enrolconfirm2.php" checked>New';
				echo ' <input type="radio" name="formtype" value="slip_enrolconfirm.php">Old';
				//echo ' <input type="radio" name="formtype" value="slip_enrolconfirm2_edited.php">sample';
				echo ' <input type="text" name="copy" value="1" size="1" style="text-align:right">copies<br>';
				echo '<input type="button" value="Assessement Slip" onClick="' . "window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')" . '">';
			}
		} else if( $_REQUEST["mode"]==$modelist[1] ) {
			$list = new model_add_class;
			$list->connect();
			$result = $list->get_class_list( $sy_id,$student_id );
			print_tab( $_REQUEST["mode"] );
			if( $result!=false ) {
				print_class_table( $list );
				if( $enrol_period ) {
					if( $list->get_numrows()>0 ) echo "<input type=\"submit\" name=\"del_add\" value=\"Delete\">";
				} else {
					echo "Changing period is finished";
				}
			}
		} else if( $_REQUEST["mode"]==$modelist[2] ) {
			$list = new model_drop_class;
			$list->connect();
			$result = $list->get_class_list( $sy_id,$student_id );
			print_tab( $_REQUEST["mode"] );
			if( $result!=false ) {
				print_class_table( $list );
				if( $enrol_period ) {
					if( $list->get_numrows()>0 ) echo "<input type=\"submit\" name=\"del_drop\" value=\"Delete\">";
				} else {
					echo "Changing period is finished";
				}
			}
		} else if( $_REQUEST["mode"]==$modelist[3] ) {
			$list = new model_change_class;
			$list->connect();
			$result = $list->get_class_list( $sy_id,$student_id );
			print_tab( $_REQUEST["mode"] );
			if( $result!=false ) {
				print_class_table( $list );
				if( $enrol_period ) {
					if( $list->get_numrows()>0 ) echo "<input type=\"submit\" name=\"del_chg\" value=\"Delete\">";
				} else {
					echo "Changing period is finished";
				}
			}
		}
	} else {
		$student_id = $_REQUEST["student_id"];
		echo '<div style="border-style:solid;border-width:thin">';
		print_studentinfo_simple( $student_id,$sy_id,true );
		echo '</div><br>';
		echo '<input type="hidden" name="student_id" value="' . $student_id . '">';

		$list_regist = new model_regist_class;
		$list_regist->connect( auth_get_writeable() );

		//$error = false;
		if( isset($_REQUEST["go_reg"]) ) $go = "reg";
		if( isset($_REQUEST["go_nreg"]) ) $go = "nreg";

		if( isset($_REQUEST["enroll_add"]) ) {
			echo "<input type=\"hidden\" name=\"enroll_add\">";
			if( isset($go) ) {
				if( count($_REQUEST["class_ids"])==0 ) {
					$error = 'Select classes';
				} else {
					foreach( $_REQUEST["class_ids"] as $idx => $id ) {
						$array = array( "sy_id"=>$sy_id, "student_id"=>$student_id, "class_id"=>$id, "date"=>date("Y-m-j"), "regist_flag"=>($go=="reg"?REGISTFLAG_REGULAR:'0') );
						if( $list_regist->check_and_add( $array,$_REQUEST['ignore'] )==false ) {
							$error = $list_regist->get_errormsg();
							break;
						}
					}
					if( ! isset($error) ) echo '<div class="message">Added successfully</div>';
				}
			}
		} else if( isset($_REQUEST["enroll_del"]) ) {
			echo "<input type=\"hidden\" name=\"enroll_del\">";
			if( count($_REQUEST["class_ids"])==0 ) {
				$error = 'Select classes';
			} else {
				foreach( $_REQUEST["class_ids"] as $idx => $id ) {
					if( $list_regist->del( $sy_id,$student_id,$id )==false ) {
						$error = $list_regist->get_errormsg();
						break;
					}
				}
				if( ! isset($error) ) echo '<div class="message">Deleted successfully</div>';
			}
		} else if( isset($_REQUEST["chg_add"]) ) {
			echo "<input type=\"hidden\" name=\"chg_add\">";
			if( isset($go) ) {
				if( count($_REQUEST["class_ids"])==0 ) {
					$error = 'Select classes';
				} else {
					$list_add = new model_add_class;
					$list_add->connect( auth_get_writeable() );
					foreach( $_REQUEST["class_ids"] as $idx => $id ) {
						$array = array( "sy_id"=>$sy_id, "student_id"=>$student_id, "class_id"=>$id, "date"=>date("Y-m-j"), "regist_flag"=>($go=="reg"?REGISTFLAG_REGULAR:'0') );
						if( $list_add->add( $array,$_REQUEST['ignore'] )==false ) {
							$error = $list_add->get_errormsg();
							break;
						}
					}
					if( ! isset($error) ) echo '<div class="message">Added successfully</div>';
				}
			}
		} else if( isset($_REQUEST["chg_drop"]) ) {
			echo "<input type=\"hidden\" name=\"enroll_drop\">";
			if( count($_REQUEST["class_ids"])==0 ) {
				$error = 'Select classes';
			} else {
				$list_drop = new model_drop_class;
				$list_drop->connect( auth_get_writeable() );
				foreach( $_REQUEST["class_ids"] as $idx => $id ) {
					$array = array( "sy_id"=>$sy_id, "student_id"=>$student_id, "class_id"=>$id, "date"=>date("Y-m-j") );
					if( $list_drop->add( $array )==false ) {
						$error = $list_drop->get_errormsg();
						break;
					}
				}
				if( ! isset($error) ) echo '<div class="message">Dropped successfully</div>';
			}
		} else if( isset($_REQUEST["chg_chg"]) ) {
			echo "<input type=\"hidden\" name=\"chg_chg\">";
			$old_class_id = $_REQUEST["class_ids"][0];
			$new_class_id = $_REQUEST["new_class_id"];
			if( ! isset($go) ) {
				if( count($_REQUEST["class_ids"])!=1 ) {
					$error = 'Select one class to be changed';
				} else {
					printf( "Change to :<br>" );
					echo "<input type=\"hidden\" name=\"class_ids[]\" value=\"${old_class_id}\">";
					echo "<input type=\"hidden\" name=\"chg_chg\">";
				}
			} else {
				if( ! isset($_REQUEST["new_class_id"]) ) {
					$error = 'Select new class';
				} else {
					$list_change = new model_change_class;
					$list_change->connect( auth_get_writeable() );
					$array = array(
						"sy_id" => $sy_id,
						"student_id" => $student_id,
						"class_id" => $old_class_id,
						"new_class_id" => $new_class_id,
						"date" => date("Y-m-j"),
						"regist_flag" => ($go=="reg"?REGISTFLAG_REGULAR:'0')
					);
					if( $list_change->add( $array,$_REQUEST['ignore'] )==false ) {
						$error = $list_change->get_errormsg();
					} else {
						echo '<div class="message">Changed successfully</div>';
					}
				}
			}
		} else if( isset($_REQUEST["del_add"]) ) {
			echo "<input type=\"hidden\" name=\"del_add\">";
			if( count($_REQUEST["class_ids"])==0 ) {
				$error = 'Select classes';
			} else {
				$list_add = new model_add_class;
				$list_add->connect( auth_get_writeable() );
				foreach( $_REQUEST["class_ids"] as $idx => $id ) {
					if( $list_add->del( $sy_id,$student_id,$id )==false ) {
						$error = $list_add->get_errormsg();
						break;
					}
				}
				if( ! isset($error) ) echo '<div class="message">Deleted successfully</div>';
			}
		} else if( isset($_REQUEST["del_drop"]) ) {
			echo "<input type=\"hidden\" name=\"del_drop\">";
			if( count($_REQUEST["class_ids"])==0 ) {
				$error = 'Select classes';
			} else {
				$list_drop = new model_drop_class;
				$list_drop->connect( auth_get_writeable() );
				foreach( $_REQUEST["class_ids"] as $idx => $id ) {
					if( $list_drop->del( $sy_id,$student_id,$id )==false ) {
						$error = $list_drop->get_errormsg();
						break;
					}
				}
				if( ! isset($error) ) echo '<div class="message">Deleted successfully</div>';
			}
		} else if( isset($_REQUEST["del_chg"]) ) {
			echo "<input type=\"hidden\" name=\"del_chg\">";
			if( count($_REQUEST["class_ids"])==0 ) {
				$error = 'Select classes';
			} else {
				$list_change = new model_change_class;
				$list_change->connect( auth_get_writeable() );
				foreach( $_REQUEST["class_ids"] as $idx => $id ) {
					if( $list_change->del( $sy_id,$student_id,$id )==false ) {
						$error = $list_change->get_errormsg();
						break;
					}
				}
				if( ! isset($error) ) echo '<div class="message">Deleted successfully</div>';
			}
		}
		if( isset($error) ) {
			echo '<div class="error">' . $error . '</div>';
		}

		if( (isset($_REQUEST["enroll_add"]) || isset($_REQUEST["chg_add"]) || isset($_REQUEST["chg_chg"]))
			&& !isset($go) && !isset($error)  ) {
			echo '<div class="prompt">Search and select subjects to add</div>';
			
			$obj_stdt = new model_enrol_student;
			$obj_stdt->connect();
			$obj_stdt->get_info($sy_id,$student_id);
			$stdt = $obj_stdt->get_fetch_assoc(0);
			
			$des_department_id = isset($_REQUEST["des_department_id"]) ? $_REQUEST["des_department_id"] : $department_id;
			$des_course_id = isset($_REQUEST["des_course_id"]) ? $_REQUEST["des_course_id"] : $stdt["course_id"];
			$des_yearlevel = isset($_REQUEST["des_yearlevel"]) ? $_REQUEST["des_yearlevel"] : $stdt["year_level"];
			$des_section = intval( isset($_REQUEST["des_section"]) ? $_REQUEST["des_section"] : $stdt["section"] );
			//$school_id = get_school_id_from_course_id($stdt['course_id']);
			$school_id = 0;
			$da = get_short_department_array($school_id);
			$ca[0] = '- all -';
			$ca += get_course_array( $school_id,$des_department_id );
			$ya[0] = '- all -';
			$ya += get_yearlevel_array();
			$sa[0] = '- all -';
			$sa += get_section_array();
			echo mkhtml_select( "des_department_id",$da,$des_department_id );
			echo mkhtml_select( "des_course_id",$ca,$des_course_id );
			echo mkhtml_select( "des_yearlevel",$ya,$des_yearlevel );
			echo mkhtml_select( "des_section",$sa,$des_section );
			echo "<input type=\"submit\" value=\"search\">";

			$result = false;
			$list_class = new model_class;
			$list_class->connect();
			$result = $list_class->get_list($sy_id,$des_department_id,$des_course_id,$des_yearlevel,$des_section);
			if( $result != false ) {
				$obj_schedule = new model_classschedule;
				$obj_schedule->connect();
				$candidates = 0;
				echo '<div' . ($list_class->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
				echo "<table border=\"1\">";
				echo "<tr>";
				echo "<th>&nbsp;</th>";
				echo "<th>Code</th><th>Subject</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Teacher</th><th>Units</th><th>StudentList</th><th>Schedule</th>";
				echo "</tr>";
				for( $i=0; $i<$list_class->get_numrows(); $i++ ) {
					$dat = $list_class->get_fetch_assoc($i);
					$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
					$schedule_str = '';
					for( $j=0; $j<count($schedule_ar); $j++ ) {
						$schedule_str .= $schedule_ar[$j][1] . ' ' . $schedule_ar[$j][2] . ' ' . $schedule_ar[$j][0] . '<br>';
					}
					printf( "<tr>" );
					$reg = $list_regist->check_regist($sy_id,$student_id,$dat["class_id"]);
					if( $reg ) {
						//printf( "<td>enrolled(%s)</td>", $reg );
						echo "<td>$reg</td>";
					} else if( isset($_REQUEST["chg_chg"]) ) {
						printf( "<td><input type=\"radio\" name=\"new_class_id\" value=\"%s\"></td>", $dat["class_id"] );
					} else {
						printf( "<td><input type=\"checkbox\" name=\"class_ids[]\" value=\"%s\"></td>", $dat["class_id"] );
						$candidates++;
					}
					$count_reg = $list_regist->get_student_count_reg($sy_id,$dat["class_id"]);
					$count_nreg = $list_regist->get_student_count_nreg($sy_id,$dat["class_id"]);
					printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%d/%d,%d/%d %s</td> <td><small>%s</small></td>\n",
						mkstr_neat($dat["subject_code"]),
						mkstr_neat($dat["subject"]),
						mkstr_neat( $dat['major_ignore'] ? strtok($ca[$dat["course_id"]],' ') : $ca[$dat["course_id"]] ),
						lookup_yearlevel($dat["year_level"]),
						mkstr_neat( lookup_section_flag($dat["section_flag"]) ),
						mkstr_neat( lookup_teacher_name($dat["teacher_id"]) ),
						mkstr_neat($dat["unit"]),
						$count_reg,$dat["max_student_reg"],
						$count_nreg,$dat["max_student_nreg"],
						'<input type="button" value="check" onClick="' . "window.open('list_classstudent.php?class_id=" . $dat["class_id"]. "','_blank')" . '">',
						mkstr_neat($schedule_str)
					);
					printf( "</tr>" );
				}
				echo "</table>";
				echo '</div>';
				echo '* R=enrolled as regular student, NR=enrolled as irregular student.<br>';
				
				// JavaScript for Mark all button
				if( $candidates>0 ) {
					echo '<script type="text/javascript">';
					echo 'function onMarkAll() {';
					echo '  if( document.mainform["class_ids\[\]"].length ) {';
					echo '    for( i=0; i<document.mainform["class_ids\[\]"].length; i++ ) {';
					echo '      if( ! document.mainform["class_ids\[\]"][i].checked ) {';
					echo '        document.mainform["class_ids\[\]"][i].click();';
					echo '      }';
					echo '    }';
					echo '  } else {';
					echo '    if( ! document.mainform["class_ids\[\]"].checked ) {';
					echo '      document.mainform["class_ids\[\]"].click();';
					echo '    }';
					echo '  }';
					echo '}';
					echo '</script>';
					echo '<input type="button" value="Mark All" onClick="onMarkAll()"><br>';
				}
				
				echo "<input type=\"submit\" name=\"go_reg\" value=\"Add as a regular student\"><br>";
				echo "<input type=\"submit\" name=\"go_nreg\" value=\"Add as an irregular student\"><br>";
				echo "<input type=\"checkbox\" name=\"ignore\" value=\"1\">Ignore conflicts of schedule";
			}
			$list_class->close();
		}
		$list_regist->close();

	}

	echo "</form>";

	print_footer();

	if( !isset($_REQUEST["enroll_add"]) && !isset($_REQUEST["enroll_del"]) && !isset($_REQUEST["chg_add"]) && !isset($_REQUEST["chg_drop"]) && !isset($_REQUEST["chg_chg"]) &&
		!isset($_REQUEST["del_add"]) && !isset($_REQUEST["del_drop"]) && !isset($_REQUEST["del_chg"]) ) {
		echo '<form action="enrol_student.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		print_hidden( $_REQUEST,array('mode_list','search_str') );
		echo '</form>';
	} else if( isset($go) && (isset($_REQUEST["enroll_add"]) || isset($_REQUEST["chg_add"])) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
		echo '<input type="hidden" name="enrol_class">';
		if( isset($_REQUEST["enroll_add"]) ) echo '<input type="hidden" name="enroll_add">';
		if( isset($_REQUEST["chg_add"]) ) echo '<input type="hidden" name="chg_add">';
		print_hidden( $_REQUEST, array('des_department_id','des_course_id','des_yearlevel','des_section') );
		echo '<input type="hidden" name="mode" value="' . $_REQUEST["mode"] . '">';
		echo '</form>';
	} else {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
		echo '<input type="hidden" name="mode" value="' . $_REQUEST["mode"] . '">';
		echo '</form>';
	}
?>

</body>

</html>
