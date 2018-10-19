<?php
##illdir

function systemid($input) {
  if($input=="CVES") $system="Champlain Valley Education Services School Library System";
  if($input=="CEFL") $system="Clinton Essex Franklin Library System";
  if($input=="FEH") $system="Franklin-Essex-Hamilton School Library System";
  if($input=="JLHO") $system="Jefferson-Lewis School Library System";
  if($input=="NCLS") $system="North Country Library System";
  if($input=="NNYLN") $system="Northern New York Library Network";
  if($input=="OSW") $system="Oswego County School Library System at CiTi";
  if($input=="SLL") $system="St. Lawrence-Lewis School Library System";
  return $system;
}

function selected($input,$filter_value) {
  if ($input == $filter_value) {
    $filterout = "selected";
  } else {
    $filterout = "";
  }
  return $filterout;
}

$resultsper = 50;
$target = "illdir";

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Pull the information of the person from Drupal users
global $user;   // load the user entity so to pick the field from.
$user_contaning_field = user_load($user->uid);  // Check if we're dealing with an authenticated user
if($user->uid) {    // Get field value;
  $AuthedUser=1;
} else {
  $AuthedUser=0;
}

if ( isset($_REQUEST['system']) || isset($_REQUEST['libname']) || isset($_REQUEST['page']) ) {
  $system = (isset($_REQUEST['system']) ? $_REQUEST['system'] : "");
  $libname = (isset($_REQUEST['libname']) ? $_REQUEST['libname'] : "");
  $page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : "0");
} else {
  $system = "";
  $libname = "";
  $page = 0;
}

$libname = mysqli_real_escape_string($db,$libname);
$libemail = mysqli_real_escape_string($db,$libemail);

#Build the SQL statement
$StartSQL = "SELECT * FROM `$sealLIB` ";
$StartCountSQL = "SELECT COUNT(*) FROM `$sealLIB` ";
if ( $libname != "" ) {
  $MiddleSQL = "WHERE `Name` LIKE '%$libname%' ";
  if ($system != "" ) {
    $MiddleSQL = $MiddleSQL . "AND `system` LIKE '%$system%' ";
  }
} else {
  if ($system != "" ) {
    $MiddleSQL = "WHERE `system` LIKE '%$system%' ";
  }
}

#Get the number of total results with defined system and libnames
$WholeSQL = $StartCountSQL . $MiddleSQL;
$retwhole = mysqli_query ($db, $WholeSQL);
$rowpage = mysqli_fetch_array($retwhole, MYSQLI_NUM );
$WholeSQLCount = $rowpage[0];
$PageCount = ceil($WholeSQLCount / $resultsper);
$DisplayPage = $page + 1;
if ( $DisplayPage > $PageCount ) {
  $DisplayPage=1;
  $page=0;
}

