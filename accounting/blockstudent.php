<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/blockstudent.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Block Student </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	print_title( get_office_name($_SESSION["office"]), "Block Student", lookup_schoolyear($sy_id) );

	echo '<form method="POST" name="mainform">';

	if( (! isset($_REQUEST["student_id"])) || isset($_REQUEST['search']) || isset($_REQUEST['searchall']) ) {	// search
		echo '<div class="prompt">Enter Student ID or Last Name to Search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"${_REQUEST["search_str"]}\">";
		echo "<input type=\"submit\" name=\"search\" value=\"Search\">";
		echo " &nbsp; <input type=\"submit\" name=\"searchall\" value=\"List all Blocked Students\"><br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';

		$result = false;
		if( isset($_REQUEST['searchall']) ) {
			$list = new model_tblblockstudent;
			$list->connect();
			$result = $list->get_list( $_SESSION["office"] );
		} else if( is_numeric($_REQUEST["search_str"]) ) {
			$list = new model_student;
			$list->connect();
			$result = $list->search_by_id( $_REQUEST["search_str"],$sy_id,$_SESSION["department_id"] );
		} else if( isset($_REQUEST["search_str"]) ) {
			$list = new model_student;
			$list->connect();
			$result = $list->search_by_lastname( $_REQUEST["search_str"],$sy_id,$_SESSION["department_id"] );
		}
		if( $result != false ) {
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>StudentID</th><th>Name</th><th>Department</th><th>Course</th><th>Blocked</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( ! isset($course_cache[$dat["course_id"]]) ) {
					list($dep,$course,$major,$minor) = get_short_names_from_course_id( $dat["course_id"] );
					$course_cache[ $dat["course_id"] ] = $course . ' ' . $major . ' ' . $minor;
					$dep_cache[ $dat["course_id"] ] = $dep;
				}
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"></td>", $dat["student_id"] );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					mkstr_neat( $dep_cache[$dat["course_id"]] ),
					mkstr_neat( $course_cache[$dat["course_id"]] ),
					(lookup_blockstudent($dat["student_id"],$_SESSION['office'])==null ? "&nbsp;" : "Blocked")
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			if( $list->get_numrows()>0 ) echo "<input type=\"submit\" name=\"block\" value=\"Block/Unblock selected student\">";
		}
	} else {
		print_hidden( $_REQUEST,array('search_str') );
		
		$student_id = $_REQUEST["student_id"];
		print_hidden( array('student_id'=>$student_id) );
		
		echo '<div style="border-style:solid;border-width:thin">';
		$student_dat = print_studentinfo_simple( $student_id,$sy_id );
		echo '</div><br>';

		$datar = lookup_blockstudent($student_id,$_SESSION['office']);
		if( $datar!=null ) $dat = current($datar);	// one block at a office, so just get first info.

		if( !isset($_REQUEST["confirm"]) ) {
			echo '<input type="checkbox" name="bl"' . ($dat!=null ? " checked" : "") . '>Blocked<br>';
			echo 'Message <input type="text" size="64" name="message" value="' . $dat['message'] . '"><br>';
			echo '<br>';
			echo '<input type="submit" name="block" value="Update">';
			echo '<input type="hidden" name="confirm">';
		} else {
			$obj = new model_tblblockstudent;
			$obj->connect( auth_get_writeable() );
			if( $dat==null ) {
				if( isset($_REQUEST["bl"]) ) {
					$ar = array(
						'student_id' => $student_id,
						'office_id' => $_SESSION["office"],
						'message' => $_REQUEST["message"],
						'date' => date('Y-m-j')
					);
					if( $obj->add( $ar )==false ) $err_msg = $obj->get_errormsg();
				} else {
					$err_msg = "No change";
				}
			} else {
				if( isset($_REQUEST["bl"]) ) {
					$dat['message'] = $_REQUEST["message"];
					$dat['date'] = date('Y-m-j');
					if( $obj->update( $dat )==false ) $err_msg = $obj->get_errormsg();
				} else {
					if( $obj->del( $dat['block_id'] )==false ) $err_msg = $obj->get_errormsg();
				}
			}
			if( $err_msg ) {
				echo '<div class="error">' . $err_msg . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	}
	echo '</form>';

	print_footer();

	if( (!isset($_REQUEST["student_id"])) || isset($_REQUEST['search']) || isset($_REQUEST['searchall'])) {
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
