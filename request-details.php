<?php
#Request details
$target = "testing";

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Pull the information of the person making the request from Drupal users
global $user;   // load the user entity so to pick the field from.
$user_fields = user_load($user->uid);  // Check if we're dealing with an authenticated user
if($user->uid) {    // Get field value;
  $userarray = field_get_items('user', $user_fields, 'field_loc_location_code');
  $item = array_shift($userarray);
  $userloc = $item['value'];
}

$illNUB = (isset($_GET['illNUB']) ? $_GET['illNUB'] : "");

if ( strlen($illNUB) > 2 ){
  $sqlget = "SELECT * FROM seal.`SENYLRC-SEAL2-STATS` where `illNUB`= '$illNUB'";
  $RequestDetails = mysqli_query($db,$sqlget);
  while ($row = mysqli_fetch_assoc($RequestDetails)) {
    $title = $row["Title"];
    $author = $row["Author"];
    $pubdate = $row["pubdate"];
    $reqisbn = $row["reqisbn"];
    $reqissn = $row["reqissn"];
    $itype = $row["Itype"];
    $callnum = $row["Call Number"];
    $location = $row["Location"];
    $availability = $row["Available"];
    $article = $row["article"];
    $destloc = $row["Destination"];
    $destsystem = $row["DestSystem"];
    $saddress = $row["saddress"];
    $caddress = $row["caddress"];
    $reqloc = $row["Requester LOC"];
    $reqphone = $row["requesterPhone"];
    $reqnote = $row["reqnote"];
    $lendnote = $row["responderNOTE"];
    $reqsystem = $row["ReqSystem"];
    $needby = $row["needbydate"];
    $lenderprivate = $row["LenderPrivate"];
    $reqp = $row["Requester person"];
    $reql = $row["Requester lib"];
    $reqemail = $row["requesterEMAIL"];
    $timestamp = $row["Timestamp"];
    $fill = $row["Fill"];
    if($fill=="1") $fillstatus="Yes Fill";
    if($fill=="0") $fillstatus="No Fill";
    if($fill=="3") $fillstatus="Waiting Response";
    if($fill=="4") $fillstatus="Expired";
    if($fill=="6") $fillstatus="Canceled";
  }
  $sqlget = "SELECT * FROM seal.`$sealLIB` where `loc`= '$destloc'";
  $lenderdetails = mysqli_query($db,$sqlget);
  while ($row = mysqli_fetch_assoc($lenderdetails)) {
    $lendername = $row["Name"];
    $lenderillemail = $row["ILL Email"];
    $lenderphone = $row["phone"];
    $lenderaddress1 = $row["address1"];
    $lenderaddress2 = $row["address2"];
    $lenderaddress3 = $row["address3"];
  }

  echo "<b>Request Number:</b> " . $illNUB;
  echo "<br><b>Status:</b> " . $fillstatus;
  echo "<br><b>Title:</b> " . $title;
  echo "<br><b>Author:</b> " . $author;
  echo "<br><b>Item Type:</b> " . $itype;
  echo "<br><b>Publication Date:</b> " . $pubdate;
  if (strlen($reqisbn)>2) {echo "<br><b>ISBN: </b>" . substr($reqisbn,7);}
  if (strlen($reqissn)>2) {echo "<br><b>ISSN: </b>" . substr($reqissn,7);}
  echo "<br><b>Call Number:</b> " . $callnum;
  echo "<br><b>Location:</b> " . $location;
  echo "<br><b>Availability (at time of request):</b> " . $availability;
  echo "<br><b>Need by date:</b> " . $needby;
  echo "<br><b>Timestamp: </b>" . $timestamp;
  echo "<br>";
  echo "<table><tr><th width='50%'>Borrower Information</th><th width='50%'>Lender Information</th></tr>";
  echo "<tr><td valign='top'>";
  echo "<i>$reql</i>";
  echo "<br>" . $saddress;
  echo "<br>" . $caddress;
  echo "<br>";
  echo "<br>ILL Code: " . $reqloc;
  echo "<br>Library System: " . $reqsystem;
  echo "<br>User Requesting: " . $reqp;
  echo "<br>Phone: " . $reqphone;
  echo "<br>Email: <a href='mailto:" . $reqemail . "?Subject=NOTE Request ILL " . $illNUB . "' target='_blank'>" . $reqemail . "</a>";
  echo "</td>";
  echo "<td valign='top'>";
  echo "<i>$lendername</i>";
  if (strlen($lenderaddress1)>2) {echo "<br>$lenderaddress1";}
  if (strlen($lenderaddress2)>2) {echo "<br>$lenderaddress2";}
  if (strlen($lenderaddress3)>2) {echo "<br>$lenderaddress3";}
  echo "<br>";
  echo "<br>ILL Code: " . $destloc;
  echo "<br>Library System: " . $destsystem;
  echo "<br>Phone: " . $lenderphone;
  echo "<br>ILL Email: <a href='mailto:" . $lenderillemail . "?Subject=NOTE Request ILL " . $illNUB . "' target='_blank'>" . $lenderillemail . "</a>";
  echo "</td></tr>";
  echo "<tr><td valign='top'>";
  echo "<br>Public Note: " . $reqnote;
  echo "</td>";
  echo "<td valign='top'>";
  echo "<br>Public Note: " . $lendnote;
  if (strcasecmp($userloc, $destloc) == 0 ) {echo "<br>Private Note: " . $lenderprivate;}
  echo "</td></tr>";
  echo "</table>";
  #echo "<br>" . $article;
} else {
  echo "Nothing to do!";
}

?>
