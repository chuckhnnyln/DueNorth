<?php
###requesthistory.php###
if (isset($_GET['loc'])){  $loc = $_GET['loc'];  }else{$loc='null';}

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Sanitize data
$loc = mysqli_real_escape_string($db,$loc);
$GETLISTSQL="SELECT * FROM `$sealSTAT` WHERE `Requester LOC` LIKE  '$loc' ORDER BY `index`  DESC";
$retval = mysqli_query($db, $GETLISTSQL);
$GETLISTCOUNTwhole = mysqli_num_rows ($retval);
$rec_limit = 25;
$row = mysqli_fetch_array($retval, MYSQLI_NUM );
$rec_count = $GETLISTCOUNTwhole;
if( isset($_GET{'page'} ) ) {
  $page = $_GET{'page'} + 1;
  $offset = $rec_limit * $page;
} else {
  $page = 0;
  $offset = 0;
}
$left_rec = $rec_count - ($page * $rec_limit);
$GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
$GETLIST = mysqli_query($db,$GETLISTSQL);
$GETLISTCOUNT = mysqli_num_rows ($GETLIST);
#List All Libraries
echo "$GETLISTCOUNTwhole results<bR>";
echo "<table><tr><TH width='20'>ILL #</th><th>Title</th><th>Author</th><th>Type</th><th>Note</th><th>Lender Note</th><th>Need By</th><th>Destination</th><th>Destination Email</th><th>Requester</th><th>Timestamp</th><th>Fill</th><th>Action</th></tr>";
$rowtype=1;
while ($row = mysqli_fetch_assoc($GETLIST)) {
  $illNUB = $row["illNUB"];
  $title = $row["Title"];
  $author = $row["Author"];
  $itype = $row["Itype"];
  $reqnote = $row["reqnote"];
  $lendnote = $row["responderNOTE"];
  $needby = $row["needbydate"];
  $dest = $row["Destination"];
  $reqp = $row["Requester person"];
  $reql = $row["Requester lib"];
  $timestamp = $row["Timestamp"];
  $fill = $row["Fill"];
  $fillnumb = $row["Fill"];
  if($fill=="1") $fill="Yes";
  if($fill=="0") $fill="No";
  if($fill=="3") $fill="No Answer";
  if($fill=="4") $fill="Expired";
  if($fill=="6") $fill="Canceled";
  $dest=trim($dest);
  #Get the Destination Name
  if (strlen($dest)>2){
    $GETLISTSQLDEST="SELECT`Name`,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$dest'  limit 1";
    $resultdest=mysqli_query($db, $GETLISTSQLDEST);
    while ($rowdest = mysqli_fetch_assoc($resultdest)) {
      $dest=$rowdest["Name"];
      $destemail=$rowdest["ILL Email"];
    }
  }else{
    $dest="Error No Library Selected";
  }
  if ( $rowtype & 1 ) {
    $rowclass="odd";
  } else {
    $rowclass="even";
  }
  if ($fillnumb == 3 ){
    #Only show cancel button if request has not been answered
    echo "<tr class='$rowclass'><td>$illNUB</td><Td>$title</td><td>$author</td><td>$itype</td><td>$reqnote</td><td>$lendnote</td><td>$needby</td><td>$dest</td><td>$destemail</td><td>$reqp<br>$reql</td><td>$timestamp</td><td>$fill</td><td><a href ='https://duenorth.nnyln.org/cancel?num=$illNUB&a=3'>Cancel Request</a></td></tr> ";
  } else {
    echo "<tr class='$rowclass'><td>$illNUB</td><Td>$title</td><td>$author</td><td>$itype</td><td>$reqnote</td><td>$lendnote</td><td>$needby</td><td>$dest</td><td>$destemail</td><td>$reqp<br>$reql</td><td>$timestamp</td><td>$fill</td><td>&nbsp</td></tr> ";
  }
  $rowtype = $rowtype + 1;
}
echo "</table>";
if(( $page > 0 ) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole)) {
  $last = $page - 2;
  echo "<a href='requesthistory?page=$last&loc=".$loc." '>Last 25 Records</a> |";
  echo "<a href='requesthistory?page=$page&loc=".$loc." '>Next 25 Records</a>";
} else if(( $page == 0 ) && ( $GETLISTCOUNTwhole  > $rec_limit )) {
  echo "<a href='requesthistory?page=$page&loc=".$loc." '>Next 25 Records</a>";
} else if(( $left_rec < $rec_limit )  && ($GETLISTCOUNTwhole > $rec_limit)) {
  $last = $page - 2;
  echo "<a href='requesthistory?page=$last&loc=".$loc." '>Last 25 Records</a>";
}
?>