#Get a screen full of results for the user to see.
$startrec = $page * $resultsper;
$endrec = $startrec + ($resultsper-1);
$LimitedSQL = $StartSQL . $MiddleSQL . "ORDER BY Name Asc LIMIT $startrec, $endrec";
$retlimited = mysqli_query ($db, $LimitedSQL);
echo "<h3>Search the directory</h3>";
echo "<form action='$target' method='post'>";
echo "<b>Library Name:</b> <input type='text' SIZE=60 MAXLENGTH=255  name='libname' value='$libname'><br>";
echo "<b>Library System</b> <select name='system'>";
echo "  <option value = '' " . selected("",$system) . ">All</option>";
echo "  <option value = 'CVES' " . selected("CVES",$system) . ">Champlain Valley Education Services School Library System</option>";
echo "  <option value = 'CEFL' " . selected("CEFL",$system) . ">Clinton Essex Franklin Library System</option>";
echo "  <option value = 'FEH' " . selected("FEH",$system) . ">Franklin-Essex-Hamilton School Library System</option>";
echo "  <option value = 'JLHO' " . selected("JLHO",$system) . ">Jefferson-Lewis School Library System</option>";
echo "  <option value = 'NCLS' " . selected("NCLS",$system) . ">North Country Library System</option>";
echo "  <option value = 'NNYLN' " . selected("NNYLN",$system) . ">Northern New York Library Network</option>";
echo "  <option value = 'OSW' " . selected("OSW",$system) . ">Oswego County School Library System at CiTi</option>";
echo "  <option value = 'SLL' " . selected("SLL",$system) . ">St. Lawrence-Lewis School Library System</option>";
echo "</select><br>";
echo $WholeSQLCount . " results - Page ";
echo "<select name='page'>";
for ($x = 1; $x <= $PageCount; $x++) {
  $RealPage=$x-1;
  echo "<option value = '$RealPage' " . selected($x,$DisplayPage) . ">$x</option>";
}
echo "</select>";
echo " of " . $PageCount . "<br>";
echo "<input type='submit' value='Update'> <a href='$target'>clear</a>";
echo "</form>";
echo "<table><tr>";
$count = 1;
$no = 1;
while ($row = mysqli_fetch_assoc($retlimited)) {
  $libaddress2 = $row["address2"];
  $libaddress3 = $row["address3"];
  $libname = $row["Name"];
  $libphone = $row["phone"];
  $illemail = $row["ILL Email"];
  $libparticipant = $row["participant"];
  $oclc = $row["oclc"];
  $loc = $row["loc"];
  $libsuspend = $row["suspend"];
  $system = $row["system"];
  $systemname = systemid($system);
  $book = $row["book"];
  $journal = $row["journal"];
  $av = $row["av"];
  $reference = $row["reference"];
  $ebook = $row["ebook"];
  if($libsuspend=="0") $libsuspend="Yes";
  if($libsuspend=="1") $libsuspend="No";
  if($libparticipant =="1") $libparticipant ="Yes";
  if($libparticipant =="0") $libparticipant ="No";
  if($book =="1") $book ="Yes";
  if($book =="0") $book ="No";
  if($journal =="1") $journal ="Yes";
  if($journal =="0") $journal ="No";
  if($av =="1") $av ="Yes";
  if($av =="0") $av ="No";
  if($reference =="1") $reference ="Yes";
  if($reference =="0") $reference ="No";
  if($ebook =="1") $ebook ="Yes";
  if($ebook =="0") $ebook ="No";
  if (($count % 2 == 1) && ($no %2 == 1) or  ($count % 2 == 0) && ($no %2 == 0)) {
    echo "<td class='grey'>";
  } else {
    #Begin the default 'everything list'
    echo "<td>";
  }
  echo "Name: <strong> $libname</strong><br>";
  echo "Address: <strong> $libaddress2 </strong><br>";
  echo "&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> $libaddress3 </strong><br>";
  echo "Phone: <strong> $libphone</strong><br>";
  if ( $libparticipant == "Yes" ) {
    if ( $AuthedUser == 1 ) {
      echo "ILL Email(s): <a href='mailto:$illemail' target='_blank'>$illemail</a><br>";
    }
    if ( $oclc != "" ) {
          echo "OCLC Symbol: <strong> $oclc</strong><br>";
    }
    echo "ILL Code: <strong> $loc</strong><br>";
    echo "System: <strong>$systemname</strong><br>";
    echo "Accepting Requests: <strong> $libsuspend </strong>";
    echo "<br><br>";
    echo "<button onclick='showHide($count)'>Show loaning options</button>";
    echo "<span class='loadoptions' id='showhide-$count' style='display: none'>";
    echo "Loaning Books: <strong>$book</strong><br>";
    echo "Loaning Journals or Articles: <strong>$journal</strong><br>";
    echo "Loaning Audio/Video: <strong>$av</strong><br>";
    echo "Loaning Reference: <strong>$reference</strong><br>";
    echo "Loaning E-Books: <strong>$ebook</strong><br><br>";
    echo "</span>";
  } else {
    echo "System: <strong>$systemname</strong><br>";
    echo "DueNorth non-participant<br><br><br><br><br>";
  }
  echo "</td>";
  if ($count++ % 2 == 0){
    echo "</tr><tr>";
    $no++;
  }
}
echo "</table>";


?>
