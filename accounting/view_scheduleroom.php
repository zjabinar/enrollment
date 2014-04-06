<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/view_schedule.inc");
	require_once("../include/room.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Class list of rooms </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
-->
</style>

<script type="text/javascript">
<!--
function funcOnBuilding() {
<?php
	$building_array = get_building_array();
	$obj_room = new model_room;
	$obj_room->connect();
	$obj_room->get_list();
	$room_count = $obj_room->get_numrows();
	echo "\troom_array = new Array();\n";
	foreach( $building_array as $build_idx=>$build_val ) {
		echo "\troom_array[$build_idx] = Array();\n";
		for( $i=0; $i<$room_count; $i++ ) {
			$dat = $obj_room->get_fetch_assoc($i);
			if( $dat["building_id"]==$build_idx ) {
				echo "\troom_array[$build_idx][${dat["room_id"]}] = '" . $dat["room_code"] . "';\n";
			}
		}
	}
?>
	building_id = document.mainform.building_id.value;
	document.mainform.room_id.length = 0;
	//document.mainform.room_id.options[0] = new Option(' - none - ',0);
	//var counter = 1;
	var counter = 0;
	for( i in room_array[building_id] ) {
		document.mainform.room_id.options[counter] = new Option(room_array[building_id][i],i);
		counter++;
	}
}
// -->
</script>

</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$department_id = $_SESSION["department_id"];
	}

	print_title( get_office_name($_SESSION["office"]), "Schedule of rooms", $str_schoolyear );

	echo '<form name="mainform" method="POST">';

	$room_id = $_REQUEST["room_id"];
	if( $room_id>0 ) {
		$building_id = get_building_id_from_room_id($room_id);
	} else {
		$building_id = 1;
	}

	echo '<div class="prompt">Select a room</div>';
//	echo mkhtml_select( "room_id", get_room_array(), $room_id );
	echo "<select name=\"building_id\" onChange=\"funcOnBuilding()\">";
	foreach( $building_array as $i=>$j ) printf( '<option value="%d"%s>%s</option>', $i, ($i==$building_id)?" selected":"", $j );
	echo "</select>";
	$room_array = get_room_array($building_id);
	echo mkhtml_select( "room_id",$room_array,$room_id );
	echo '<input type="submit" value="View">';

	if( $room_id>0 ) {
		$room_name = lookup_room_name($room_id);

		$list = new model_class;
		$list->connect();
		$list->get_list_by_room( $sy_id,$room_id );

		$obj_schedule = new model_classschedule;
		$obj_schedule->connect();

		echo '<p>';
		echo '<table border="1">';
		echo '<caption>Subject List</caption>';
		echo '<tr><th>Subject</th><th>SubjectCode</th><th>Course</th><th>YearLevel</th><th>Teacher</th><th>Schedule</th><th>StudentList</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			if( ! isset($cache_course[$dat["course_id"]]) ) {
				$cache_course[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
			}
			$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
			$schedule_str = '';
			for( $j=0; $j<count($schedule_ar); $j++ ) {
				//if( $schedule_ar[$j][0]==$room_name ) {
					$schedule_str .= $schedule_ar[$j][1] . ' ' . $schedule_ar[$j][2] . ' ' . $schedule_ar[$j][0] . '<br>';
				//}
			}
			printf( "<tr>" );
			printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
				mkstr_neat($dat["subject"]),
				mkstr_neat($dat["subject_code"]),
				mkstr_neat($cache_course[$dat["course_id"]]),
				lookup_yearlevel($dat["year_level"]),
				mkstr_neat(lookup_teacher_name($dat["teacher_id"])),
				mkstr_neat($schedule_str),
				'<input type="button" value="check" onClick="' . "window.open('list_classstudent.php?sy_id=$sy_id&class_id=" . $dat["class_id"]. "','_blank')" . '">'
			);
			printf( "</tr>" );
		}
		echo '</table>';
		echo '</p>';

		echo '<p>';
		$list_schedule = new model_classschedule;
		$list_schedule->connect();
		$list_schedule->get_list_of_room($sy_id,$room_id);
		print_classschedule_table( $list_schedule, "Time table" );
		echo '</p>';
	}

	echo '</form>';

	print_footer();

	echo '<form action="index.php" method="POST" id="goback">';
	echo '<input type="submit" value="Go back">';
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '</form>';
?>

</body>

</html>
