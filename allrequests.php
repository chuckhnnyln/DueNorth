<?php

###allrequests.php###

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$orderBy = array('Timestamp', 'illNUB' );

$order = 'Timestamp';
if (isset($_GET['orderBy']) && in_array($_GET['orderBy'], $orderBy)) {
    $order = $_GET['orderBy'];
}


$GETLISTSQL="SELECT * FROM `$sealSTAT`  ORDER BY `$order`  DESC";


$retval = mysqli_query($db, $GETLISTSQL);
$GETLISTCOUNTwhole = mysqli_num_rows ($retval);
$rec_limit = 10;
$row = mysqli_fetch_array($retval, MYSQLI_NUM );
$rec_count = $row[0];

 if( isset($_GET{'page'} ) )
         {
            $page = $_GET{'page'} + 1;
            $offset = $rec_limit * $page ;
         }
         else
         {
            $page = 0;
            $offset = 0;
         }
         $left_rec = $rec_count - ($page * $rec_limit);

$GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
$GETLIST = mysqli_query($db,$GETLISTSQL);
$GETLISTCOUNT = mysqli_num_rows ($GETLIST);
#List All Libraries

echo "$GETLISTCOUNTwhole results<bR>";
echo "<table><tr><TH width='20'><a href='?orderBy=illNUB'>ILL #</a></th><th>Title</th><th>Author</th><th>Type</th><th>Available</th><th>Req Note</th><th>Responder Note</th><th>Need By</th><th>Destination</th><th>Requester</th><th> <a href='?orderBy=Timestamp'>Timestamp</a></th><th>Fill</th></tr>";
  while ($row = mysqli_fetch_assoc($GETLIST)) {
        $illNUB = $row["illNUB"];
        $title = $row["Title"];
        $author = $row["Author"];
        $itype = $row["Itype"];
        $avail = $row["Available"];
        $reqnote = $row["reqnote"];
        $respnote = $row["responderNOTE"];
        $needby = $row["needbydate"];
        $dest = $row["Destination"];
        $reqp = $row["Requester person"];
        $reql = $row["Requester lib"];
        $timestamp = $row["Timestamp"];
        $fill = $row["Fill"];
         if($fill=="1") $fill="Yes";
         if($fill=="0") $fill="No";
         if($fill=="3") $fill="No Answer";
          if($fill=="4") $fill="Expired";
         if($fill=="6") $fill="Canceled";
          $dest=trim($dest);
         ###Get the Destination Name
   if (strlen($dest)>2){
            $GETLISTSQLDEST="SELECT`Name` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$dest'  limit 1";
            $resultdest=mysqli_query($db, $GETLISTSQLDEST);
                while ($rowdest = mysqli_fetch_assoc($resultdest)) {
                                $dest=$rowdest["Name"];
                        }
        }else{
            $dest ='Error';
        }

       echo " <Tr><td>$illNUB</td><Td>$title</td><td>$author</td><td>$itype</td><td>$avail</td><td>$reqnote</td><td>$respnote<td>$needby</td><td>$dest</td><td>$reqp<br>$reql</td><td>$timestamp</td><td>$fill</td></tr> ";
   }
echo "</table>";
 if(( $page > 0 ) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole))
         {
            $last = $page - 2;
            echo "<a href='allrequests?page=$last\'>Last 10 Records</a> |";
            echo "<a href='allrequests?page=$page\'>Next 10 Records</a>";
         }

         else if(( $page == 0 ) && ( $GETLISTCOUNTwhole  > $rec_limit ))
         {
            echo "<a href='allrequests?page=$page\'>Next 10 Records</a>";
			}

        else if(( $left_rec < $rec_limit )  && ($GETLISTCOUNTwhole > $rec_limit))
         {
            $last = $page - 2;
            echo "<a href='allrequests?page=$last\'>Last 10 Records</a>";
         }

?>
