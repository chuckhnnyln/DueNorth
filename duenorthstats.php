<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
     $("#datepicker").datepicker();
     $("#datepicker2").datepicker();
     $("#expdatepicker").datepicker();
     $("#expdatepicker2").datepicker();
     $("#top10datepicker").datepicker();
     $("#top10datepicker2").datepicker();
     $("#top10fdatepicker").datepicker();
     $("#top10fdatepicker2").datepicker();
  });
  </script>
<?php

###sealstats.php###

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

if ( ($_SERVER['REQUEST_METHOD'] == 'POST')   || ( isset($_GET{'page'}))   ) {
  if ( $_REQUEST['stattype'] == 'wholesystem')   {
    #A request to generate stats has been posted
    $startdate = date('Y-m-d', strtotime('-7 days'));
    $enddated = $_REQUEST["enddate"];
    $startdated =   $_REQUEST["startdate"];
    $libsystem = $_REQUEST["system"];


    $startdate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $startdated)));
    $enddate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $enddated)));

    #Get total requests
    $GETREQUESTCOUNTSQLL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00' ";
    $retval = mysqli_query($db, $GETREQUESTCOUNTSQLL);
    $row_cnt = mysqli_num_rows($retval);

    #Get total filled requests
    $FINDFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =1 ";
    $retfilled =   mysqli_query($db, $FINDFILL);
    $row_fill = mysqli_num_rows($retfilled);
    #Get percentage fill
    $percentfill = $row_fill/$row_cnt;
    $percent_friendly_fill = number_format( $percentfill * 100, 2 ) . '%';

    #Get total not filled requests
    $FINDNOTFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =0 ";
    $retnotfilled =   mysqli_query($db, $FINDNOTFILL);
    $row_notfill = mysqli_num_rows($retnotfilled);
    #Get percentage fill
    $percentnotfill = $row_notfill/$row_cnt;
    $percent_friendly_notfill = number_format( $percentnotfill * 100, 2 ) . '%';

    #Get total requests expired
    $FINDEXPIRE= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =4  ";
    $retexpire =   mysqli_query($db, $FINDEXPIRE);
    $row_expire = mysqli_num_rows($retexpire);
    #Get percentage fill
    $percentexpire = $row_expire/$row_cnt;
    $percent_friendly_expire = number_format( $percentexpire * 100, 2 ) . '%';

    #Get total requests not answered
    $FINDNOANSW= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =3 ";
    $retnoansw =   mysqli_query($db, $FINDNOANSW);
    $row_noansw = mysqli_num_rows($retnoansw);
    #Get percentage fill
    $percentnoansw = $row_noansw/$row_cnt;
    $percent_friendly_noansw = number_format( $percentnoansw * 100, 2 ) . '%';

    #Get total requests canceled
    $CANANSW= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE  `ReqSystem` LIKE '%$libsystem%'  and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =6 ";
    $canansw =   mysqli_query($db, $CANANSW);
    $row_cancel = mysqli_num_rows($canansw);
    #Get percentage fill
    $percentcancel = $row_cancel/$row_cnt;
    $percent_friendly_cancel = number_format( $percentcancel * 100, 2 ) . '%';

        #translate system code to text name
