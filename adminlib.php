<?php

###adminlib.php###

#check if an action has been requested
if (isset($_REQUEST['action'])) {
  #set the pageaction to what has been requested
  $pageaction = $_REQUEST['action'];
  #Chec if the librecnumb variable has been sent with action and set a variable to be used for edit and delete
  if (isset($_REQUEST['librecnumb'])) $librecnumb = $_REQUEST['librecnumb'];
}else{
  $pageaction = '0';
}

if (isset($_REQUEST['libname'])) $libname = $_REQUEST['libname'];
if (isset($_REQUEST['libalias'])) $libalias = $_REQUEST['libalias'];
if (isset($_REQUEST['libemail'])) $libemail = $_REQUEST['libemail'];
if (isset($_REQUEST['participant'])) $participant = $_REQUEST['participant'];
if (isset($_REQUEST['suspend'])) $suspend = $_REQUEST['suspend'];
if (isset($_REQUEST['system'])) $system = $_REQUEST['system'];
if (isset($_REQUEST['phone'])) $phone = $_REQUEST['phone'];
if (isset($_REQUEST['address1'])) $address1 = $_REQUEST['address1'];
if (isset($_REQUEST['address2'])) $address2 = $_REQUEST['address2'];
if (isset($_REQUEST['address3'])) $address3 = $_REQUEST['address3'];
if (isset($_REQUEST['oclc'])) $oclc = $_REQUEST['oclc'];
if (isset($_REQUEST['loc'])) $loc = $_REQUEST['loc'];
if (isset($_REQUEST['book'])) $book = $_REQUEST['book'];
if (isset($_REQUEST['journal'])) $journal = $_REQUEST['journal'];
if (isset($_REQUEST['ebook'])) $ebook = $_REQUEST['ebook'];
if (isset($_REQUEST['reference'])) $reference = $_REQUEST['reference'];
if (isset($_REQUEST['av'])) $av = $_REQUEST['av'];

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);


