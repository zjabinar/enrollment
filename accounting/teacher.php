<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	auth_check( $_SESSION['office'] );

$val = array(
	"teacher_id"		=> "TeacherID",
	"title"				=> "Title",
	"first_name"		=> "First Name",
	"middle_name"		=> "Middle Name",
	"last_name"			=> "Last Name",
	"rank"				=> "Rank",
	"position"			=> "Position",
	"department_id"		=> "Department",
	"address"			=> "Address",
	"noclass"			=> "No teaching load",
	"date_of_birth"		=> "Date of birth",
	"doctor_degree"		=> "Doctoral Degree",
	"master_degree"		=> "Masteral Degree",
	"bachelor_degree"	=> "Bachelors Degree"
);

function print_edit_form( $teacher, $bool_new )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx == "teacher_id" ) {
			if( $bool_new ) {
				echo "<td>" . $name . "</td>";
				echo "<td>";
				echo "<input type=\"text\" name=\"${idx}\" value=\"" . mkstr_teacher_id($teacher[$idx]) . "\">(6 digit numeral)";
				echo " <input type=\"checkbox\" name=\"auto_id\" value=\"1\"" . (isset($teacher['auto_id']) ? " checked" : "") . " onClick=\"funcOnAutoID()\">Generate auto";
				echo "</td>";
			} else {
				echo "<td>" . $name . "</td>";
				echo "<td>" . mkstr_teacher_id($teacher[$idx]);
				echo "<input type=\"hidden\" name=\"${idx}\" value=\"" . $teacher[$idx] . "\"></td>";
			}
		} else if( $idx == "department_id" ) {
			echo "<td> $name </td>";
			$da = get_department_array();
			$da['NULL'] = 'Other';
			if( $teacher[$idx]=='' ) $teacher[$idx] = 'NULL';
			echo "<td>" . mkhtml_select( $idx, $da, $teacher[$idx] ) . "</td>";
		} else if( $idx == "first_name" ) {
			echo "<td>" . $name . "</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"${teacher[$idx]}\">(Jr,II,etc.. should be added here)</td>";
		} else if( $idx == "noclass" ) {
			echo "<td>Option</td>";
			echo "<td><input type=\"checkbox\" name=\"${idx}\" value=\"1\"" . ($teacher[$idx] ? " checked" : "") . ">$name</td>";
		} else if( $idx == "date_of_birth" ) {
			echo "<td>$name</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"" . $teacher[$idx] . "\">(MM/DD/YYYY)</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"${teacher[$idx]}\"></td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";

	if( $bool_new ) {
		echo '<script type="text/javascript">document.mainform.teacher_id.focus();</script>';
	}
}

