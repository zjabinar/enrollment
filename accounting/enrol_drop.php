<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/semester.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/enrol_student.inc");
	auth_check( $_SESSION["office"] );

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Drop Student Officially</title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$str_schoolyear = lookup_schoolyear($_SESSION["sy_id"]);

	print_title( get_office_name($_SESSION["office"]), "Drop Student Officially", $str_schoolyear );

	echo '<form method="POST" name="mainform">';

	$sy_id = $_SESSION["sy_id"];

	$list = new model_enrol_student;

	if( isset($_REQUEST["drop"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		if( $_REQUEST["student_id"]=='' ) {
			echo '<div class="error">Student not selected!</div>';
		} else {
			$list->connect( auth_get_writeable() );
			$list->get_by_id( $sy_id,$_REQUEST["student_id"] );
			if( $list->get_numrows()!=1 ) {
				echo '<div class="error">Bad student_id!</div>';
			} else {
				$dat = $list->get_fetch_assoc(0);
				if( isset($dat["date_dropped"]) ) {
					echo '<div class="error">Student is already dropped!</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					echo '<div class="prompt">Officially drop following student?</div>';
					echo '<div class="warning">(All the classes will also be dropped!)</div>';
					print_studentinfo_simple( $_REQUEST["student_id"] );
					echo 'Refund rate <input type="text" name="refund_rate" align="right">%<br>';
					echo '<input type="submit" name="exec" value="Officially drop">';
					echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
					echo '<input type="hidden" name="drop">';
				} else {
					if( $list->begin_transaction()==false ) {
						$err_msg = $list->get_errormsg();
					} else {
						$obj_drop = new model_drop_class;
						$obj_drop->connect( auth_get_writeable() );
						if( $obj_drop->drop_all( $sy_id,$_REQUEST["student_id"] )==false ) {
							$err_msg = $obj_drop->get_errormsg();
						} else {
							$dat["date_dropped"] = date("Y-m-d H:i:s");
							$dat["refund_rate"] = $_REQUEST["refund_rate"];
							if( $list->update( $dat )==false ) {
								$err_msg = $list->get_errormsg();
							} else {
								$list->end_transaction();
							}
						}
					}
					if( isset($err_msg) ) {
						echo '<div class="error">' . $err_msg . '</div>';
						$list->rollback();
					} else {
						echo '<div class="message">Dropped successfully</div>';
					}
				}
			}
		}
	} else if( isset($_REQUEST["cancel"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		if( $_REQUEST["student_id"]=='' ) {
			echo '<div class="error">Student not selected!</div>';
		} else {
			$list->connect( auth_get_writeable() );
			$list->get_by_id( $sy_id,$_REQUEST["student_id"] );
			if( $list->get_numrows()!=1 ) {
				echo '<div class="error">Bad student_id!</div>';
			} else {
				$dat = $list->get_fetch_assoc(0);
				if( ! isset($dat["date_dropped"]) ) {
					echo '<div class="error">Student is not dropped!</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					echo '<div class="prompt">Cancel following student?</div>';
					print_studentinfo_simple( $_REQUEST["student_id"] );
					echo '<input type="submit" name="exec" value="Cancel">';
					echo '<input type="hidden" name="student_id" value="' . $_REQUEST["student_id"] . '">';
					echo '<input type="hidden" name="cancel">';
				} else {
					$dat["date_dropped"] = 'NULL';
					$dat["refund_rate"] = 'NULL';
					if( $list->update( $dat )==false ) {
						echo '<div class="error">' . $list->get_errormsg() . '</div>';
					} else {
						echo '<div class="message">Cancelled successfully</div>';
						echo '<div class="warning">Classes will remain all dropped!</div>';
					}
				}
			}
		}
	} else {
		echo '<div class="prompt">Enter Student ID or Last name to search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"" . $_REQUEST['search_str'] . "\">";
		echo " <input type=\"submit\" value=\"Search\"><br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';
		
		$result = false;
		if( is_numeric($_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_enrolled_by_id( $sy_id,$_REQUEST["search_str"],0,true );
		} else if( isset( $_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_enrolled_by_lastname( $sy_id,$_REQUEST["search_str"],0,true );
		}
		if( $result != false ) {
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Course</th><th>Year</th><th>Date officially</th><th>Date dropped</th><th>refund rate</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
					$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
				}
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"%s></td>", $dat["student_id"], ($i==0 && $list->get_numrows()==1) ? " checked" : "" );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					mkstr_neat( $course_cache[$dat["course_id"]] ),
					lookup_yearlevel( $dat["year_level"] ),
					mkstr_date( $dat["date_officially"] ),
					mkstr_neat( mkstr_date($dat["date_dropped"]) ),
					(isset($dat["refund_rate"]) ? $dat["refund_rate"] . '%' : '&nbsp;')
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo '<input type="submit" name="drop" value="Officially drop">';
				echo '<input type="submit" name="cancel" value="Cancel officially drop">';
				echo '<br>';
				echo '<input type="button" value="View Details" onClick="' . "OnRadioOpen('mainform','student_id','view_studentinfo.php','','_blank')" . '">';
				echo '<input type="button" value="View Subject List" onClick="' . "OnRadioOpen('mainform','student_id','list_subject.php','sy_id=$sy_id','_blank')" . '">';
				echo '<input type="button" value="View Assessement Slip" onClick="' . "OnRadioOpen('mainform','student_id','assessment_slip.php','sy_id=$sy_id','_blank')" . '">';
			}
			$list->close();
		}
	}

	echo "</form>";

	print_footer();

	if( isset($_REQUEST["drop"]) || isset($_REQUEST["cancel"]) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		print_hidden( $_REQUEST,array('search_str') );
		echo " <input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>";
	} else {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		echo " <input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>";
	}
?>

</body>

</html>
