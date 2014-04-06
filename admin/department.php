<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	auth_check( AUTH_ADMIN );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
</head>

<body>

<?php
	print_title( "System Administration", "Department management" );

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$teacher_array[0] = ' - none - ';
			$teacher_array += get_teacher_array();
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>long name</td><td><input type="text" name="long_name"></td></tr>';
			echo '<tr><td>short name</td><td><input type="text" name="short_name"></td></tr>';
			echo '<tr><td>dean</td><td>' . mkhtml_select( 'dean_id',$teacher_array,0) . '</td></tr>';
			echo '<tr><td>order</td><td><input type="text" name="order_no"></td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="add">';
			echo '<input type="submit" name="exec" value="Add">';
			echo '</form>';
		} else {
			$dep = new model_department;
			$dep->connect( auth_get_writeable() );
			if( $dep->add_auto_compliment( $_REQUEST )==false ) {
				echo '<div class="error">' . $dep->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$teacher_array[0] = ' - none - ';
			$teacher_array += get_teacher_array();
			$dep = new model_department;
			$dep->connect();
			$dep->get_by_id( $_REQUEST["department_id"] );
			$dat = $dep->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>long name</td><td><input type="text" name="long_name" value="' . $dat["long_name"] . '"></td></tr>';
			echo '<tr><td>short name</td><td><input type="text" name="short_name" value="' . $dat["short_name"] . '"</td></tr>';
			echo '<tr><td>dean</td><td>' . mkhtml_select( 'dean_id',$teacher_array,$dat["dean_id"]) . '</td></tr>';
			echo '<tr><td>order</td><td><input type="text" name="order_no" value="' . $dat["order_no"] . '"></td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="edit">';
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="department_id" value="' . $_REQUEST["department_id"] . '">';
			echo '</form>';
		} else {
			$dep = new model_department;
			$dep->connect( auth_get_writeable() );
			if( $dep->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $dep->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$dep = new model_department;
			$dep->connect();
			$dep->get_by_id( $_REQUEST["department_id"] );
			$dat = $dep->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<div class="prompt">';
			echo 'Deleting department "' . $dat["long_name"] . '".<br>';
			echo 'OK?';
			echo '</div>';
			echo '<input type="hidden" name="del">';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="department_id" value="' . $_REQUEST["department_id"] . '">';
			echo '</form>';
		} else {
			$dep = new model_department;
			$dep->connect( auth_get_writeable() );
			if( $dep->del( $_REQUEST["department_id"] )==false ) {
				echo '<div class="error">' . $dep->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		$list = new model_department;
		$list->connect();
		$list->get_list();
	
		echo '<form method="POST">';
		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>long name</th><th>short name</th><th>dean</th><th>Order</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			echo '<tr>';
			echo '<td><input type="radio" name="department_id" value="' . $dat["department_id"] . '"></td>';
			echo '<td>' . $dat["long_name"] . '</td>';
			echo '<td>' . $dat["short_name"] . '</td>';
			echo '<td>' . mkstr_neat(lookup_teacher_name($dat["dean_id"])) . '</td>';
			echo '<td>' . mkstr_neat($dat['order_no']) . '</td>';
			echo '</tr>';
		}
		echo '</table>';

		echo '<input type="submit" name="add" value="Add">';
		echo '<input type="submit" name="edit" value="Edit">';
		echo '<input type="submit" name="del" value="Delete">';
		echo '</form>';
	}

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) || isset($_REQUEST["cmd"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	} else {
		echo '<form action="index.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	}
?>

</body>

</html>
