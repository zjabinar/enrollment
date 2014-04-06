<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/room.inc");
	require_once("../include/classschedule.inc");
	auth_check( $_SESSION["office"] );

$ampm_array = array( 'AM','PM' );
$weekday_array = get_weekday_array();
$building_array = get_building_array();

function print_edit_form( $dat )
{
	global $ampm_array;
	global $weekday_array;
	global $building_array;

	$weekday = array();
	
	if( isset($_REQUEST["room_id"]) ) {
		// User came back from error (so let's read datas from previous form)
		list( $st_ampm,$st_hour,$st_min,$len_hour,$len_min) = array(
			$_REQUEST["from_ampm"],$_REQUEST["from_hour"],$_REQUEST["from_min"],
			$_REQUEST["len_hour"],$_REQUEST["len_min"]
		);
		if( isset($_REQUEST["wday"]) ) $wday = $_REQUEST["wday"];
		if( count($_REQUEST["weekday"])>0 ) $weekday = $_REQUEST["weekday"];
		$dat["room_id"] = $_REQUEST["room_id"];
	} else if( isset($dat) ) {
		// or let's fill data of existing
		list( $wday,$st_ampm,$st_hour,$st_min,$end_ampm,$end_hour,$end_min,$len_hour,$len_min) = retrieve_time($dat);
	}

	if( $dat["room_id"]>0 ) {
		$building_id = get_building_id_from_room_id($dat["room_id"]);
	} else {
		$building_id = 1;
	}
	$room_array[0] = ' - none - ';
	$room_array += get_room_array($building_id);

	echo '<table border="1">';
	echo '<tr><td>Room</td><td>';
	echo "<select name=\"building_id\" onChange=\"funcOnBuilding()\">";
	foreach( $building_array as $i=>$j ) printf( '<option value="%d"%s>%s</option>', $i, ($i==$building_id)?" selected":"", $j );
	echo "</select>";
	echo mkhtml_select( "room_id",$room_array,$dat["room_id"] );
	echo '</td></tr>';
	echo '<tr><td>Weekday</td><td>';
	foreach( $weekday_array as $n=>$str ) {
		if( isset($wday) ) {
			echo '<input type="radio" name="wday" value="' . $n . '"' . ($wday==$n ? ' checked' : '') . ' id="' . $str . '"><label for="' . $str . '">' . $str . '</label>';
		} else {
			echo '<input type="checkbox" name="weekday[]" value="' . $n . '"' . (in_array($n,$weekday) ? ' checked' : '') . ' id="' . $str . '"><label for="' . $str . '">' . $str . '</label>';
		}
	}
	echo '</td></tr>';
	echo '<tr><td>Start time</td><td>' .
		'<input type="text" name="from_hour" value="' . $st_hour . '" size="2" style="text-align:right"> : ' .
		'<input type="text" name="from_min" value="' . sprintf("%02d",$st_min) . '" size="2" style="text-align:right">' .
		//mkhtml_select( "from_ampm",$ampm_array,$st_ampm ) .
		'&nbsp;<input type="radio" name="from_ampm" value="0"' . ($st_ampm=='0' ? ' checked' : '') . ' id="AM"><label for="AM">' . $ampm_array[0] . '</label>' .
		'&nbsp;<input type="radio" name="from_ampm" value="1"' . ($st_ampm=='1' ? ' checked' : '') . ' id="PM"><label for="PM">' . $ampm_array[1] . '</label>' .
		'</td></tr>';
	echo '<tr><td>Length</td><td>' .
		'<input type="text" name="len_hour" value="' . $len_hour . '" size="2" style="text-align:right">hour ' .
		'<input type="text" name="len_min" value="' . $len_min . '" size="2" style="text-align:right">minute' .
		'</td></tr>';
	echo '</table>';
}

