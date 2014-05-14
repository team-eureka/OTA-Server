<?php

/**
 * 
 * @name Team-Eureka OTA Backend
 * @author ddggttff3 (chrisrblake93@gmail.com)
 * @license GPLv3 (When we Release)
 * @version 2.4 (Updated 5/13/2014)
 * @copyright Team-Eureka 2014
 * 
 */
 
# DEBUG
#error_reporting(E_ALL);
#ini_set('display_errors', '1');

#Get vars
include ("../variables.php");

# Define Rollout Range
# This is a copy of the version stored in update.php's check function
$ror = round(1 + (round(time() - $array['Timestamp']) / 86400) * 60);

# Compare version to latest ver
if ($_GET['version'] != null && $_GET['serial'] != null) {
    $VersionCheck = mysqli_real_escape_string($DBcon, $_GET['version']);
    $DeviceSerial = mysqli_real_escape_string($DBcon, $_GET['serial']);

    # Latest Rom Ver
    $RomVerCall = mysqli_fetch_array(mysqli_query($DBcon,
        "SELECT `Version` FROM `$DBprefix-available_updates` WHERE `TestBuild`=\"0\" ORDER BY `ID` DESC LIMIT 1;"));
    
    # Do the check
    if ($VersionCheck < $RomVerCall['Version'] && hexdec(substr($DeviceSerial, -2)) <= $ror ){
        echo "1";
    } else {
        echo "0";
    }
} else {
    echo "0";
}
mysqli_close($DBcon);
?>