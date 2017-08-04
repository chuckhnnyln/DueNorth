<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
    $("#datepicker").datepicker();
     $("#datepicker2").datepicker();
  });
  </script>
<?php

###liblenderstat.php###

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);


if ( ($_SERVER['REQUEST_METHOD'] == 'POST')   || ( isset($_GET{'page'}))  ) {
  $startdate = date('Y-m-d', strtotime('-7 days'));
 $enddated = $_REQUEST["enddate"];
 $startdated =   $_REQUEST["startdate"];
 $libname = $_REQUEST["libname"];
 $loc = $_REQUEST["loc"];
 $libname=$_REQUEST["libname"];

if (strlen ($libname) >2){
  $loc = $libname;
}

$loc = mysqli_real_escape_string($db,$loc);
$startdate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $startdated)));
$enddate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $enddated)));

 #Get total requests received
$GETREQUESTCOUNTSQLL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00' ";
$retval = mysqli_query($db, $GETREQUESTCOUNTSQLL);
$row_cnt = mysqli_num_rows($retval);

 #Get total filled requests
$FINDFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'     and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =1 ";
$retfilled =   mysqli_query($db, $FINDFILL);
$row_fill = mysqli_num_rows($retfilled);
#Get percentage fill
$percentfill = $row_fill/$row_cnt;
$percent_friendly_fill = number_format( $percentfill * 100, 2 ) . '%';

 #Get total not filled requests
$FINDNOTFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =0 ";
$retnotfilled =   mysqli_query($db, $FINDNOTFILL);
$row_notfill = mysqli_num_rows($retnotfilled);

#Get percentage fill
$percentnotfill = $row_notfill/$row_cnt;
$percent_friendly_notfill = number_format( $percentnotfill * 100, 2 ) . '%';

 #Get total requests expired
$FINDEXPIRE= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =4 ";
$retexpire =   mysqli_query($db, $FINDEXPIRE);
$row_expire = mysqli_num_rows($retexpire);
#Get percentage fill
$percentexpire = $row_expire/$row_cnt;
$percent_friendly_expire = number_format( $percentexpire * 100, 2 ) . '%';

 #Get total requests not answered
$FINDNOANSW= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =3 ";
$retnoansw =   mysqli_query($db, $FINDNOANSW);
$row_noansw = mysqli_num_rows($retnoansw);
#Get percentage fill
$percentnoansw = $row_noansw/$row_cnt;
$percent_friendly_noansw = number_format( $percentnoansw * 100, 2 ) . '%';

 #Get total requests canceled
$CANANSW= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'    and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =6 ";
$canansw =   mysqli_query($db, $CANANSW);
$row_cancel = mysqli_num_rows($canansw);
#Get percentage fill
$percentcancel = $row_cancel/$row_cnt;
$percent_friendly_cancel = number_format( $percentcancel * 100, 2 ) . '%';