if (strcmp($libsystem,'CVES')==0){
    $libsystemtxt = "Champlain Valley Education Services School Library System";
}else if (strcmp($libsystem,'CEFL')==0){
    $libsystemtxt = "Clinton Essex Franklin Library System";
}else if (strcmp($libsystem,'FEH')==0){
    $libsystemtxt = "Franklin-Essex-Hamilton School Library System";
}else if (strcmp($libsystem,'NCLS')==0){
    $libsystemtxt = "North Country Library System";
}else if (strcmp($libsystem,'OSW')==0){
    $libsystemtxt = "Oswego County School Library System at CiTi";
}else if (strcmp($libsystem,'SLL')==0){
    $libsystemtxt = "St. Lawrence-Lewis School Library System";
}else{
   $libsystemtxt = "Northern New York Library Network";
   }

    #Stats overall in the time frame chosen
    echo "<h1>DueNorth Stats from $startdated to $enddated </h1>";
    echo "<br><h3>Library System ".$libsystemtxt." </h3>";
    echo "Total Request ".$row_cnt." <br>";
    echo "Number of Request Filled: ".$row_fill." (".$percent_friendly_fill.")<br>";
    echo "Number of Request Not Filled: ".$row_notfill." (".$percent_friendly_notfill.")<br>";
    echo "Number of Request Expired: ".$row_expire." (".$percent_friendly_expire.")<br>";
    echo "Number of Request Canceled: ".$row_cancel." (".$percent_friendly_cancel.")<br>";
    echo "Number of Not Answered Yet: ".$row_noansw." (".$percent_friendly_noansw.")<br><br>";

    #Calulcate fill from other systems
    if (strlen($libsystem)>1){
      echo "<h1>Break down of requests</h1>";
      #Find which systems they sent request to
      $destsystem=" SELECT distinct (`DestSystem` )  FROM `SENYLRC-SEAL2-STATS` WHERE `ReqSystem`='$libsystem' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  ";
      $destsystemq = mysqli_query($db, $destsystem);
      #loop through the results of destion systems
      while ($row = mysqli_fetch_assoc($destsystemq)) {
        $dessysvar= $row['DestSystem'];
        $destsystemcount=" SELECT `itype`  FROM `SENYLRC-SEAL2-STATS` WHERE `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  ";
        $destsystemcountq = mysqli_query($db,  $destsystemcount);
        #Count the number of requests to that system
        $destnum_rows = mysqli_num_rows($destsystemcountq);

        #Get percentage
        $percentdestnum_rows = $destnum_rows/$row_cnt;
        $percent_friendly_destnum = number_format( $percentdestnum_rows * 100, 2 ) . '%';
        #translate system code to text name
if (strcmp($dessysvar,'CVES')==0){
    $dessysvartxt = "Champlain Valley Education Services School Library System";
}else if (strcmp($dessysvar,'CEFL')==0){
    $dessysvartxt = "Clinton Essex Franklin Library System";
}else if (strcmp($dessysvar,'FEH')==0){
    $dessysvartxt = "Franklin-Essex-Hamilton School Library System";
}else if (strcmp($dessysvar,'NCLS')==0){
    $dessysvartxt = "North Country Library System";
}else if (strcmp($dessysvar,'OSW')==0){
    $dessysvartxt = "Oswego County School Library System at CiTi";
}else if (strcmp($dessysvar,'SLL')==0){
    $dessysvartxt = "St. Lawrence-Lewis School Library System";
}else{
   $dessysvartxt = "Northern New York Library Network";
   }
        echo " ".$destnum_rows." (".$percent_friendly_destnum.") overall requests were made  to <strong> ".$dessysvartxt."</strong><br>";
        #Find which item types were requests
        $destitype=" SELECT distinct (`itype` )  FROM `SENYLRC-SEAL2-STATS`  WHERE `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00' ";
        $destitypeq = mysqli_query($db, $destitype);
        #loop through the results of items from that destination
        while ($row2 = mysqli_fetch_assoc($destitypeq)) {
          $dessysitype= $row2['itype'];
          #Remove any white space
          $destitemcount=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountq = mysqli_query($db,  $destitemcount);
          #Count the number of reuqests to that system
          $destnumitype_rows = mysqli_num_rows($destitemcountq);
          #Get percentage
          $percenttypesys_rows = $destnumitype_rows/$row_cnt;
          $percent_friendly_typesys = number_format($percenttypesys_rows * 100, 2 ) . '%';
          echo "&nbsp&nbsp&nbsp".$destnumitype_rows." (".$percent_friendly_typesys.") of the request to ".$dessysvartxt." were <strong>".$dessysitype."</strong><br>";
          #Find what the fill rate is
          $destitemcountfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='1' and `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountfillq = mysqli_query($db,  $destitemcountfill);
          #Count the number of fills
          $destnumfilled_rows = mysqli_num_rows($destitemcountfillq);
          #Get percentage
          $percent1_rows =$destnumfilled_rows/ $destnumitype_rows;
          $percent_friendly_1 = number_format($percent1_rows * 100, 2 ) . '%';
          echo " &nbsp&nbsp&nbsp&nbsp&nbsp      $destnumfilled_rows (".$percent_friendly_1.") were filled<br>";
          #Find what the unfill rate is
          $destitemcountunfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='0' and `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountunfillq = mysqli_query($db,  $destitemcountunfill);
          #Count the number of unfilled
          $destnumunfilled_rows = mysqli_num_rows($destitemcountunfillq);
          #Get percentage
          $percent2_rows =$destnumunfilled_rows/ $destnumitype_rows;
          $percent_friendly_2 = number_format($percent2_rows * 100, 2 ) . '%';
          echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $destnumunfilled_rows (". $percent_friendly_2.") were not filled<br>";
          #Find what the expire rate is
          $destitemcountexfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='4' and `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountexfillq = mysqli_query($db,  $destitemcountexfill);
          #Count the number of expired requests
          $destnumexfilled_rows = mysqli_num_rows($destitemcountexfillq);
          #Get percentage
          $percent3_rows = $destnumexfilled_rows/$destnumitype_rows;;
          $percent_friendly_3 = number_format($percent3_rows * 100, 2 ) . '%';
          echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $destnumexfilled_rows (". $percent_friendly_3.") were expired<br>";
          #Find what the cancel rate is
          $destitemcountcanfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='6' and `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountcanfillq = mysqli_query($db,  $destitemcountcanfill);
          #Count the number of canceled requests
          $destnumcanfilled_rows = mysqli_num_rows($destitemcountcanfillq);
          #Get percentage
          $percent4_rows =$destnumcanfilled_rows/$destnumitype_rows;
          $percent_friendly_4 = number_format($percent4_rows * 100, 2 ) . '%';
          echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $destnumcanfilled_rows  (". $percent_friendly_4.") were canceled<br>";
          #Find the numbered not answer yet
          $destitemcountnoanswfill=" SELECT `fill`  FROM `SENYLRC-SEAL2-STATS` WHERE Fill='3' and `Itype`='$dessysitype' and `ReqSystem`='$libsystem'  and `DestSystem`='$dessysvar' and `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'";
          $destitemcountnoanswfillq = mysqli_query($db,  $destitemcountnoanswfill);
          #Count the number of requests not answered yet
          $destnumnoanswfilled_rows = mysqli_num_rows($destitemcountnoanswfillq);
          #Get percentage
          $percent5_rows =$destnumnoanswfilled_rows/ $destnumitype_rows;
          $percent_friendly_5 = number_format($percent5_rows * 100, 2 ) . '%';
          echo "&nbsp&nbsp&nbsp&nbsp&nbsp   $destnumnoanswfilled_rows  (". $percent_friendly_5.") of requests not answered yet<br>";
          echo "<br>";
        }
        echo "<hr>";
      }
    }
}else if ( $_REQUEST['stattype'] == 'top10fstats')   {
#Generate the top 10 filling requests
  $startdate = date('Y-m-d', strtotime('-7 days'));
  $enddated = $_REQUEST["enddate"];
  $startdated =   $_REQUEST["startdate"];

  $startdate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $startdated)));
  $enddate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $enddated)));
  echo "<h1><center>Top 10 Libraries Filling Requests from $startdated to $enddated </h1></center>";
  $GETREQUESTCOUNTSQLL= "SELECT  `Destination`, count(*)  FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00' and Fill=1  GROUP BY `Destination` ORDER BY count(*) DESC LIMIT 10 ";
  $retval = mysqli_query($db, $GETREQUESTCOUNTSQLL);
  $row_cnt = mysqli_num_rows($retval);
	echo "<table><tr><th>Library Name</th><th>Number of Fills</th></tr>";
  while ($row2 = mysqli_fetch_assoc($retval )) {
    $reqestid = $row2['Destination'];
	  $countnumb =  $row2["count(*)"];
    $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$reqestid' ";
    $result = mysqli_query($db, $libnames);
    while($row =  $result->fetch_assoc()) {
      $reqestid =  $row["Name"];
    }
    echo "<tr><td>$reqestid</td><td>$countnumb</td></tr>";
	 }
    echo "</table>";
  }else if ( $_REQUEST['stattype'] == 'top10stats')   {
    $startdate = date('Y-m-d', strtotime('-7 days'));
    $enddated = $_REQUEST["enddate"];
    $startdated =   $_REQUEST["startdate"];
    $startdate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $startdated)));
    $enddate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $enddated)));
    echo "<h1><center>Top 10 Libraries Making Requests from $startdated to $enddated </h1></center>";
    $GETREQUESTCOUNTSQLL= "SELECT  `Requester LOC`, count(*)  FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'   GROUP BY `Requester LOC` ORDER BY count(*) DESC LIMIT 10 ";
    $retval = mysqli_query($db, $GETREQUESTCOUNTSQLL);
    $row_cnt = mysqli_num_rows($retval);
	   echo "<table><tr><th>Library Name</th><th>Number of Requests</th></tr>";
     while ($row2 = mysqli_fetch_assoc($retval )) {
       $reqestid = $row2['Requester LOC'];
	      $countnumb =  $row2["count(*)"];
        $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$reqestid' ";
        $result = mysqli_query($db, $libnames);
        while($row =  $result->fetch_assoc()) {
          $reqestid =  $row["Name"];
        }
        echo "<tr><td>$reqestid</td><td>$countnumb</td></tr>";
	   }
     echo "</table>";
   }else if ( $_REQUEST['stattype'] == 'expirestats')   {
     $startdate = date('Y-m-d', strtotime('-7 days'));
     $enddated = $_REQUEST["enddate"];
     $startdated =   $_REQUEST["startdate"];
     $startdate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $startdated)));
     $enddate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $enddated)));
     #Get total requests that expired
     $GETREQUESTCOUNTSQLL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Timestamp` < '$enddate 00:00:00'  and  Fill =4 ORDER BY illNUB";
     $retval = mysqli_query($db, $GETREQUESTCOUNTSQLL);
     $row_cnt = mysqli_num_rows($retval);

     #loop through the results of items from that destination
     echo "<h1><center>DueNorth Requests that expired from $startdated to $enddated </h1></center>";
     echo  "$row_cnt  results";
     ?>
     <table><tr><th>ILL #</th><th>Destination Library</th><th>Requesting Library</th><th>Title</th><th>Item Type</th><th>Date</th></tr>
      <?php
      while ($row2 = mysqli_fetch_assoc($retval )) {
        $dessid= $row2['Destination'];
        $illid= $row2['illNUB'];
        $title= $row2['Title'];
        $itype= $row2['Itype'];
        $date= $row2['Timestamp'];
        $reqestid = $row2['Requester LOC'];
        $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$dessid' ";
        $result = mysqli_query($db, $libnames);
        while($row =  $result->fetch_assoc()) {
          $dessid =  $row["Name"];
        }
        $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$reqestid' ";
        $result = mysqli_query($db, $libnames);
        while($row =  $result->fetch_assoc()) {
          $reqestid =  $row["Name"];
        }
        echo "<tr><td>".$illid."</td><td>".$dessid."</td><td>".$reqestid."</td><td>".$title."</td><td>".$itype."</td><td>".$date."</td></tr>";
      }
      echo "</table>";
    }
  }else{
      ?>
     <h3>Enter your desired date range:</h3>
     <form action="/duenorthstats?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
     Start Date:
     <input id="datepicker" name="startdate"/>
     End Date:
     <input id="datepicker2" name="enddate"/>
     <br><br>Requesting Library System: <select name="system">
                    <option value="">All</option>
<option value = "CVES">Champlain Valley Education Services School Library System</option>
<option value = "CEFL">Clinton Essex Franklin Library System</option>
<option value = "FEH">Franklin-Essex-Hamilton School Library System</option>
<option value = "JLHO">Jefferson-Lewis School Library System</option>
<option value = "NCLS">North Country Library System</option>
<option value = "NNYLN">Northern New York Library Network</option>
<option value = "OSW">Oswego County School Library System at CiTi</option>
<option value = "SLL">St. Lawrence-Lewis School Library System</option>
     <input type="hidden" name="stattype" value="wholesystem">
     <input type="submit" value="Submit">
    </form>
    <br>
    <?php
    #Generate the drop down for borrower stats
    $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant`=1 order by `Name` ";
    $libnameq =   mysqli_query($db, $libnames);
    echo "<form action='/libstats'   method='post2' >";
    echo "<h3>Generate borrowing stats for a specific library:</h3>";
    echo "<select name=libname>";
    while($row = $libnameq->fetch_assoc()) {
      $libname =  $row["Name"];
      $loccode = $row["loc"];
      echo "<option value=".$loccode.">".$libname."</option><br>";
    }
    echo "<input type='submit' value='Submit'>";
    echo "</select></form>";
    #Generating the links for borrowing stats
    $libnames= "SELECT loc,Name FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant`=1 order by `Name` ";
    $libnameq =   mysqli_query($db, $libnames);
    echo "<form action='/liblenderstat'   method='post2' >";
    echo "<h3>Generate lending stats for a specific library:</h3>";
    echo "<select name=libname>";
    while($row = $libnameq->fetch_assoc()) {
      $libname =  $row["Name"];
      $loccode = $row["loc"];
        echo "<option value=".$loccode.">".$libname."</option><br>";
    }
    echo "<input type='submit' value='Submit'>";
    echo "</select></form>";
    ?>
    <form action="/duenorthstats?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <h3>Generate list of expired requests:</h3>
     Start Date:
     <input id="expdatepicker" name="startdate"/>
     End Date:
     <input id="expdatepicker2" name="enddate"/>
     <input type="hidden" name="stattype" value="expirestats">
     <input type="submit" value="Submit">
    </form>
    <form action="/duenorthstats?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <h3>Generate list of top 10 libraries making requests:</h3>
    Start Date:
    <input id="top10datepicker" name="startdate"/>
    End Date:
    <input id="top10datepicker2" name="enddate"/>
    <input type="hidden" name="stattype" value="top10stats">
    <input type="submit" value="Submit">
    </form>
    <form action="duenorthstats?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <h3>Generate list of top 10 libraries filling requests:</h3>
    Start Date:
    <input id="top10fdatepicker" name="startdate"/>
    End Date:
    <input id="top10fdatepicker2" name="enddate"/>
    <input type="hidden" name="stattype" value="top10fstats">
    <input type="submit" value="Submit">
  </form>
  <?php
}
?>
