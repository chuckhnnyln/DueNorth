<?php
//This script cleans out the Borrower and Lender tasks screen for the folks
//that don't use them regularly.

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
