<?php

###libprofile.php###

#####See if LOC is set, if not set to 0
if (isset($_GET['loc'])){  $loc = $_GET['loc'];  }else{$loc='null';}

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);


 if ($_SERVER['REQUEST_METHOD'] == 'POST'){
       $libname=$_REQUEST["libname"];
       $libemail=$_REQUEST["libemail"];
       $phone=$_REQUEST["phone"];
       $address1=$_REQUEST["address1"];
       $address2=$_REQUEST["address2"];
       $address3=$_REQUEST["address3"];
       $oclc=$_REQUEST["oclc"];
       $suspend=$_REQUEST["suspend"];
       $book=$_REQUEST["book"];
       $av=$_REQUEST["av"];
       $journal=$_REQUEST["journal"];
       $ebook=$_REQUEST["ebook"];
       $reference=$_REQUEST["reference"];
       $libname = mysqli_real_escape_string($db,$libname);
       $libemail =mysqli_real_escape_string($db,$libemail);
       $phone = mysqli_real_escape_string($db,$phone);
       $address1 =mysqli_real_escape_string($db,$address1);
       $address2 =mysqli_real_escape_string($db,$address2);
       $address3 =mysqli_real_escape_string($db,$address3);
       $oclc = mysqli_real_escape_string($db,$oclc);
       $suspend = mysqli_real_escape_string($db,$suspend);
       $book = mysqli_real_escape_string($db,$book);
       $journal = mysqli_real_escape_string($db,$journal);
       $av = mysqli_real_escape_string($db,$av);
       $ebook = mysqli_real_escape_string($db,$ebook);
       $reference = mysqli_real_escape_string($db,$reference);
        $oclc = trim($oclc);
       $libemail=trim($libemail);
       $sqlupdate = "UPDATE `$sealLIB` SET Name = '$libname',  `ILL Email` ='$libemail',suspend=$suspend,phone='$phone',address1='$address1',address2='$address2',address3='$address3',oclc='$oclc',book='$book',journal='$journal',av='$av',ebook='$ebook',reference='$reference'  WHERE `loc` ='$loc'";
       #echo $sqlupdate;
       $result = mysqli_query($db,$sqlupdate);

       echo  "Library Had Been Edited<br><br>";
       echo "<a href='/user'>Return to My Account</a>";
   }else{
      $GETLISTSQL="SELECT * FROM  `$sealLIB` WHERE `loc` ='$loc' limit 1 ";
      $GETLIST = mysqli_query($db,$GETLISTSQL);
      $GETLISTCOUNT = '1';


         while ($row = mysqli_fetch_assoc($GETLIST)) {
        $libname = $row["Name"];
        $libalias = $row["alias"];
        $libemail = $row["ILL Email"];
        $oclc = $row["oclc"];
        $loc = $row["loc"];
        $phone = $row["phone"];
        $address1  = $row["address1"];
        $address2  = $row["address2"];
        $address3  = $row["address3"];
        $libparticipant  = $row["participant"];
        $libsuspend  = $row["suspend"];
        $system = $row["system"];
        $book = $row["book"];
        $reference = $row["reference"];
        $av = $row["av"];
        $ebook = $row["ebook"];
        $journal = $row["journal"];
     }
    if ($loc != 'null'){
     ?>
     <form action="/libprofile?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
     <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname" value="<?php echo $libname?>"><br>
     <B>Library Alias:</b> <?php echo $libalias?><br>
     <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail" value="<?php echo $libemail?>"><br>
     <B>Library Phone:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="phone" value="<?php echo $phone?>"><br>
     <B>Library Address Dept:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address1" value="<?php echo $address1?>"><br>
     <B>Library Address Street</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address2" value="<?php echo $address2?>"><br>
     <B>Library Address City and State</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address3" value="<?php echo $address3?>"><br>
     <B>OCLC Symbol:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="oclc" value="<?php echo $oclc?>"><br>
     <B>ILL Code:</b> <?php echo $loc?><br>

     <B>Suspend Your Library's lending status?   </b><select name="suspend">  <option value="0" <?php if($libsuspend=="0") echo "selected=\"selected\""; ?>>No</option><option value="1" <?php if($libsuspend=="1") echo "selected=\"selected\""; ?>>Yes</option></select><br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>YES</strong> will <strong>prevent</strong> your library getting ILL requests<br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>NO</strong> will <strong>allow</strong> your library to receive ILL requests.<br>
    <?php if($system=="CVES"){ $system="Champlain Valley Education Services School Library System";}?>
     <?php if($system=="CEFL"){ $system="Clinton Essex Franklin Library System";}?>
     <?php if($system=="FEH"){ $system="Franklin-Essex-Hamilton BOCES School Library System";}?>
     <?php if($system=="JLHO"){ $system="Jefferson-Lewis BOCES School Library System";}?>
     <?php if($system=="NCLS"){ $system="North Country Library System";}?>
     <?php if($system=="NNYLN"){ $system="Northern New York Library Network";}?>
     <?php if($system=="OSW"){ $system="Oswego County School Library System at CiTi";}?>
     <?php if($system=="SLL"){ $system="St. Lawrence-Lewis BOCES School Library System";}?>
    <B>Library System:</b> <?php echo $system?><br>
      <br><br>
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
      <?php echo "<input type='hidden' name='loc' value= ' ".$loc ." '>";?><br><br>
<strong>Please click on Submit to save your profile<br></strong>
     <input type="submit" value="Submit">
    </form>
   <?php
    }
   }
