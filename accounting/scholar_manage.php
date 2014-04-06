<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/scholarship.inc");
	auth_check( AUTH_SCHOLARSHIP );

$val = array(
	"title"	=> "Title",
	"tuition_deduction_rate"	=> "Tuition deduction rate",
	"tuition_deduction_amount"	=> "Tuition deduction amount",
	"flag"						=> "Totally free"
);

function print_edit_form( $dat )
{
	global $val;

	echo "<table border=\"1\">";
	foreach( $val as $idx => $name ) {
		echo "<tr>";
		if( $idx=="tuition_deduction_rate" ) {
			echo "<td>" . $name . "</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"${dat[$idx]}\" size=\"4\">% (100=free)</td>";
		} else if( $idx=="tuition_deduction_amount" ) {
			echo "<td>" . $name . "</td>";
			echo "<td><input class=\"peso\" type=\"text\" name=\"${idx}\" value=\"" . mkstr_peso($dat[$idx]) . "\"></td>";
		} else if( $idx=="flag" ) {
			echo "<td> Other </td>";
			echo "<td><input type=\"checkbox\" name=\"${idx}[]\" value=\"" . SCHOLARFLAG_TOTALLYFREE . "\"" . ($dat[$idx] & SCHOLARFLAG_TOTALLYFREE ? " checked" : "") . ">$name</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td><input type=\"text\" name=\"${idx}\" value=\"${dat[$idx]}\" size=\"40\"></td>";
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
		if( $idx=="tuition_deduction_rate" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . $dat[$idx] . "%</td>";
		} else if( $idx=="tuition_deduction_amount" ) {
			echo "<td>" . $name . "</td>";
			echo "<td>" . mkstr_peso($dat[$idx]) . "</td>";
		} else if( $idx=="flag" ) {
			echo "<td> Other </td>";
			echo "<td>";
			if( $dat[$idx] & SCHOLARFLAG_TOTALLYFREE ) echo $name;
			echo "&nbsp;</td>";
		} else {
			echo "<td>" . $name . "</td>";
			echo "<td>" . $dat[$idx] . "</td>";
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
<title> Scholarship </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION['sy_id'];
	print_title( get_office_name($_SESSION["office"]), "Scholarship definition", lookup_schoolyear($sy_id) );

	echo "<form method=\"POST\">";

	if( (! isset($_REQUEST["scholartype_id"])) && (! isset($_REQUEST["add"])) ) {
		$result = false;
		$list = new model_scholartype;
		$list->connect();
		$result = $list->get_list($sy_id);
		if( $result != false ) {
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<th>Title</th><th>Tuition deduction rate</th><th>Tuition deduction amount</th><th>Other</th>";
			echo "</tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"scholartype_id\" value=\"%s\"></td>", $dat["scholartype_id"] );
				printf( "<td>%s</td> <td align=\"right\">%s%%</td> <td class=\"peso\">%s</td> <td>%s</td>\n",
					mkstr_neat($dat["title"]),
					mkstr_neat($dat["tuition_deduction_rate"]),
					mkstr_peso($dat["tuition_deduction_amount"]),
					($dat["flag"] & SCHOLARFLAG_TOTALLYFREE ? "Totally Free" : "&nbsp;")
				);
				printf( "</tr>" );
			}
			echo "</table>";
			echo '</div>';
			if( $list->get_numrows()>0 ) {
				echo "<input type=\"submit\" name=\"edit\" value=\"Edit\">";
				echo "<input type=\"submit\" name=\"del\" value=\"Delete\"><br>";
			}
		}
		$list->close();

		echo "<input type=\"submit\" name=\"add\" value=\"Add new scholar type\">";
	} else if( isset($_REQUEST["add"]) ) {
		if( !isset($_REQUEST["confirm"]) ) {
			echo '<div class="prompt">Enter scholar type details</div>';
			print_edit_form(null);
			echo '<input type="submit" name="add" value="Add">';
			echo '<input type="hidden" name="confirm">';
		} else {
			$data = new model_scholartype();
			$data->connect( auth_get_writeable() );
			$ar = array(
				"sy_id" => $sy_id,
				"title" => $_REQUEST["title"],
				"tuition_deduction_rate" => $_REQUEST["tuition_deduction_rate"],
				"tuition_deduction_amount" => retrieve_peso($_REQUEST["tuition_deduction_amount"]),
				"flag" => '0'
			);
			if( isset($_REQUEST["flag"]) ) {
				foreach( $_REQUEST["flag"] as $idx=>$val ) $ar["flag"] |= $val;
			}
			if( $data->add( $ar )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$data = new model_scholartype();
		$data->connect( auth_get_writeable() );
		if( !isset($_REQUEST["confirm"]) ) {
			if( $data->get_by_id($_REQUEST["scholartype_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				$scholartype = $data->get_fetch_assoc(0);
				echo '<div class="prompt">Edit scholartype details</div>';
				print_edit_form($scholartype);
				echo '<input type="submit" name="edit" value="Update">';
				echo '<input type="hidden" name="confirm">';
				echo '<input type="hidden" name="scholartype_id" value="' . $scholartype["scholartype_id"] . '">';
			}
		} else {
			$ar = array(
				"scholartype_id" => $_REQUEST["scholartype_id"],
				"title" => $_REQUEST["title"],
				"tuition_deduction_rate" => $_REQUEST["tuition_deduction_rate"],
				"tuition_deduction_amount" => retrieve_peso($_REQUEST["tuition_deduction_amount"]),
				"flag" => '0'
			);
			if( isset($_REQUEST["flag"]) ) {
				foreach( $_REQUEST["flag"] as $idx=>$val ) $ar["flag"] |= $val;
			}
			if( $data->update( $ar )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		$data->close();
	} else if( isset($_REQUEST["del"]) ) {
		$data = new model_scholartype();
		$data->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["confirm"]) ) {
			if( $data->get_by_id($_REQUEST["scholartype_id"])==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				$scholartype = $data->get_fetch_assoc(0);
				echo '<div class="prompt">Delete following data?</div>';
				print_edit_info($scholartype);
				echo '<input type="hidden" name="scholartype_id" value="' . $_REQUEST["scholartype_id"] . '">';
				echo '<input type="submit" name="del" value="Delete">';
				echo '<input type="hidden" name="confirm">';
			}
		} else {
			if( $data->del( $_REQUEST["scholartype_id"] )==false ) {
				echo '<div class="error">' . $data->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
		$data->close();
	}

	echo "</form>";

	print_footer();

	if( (!isset($_REQUEST["scholartype_id"])) && (!isset($_REQUEST["add"])) ) {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		print_hidden( array('sy_id'=>$sy_id) );
		echo "</form>";
	} else {
		echo "<form method=\"POST\" id=\"goback\">";
		echo " <input type=\"submit\" value=\"Go back\">";
		echo "</form>";
	}
?>

</body>

</html>
