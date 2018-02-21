<?php

function getWorkingDays($startDate,$endDate,$holidays){
  // do strtotime calculations just once
  $endDate = strtotime($endDate);
  $startDate = strtotime($startDate);
  //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
  //We add one to inlude both dates in the interval.
  $days = ($endDate - $startDate) / 86400 + 1;

  $no_full_weeks = floor($days / 7);
  $no_remaining_days = fmod($days, 7);

  //It will return 1 if it's Monday,.. ,7 for Sunday
  $the_first_day_of_week = date('N', $startDate);
  $the_last_day_of_week = date('N', $endDate);

  //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
  //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.

  if ($the_first_day_of_week <= $the_last_day_of_week) {
    if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
    if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
  } else {
    // (edit by Tokes to fix an edge case where the start day was a Sunday
    // and the end day was NOT a Saturday)
    // the day of the week for start is later than the day of the week for end
    if ($the_first_day_of_week == 7) {
      // if the start date is a Sunday, then we definitely subtract 1 day
      $no_remaining_days--;
      if ($the_last_day_of_week == 6) {
        // if the end date is a Saturday, then we subtract another day
        $no_remaining_days--;
      }
    } else {
      // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
      // so we skip an entire weekend and subtract 2 days
      $no_remaining_days -= 2;
    }
  }
  //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
  //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
  $workingDays = $no_full_weeks * 5;
  if ($no_remaining_days > 0 ) $workingDays += $no_remaining_days;
  //We subtract the holidays
  foreach($holidays as $holiday){
      $time_stamp=strtotime($holiday);
      //If the holiday doesn't fall in weekend
      if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7) $workingDays--;
  }
  return $workingDays;
} #End getWorkingDays function

$holidays=array("2017-09-04","2017-10-09","2017-11-10","2017-11-23","2017-12-25","2018-01-01","2018-01-15","2018-02-19","2018-05-28","2018-07-04","2018-09-03","2018-10-08","2018-11-12","2018-11-22","2018-12-25","2019-01-01","2019-01-21","2019-02-18","2019-05-27","2019-09-02","2019-10-14","2019-11-11","2019-11-28","2019-12-25");

#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$sqlselect="select * from `SENYLRC-SEAL2-STATS` where emailsent='0' and fill='3'";
$retval = mysqli_query($db,$sqlselect);
$GETLISTCOUNT = mysqli_num_rows ($retval);

while ($row = mysqli_fetch_assoc($retval)) {
	$timestamp	= $row["Timestamp"];
	$destination = $row["Destination"];
  if ($destination == "") continue;
	$illnum = $row["illNUB"];
	$title = $row["Title"];
	$author = $row["Author"];
	$itype = $row["Itype"];
	$pubdate = $row["pubdate"];
  $isbn = $row["reqisbn"];
  $issn = $row["reqissn"];
	$itemavail = $row["Available"];
	$article = $row["article"];
	$inst = $row["Requester lib"];
	$address = $row["saddress"];
	$caddress = $row["caddress"];
	$needbydatet = $row["needbydate"];
  $reqnote = $row["reqnote"];
	$fname = $row["Requester person"];
	$email = $row["requesterEMAIL"];
	$wphone = $row["requesterPhone"];
	#Get just the date from time stamp
	$reqdate = substr($timestamp, 0, 10);
	#Calculate date what three days from request is
  $calenddate= date( "Y-m-d", strtotime( "$reqdate +3 day" ) );
	$nubworkdays= getWorkingDays($reqdate,$calenddate,$holidays);
	if ($nubworkdays < '3'){
		$diff =  3 - $nubworkdays;
		$calenddate= date( "Y-m-d", strtotime( "$calenddate  +$diff day" ) );
	} else {
		$diff='0';
	}
	$today = date("Y-m-d");
	#Get the Destination
  $GETLISTSQLDESTEMAIL="SELECT `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc = '$destination'";
  $resultdestemail=mysqli_query($db, $GETLISTSQLDESTEMAIL);
  while ($rowdesteamil = mysqli_fetch_assoc($resultdestemail)) {
    $destemail=$rowdesteamil["ILL Email"];
  }
	$destemailarray = explode(';', $destemail);
	if ($calenddate < $today) {
    #SETUP email
	  #Well set these to white space if they are empty to prevent an error message
		if ( empty($needbydatet)) $needbydatet='';
		if ( empty($reqnote)) $reqnote='';
	  if ( empty($isbn)) $isbn='';
		if ( empty($issn)) $issn='';
		if ( empty($arttile)) $article='';
		#Copy of message sent to the requester
		$messagereq = "An ILL request ($illnum) has been created for the following: <br><br>
		Title: $title<br>
		Author: $author<br>
		Item Type: $itype<br>
		Publication Date: $pubdate<br>
		$isbn<br>
		$issn<br>
		Call Number: $itemcall<br>
		Availability Status: $itemavail<br>
		$article<br><br>
		The title is requested by the following library:<br>
		$inst<br>
		$address<br>$caddress<br><br>
		$needbydatet<br>
		$reqnote<br>
		The request was created by:<br>
		$fname $lname<br>
		$email<br>
		$wphone<br>
		<br>";

		#Message for the destination library
		$messagedest = "An ILL request ($illnum) has been created for the following: <br><br>
		Title: $title <br>
		Author: $author<br>
		Item Type: $itype<br>
		Publication Date: $pubdate<br>
		$isbn<br>
 		$issn<br>
		Call Number: $itemcall <br>
		Availability Status: $itemavail<br>
		$article<br><br><br>
		The title is request to delivered to the following institution:<br>
		$inst<br>
		$address<br>$caddress<br><br>
		$needbydatet<br>
		$reqnote<br>
		The request was created by:<br>
		$fname $lname<br>
		$email<br>
		$wphone<br>
		<br>
		Will you fill this request?  <a href='https://duenorth.nnyln.org/respond?num=$illnum&a=1' >Yes</a> or  <a href='https://duenorth.nnyln.org/respond?num=$illnum&a=0' >No</a>
		<br>";

		#Set email subject for request
		$subject = "REMINDER ILL Request from $inst ILL# $illnum";

    #SEND EMAIL to Detestation Library with DKIM sig
    $email_to = implode(',', $destemailarray);
    $headers = "From: DueNorth <duenorth@nnyln.org>\r\n" ;
    $headers .= "Reply-to: " . $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    mail($email_to, $subject, $messagedest, $headers);

    #SEND a copy of EMAIL to requester with DKIM sig
    $headers = "From: DueNorth <duenorth@nnyln.org>\r\n" ;
    $headers .= "Reply-to: " . $email_to . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
    $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
    mail($email, $subject, $messagereq, $headers);

		$sqlupdate = "UPDATE `SENYLRC-SEAL2-STATS` SET `emailsent` = '2' , `responderNOTE` =  'REMINDER MSG Sent' WHERE `illNUB` = '$illnum'";
		mysqli_query($db, $sqlupdate);
	}
}
?>
