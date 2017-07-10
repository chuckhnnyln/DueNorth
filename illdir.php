<?php
###illdir.php####

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$GETLISTSQL="SELECT * FROM `$sealLIB` where participant = '1' ORDER BY Name Asc";
$retval = mysqli_query($db, $GETLISTSQL );
$GETLISTCOUNTwhole = mysqli_num_rows ($retval);
$rec_limit = 50;
$rowpage = mysqli_fetch_array($retval, MYSQLI_NUM );
$rec_count = $rowpage[0];

if( isset($_GET{'page'} ) ){
  $page = $_GET{'page'} + 1;
  $offset = $rec_limit * $page ;
}else{
  $page = 0;
  $offset = 0;
}
$left_rec = $rec_count - ($page * $rec_limit);
$GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
#echo $GETLISTSQL;
$GETLIST = mysqli_query($db, $GETLISTSQL);
$GETLISTCOUNT = mysqli_num_rows ($GETLIST);
#List All Libraries
echo "$GETLISTCOUNTwhole results<bR>";
echo "<table><tr>";
$count = 1;
$no = 1;
while ($row = mysqli_fetch_assoc($GETLIST)) {
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
  echo "<Td>";
  echo "Name: <strong> $libname</strong><br>";
  echo "Address: <strong> $libaddress2 </strong><br>";
  echo "&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> $libaddress3 </strong><br>";
  echo "Phone: <strong> $libphone</strong><br>";
  echo "OCLC Symbol:<strong> $oclc</strong><br>";
  echo "LOC Code :<strong> $loc</strong><br>";
  echo "Accepting Request: <strong> $libsuspend </strong>";
  echo "<br><br>";
  echo "Loaning Books: <strong>$book</strong><br>";
  echo "Loaning Journals or Articles: <strong>$journal</strong><br>";
  echo "Loaning Audio/Video: <strong>$av</strong><br>";
  echo "Loaning Reference: <strong>$reference</strong><br>";
  echo "Loaning E-Books: <strong>$ebook</strong><br>";
  echo "</td>";
  if ($count++ % 2 == 0){
    echo "</tr><tr>";
    $no++;
  }
}
echo "</table>";
if(( $page > 0 ) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole)){
  $last = $page - 2;
  echo "<a href='illdir?page=$last\'>Last 50 Records</a> |";
  echo "<a href='illdir?page=$page\'>Next 50 Records</a>";
  }else if(( $page == 0 ) && ( $GETLISTCOUNTwhole  > $rec_limit )){
    echo "<a href='illdir?page=$page\'>Next 50 Records</a>";
	}else if(( $left_rec < $rec_limit )  && ($GETLISTCOUNTwhole > $rec_limit)){
    $last = $page - 2;
    echo "<a href='illdir?page=$last\'>Last 50 Records</a>";
  }

?>
