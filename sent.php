<?php

###sent.php###

$fname=$_REQUEST["fname"];
$lname=$_REQUEST["lname"];

$email=$_REQUEST["email"];
$inst=$_REQUEST["inst"];
$address=$_REQUEST["address"];
$caddress=$_REQUEST["caddress"];
$wphone=$_REQUEST["wphone"];

if (isset($_REQUEST['reqLOCcode'])) $reqLOCcode = $_REQUEST['reqLOCcode'];
if (isset($_REQUEST['bibauthor'])) $author = $_REQUEST['bibauthor'];
if (isset($_REQUEST['bibtitle'])) $title = $_REQUEST['bibtitle'];
if (isset($_REQUEST['destination'])) $destination = $_REQUEST['destination'];
if (isset($_REQUEST['bibtype'])) $itype = $_REQUEST['bibtype'];
if (isset($_REQUEST['pubdate'])) $pubdate = $_REQUEST['pubdate'];
if (isset($_REQUEST['isbn'])) $isbn = $_REQUEST['isbn'];
if (isset($_REQUEST['issn'])) $issn = $_REQUEST['issn'];
if (isset($_REQUEST['needbydate'])) $needbydate = $_REQUEST['needbydate'];
if (isset($_REQUEST['reqnote'])) $reqnote = $_REQUEST['reqnote'];

#Pull all the articles files and then combine into one variable
if (isset($_REQUEST['arttile'])) $arttile = $_REQUEST['arttile'];
if (isset($_REQUEST['artauthor'])) $artauthor = $_REQUEST['artauthor'];
if (isset($_REQUEST['artissue'])) $artissue = $_REQUEST['artissue'];
if (isset($_REQUEST['artvolume'])) $artvolume = $_REQUEST['artvolume'];
if (isset($_REQUEST['artpage'])) $artpage = $_REQUEST['artpage'];
if (isset($_REQUEST['artmonth'])) $artmonth = $_REQUEST['artmonth'];
if (isset($_REQUEST['artyear'])) $artyear = $_REQUEST['artyear'];
if (isset($_REQUEST['artcopyright'])) $artcopyright = $_REQUEST['artcopyright'];
$article="Article Title: ". $arttile ." <br>Article Author: ". $artauthor ." <br>Volume: ". $artvolume ."<br>Issue: ". $artissue ."<br> Pages: ". $artpage ." <br>Year: ".$artyear." <br>Month:  ".$artmonth."<br>Copyright: ".$artcopyright." ";
if (strlen($needbydate)>0)  $needbydatet="This item is needed by $needbydate";
if (strlen($reqnote)>0)  $reqnote="Note: $reqnote";
if (strlen($isbn)>2)  $isbn="ISBN: $isbn";
if (strlen($issn)>2)  $issn="ISSN: $issn";

#Requesting person library system, used for stats

#Pull the information of the person making the request
global $user;   // load the user entity so to pick the field from.
$user_contaning_field = user_load($user->uid);  // Check if we're dealing with an authenticated user
if($user->uid) {    // Get field value;
$field_home_library_system =   field_get_items('user', $user_contaning_field, 'field_home_library_system');
$reqsystem=$field_home_library_system[0]['value'];
}

#Split Destination contents
list($libcode,$library,$destsystem, $itemavail, $itemcall, $itemlocation, $destemail, $destloc) = explode(":", $destination);

#Put the dest email in an array in case the library has more than one person who gets the message
$destemailarray = explode(';', $destemail);

#Check to see if data was posted to forum
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

#Insert request into Database
$today = date('Y-m-d H:i:s');

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Add escape for title, author, call number, Library name, and Requester Name
$ititle = mysqli_real_escape_string($db, $title);
$article = mysqli_real_escape_string($db, $article);
$iauthor = mysqli_real_escape_string($db, $author);
$pubdate = mysqli_real_escape_string($db, $pubdate);
$isbn = mysqli_real_escape_string($db, $isbn);
$issn = mysqli_real_escape_string($db, $issn);
$itemcall = mysqli_real_escape_string($db, $itemcall);
$itemlocation = mysqli_real_escape_string($db, $itemlocation);
$itype = mysqli_real_escape_string($db, $itype);
$itemavail = mysqli_real_escape_string($db, $itemavail);
$inst = mysqli_real_escape_string($db, $inst);
$fname = mysqli_real_escape_string($db, $fname);
$lname = mysqli_real_escape_string($db, $lname);
$email = mysqli_real_escape_string($db, $email);
$library = mysqli_real_escape_string($db, $library);
$needbydate = mysqli_real_escape_string($db, $needbydate);
$reqnote = mysqli_real_escape_string($db, $reqnote);
$destloc = mysqli_real_escape_string($db, $destloc);
$reqLOCcode = mysqli_real_escape_string($db, $reqLOCcode);
$wphone = mysqli_real_escape_string($db, $wphone);
$saddress = mysqli_real_escape_string($db, $address);
$caddress = mysqli_real_escape_string($db, $caddress);

