<?php



$regulartimes = array(
	array( 0*60*24+8*60,	0*60*24+12*60 ),	// M  8-12
	array( 0*60*24+13*60,	0*60*24+17*60 ),	// M  12-17
	array( 1*60*24+8*60,	1*60*24+12*60 ),	// T  8-12
	array( 1*60*24+13*60,	1*60*24+17*60 ),	// T  12-17
	array( 2*60*24+8*60,	2*60*24+12*60 ),	// W  8-12
	array( 2*60*24+13*60,	2*60*24+17*60 ),	// W  12-17
	array( 3*60*24+8*60,	3*60*24+12*60 ),	// TH 8-12
	array( 3*60*24+13*60,	3*60*24+17*60 ),	// TH 12-17
	array( 4*60*24+8*60,	4*60*24+12*60 ),	// F  8-12
	array( 4*60*24+13*60,	4*60*24+17*60 ),	// F  12-17
);



//===============================================
//  print time schedule table
//    $list must be model_classschedule and list must have been
//    retrieved before calling this function
//    If $caption is set, it will produce <caption>tag.
//    If $displink is true, it will produce link to each class details.
//===============================================
function print_classschedule_table( $list, $caption=null, $displink=true )
{
	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	// set borders (8:00,9:00,...)
	$border_time = array();
	for( $j=8; $j<20; $j++ ) $border_time[$j*60] = 1;
	for( $i=0; $i<7; $i++ ) {
		$wday_rowspan[$i] = array();
		$wday_schedule[$i] = array();
	}
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$sch = $list->get_fetch_assoc($i);
		$time_st = $sch["time_st"];
		$wday = intval($time_st / (24*60));
		$min = $time_st % (24*60);
		$wday_schedule[$wday][$min][] = $sch;
		if( ! $border_time[$min] ) $border_time[$min] = 0;	// adding border
		$time_end = $sch["time_end"];
		$min = $time_end % (24*60);
		$wday_schedule[$wday][$min][] = '';
		if( ! $border_time[$min] ) $border_time[$min] = 0;	// adding border
	}
	ksort( $border_time );

	// Calculate rowspan value
	reset( $border_time );
	foreach( $border_time as $tm=>$v ) {
		if( ($tm % 60)!=0 && isset($pre_border) ) {
			$border_time[$pre_border]++;
		} else {
			$pre_border = $tm;
			$border_time[$tm] = 1;
		}
		for( $wday=0; $wday<7; $wday++ ) {
			if( count($wday_schedule[$wday][$tm])>0 ) {
				$edited = false;
				foreach( $wday_schedule[$wday][$tm] as $i=>$dat ) {
					if( $dat=='' ) {
						$nest[$wday]--;
						if( $nest[$wday]==0 ) {
							$prev_tm[$wday] = 0;
							$edited = true;
						}
					} else {
						if( $nest[$wday]==0 ) {
							$prev_tm[$wday] = $tm;
							$wday_rowspan[$wday][$tm] = 1;
							$edited = true;
						}
						$wday_contents[$wday][$prev_tm[$wday]][] = $dat;
						$nest[$wday]++;
					}
				}
				if( ! $edited ) $wday_rowspan[$wday][$prev_tm[$wday]]++;
			} else if( $prev_tm[$wday]>0 ) {
				$wday_rowspan[$wday][$prev_tm[$wday]]++;
			}
		}
	}

	// output table
	reset( $border_time );
	echo '<table border="1" class="timetable">';
	if( $caption!=null ) echo "<caption>$caption</caption>";
	echo '<tr><th>&nbsp;</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>';
	foreach( $border_time as $tm=>$v ) {
		echo '<tr>';
		if( $v > 0 ) {
			printf( '<td rowspan=%d align="right">%d:%02d</td>', $v, intval($tm/60-1)%12+1, $tm%60 );
		}
		for( $wday=0; $wday<7; $wday++ ) {
			if( $row[$wday]>0 ) $row[$wday]--;
			if( isset($wday_rowspan[$wday][$tm]) ) {
				$row[$wday] = $wday_rowspan[$wday][$tm];
				echo '<td class="class"' . ($row[$wday]>1 ? (' rowspan="' . $row[$wday] . '"') : ("") ) . '>';
				foreach( $wday_contents[$wday][$tm] as $i=>$dat ) {
					$sec = "";

					if( $displink ) echo '<a href="list_classstudent.php?sy_id=' . $dat["sy_id"] . '&class_id=' . $dat["class_id"] . '" target="_blank">';
						if ($dat["section_flag"]==1){
						   $sec = " - A";	
						}elseif ($dat["section_flag"]==2) {
						   $sec = " - B";
						}elseif ($dat["section_flag"]==3) {
						   $sec = " - C";
						}elseif ($dat["section_flag"]==4) {
						   $sec = " - D";
						}elseif ($dat["section_flag"]==5) {
						   $sec = " - E";
						}elseif ($dat["section_flag"]==6) {
						   $sec = " - F";
						}

						echo $dat["subject_code"] . $sec;

					if( $displink ) echo '</a>';
					echo '<br>';
				}
				echo '</td>';
			} else if( $row[$wday]==0 ) {
				echo '<td>&nbsp;</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
}



//===============================================
//  calculate total time of the schedule
//    $list must be model_classschedule and list must have been
//    retrieved before calling this function
//
//    return values will be array of regular_time and extra_time
//===============================================
function calc_classschedule_total_time( $list )
{
	$prev_end = 0;
	$total_regular = 0;
	$total_irregular = 0;
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$sch = $list->get_fetch_assoc($i);
		$time_st = $sch["time_st"];
		$time_end = $sch["time_end"];
		if( $time_st < $prev_end ) {
			// Schedule conflicting! Let's not count twice.
			if( $time_end <= $prev_end ) {
				// Its included. No additional time
			} else {
				list($reg,$ireg) = calc_regular_irregular( $prev_end, $time_end );
				$total_regular += $reg;
				$total_irregular_time += $ireg;
			}
		} else {
			list($reg,$ireg) = calc_regular_irregular( $time_st, $time_end );
			$total_regular += $reg;
			$total_irregular_time += $ireg;
		}
		$prev_end = $time_end;
	}

	return array( $total_regular,$total_irregular_time );
}

function calc_regular_irregular( $time_st, $time_end )
{
	global $regulartimes;
	$regular = 0;
	foreach( $regulartimes as $dat ) {
		if( $time_st<$dat[1] && $dat[0]<$time_end ) {
			if( $time_st < $dat[0] ) {
				if( $time_end < $dat[1] ) {
					$regular += $time_end - $dat[0];
				} else {
					$regular += $dat[1] - $dat[0];
				}
			} else {
				if( $time_end < $dat[1] ) {
					$regular += $time_end - $time_st;
				} else {
					$regular += $dat[1] - $time_st;
				}
			}
		}
	}
	return array( $regular,($time_end-$time_st)-$regular );
}



?>
