<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/scholarship.inc");
	require_once("../include/enrol_student.inc");
	auth_check( AUTH_SCHOLARSHIP );

$val = array(
	"scholar_type"		=> "Scholar type",
	"date"				=> "Officially enrol date"
);

function print_edit_form( $dat )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx=='scholar_type' ) {
			$ar[0] = ' - none - ';
			$ar += get_scholartype_array( $dat["sy_id"] );
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkhtml_select( 'scholartype_id',$ar,$dat["scholartype_id"]) . "</td>";
		} else if( $idx=="date" ) {
			echo '<td>' . $name . '</td>';
			echo '<td>';
			$obj = new model_enrol_student;
			$obj->connect();
			$obj->get_by_id($dat["sy_id"],$dat["student_id"]);
			$enrol_dat = $obj->get_fetch_assoc(0);
			if( isset($enrol_dat["date_officially"]) ) {
				echo '<input type="text" name="date" value="' . mkstr_date($enrol_dat["date_officially"]) . '">';
			} else if( isset($enrol_dat["date"]) ) {
				echo '<input type="text" name="date" value="' . mkstr_date( date('Y-m-d') ) . '">';
			} else {
				echo 'Not yet enrolled';
			}
			echo '</td>';
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"${dat[$idx]}\"></td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";
}

function print_edit_info( $dat )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx=='scholar_type' ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . lookup_scholartype($dat["scholartype_id"]) . "</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td>" . $dat[$idx] . "</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";
}

function update_officially_enrol_date( $sy_id,$student_id,$date_officially )
{
	$obj_enrol = new model_enrol_student;
	$obj_enrol->connect( auth_get_writeable() );
	$obj_enrol->get_by_id($sy_id,$_REQUEST["student_id"]);
	if( $obj_enrol->get_numrows() > 0 ) {
		$dat_enrol = $obj_enrol->get_fetch_assoc(0);
		$dat_enrol["date_officially"] = $date_officially;
		$result = $obj_enrol->update( $dat_enrol );
		if( $result==false ) {
			return $obj_enrol->get_errormsg();
		}
	}
	return null;
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Scholarship </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	print_title( get_office_name($_SESSION["office"]), "Scholarship assignment", lookup_schoolyear($sy_id) );

	echo '<form method="POST" name="mainform">';

	if( ! isset($_REQUEST["edit"]) ) {	// search
		echo '<div class="prompt">Enter Student ID or Last Name to Search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"${_REQUEST["search_str"]}\">";
		echo " <input type=\"submit\" value=\"Search\"><br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';

		$result = false;
		$list = new model_student;
		if( is_numeric($_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_by_id( $_REQUEST["search_str"] );
		} else if( isset($_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_by_lastname( $_REQUEST["search_str"] );
		}
		if( $result != false ) {
			$obj_scholarship = new model_scholarship;
			$obj_scholarship->connect();
			echo '<div' . ($list->get_numrows()>0 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Department</th><th>Course</th><th>Scholar type</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					list($dep,$course,$major,$minor) = get_short_names_from_course_id( $dat["course_id"] );
					$course_cache[ $dat["course_id"] ] = $course . ' ' . $major . ' ' . $minor;
					$dep_cache[ $dat["course_id"] ] = $dep;
				}
				$obj_scholarship->get($sy_id,$dat["student_id"]);
				if( $obj_scholarship->get_numrows()>0 ) {
					$sch = $obj_scholarship->get_fetch_assoc(0);
					$scholarship = lookup_scholartype($sch["scholartype_id"]);
				} else {
					$scholarship = '&nbsp;';
				}
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"%s></td>", $dat["student_id"], ($list->get_numrows()==1 ? " checked":"") );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_fml($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					mkstr_neat( $dep_cache[$dat["course_id"]] ),
					mkstr_neat( $course_cache[$dat["course_id"]] ),
					$scholarship
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo "<input type=\"submit\" name=\"edit\" value=\"Edit\">";
			}
		}
		$list->close();
	} else if( isset($_REQUEST["edit"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		if( isset($_REQUEST['student_id']) ) {
			echo '<div style="border-style:solid;border-width:thin">';
			print_studentinfo_simple( $_REQUEST["student_id"],$sy_id );
			echo '</div><br>';
		}
		$data = new model_scholarship();
		$data->connect( auth_get_writeable() );
		if( !isset($_REQUEST['student_id']) ) {
			echo '<div class="error">Select student!</div>';
		} else if( !isset($_REQUEST["confirm"]) ) {
			$data->get($sy_id,$_REQUEST["student_id"]);
			if( $data->get_numrows() > 0 ) {
				$dat = $data->get_fetch_assoc(0);
				print_hidden( array('scholarship_id'=>$dat["scholarship_id"]) );
			} else {
				$dat = array( 'sy_id'=>$sy_id,'student_id'=>$_REQUEST["student_id"] );
			}
			echo '<div class="prompt">Edit scholarship details</div>';
			print_edit_form($dat);
			echo '<input type="submit" name="edit" value="Update">';
			echo '<input type="hidden" name="confirm">';
			print_hidden( array('student_id'=>$_REQUEST["student_id"]) );
		} else {
			$_REQUEST["sy_id"] = $sy_id;
			$result = false;
			if( isset($_REQUEST["scholarship_id"]) ) {
				if( $_REQUEST["scholartype_id"]==0 ) {
					$result = $data->begin_transaction();
					if( $result ) $result = $data->del( $_REQUEST["scholarship_id"] );
					if( $result ) {
						if( check_officially_enrolled_direct($sy_id,$_REQUEST["student_id"])==false ) {
							$obj_enrol = new model_enrol_student;
							$obj_enrol->connect( auth_get_writeable() );
							$result = $obj_enrol->cancel_official_enrol($sy_id,$_REQUEST["student_id"]);
							if( $result==false ) $data->set_error( $obj_enrol->get_errormsg() );
						}
					}
					if( $result ) {
						$data->end_transaction();
					} else {
						$data->rollback();
					}
				} else {
					$result = $data->begin_transaction();
					if( $result ) {
						$err = update_officially_enrol_date( $sy_id,$_REQUEST["student_id"],retrieve_date($_REQUEST["date"]) );
						if( $err ) {
							$result = false;
							$data->set_error( $err );
						}
					}
					if( $result ) $result = $data->update( $_REQUEST );
					if( $result ) {
						$data->end_transaction();
					} else {
						$data->rollback();
					}
				}
			} else if( $_REQUEST["scholartype_id"]>0 ) {
				$result = $data->begin_transaction();
				if( $result ) {
					$err = update_officially_enrol_date( $sy_id,$_REQUEST["student_id"],retrieve_date($_REQUEST["date"]) );
					if( $err ) {
						$result = false;
						$data->set_error( $err );
					}
				}
				if( $result ) $result = $data->add( $_REQUEST );
				if( $result ) {
					$data->end_transaction();
				} else {
					$data->rollback();
				}
			} else {
				$result = true;
			}
			if( $result==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		$data->close();
	}
	echo '</form>';

	print_footer();

	if( !isset($_REQUEST["edit"]) ) {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		print_hidden( array('sy_id'=>$sy_id) );
		echo "</form>";
	} else {
		echo "<form method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		print_hidden( $_REQUEST,array('search_str') );
		echo "</form>";
	}
?>

</body>

</html>
