<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	auth_check( AUTH_ADMIN );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">

<?php
	print_title( "System Administration", "User management" );

	$officearray[AUTH_ADMIN] = "SYSADMIN";
	$officearray[AUTH_REGISTRAR] = "Registrar";
	$officearray[AUTH_ACCOUNTING] = "Accounting";
	$officearray[AUTH_CASHIER] = "Cashier";
	$officearray[AUTH_SCHOLARSHIP] = "Scholarship";
	$officearray[AUTH_GRADE] = "Grade";
	$officearray[AUTH_HRMO] = "HRMO";
	$ar = get_short_department_array();
	foreach( $ar as $idx => $nm ) $officearray[$idx] = $nm;

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>username</td><td><input type="text" name="username"></td></tr>';
			echo '<tr><td>password</td><td><input type="password" name="passwd"></td></tr>';
			echo '<tr><td>full name</td><td><input type="text" name="fullname"></td></tr>';
			foreach( $officearray as $idx => $value ) {
				echo '<tr>'
					. '<td>' . $value . '</td><td>'
					. 'R<input type="checkbox" name="' . $value . '"> '
					. 'W<input type="checkbox" name="W' . $value . '">'
					. '</td></tr>';
			}
			echo '<tr><td>active</td><td><input type="checkbox" name="active" value="1" checked></td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="add">';
			echo '<input type="submit" name="exec" value="Add">';
			echo '</form>';
		} else {
			$user = new model_auth;
			$user->connect( auth_get_writeable() );
			$_REQUEST["authflag"] = "0";
			$_REQUEST["authflag_w"] = "0";
			foreach( $officearray as $idx => $value ) {
				if( isset($_REQUEST[$value]) ) $_REQUEST["authflag"] |= 0x01<<$idx;
				if( isset($_REQUEST['W'.$value]) ) $_REQUEST["authflag_w"] |= 0x01<<$idx;
			}
			$_REQUEST["passwd"] = md5($_REQUEST["passwd"]);
			if( ! isset($_REQUEST["active"]) ) $_REQUEST["active"]="0";
			if( $user->add( $_REQUEST )==false ) {
				echo '<div class="error">' . $user->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$user = new model_auth;
			$user->connect();
			$user->get_by_id( $_REQUEST["user_id"] );
			$dat = $user->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>username</td><td><input type="text" name="username" value="' . $dat["username"] . '"></td></tr>';
			echo '<tr><td>password</td><td><input type="password" name="passwd"</td></tr>';
			echo '<tr><td>full name</td><td><input type="text" name="fullname" value="' . $dat["fullname"] . '"></td></tr>';
			foreach( $officearray as $idx => $value ) {
				echo '<tr>'
					. '<td>' . $value . '</td><td>'
					. 'R<input type="checkbox" name="' . $value . '"' . ($dat["authflag"] & (0x01<<$idx) ? " checked" : "") . '> '
					. 'W<input type="checkbox" name="W' . $value . '"' . ($dat["authflag_w"] & (0x01<<$idx) ? " checked" : "") . '>'
					. '</td></tr>';
			}
			echo '<tr><td>active</td><td><input type="checkbox" name="active" value="1"' . ($dat["active"]==1 ? " checked" : "") . '></td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="edit">';
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="user_id" value="' . $_REQUEST["user_id"] . '">';
			echo '</form>';
		} else {
			$user = new model_auth;
			$user->connect( auth_get_writeable() );
			$_REQUEST["authflag"] = "0";
			$_REQUEST["authflag_w"] = "0";
			foreach( $officearray as $idx => $value ) {
				if( isset($_REQUEST[$value]) ) $_REQUEST["authflag"] |= 0x01<<$idx;
				if( isset($_REQUEST['W'.$value]) ) $_REQUEST["authflag_w"] |= 0x01<<$idx;
			}
			if( $_REQUEST["passwd"]=="" ) {
				unset( $_REQUEST["passwd"] );
			} else {
				$_REQUEST["passwd"] = md5($_REQUEST["passwd"]);
			}
			if( ! isset($_REQUEST["active"]) ) $_REQUEST["active"]="0";
			if( $user->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $user->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$user = new model_auth;
			$user->connect();
			$user->get_by_id( $_REQUEST["user_id"] );
			$dat = $user->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<div class="prompt">';
			echo 'Deleting user "' . $dat["username"] . '".<br>';
			echo 'OK?';
			echo '</div>';
			echo '<input type="hidden" name="del">';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="user_id" value="' . $_REQUEST["user_id"] . '">';
			echo '</form>';
		} else {
			$user = new model_auth;
			$user->connect( auth_get_writeable() );
			if( $user->del( $_REQUEST["user_id"] )==false ) {
				echo '<div class="error">' . $user->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		$list = new model_auth;
		$list->connect();
		$list->get_list();
	
		echo '<form method="POST">';
		echo '<div id="scrolllist">';
		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>username</th><th>authority</th><th>active</th><th>fullname</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			echo '<tr>';
			echo '<td><input type="radio" name="user_id" value="' . $dat["user_id"] . '"></td>';
			echo '<td' . (!$dat["active"] ? ' style="text-decoration:line-through"' : '') . '>' . $dat["username"] . '</td>';
			echo '<td>';
			$j = 0;
			foreach( $officearray as $idx => $value ) {
				if( $dat["authflag"] & (0x01<<$idx) ) {
					if( $j>0 ) echo ' / ';
					echo $value;
					echo ($dat["authflag_w"] & (0x01<<$idx) ) ? '(RW)' : '(R)';
					$j++;
				}
			}
			echo '</td>';
			echo '<td>' . ($dat["active"]==1 ? "active" : "inactive") . '</td>';
			echo '<td>' . mkstr_neat($dat["fullname"]) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';

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