function print_edit_info( $teacher )
{
	global $val;
	
	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx == "teacher_id" ) {
			echo "<td> $name </td>";
			echo "<td> " . mkstr_teacher_id($teacher[$idx]) . "</td>";
		} else if( $idx == "department_id" ) {
			echo "<td> $name </td>";
			echo "<td> " . get_department_from_course_id($teacher[$idx]) . "</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td>" . $teacher[$idx] . "</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Teacher Registration </title>
<script type="text/JavaScript">
function funcOnAutoID() {
	var elem = document.mainform.elements['auto_id'];
	if( elem==null ) return;
	if( elem.checked ) {
		document.mainform.teacher_id.style.visibility="hidden";
	} else {
		document.mainform.teacher_id.style.visibility="visible";
	}
}
</script>
</head>

<body onLoad="optionOnLoad();funcOnAutoID()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	print_title( get_office_name($_SESSION["office"]), "Teacher Registration" );

	echo "<form method=\"POST\" name=\"mainform\">";

	if( ((! isset($_REQUEST["teacher_id"])) && (! isset($_REQUEST["add"]))) || isset($_REQUEST['search']) || isset($_REQUEST['search_all']) ) {
		echo '<div class="prompt">Enter Teacher ID or Last Name to Search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"${_REQUEST["search_str"]}\">";
		echo " <input type=\"submit\" name=\"search\" value=\"Search\">";
		echo " &nbsp; <input type=\"submit\" name=\"search_all\" value=\"List all\"><br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';

		$result = false;
		$list = new model_teacher;
		if( isset($_REQUEST['search_all']) ) {
			$list->connect();
			$result = $list->get_list();
		} else if( is_numeric($_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_by_id( $_REQUEST["search_str"] );
		} else if( isset($_REQUEST["search_str"]) ) {
			$list->connect();
			$result = $list->search_by_lastname( $_REQUEST["search_str"] );
		}
		if( $result != false ) {
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>TeacherID</th><th>Title</th><th>FirstName</th><th>MiddleName</th><th>LastName</th><th>Department</th><th>Position</th><th>Load</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"teacher_id\" value=\"%s\"></td>", $dat["teacher_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_teacher_id($dat["teacher_id"]),
					mkstr_neat($dat["title"]),
					mkstr_neat($dat["first_name"]),
					mkstr_neat($dat["middle_name"]),
					mkstr_neat($dat["last_name"]),
					mkstr_neat( get_department_from_department_id($dat["department_id"]) ),
					mkstr_neat($dat["position"]),
					($dat["noclass"] ? "x" : "&nbsp;")
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			echo "<input type=\"submit\" name=\"edit\" value=\"Edit\">";
			echo "<input type=\"submit\" name=\"del\" value=\"Delete\">";
		}
		$list->close();

		echo "<input type=\"submit\" name=\"add\" value=\"Add New Teacher\">";
	} else if( isset($_REQUEST["add"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		if( !isset($_REQUEST["confirm"]) ) {
			echo '<div class="prompt">Enter teacher details</div>';
			if( isset($_REQUEST['error']) ) {
				print_edit_form($_POST,true);
			} else {
				print_edit_form(null,true);
			}
			echo '<input type="submit" name="add" value="Add">';
			echo '<input type="hidden" name="confirm">';
		} else {
			$_REQUEST["teacher_id"] = retrieve_teacher_id($_REQUEST["teacher_id"]);
			if( isset($_REQUEST['date_of_birth']) ) $_REQUEST['date_of_birth'] = retrieve_date($_REQUEST['date_of_birth']);
			if( ($_REQUEST["teacher_id"]<=0) && (!isset($_REQUEST['auto_id'])) ) {
				$errmsg = 'Bad teacher ID';
			} else {
				$data = new model_teacher();
				$data->connect( auth_get_writeable() );
				if( isset($_REQUEST['auto_id']) ) {
					unset($_REQUEST['teacher_id']);
					$result = $data->add_auto( $_REQUEST );
					if( $result==false ) {
						$errmsg = $data->get_errormsg();
					} else {
						echo '<div class="message">Added successfully<br>Teacher ID is "<b>' . $result . '</b>"</div>';
					}
				} else {
					if( $data->add( $_REQUEST )==false ) {
						$errmsg = $data->get_errormsg();
					} else {
						echo '<div class="message">Added successfully</div>';
					}
				}
			}
			if( isset($errmsg) ) {
				echo '<div class="error">' . $errmsg . '</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		$data = new model_teacher();
		$data->connect( auth_get_writeable() );
		if( !isset($_REQUEST["confirm"]) ) {
			if( $data->get_by_id($_REQUEST["teacher_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				$teacher = $data->get_fetch_assoc(0);
				echo '<div class="prompt">Edit teacher details</div>';
				if( isset($_REQUEST['error']) ) {
					print_edit_form($_POST,false);
				} else {
					if( $teacher['date_of_birth']!='' ) $teacher['date_of_birth'] = mkstr_date($teacher['date_of_birth']);
					print_edit_form($teacher,false);
				}
				echo '<input type="submit" name="edit" value="Update">';
				echo '<input type="hidden" name="confirm">';
			}
		} else {
			if( isset($_REQUEST['date_of_birth']) ) $_REQUEST['date_of_birth'] = retrieve_date($_REQUEST['date_of_birth']);
			if( $data->update( $_REQUEST )==false ) {
				$errmsg = $data->get_errormsg();
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
			if( isset($errmsg) ) {
				echo '<div class="error">' . $errmsg . '</div>';
			}
		}
		$data->close();
	} else if( isset($_REQUEST["del"]) ) {
		print_hidden( $_REQUEST,array('search_str') );
		$data = new model_teacher();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["confirm"]) ) {
			if( $data->get_by_id($_REQUEST["teacher_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				$teacher = $data->get_fetch_assoc(0);
				echo '<div class="prompt">Delete following data?</div>';
				print_edit_info($teacher);
				echo '<input type="hidden" name="teacher_id" value="' . $_REQUEST["teacher_id"] . '">';
				echo '<input type="submit" name="del" value="Delete">';
				echo '<input type="hidden" name="confirm">';
			}
		} else {
			if( $data->del( $_REQUEST["teacher_id"] )==false ) {
				$errmsg = $data->get_errormsg();
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
			if( isset($errmsg) ) {
				echo '<div class="error">' . $errmsg . '</div>';
			}
		}
		$data->close();
	}

	echo "</form>";

	print_footer();

	if( isset($errmsg) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		unset($_POST['confirm']);
		print_hidden( $_POST );
		print_hidden( array('error'=>1) );
		echo "</form>";
	} else if( ((!isset($_REQUEST["teacher_id"])) && (!isset($_REQUEST["add"]))) || isset($_REQUEST['search']) || isset($_REQUEST['search_all']) ) {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		echo ' <input type="hidden" name="sy_id" value="' . $_SESSION["sy_id"] . '">';
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