function print_edit_info( $dat )
{
	global $ampm_array;
	global $weekday_array;

	list( $wday,$st_ampm,$st_hour,$st_min,$end_ampm,$end_hour,$end_min) = retrieve_time($dat);

	echo '<table border="1">';
	echo '<tr><td>Room</td><td>' . lookup_room_name($dat["room_id"]) . '</td></tr>';
	echo '<tr><td>Weekday</td><td>' . $weekday_array[$wday] . '</td></tr>';
	printf( '<tr><td>From</td><td>%s %d:%02d - %d:%02d</td></tr>',
		$ampm_array[$st_ampm], $st_hour, $st_min,
		$end_hour, $end_min
	);
	echo '</table>';
}

?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Class Registration</title>

<script type="text/javascript">
<!--
function funcOnBuilding() {
<?php
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
	document.mainform.room_id.options[0] = new Option(' - none - ',0);
	var counter = 1;
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
	$sy_id = $_SESSION["sy_id"];
	$department_id = $_SESSION["department_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$str_department = get_department_from_department_id($department_id);

	print_title( "$str_department", "Class Schedule", $str_schoolyear );

	echo '<form name="mainform" method="POST">';

//	echo '<input type="hidden" name="sy_id" value="' . $_SESSION["sy_id"] . '">';
//	echo '<input type="hidden" name="department_id" value="' . $_SESSION["department_id"] . '">';

	$class_id = $_REQUEST["class_id"];
//	echo '<input type="hidden" name="class_id" value="' . $_REQUEST["class_id"] . '">';
	print_hidden( $_POST, array('class_id','course_id','year_level','section') );

	echo '<div style="border-style:solid;border-width:thin">';
	print_classinfo_simple( $class_id );
	echo '</div><br>';

	$schedule_id = $_REQUEST["schedule_id"];

	if( $class_id==0 ) {
		echo '<div class="error">Select a class</div>';
	} else if( $schedule_id==0 && !isset($_REQUEST["add"]) ) {
		$result = false;
		$list = new model_classschedule;
		$list->connect();
		$result = $list->get_list($class_id);
		if( $result != false ) {
			echo '<table border="1">';
			echo '<tr>';
			echo '<th>&nbsp;</th>';
			echo '<th>Room code</th><th>Weekday</th><th>Time</th>';
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				list( $wday,$st_ampm,$st_hour,$st_min,$end_ampm,$end_hour,$end_min,$len_hour,$len_min) = retrieve_time($dat);
				$str_time = sprintf( "%s %d:%02d - %d:%02d",
					$ampm_array[ $st_ampm ], $st_hour, $st_min,
					$end_hour, $end_min
				);
				printf( "<tr>" );
				printf( '<td><input type="radio" name="schedule_id" value="%s"></td>', $dat["schedule_id"] );
				printf( '<td>%s</td> <td>%s</td> <td>%s</td>' . "\n",
					mkstr_neat( lookup_room_name($dat["room_id"]) ),
					$weekday_array[$wday],
					$str_time
				);
				printf( '</tr>' );
			}
			echo '</table>';
			if( $list->get_numrows() > 0 ) {
				echo '<input type="submit" name="edit" value="Edit">';
				echo '<input type="submit" name="del" value="Delete">';
			}
		}
		$list->close();

		echo '<br><input type="submit" name="add" value="Add new schedule">';
		echo '</form>';
	} else if( isset($_REQUEST["add"]) ) {
		if( !isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Enter schedule details</div>';
			print_edit_form( null );
			echo '<input type="checkbox" name="ignore_conflicts" value="1">Ignore conflicts of schedules<br>';
			echo '<input type="submit" name="exec" value="Add">';
			echo '<input type="hidden" name="add">';
		} else {
			$data = new model_classschedule();
			$data->connect( auth_get_writeable() );
			$time_st =
				($_REQUEST["from_hour"] + ($_REQUEST['from_ampm']==1 ? 12 : 0)) * 60
				+ $_REQUEST["from_min"];
			$time_len = ($_REQUEST["len_hour"] * 60) + $_REQUEST["len_min"];
			unset( $dat );
			if( $_REQUEST["room_id"]>0 ) $dat["room_id"] = $_REQUEST["room_id"];
			$dat["class_id"] = $_REQUEST["class_id"];
			$dat["sy_id"] = $_SESSION["sy_id"];
			if( count($_REQUEST["weekday"])<=0 ) {
				$err_msg = 'Select weekday!';
			} else if( !isset($_REQUEST['from_ampm']) ) {
				$err_msg = 'Select AM or PM';
			} else {
				foreach( $_REQUEST["weekday"] as $wday ) {
					$dat["time_st"] = $time_st + ($wday*24*60);
					$dat["time_end"] = $dat["time_st"] + $time_len;
					$result = false;
					if( $_REQUEST["ignore_conflicts"]>0 ) {
						$result = $data->add( $dat );
					} else {
						$result = $data->check_and_add( $dat );
					}
					if( $result==false ) {
						$err_msg = $data->get_errormsg();
						break;
					}
				}
			}
			if( isset($err_msg) ) {
				echo '<div class="error">' . $err_msg . '</div>';
				$exec_error = 1;
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$data = new model_classschedule();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			if( $data->get_by_id($_REQUEST["schedule_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="prompt">Edit schedule details</div>';
				$dat = $data->get_fetch_assoc(0);
				print_edit_form($dat);
				echo '<input type="hidden" name="schedule_id" value="' . $_REQUEST["schedule_id"] . '">';
				echo '<input type="checkbox" name="ignore_conflicts" value="1">Ignore conflicts of schedules<br>';
				echo '<input type="submit" name="exec" value="Update">';
				echo '<input type="hidden" name="edit">';
			}
		} else {
			$_REQUEST["sy_id"] = $_SESSION["sy_id"];
			if( $_REQUEST["room_id"]==0 ) $_REQUEST["room_id"] = 'NULL';
			$_REQUEST["time_st"] =
				($_REQUEST["wday"] * 24 * 60)
				+ ($_REQUEST["from_hour"] + ($_REQUEST[from_ampm]==1 ? 12 : 0)) * 60
				+ $_REQUEST["from_min"];
			$_REQUEST["time_end"] = $_REQUEST["time_st"] + ($_REQUEST["len_hour"] * 60) + $_REQUEST["len_min"];
			$result = false;
			if( $_REQUEST["ignore_conflicts"]>0 ) {
				$result = $data->update( $_REQUEST );
			} else {
				$result = $data->check_and_update( $_REQUEST );
			}
			if( $result==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
				$exec_error = 1;
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		$data->close();
	} else if( isset($_REQUEST["del"]) ) {
		$data = new model_classschedule;
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			if( $data->get_by_id($_REQUEST["schedule_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="prompt">Delete following schedule?</div>';
				$class = $data->get_fetch_assoc(0);
				print_edit_info($class);
				echo '<input type="hidden" name="schedule_id" value="' . $_REQUEST["schedule_id"] . '">';
				echo '<input type="submit" name="exec" value="Delete">';
				echo '<input type="hidden" name="del">';
			}
		} else {
			if( $data->del( $_REQUEST["schedule_id"] )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
		$data->close();
	}
	echo "</form>";

	print_footer();

	if( ($schedule_id==0) && (!isset($_REQUEST["add"])) ) {
		echo '<form action="class.php" method="POST" id="goback">';
		echo "<input type=\"submit\" value=\"Go back\">";
		echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
		echo '<input type="hidden" name="department_id" value="' . $department_id . '">';
		print_hidden( $_POST,array('course_id','year_level','section') ); 
		echo "</form>";
	} else {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		if( $exec_error ) {
			unset( $_POST["exec"] );
			print_hidden( $_POST );
		} else {
			print_hidden( $_POST,array('class_id','course_id','year_level','section') );
		}
		echo '</form>';
	}
?>

</body>

</html>