#Get the library name
$libnames= "SELECT Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `LOC` LIKE '$loc'  ";
$libnameq =   mysqli_query($db, $libnames);
while($row = $libnameq->fetch_assoc()) {
$libname =  $row["Name"];
}

    #Stats overall in the time frame chosen
   echo "<h1>DueNorth Stats from $startdated to $enddated </h1>";
   echo "<br><h3>Lender request statistics for ".$libname."  </h3>";
   echo "Total Requests received ".$row_cnt." <br>";
   echo "Number of Requests Filled: ".$row_fill." (".$percent_friendly_fill.")<br>";
   echo "Number of Requests Not Filled: ".$row_notfill." (".$percent_friendly_notfill.")<br>";
   echo "Number of Requests Expired: ".$row_expire." (".$percent_friendly_expire.")<br>";
   echo "Number of Requests Canceled: ".$row_cancel." (".$percent_friendly_cancel.")<br>";
   echo "Number of Requests Not Answered Yet: ".$row_noansw." (".$percent_friendly_noansw.")<br><br>";

    echo "<h3>Break down of requests</h3>";
   #Find which systems they sent request to
   $reqsystem=" SELECT distinct (`ReqSystem` )  FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  ";
   $reqsystemq = mysqli_query($db, $reqsystem);
    #loop through the results of destination systems
    while ($row = mysqli_fetch_assoc($reqsystemq)) {
        $reqsysvar= $row['ReqSystem'];
        $reqsystemcount=" SELECT `itype`  FROM `SENYLRC-SEAL2-STATS` WHERE `Destination` LIKE '$loc'   and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  ";
        $reqsystemcountq = mysqli_query($db,  $reqsystemcount);
        #Count the number of requests to that system
        $reqnum_rows = mysqli_num_rows($reqsystemcountq);

       #Get percentage
       $percentreqnum_rows = $reqnum_rows/$row_cnt;
       $percent_friendly_reqnum = number_format( $percentreqnum_rows * 100, 2 ) . '%';


        #translate system code to text name
if (strcmp($reqsysvar,'CVES')==0){
    $reqsysvartxt = "Champlain Valley Education Services School Library System";
}else if (strcmp($reqsysvar,'CEFL')==0){
    $reqsysvartxt = "Clinton Essex Franklin Library System";
}else if (strcmp($reqsysvar,'FEH')==0){
    $reqsysvartxt = "Franklin-Essex-Hamilton School Library System";
}else if (strcmp($reqsysvar,'NCLS')==0){
    $reqsysvartxt = "North Country Library System";
}else if (strcmp($reqsysvar,'OSW')==0){
    $reqsysvartxt = "Oswego County School Library System at CiTi";
}else if (strcmp($reqsysvar,'SLL')==0){
    $reqsysvartxt = "St. Lawrence-Lewis School Library System";
}else{
   $reqsysvartxt = "Northern New York Library Network";
}

        echo " ".$reqnum_rows." (".$percent_friendly_reqnum.") overall requests were made  from <strong> ".$reqsysvartxt."</strong><br>";
        #Find which item types were requests
        $reqtitype=" SELECT distinct (`itype` )  FROM `SENYLRC-SEAL2-STATS`  WHERE `Destination` LIKE '$loc'    and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00' ";
        $reqtitypeq = mysqli_query($db, $reqtitype);
        #loop through the results of items from that destination
        while ($row2 = mysqli_fetch_assoc($reqtitypeq)) {
            $reqsysitype= $row2['itype'];
             #Remove any white space

            $reqitemcount=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE `Itype`='$reqsysitype' and `Destination` LIKE '$loc'  and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";

            $reqitemcountq = mysqli_query($db,  $reqitemcount);
            #Count the number of requests to that system
            $reqnumitype_rows = mysqli_num_rows($reqitemcountq);

             #Get percentage
           $percenttypesys_rows = $reqnumitype_rows/$reqnum_rows;
           $percent_friendly_typesys = number_format($percenttypesys_rows * 100, 2 ) . '%';


            echo "&nbsp&nbsp&nbsp".$reqnumitype_rows." (".$percent_friendly_typesys.") of the requests from  ".$reqsysvartxt." were <strong>".$reqsysitype."</strong><br>";

            #Find what the fill rate is
            $reqtitemcountfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='1' and `Itype`='$reqsysitype' and `Destination` LIKE '$loc'   and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
            $reqtitemcountfillq = mysqli_query($db,  $reqtitemcountfill);
            #Count the number of fills
            $reqnumfilled_rows = mysqli_num_rows($reqtitemcountfillq);

            #Get percentage
           $percent1_rows =$reqnumfilled_rows/ $reqnumitype_rows;
           $percent_friendly_1 = number_format($percent1_rows * 100, 2 ) . '%';

            echo " &nbsp&nbsp&nbsp&nbsp&nbsp      $reqnumfilled_rows (".$percent_friendly_1.") were filled<br>";

            #Find what the unfill rate is
            $reqitemcountunfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='0' and `Itype`='$reqsysitype' and `Destination` LIKE '$loc'   and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";

            $reqitemcountunfillq = mysqli_query($db,  $reqitemcountunfill);
            #Count the number of unfilled
            $reqnumunfilled_rows = mysqli_num_rows($reqitemcountunfillq);

           #Get percentage
           $percent2_rows =$reqnumunfilled_rows/ $reqnumitype_rows;
           $percent_friendly_2 = number_format($percent2_rows * 100, 2 ) . '%';

            echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $reqnumunfilled_rows (". $percent_friendly_2.") were not filled<br>";

           #Find what the expire rate is
            $reqitemcountexfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='4' and `Itype`='$reqsysitype' and `Destination` LIKE '$loc'  and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
            $reqitemcountexfillq = mysqli_query($db,  $reqitemcountexfill);
            #Count the number of expired requests
            $reqnumexfilled_rows = mysqli_num_rows($reqitemcountexfillq);

            #Get percentage
           $percent3_rows =$reqnumexfilled_rows/  $reqnumitype_rows;
           $percent_friendly_3 = number_format($percent3_rows * 100, 2 ) . '%';

           echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $reqnumexfilled_rows (". $percent_friendly_3.") were expired<br>";

           #Find what the cancel rate is
            $reqitemcountcanfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='6' and `Itype`='$reqsysitype' and `Destination` LIKE '$loc'  and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
            $reqitemcountcanfillq = mysqli_query($db,  $reqitemcountcanfill);
            #Count the number of canceled requests
            $reqnumcanfilled_rows = mysqli_num_rows($reqitemcountcanfillq);

           #Get percentage
           $percent4_rows =$reqnumcanfilled_rows/ $reqnumitype_rows ;
           $percent_friendly_4 = number_format($percent4_rows * 100, 2 ) . '%';

            echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $reqnumcanfilled_rows  (". $percent_friendly_4.") were canceled<br>";

            #Find the numbered not answer yet
            $reqitemcountnoanswfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='3' and `Itype`='$reqsysitype' and `Destination` LIKE '$loc'  and `ReqSystem`='$reqsysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";

            $reqitemcountnoanswfillq = mysqli_query($db,  $reqitemcountnoanswfill);
            #Count the number of requests not answered yet
            $reqnumnoanswfilled_rows = mysqli_num_rows($reqitemcountnoanswfillq);

            #Get percentage
           $percent5_rows =$reqnumnoanswfilled_rows/ $reqnumitype_rows ;
           $percent_friendly_5 = number_format($percent5_rows * 100, 2 ) . '%';

            echo "&nbsp&nbsp&nbsp&nbsp&nbsp    $reqnumnoanswfilled_rows  (". $percent_friendly_5.") of requests not answered<br>";
            echo "<br>";
       }
   echo "<br><hr><br>";
  }


 }else{
if (isset($_GET['loc'])){  $loc = $_GET['loc'];  }else{$loc='null';}
          ?>
     <h3>Enter your desired date range:</h3>

     <form action="/liblenderstat?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
     Start Date:
     <input id="datepicker" name="startdate"/>
     End Date:
     <input id="datepicker2" name="enddate"/>
<br><br>
<?php echo "<input type='hidden' name='loc' value='".$loc."'>";?>

     <input type="submit" value="Submit">
    </form>

   <?php
     }

?>