if ($pageaction ==3){
##Delete a library
  if ( ($_SERVER['REQUEST_METHOD'] == 'POST')   || ( isset($_GET{'page'}))  ) {
    $librecnumb = mysqli_real_escape_string($db, $librecnumb);
    $sqldel = "DELETE FROM `$sealLIB` WHERE recnum='$librecnumb'";
    mysqli_query($db,$sqldel);
    echo "Library has been deleted<br><br>";
    echo "<a href='/adminlib'>Return to main list</a>";
  }else{
    ?><form action="/adminlib?<?php echo $_SERVER['QUERY_STRING'];?>" method="post"><?php
    echo "<input type='hidden' name='action' value='$pageaction''>";
    echo "<input type='hidden' name='librecnumb' value='$librecnumb''>";
    echo  "Confirm you want to delete?";
    echo  "<input type='submit' value='Confirm'>";
    echo "</form>";
  }
}elseif ($pageaction ==4){
##search for a library
  if ( ($_SERVER['REQUEST_METHOD'] == 'POST')   || ( isset($_GET{'page'}))  ) {
    #Run search if page has posted
    $libname = mysqli_real_escape_string($db,$libname);
    $libemail = mysqli_real_escape_string($db,$libemail);
    $GETLISTSQL1="SELECT * FROM `$sealLIB` WHERE `Name` LIKE '%$libname%' and `alias` LIKE '%$libalias%' and `ILL Email` LIKE '%$libemail%' and `suspend` LIKE '%$suspend%' and `participant`LIKE '%$participant%' and `system` LIKE '%$system%'  ORDER BY Name Asc";
    #echo  $GETLISTSQL1;
    $retval = mysqli_query ($db, $GETLISTSQL1 );
    $GETLISTCOUNTwhole = mysqli_num_rows ($retval);
    $row = mysqli_fetch_array($retval, MYSQLI_NUM );
    $rec_count = $row[0];
    $GETLIST = mysqli_query($db, $GETLISTSQL1);
    $GETLISTCOUNT = mysqli_num_rows ($GETLIST);
    echo "<a href='adminlib?action=1'>Would you like to add a library?</a><br>";
    echo "<a href='adminlib?action=4'>Would you like to search for a library?</a><br><br>";
    echo " $GETLISTCOUNTwhole  results";
    echo "<table><tr><th>Library</th><th>Alias</th><th>Phone</th><th>Participant</th><th>Suspend</th><th>System</th><th>OCLC</th><th>ILL</th><th>Action</th></tr>";
    $rowtype=1;
    while ($row = mysqli_fetch_assoc($GETLIST)) {
      $librecnumb = $row["recnum"];
      $libname = $row["Name"];
      $libalias = $row["alias"];
      $phone = $row["phone"];
      $libparticipant = $row["participant"];
      $oclc = $row["oclc"];
      $loc = $row["loc"];
      $libsuspend = $row["suspend"];
      $system = $row["system"];
      if($libsuspend=="1") $libsuspend="Yes";
      if($libsuspend=="0") $libsuspend="No";
      if($libparticipant =="1") $libparticipant ="Yes";
      if($libparticipant =="0") $libparticipant ="No";
      if ($rowtype & 1 ) {
        echo "<tr class='odd'><Td>$libname</td><td>$libalias</td><td>$phone</td><td>$libparticipant</td><td>$libsuspend</td><td>$system</td><td>$oclc</td><td>$loc</td> ";
        echo "<td><a href='adminlib?action=2&librecnumb=$librecnumb'>Edit</a>  <a href='adminlib?action=3&librecnumb=$librecnumb''>Delete</a> </td></tr>";
      } else {
        echo "<tr class='even'><Td>$libname</td><td>$libalias</td><td>$phone</td><td>$libparticipant</td><td>$libsuspend</td><td>$system</td><td>$oclc</td><td>$loc</td> ";
        echo "<td><a href='adminlib?action=2&librecnumb=$librecnumb'>Edit</a>  <a href='adminlib?action=3&librecnumb=$librecnumb''>Delete</a> </td></tr>";
      }
      $rowtype=$rowtype+1;
    }
    echo "</table>";
  }else{
    #Preset form for search criteria?>
    <h2>Enter the criteria you want to search:</h2>
    <form action="/adminlib?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname"><br>
    <B>Library Alias:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libalias"><br>
    <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail"><br>
    <B>Library ILL participant</b><select name="participant"><option value=""></option> <option value="1">Yes</option><option value="0">No</option></select><br>
    <B>Suspend ILL</b><select name="suspend"><option value=""></option><option value="0">No</option><option value="1">Yes</option></select><br>
    <B>Library System</b><select name="system">
      <option value=""></option>
<option value = "CVES">Champlain Valley Education Services School Library System</option>
<option value = "CEFL">Clinton Essex Franklin Library System</option>
<option value = "FEH">Franklin-Essex-Hamilton BOCES School Library System</option>
<option value = "JLHO">Jefferson-Lewis BOCES School Library System</option>
<option value = "NCLS">North Country Library System</option>
<option value = "NNYLN">Northern New York Library Network</option>
<option value = "OSW">Oswego County School Library System at CiTi</option>
<option value = "SLL">St. Lawrence-Lewis BOCES School Library System</option>
    </select>
    <br>
    <input type="submit" value="Submit">
    </form>
    <?php
  }
}elseif ($pageaction ==1){
  ##Adding a library
  if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $libname = mysqli_real_escape_string($db, $libname);
    $libemail = mysqli_real_escape_string($db,$libemail);
    $address1 = mysqli_real_escape_string($db,$address1);
    $address2 = mysqli_real_escape_string($db,$address2);
    $address3 = mysqli_real_escape_string($db,$address3);
    $phone = mysqli_real_escape_string($db,$phone);
    $loc = mysqli_real_escape_string($db,$loc);
    $book = mysqli_real_escape_string($db,$book);
    $journal = mysqli_real_escape_string($db,$journal);
    $av = mysqli_real_escape_string($db,$av);
    $ebook = mysqli_real_escape_string($db,$ebook);
    $reference = mysqli_real_escape_string($db,$reference);
    $oclc = mysqli_real_escape_string($db,$oclc);
    $oclc=trim($oclc);
    $loc=trim($loc);
    $libemail=trim($libemail);
    $insertsql  = "
    INSERT INTO `$sealLIB` (`recnum`, `Name`, `ILL Email`, `alias`, `participant`, `suspend`, `system`, `phone`, `address1`, `address2`, `address3`,  `loc`, `oclc`, `book`,`journal`,`av`, `ebook`, `reference`)
      VALUES (NULL,'$libname','$libemail','$libalias','$participant','$suspend','$system','$phone','$address1','$address2','$address3','$loc','$oclc','$book','$journal','$av','$ebook','$reference')";
    echo $insertsql;
    $result = mysqli_query($db, $insertsql);
    echo  "Library Had Been Added";
    echo "<br><a href='/adminlib'>Return to main list</a>";
  }else{
    ?>
    <form action="/adminlib?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname"><br>
    <B>Library Alias:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libalias"><br>
    <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail"><br>
    <B>Library Phone:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="phone"><br>
    <B>ILL Dept:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address1"><br>
    <B>Street Address:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address2"><br>
    <B>City State Zip:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address3"><br>
    <B>OCLC Symbol:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="oclc"><br>
    <B>ILL Code:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="loc"><br>
    <B>Library ILL participant</b><select name="participant">  <option value="1">Yes</option><option value="0">No</option></select><br>
    <B>Suspend ILL</b><select name="suspend">  <option value="0">No</option><option value="1">Yes</option></select><br>
    <B>Library System</b><select name="system">
<option value = "CVES">Champlain Valley Education Services School Library System</option>
<option value = "CEFL">Clinton Essex Franklin Library System</option>
<option value = "FEH">Franklin-Essex-Hamilton BOCES School Library System</option>
<option value = "JLHO">Jefferson-Lewis BOCES School Library System</option>
<option value = "NCLS">North Country Library System</option>
<option value = "NNYLN">Northern New York Library Network</option>
<option value = "OSW">Oswego County School Library System at CiTi</option>
<option value = "SLL">St. Lawrence-Lewis BOCES School Library System</option>
      <B>Items Willing to loan in SEAL</b><br>
        <ul>
        <li><b>Print Book</b>
          <input type="radio" name="book" value="1" checked> Yes
          <input type="radio" name="book" value="0" > No <br>
        </li>
        <li><b>Print Journal or Article</b>
          <input type="radio" name="journal" value="1" checked> Yes
          <input type="radio" name="journal" value="0" > No <br>
        </li>
        <li><b>Audio Video Materials</b>
          <input type="radio" name="av" value="1" checked> Yes
          <input type="radio" name="av" value="0" > No <br>
        </li>
        <li><b>Reference</b>
          <input type="radio" name="reference" value="1" > Yes
          <input type="radio" name="reference" value="0" checked>No <br>
        </li>
        <li><b>Electronic Book</b>
          <input type="radio" name="ebook" value="1" > Yes
          <input type="radio" name="ebook" value="0" checked>> No <br>
        </li>
      </ul>
      <br>
      <input type="submit" value="Submit">
      </form>
      <?php
    }
}elseif($pageaction ==2){
#Edit a Library
  if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    #if the edit form was posted update the database with the data posted
    $libname = mysqli_real_escape_string($db,$libname);
    $libemail = mysqli_real_escape_string($db,$libemail);
    $address1 = mysqli_real_escape_string($db,$address1);
    $address2 = mysqli_real_escape_string($db,$address2);
    $address3 = mysqli_real_escape_string($db,$address3);
    $phone = mysqli_real_escape_string($db,$phone);
    $book = mysqli_real_escape_string($db,$book);
    $journal = mysqli_real_escape_string($db,$journal);
    $av = mysqli_real_escape_string($db,$av);
    $ebook = mysqli_real_escape_string($db,$ebook);
    $reference = mysqli_real_escape_string($db,$reference);
    $oclc = mysqli_real_escape_string($db,$oclc);
    $oclc=trim($oclc);
    $loc=trim($loc);
    $libemail=trim($libemail);
    $sqlupdate = "UPDATE `$sealLIB` SET Name = '$libname', alias='$libalias', `ILL Email` ='$libemail',participant=$participant,suspend=$suspend,system='$system',phone='$phone',address1='$address1',address2='$address2',address3='$address3',oclc='$oclc',loc='$loc',book='$book',journal='$journal',av='$av',ebook='$ebook',reference='$reference'  WHERE `recnum` = '$librecnumb' ";
    #echo $sqlupdate;
    $result = mysqli_query($db,$sqlupdate);
    echo  "Library Had Been Edited<br><br>";
    echo "<a href='/adminlib?action=4'>Search for another library</a><br>";
    echo "<a href='/adminlib'>Return to main list</a>";
  }else{
    $GETLISTSQL="SELECT * FROM  `$sealLIB` WHERE `recnum` ='$librecnumb'";
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = '1';
    $row = mysqli_fetch_assoc($GETLIST);
    $libname = $row["Name"];
    $libalias = $row["alias"];
    $libemail = $row["ILL Email"];
    $phone = $row["phone"];
    $libparticipant = $row["participant"];
    $oclc = $row["oclc"];
    $loc = $row["loc"];
    $libsuspend = $row["suspend"];
    $system = $row["system"];
    $address1 = $row["address1"];
    $address2 = $row["address2"];
    $address3 = $row["address3"];
    $book = $row["book"];
    $reference = $row["reference"];
    $av = $row["av"];
    $ebook = $row["ebook"];
    $journal = $row["journal"];
    ?>
    <form action="/adminlib?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
    <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname" value="<?php echo $libname?>"><br>
    <B>Library Alias:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libalias" value="<?php echo $libalias?>"><br>
    <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail" value="<?php echo $libemail?>"><br>
    <B>Library Phone:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="phone" value="<?php echo $phone?>"><br>
    <B>ILL Dept:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address1" value="<?php echo $address1?>"><br>
    <B>Street Address:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address2" value="<?php echo $address2?>"><br>
    <B>City State Zip:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address3" value="<?php echo $address3?>"><br>
    <B>OCLC Symbol:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="oclc" value="<?php echo $oclc?>"><br>
    <B>LOC Location:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="loc" value="<?php echo $loc?>"><br>
    <B>Library ILL participant</b><select name="participant">  <option value="1" <?php if($libparticipant=="1") echo "selected=\"selected\""; ?>>Yes</option><option value="0" <?php if($libparticipant=="0") echo "selected=\"selected\""; ?>>No</option></select><br>
    <B>Suspend ILL</b><select name="suspend">  <option value="0" <?php if($libsuspend=="0") echo "selected=\"selected\""; ?>>No</option><option value="1" <?php if($libsuspend=="1") echo "selected=\"selected\""; ?>>Yes</option></select><br>
    <B>Library System</b><select name="system">
<option value = "CVES" <?php if($system=="CVES") echo "selected=\"selected\""; ?>>Champlain Valley Education Services School Library System</option>
<option value = "CEFL" <?php if($system=="CEFL") echo "selected=\"selected\""; ?>>Clinton Essex Franklin Library System</option>
<option value = "FEH" <?php if($system=="FEH") echo "selected=\"selected\""; ?>>Franklin-Essex-Hamilton BOCES School Library System</option>
<option value = "JLHO" <?php if($system=="JLHO") echo "selected=\"selected\""; ?>>Jefferson-Lewis BOCES School Library System</option>
<option value = "NCLS" <?php if($system=="NCLS") echo "selected=\"selected\""; ?>>North Country Library System</option>
<option value = "NNYLN" <?php if($system=="NNYLN") echo "selected=\"selected\""; ?>>Northern New York Library Network</option>
<option value = "OSW" <?php if($system=="OSW") echo "selected=\"selected\""; ?>>Oswego County School Library System at CiTi</option>
<option value = "SLL" <?php if($system=="SLL") echo "selected=\"selected\""; ?>>St. Lawrence-Lewis BOCES School Library System</option>
    </select><br><br>
    <B>Items willing to loan in SEAL</b><br>
      <table>
      <tr>
      <td><b>Print Book</b><td>
        <input type="radio" name="book" value="1" <?php if($book=="1") echo "checked"; ?>> Yes
        <input type="radio" name="book" value="0" <?php if($book=="0") echo "checked"; ?>> No <br>
      </td></tr>
      <tr>
      <td><b>Print Journal or Article</b><td>
        <input type="radio" name="journal" value="1" <?php if($journal=="1") echo "checked"; ?>> Yes
        <input type="radio" name="journal" value="0" <?php if($journal=="0") echo "checked"; ?>> No <br>
      </td></tr>
      <tr>
      <td><b>Audio Video Materials</b><td>
        <input type="radio" name="av" value="1" <?php if($av=="1") echo "checked"; ?>> Yes
        <input type="radio" name="av" value="0" <?php if($av=="0") echo "checked"; ?>> No <br>
      </td></tr>
      <tr>
      <td><b>Reference</b><td>
        <input type="radio" name="reference" value="1" <?php if($reference=="1") echo "checked"; ?>> Yes
        <input type="radio" name="reference" value="0" <?php if($reference=="0") echo "checked"; ?>>No <br>
      </td></tr>
      <tr>
      <td><b>Electronic Book</b><td>
        <input type="radio" name="ebook" value="1" <?php if($ebook=="1") echo "checked"; ?>> Yes
        <input type="radio" name="ebook" value="0" <?php if($ebook=="0") echo "checked"; ?>> No <br>
      </td></tr>
    </table>
    <input type="submit" value="Submit">
    </form>
    <?php
  }
}else{
  #By default show all libraries in a browse list
  $GETLISTSQL="SELECT * FROM `$sealLIB` ORDER BY Name Asc";
  $retval = mysqli_query($db, $GETLISTSQL);
  $GETLISTCOUNTwhole = mysqli_num_rows ($retval);
  #limit 10 libraries per page
  $rec_limit = 25;
  $pagerow = mysqli_fetch_array($retval, MYSQLI_NUM );
  #Setting up Pagination
  $rec_count = $pagerow[0];
  if( isset($_GET{'page'} ) ){
    $page = $_GET{'page'} + 1;
    $offset = $rec_limit * $page ;
  }else{
    $page = 0;
    $offset = 0;
  }
  $left_rec = $rec_count - ($page * $rec_limit);
  $GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
  #Get the actual 25 libraries for the page we are browsing
  $GETLIST = mysqli_query($db, $GETLISTSQL);
  $GETLISTCOUNT = mysqli_num_rows ($GETLIST);
  #Display the result as html for user with action
  echo "<a href='adminlib?action=1'>Would you like to add a library?</a><br>";
  echo "<a href='adminlib?action=4'>Would you like to search for a library?</a><br><br>";
  echo "$GETLISTCOUNTwhole results";
  echo "<table><tr><th>Library</th><th>Alias</th><th>Participant</th><th>Suspend</th><th>System</th><th>OCLC</th><th>ILL</th><th>Action</th></tr>";
  $rowtype=1;
    while ($row = mysqli_fetch_assoc($GETLIST)) {
        $librecnumb = $row["recnum"];
        $libname = $row["Name"];
        $libalias = $row["alias"];
        $libparticipant = $row["participant"];
        $oclc = $row["oclc"];
        $loc = $row["loc"];
        $libsuspend = $row["suspend"];
        $system = $row["system"];
        if($libsuspend=="1") $libsuspend="Yes";
        if($libsuspend=="0") $libsuspend="No";
        if($libparticipant =="1") $libparticipant ="Yes";
        if($libparticipant =="0") $libparticipant ="No";
        if ($rowtype & 1 ) {
          echo "<tr class='odd'><Td>$libname</td><td>$libalias</td><td>$libparticipant</td><td>$libsuspend</td><td>$system</td><td>$oclc</td><td>$loc</td> ";
          echo "<td><a href='adminlib?action=2&librecnumb=$librecnumb'>Edit</a>  <a href='adminlib?action=3&librecnumb=$librecnumb''>Delete</a> </td></tr>";
        } else {
          echo "<tr class='even'><Td>$libname</td><td>$libalias</td><td>$libparticipant</td><td>$libsuspend</td><td>$system</td><td>$oclc</td><td>$loc</td> ";
          echo "<td><a href='adminlib?action=2&librecnumb=$librecnumb'>Edit</a>  <a href='adminlib?action=3&librecnumb=$librecnumb''>Delete</a> </td></tr>";
        }
        $rowtype = $rowtype + 1;
     }
  echo "</table>";
  #Show pagination links under the data that has been displayed
   if(( $page > 0 ) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole)){
     $last = $page - 2;
     echo "<a href='adminlib?page=$last\'>Last 25 Records</a> |";
     echo "<a href='adminlib?page=$page\'>Next 25 Records</a>";
   }else if (( $page == 0 ) && ( $GETLISTCOUNTwhole  > $rec_limit )){
     echo "<a href='adminlib?page=$page\'>Next 25 Records</a>";
   }else if ($GETLISTCOUNTwhole > $rec_limit){
     $last = $page - 2;
    echo "<a href='adminlib?page=$last\'>Last 25 Records</a>";
  }
}
?>
