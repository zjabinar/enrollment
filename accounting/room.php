<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/room.inc");
	auth_check( $_SESSION["office"] );

$val = array(
	"building_id"	=> "Building",
	"room_code"		=> "Room code",
	"description"	=> "Description",
);

$val_build = array(
	"description"	=> "Description",
);

function print_edit_form( $room )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx=="building_id" ) {
			$ar = get_building_array();
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkhtml_select( "building_id",$ar,$room[$idx] );
			echo '<input type="submit" name="build_add" value="new">';
			echo '<input type="submit" name="build_edit" value="edit">';
			echo '</td>';
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td>" . "<input type=\"text\" name=\"${idx}\" value=\"${room[$idx]}\">";
		}
		echo "</tr>\n";
	}
	echo "</table>";
}

function print_edit_info( $room )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		echo "<td>" . $name . "</td>";
		echo "<td>" . $room[$idx] . "</td>";
		echo "</tr>\n";
	}
	echo "</table>";
}

function print_edit_form_build( $build )
{
	global $val_build;

	echo "<table border=\"1\">";
	foreach( $val_build as $idx => $name ) {
		echo "<tr>";
		echo "<td>" . $name . "</td>";
		echo "<td>" . "<input type=\"text\" name=\"${idx}\" value=\"${build[$idx]}\">";
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
<title> Room Registration</title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	print_title( get_office_name($_SESSION["office"]), "Room Registration" );

	echo '<form method="POST">';

	if( (! isset($_REQUEST["room_id"])) && (! isset($_REQUEST["add"])) && (! isset($_REQUEST["build_add"])) ) {
		$result = false;
		$building_array[0] = ' - all - ';
		$building_array += get_building_array();

		echo '<div class="prompt">Select building</div>';
		echo mkhtml_select( "building_id",$building_array,$_REQUEST["building_id"] );
		echo '<input type="submit" value="search">';

		if( isset( $_REQUEST["building_id"] ) ) {
			$list = new model_room;
			$list->connect();
			$result = $list->get_list($_REQUEST["building_id"]);
			if( $result != false ) {
				echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
				echo '<table border="1">';
				echo '<tr>';
				echo '<th>&nbsp;</th>';
				echo '<th>Room code</th><th>Building</th><th>Description</th>';
				echo '</tr>';
				for( $i=0; $i<$list->get_numrows(); $i++ ) {
					$dat = $list->get_fetch_assoc($i);
					printf( '<tr>' );
					printf( '<td><input type="radio" name="room_id" value="%s"></td>', $dat["room_id"] );
					printf( '<td>%s</td> <td>%s</td> <td>%s</td>' . "\n",
						mkstr_neat($dat["room_code"]),
						mkstr_neat( $building_array[$dat["building_id"]] ),
						mkstr_neat($dat["description"])
					);
					printf( '</tr>' );
				}
				echo '</table>';
				echo '</div>';
				echo '<input type="submit" name="edit" value="Edit">';
				echo '<input type="submit" name="del" value="Delete">';
			}
			$list->close();
			echo '<input type="submit" name="add" value="Add">';
		}
	} else if( isset($_REQUEST["build_add"]) ) {
		if( !isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Enter building details</div>';
			print_edit_form_build( null );
			echo '<input type="submit" name="exec" value="Add">';
			echo '<input type="hidden" name="build_add">';
			if( isset($_REQUEST["edit"]) ) echo '<input type="hidden" name="room_id" value="' . $_REQUEST["room_id"] . '">';
			echo '<input type="hidden" name="building_id" value="' . $_REQUEST["building_id"] . '">';
		} else {
			$data = new model_building();
			$data->connect( auth_get_writeable() );
			unset( $dat );
			$dat["description"] = $_REQUEST["description"];
			if( $data->add( $dat )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["build_edit"]) ) {
		$data = new model_building();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			if( $data->get_by_id($_REQUEST["building_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="prompt">Edit building details</div>';
				$build = $data->get_fetch_assoc(0);
				print_edit_form_build($build);
				echo '<input type="hidden" name="building_id" value="' . $_REQUEST["building_id"] . '">';
				echo '<input type="submit" name="exec" value="Update">';
				echo '<input type="hidden" name="build_edit">';
				if( isset($_REQUEST["edit"]) ) echo '<input type="hidden" name="room_id" value="' . $_REQUEST["room_id"] . '">';
			}
		} else {
			if( $data->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		$data->close();
	} else if( isset($_REQUEST["add"]) ) {
		if( !isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Enter room details</div>';
			print_edit_form( array("building_id"=>$_REQUEST["building_id"]) );
			echo '<input type="submit" name="exec" value="Add">';
			echo '<input type="hidden" name="add">';
		} else {
			$data = new model_room();
			$data->connect( auth_get_writeable() );
			if( $data->add( $_REQUEST )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$data = new model_room();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			if( $data->get_by_id($_REQUEST["room_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="prompt">Edit room details</div>';
				$room = $data->get_fetch_assoc(0);
				print_edit_form($room);
				echo '<input type="hidden" name="room_id" value="' . $_REQUEST["room_id"] . '">';
				echo '<input type="submit" name="exec" value="Update">';
				echo '<input type="hidden" name="edit">';
			}
		} else {
			if( $data->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		$data->close();
	} else if( isset($_REQUEST["del"]) ) {
		$data = new model_room();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			if( $data->get_by_id($_REQUEST["room_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="prompt">Delete following room?</div>';
				$room = $data->get_fetch_assoc(0);
				print_edit_info($room);
				echo '<input type="hidden" name="room_id" value="' . $_REQUEST["room_id"] . '">';
				echo '<input type="submit" name="exec" value="Delete">';
				echo '<input type="hidden" name="del">';
				echo '<input type="hidden" name="building_id" value="' . $_REQUEST["building_id"] . '">';
			}
		} else {
			if( $data->del( $_REQUEST["room_id"] )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
		$data->close();
	}
	echo "</form>";

	print_footer();

	if( isset($_REQUEST["build_add"]) || isset($_REQUEST["build_edit"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '<input type="hidden" name="building_id" value="' . $_REQUEST["building_id"] . '">';
		if( isset($_REQUEST["room_id"]) ) {
			echo '<input type="hidden" name="edit">';
			echo '<input type="hidden" name="room_id" value="' . $_REQUEST["room_id"] . '">';
		} else {
			echo '<input type="hidden" name="add">';
		}
		echo '</form>';
	} else if( (! isset($_REQUEST["room_id"])) && (! isset($_REQUEST["add"])) ) {
		echo '<form action="index.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo ' <input type="hidden" name="sy_id" value="' . $_SESSION["sy_id"] . '">';
		echo "</form>";
	} else {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '<input type="hidden" name="building_id" value="' . $_REQUEST["building_id"] . '">';
		echo '</form>';
	}
?>

</body>

</html>