$destloc = trim($destloc);
$reqLOCcode = trim($reqLOCcode);

#The SQL statement to insert for Stats and to recall if needed in the future
$sql = "INSERT INTO `seal`.`SENYLRC-SEAL2-STATS` (`illNUB`,`Title`,`Author`,`pubdate`,`reqisbn`,`reqissn`,`itype`,`Call Number`,`Location`,`Available`,`article`,`needbydate`,`reqnote`,`Destination`,`DestSystem`,`Requester lib`,`Requester LOC`,`ReqSystem`,`Requester person`,`requesterEMAIL`,`Timestamp`,`Fill`,`responderNOTE`,`requesterPhone`,`saddress`,`caddress`)
VALUES ('0','$ititle','$iauthor','$pubdate','$isbn','$issn','$itype','$itemcall','$itemlocation','$itemavail','$article','$needbydate','$reqnote','$destloc','$destsystem','$inst','$reqLOCcode','$reqsystem','$fname $lname','$email','$today','3','','$wphone','$saddress','$caddress')";

if (mysqli_query($db, $sql)) {
  #Get the SQL id and create a ILL Number
  $sqlidnumb= mysqli_insert_id($db);
  $yearid=date('Y');
  $illnum="$yearid-$sqlidnumb";
  $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `illNUB` =  '$illnum' WHERE `index` = $sqlidnumb";

  #This will generate the web page response
  echo "Your ILL number is $illnum, your request has been emailed to $library for the following<br>
  Title: $title.<br>
  Author: $author<br>
  Publication Date: $pubdate<br><br>
  A copy of this request has also been emailed to the requester $fname $lname at $email " ;
  mysqli_query($db, $sqlupdate);

  #SETUP email
  #We'll set these to white space if they are empty to prevent an error message
  if ( empty($needbydatet)) $needbydatet='';
  if ( empty($reqnote)) $reqnote='';
  if ( empty($arttile)) $article='';

  #Copy of message sent to the requester
  $messagereq = "An ILL request ($illnum) has been created for the following: <br><br>
  Title: $title <br>
  Author: $author<br>
  Item Type: $itype<br>
  Publication Date: $pubdate<br>
  $isbn<br>
  $issn<br>
  Call Number: $itemcall <br>
  Availability Status: $itemavail<br>
  Location: $itemlocation<br>
  $article<br><br>
  <a href='https://duenorth.nnyln.org/cancel?num=$illnum&a=3' >Do you need to cancel this request? </a>
  <br><br>
  The title is requested by the following library:<br>
  $inst<br>
  $address<br>
  $caddress<br><br>
  $needbydate<br>
  $reqnote<br> <br><br>
  The request was created by:<br>
  $fname $lname<br>
  $email<br>
  $wphone<br>";

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
  Location: $itemlocation<br>
  $article<br><br><br>
  The title is requested by the following library:<br>
  $inst<br>
  $address<br>
  $caddress<br><br>
  $needbydate<br>
  $reqnote<br>
  The request was created by:<br>
  $fname $lname<br>
  $email<br>
  $wphone<br><br>
  Will you fill this request?  <a href='https://duenorth.nnyln.org/respond?num=$illnum&a=1' >Yes</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='https://duenorth.nnyln.org/respond?num=$illnum&a=0' >No</a><br>";

  #Set email subject for request
  $subject = "ILL Request from $inst ILL# $illnum";

  #SEND EMAIL to Detestation Library with DKIM Signature
  $email_to = implode(',', $destemailarray);
  $headers =
  'MIME-Version: 1.0
  From: "DueNorth" <duenorth@nnyln.org>
  Content-type: text/html; charset=utf8';

  $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
  $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
  mail($email_to, $subject, $messagedest, $headers);

  #SEND a copy of EMAIL to requester with DKIM sig
  $headers =
  'MIME-Version: 1.0
  From: "DueNorth" <duenorth@nnyln.org>
  Content-type: text/html; charset=utf8';
  #DKIM Signature to destination
  $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
  $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
  mail($email, $subject, $messagereq, $headers);

  #Ask the requester if they would like to do another request
  echo "<br><br><a href='https://duenorth.nnyln.org'>Would you like to do another request?<a><br>";
} else {
  #Something happened and could not create a request
  echo "Error: " . $sql . "<br>" . mysqli_error($db);
  echo "Unable to create request due a technical issue, if this happens again please contact NNYLN Tech Support";
}
mysqli_close($db);
}
?>
