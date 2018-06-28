<?php
### _tasks.php :: this is a library containing functions for lender_tasks and borrower_tasks.

function build_notes($reqnote,$lendnote) {
  $displaynotes = "";
  if ( (strlen($reqnote) > 2) && (strlen($lendnote) > 2) ) {
    $displaynotes = $reqnote . "</br>Lender Note: " . $lendnote;
  }
  if ( (strlen($reqnote) > 2) && (strlen($lendnote) < 2) ) $displaynotes=$reqnote;
  if ( (strlen($reqnote) < 2) && (strlen($lendnote) > 2) ) $displaynotes= "Lender Note: " . $lendnote;
  return $displaynotes;
}

function checked($filter_value) {
  if ( ($filter_value == "yes") ) {
    $filterout="checked";
  } else {
    $filterout="";
  }
  return $filterout;
}

function selected($days,$filter_value) {
  if ($days == $filter_value) {
    $filterout = "selected";
  } else {
    $filterout = "";
  }
  return $filterout;
}

function displaymodenav($task,$pagemode,$loc,$target,$hasnew) {
  if ($task == "lend") {
    echo "<br>";
    switch ($pagemode) {
      case 0: #Open
        if ($hasnew == "1") {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>{{new}}</a> : ";
        } else {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>new</a> : ";
        }
        echo "OPEN : ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'>complete</a>";
        break;
      case 1: #New
        echo "NEW : ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'>open</a> : ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'>complete</a>";
        break;
      case 2: #Complete
        if ($hasnew == "1") {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>{{new}}</a> : ";
        } else {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>new</a> : ";
        }
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'>open</a> : ";
        echo "COMPLETE";
        break;
    }
  } else {
    switch ($pagemode) {
      case 0:
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>new</a> : ";
        echo "OPEN : ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'>complete</a>";
        break;
      case 2:
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>new</a> : ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'>open</a> : ";
        echo "COMPLETE";
        break;
    }
  }
}

function buildsql($task,$pagemode,$loc,$filter_yes,$filter_no,$filter_expire,$filter_cancel,$filter_days,$filter_sent,$filter_noans,$sealSTAT) {

  if ($task == "lend") {
    $subject = "Destination";
  } else {
    $subject = "Requester LOC";
  }

  $SQLBASE="SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$sealSTAT` WHERE `$subject` = '$loc' ";
  $SQLEND=" ORDER BY `index`  DESC";

  if ($pagemode == 1) {
    $returnsql = $SQLBASE . "and `Fill` = 3" . $SQLEND;
    return $returnsql;
  }

  if ($filter_days == "all") {
    $SQL_DAYS = "";
  } else {
    $SQL_DAYS = " AND (DATE(`Timestamp`) BETWEEN NOW() - INTERVAL " . $filter_days . " DAY AND NOW() )";
  }

  #if (strlen($filter_illnum) > 2 ) {
  #  $SQLILL = " AND `illNUB` = '" . $filter_illnum . "'";
  #} else {
  #  $SQLILL = "";
  #}
  #
  #if (strlen($filter_destination) > 2 ) {
  #  $SQL_Dest_Search="SELECT `loc` FROM `SENYLRC-SEAL2-Library-Data` where `Name` like '%$filter_destination%'";
  #  $PossibleDests=mysqli_query($db, $SQL_Dest_Search);
  #  while ($rowdest = mysqli_fetch_assoc($PossibleDests)) {
  #    $destloc=$rowdest["loc"];
  #    if (strlen($SQL_DESTINATION) > 2) {
  #      $SQL_DESTINATION = $SQL_DESTINATION . " OR `Requester LOC` = '$destloc'";
  #    } else {
  #      $SQL_DESTINATION = " AND (`Requester LOC` = '$destloc'";
  #    }
  #  }
  #  $SQL_DESTINATION = $SQL_DESTINATION . ")";
  #} else {
  #  $SQL_DESTINATION = "";
  #}

  $SQLMIDDLE ='';

  if ($pagemode == 2) {
    if ($filter_yes == "yes") $SQLMIDDLE = "`fill`= 1 ";
    if ($filter_no == "yes") {
      if (strlen($SQLMIDDLE) > 2 ) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 0 ";
      } else {
        $SQLMIDDLE = "`fill`= 0 ";
      }
    }
    if ($filter_noans == "yes") {
      if (strlen($SQLMIDDLE) > 2 ) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 3 ";
      } else {
        $SQLMIDDLE = "`fill`= 3 ";
      }
    }
    if ($filter_expire == "yes") {
      if (strlen($SQLMIDDLE) > 2 ) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 4 ";
      } else {
        $SQLMIDDLE = "`fill`= 4 ";
      }
    }
    if ($filter_cancel == "yes") {
      if (strlen($SQLMIDDLE) > 2 ) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 6 ";
      } else {
        $SQLMIDDLE = "`fill`= 6 ";
      }
    }
    if ($filter_sent == "yes") {
      if (strlen($SQLMIDDLE) > 2 ) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `LenderStatus`= 'Sent' ";
      } else {
        $SQLMIDDLE = "`LenderStatus`= 'Sent' ";
      }
    }
  }

  #$GETLISTSQL = $SQLBASE . $SQL_DESTINATION . $SQL_DAYS . $SQLILL . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
  if ($pagemode == 0 ) {
    $returnsql = $SQLBASE . $SQL_DAYS . "AND `fill` = 1  AND `LenderStatus` = '' ". $SQLEND;
  } else {
    $returnsql = $SQLBASE . $SQL_DAYS . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
  }
  return $returnsql;
}
