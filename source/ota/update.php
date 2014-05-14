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

# connect to database, else die/fail
if (!$DBcon) {
    echo "NoUpdate"; # we do this so chromecasts just think they are up to date
    exit();
}

# Ban known attempted exploiter(s)
# We need to automate this process but I am lazy
if ($_SERVER['REMOTE_ADDR'] == "98.232.46.115") {
    echo "Enjoy the BanHammer! :D";
    exit();
}

# Define Functions

# Used to check if a device serial is that of a test device
function TestDeviceCheck($DB, $Prefix, $Serial)
{
    #Default to false
    $return = "False";
    $MySQLPull = mysqli_query($DB,
        "SELECT `Serial` FROM `$Prefix-test_devices` ORDER BY `ID`;");
    while ($daserial = mysqli_fetch_array($MySQLPull)) {
        if ($daserial['Serial'] == $Serial) {
            $return = "True";
            break;
        }
    }
    return $return;
}

# Used to log every call to the server for debug purposes
function LogCall($DB, $Prefix, $A, $B, $C, $D, $E)
{
    mysqli_query($DB, "INSERT INTO `$Prefix-update_checks` (`Time`, `Serial`, `Device_Version`, `Test_Device`, `Update_Sent`) VALUES ('$A', '$B', '$C', '$D', '$E');");
}

# Used to add a new device to the database
function CreateDevice($DB, $Prefix, $B, $C, $D, $E)
{
    mysqli_query($DB, "INSERT INTO `$Prefix-devices` (`Serial`, `Version`, `First_Seen`, `Last_Update`, `Last_Seen`, `Count`) VALUES ('$B', '$C', '$D', '$E', '$D', '1');");
}

# Used to check for, and return if a update is available and a DL link
function UpdateCheck($db, $Prefix, $currentver, $testdev, $deviceserial, $ForceMode)
{
    # Pull all updates and put into an array, newest update first (cut down on run time)
    $MySQLPull = mysqli_query($db,
        "SELECT * FROM `$Prefix-available_updates` ORDER BY `ID` DESC;");

    #Lets default to no updates before the loop
    $return['available'] = false;
    $return['link'] = "";

    #For each update, find one we need
    while ($array = mysqli_fetch_array($MySQLPull)) {
        # Define Rollout Range
        # Starting digit of 1, moves up by 60 every day, our max range needed is 256
        # This allows rollouts to be complete within 4.26 days, but with the 20 hour sleep, it makes it around 5 days
        $ror = round(1 + (round(time() - $array['Timestamp']) / 86400) * 60);

        # checking if the update is for us
        if ($testdev == "True") {
            # Test Device
            if ($array['Version'] > $currentver) {
                #Is it newer?
                if ($array['Requires'] <= $currentver) {
                    #Do we meet the req version?
                    #If so do the update
                    $return['available'] = true;
                    $return['link'] = $array['DownloadURL'];
                    break;
                }
            }
        } else {
            # Normal Device
            if ($array['TestBuild'] != "1") {
                # Is it a non test-device image?
                if ($ForceMode == "true" || hexdec(substr($deviceserial, -2)) <= $ror) {
                    # are we in the rollout range or forced?
                    if ($array['Version'] > $currentver) {
                        #Is it newer?
                        if ($array['Requires'] <= $currentver) {
                            #Do we meet the req version?
                            #If so do the update
                            $return['available'] = true;
                            $return['link'] = $array['DownloadURL'];
                            break;
                        }
                    }
                }
            }
        }
    }
    # Return Array
    return $return;
}

# Used to update a device already in the database
function UpdateDevice($DB, $Prefix, $A, $B, $C, $D, $E)
{
    $DBDataPull = mysqli_fetch_assoc(mysqli_query($DB,
        "SELECT `Count` FROM `$Prefix-devices` WHERE `Serial`='$C';"));
    $FinalCount = $DBDataPull["Count"] + 1;
    if ($D == true) {
        mysqli_query($DB, "UPDATE `$Prefix-devices` SET `Version`='$A', `Last_Seen`='$B', `Last_Update`='$E', `Count`='$FinalCount' WHERE `Serial`='$C'");
    } else {
        mysqli_query($DB, "UPDATE `$Prefix-devices` SET `Version`='$A', `Last_Seen`='$B', `Count`='$FinalCount' WHERE `Serial`='$C'");
    }
}

# Are we doing a real check?
if ($_GET['version'] != null && $_GET['serial'] != null) {

    #Vars for logging
    $SerialNum = mysqli_real_escape_string($DBcon, $_GET['serial']);
    $VersionNum = mysqli_real_escape_string($DBcon, $_GET['version']);
    $CheckTime = time();

    # Set force mode, even if not sent vi URL. Prevents errors/bugs/problems/bad stuff
    if (isset($_GET['force'])) {
        $ForceCheck = mysqli_real_escape_string($DBcon, $_GET['force']);
    } else {
        $ForceCheck = "false";
    }

    # Str Replace so math works
    # This is a dirty hack to fix issues. because issues.
    # actually its to fix my fail of using a string instead of a number. we fix that now
    # We use .00 because this is to fix versions 9 and below. at version 10 we have it run proper
    if (strpos($VersionNum, '-')) {
        $VersionNum = str_replace("-", ".00", $VersionNum);
    }

    # Dirty Hack #2
    # I derped the release serial for 10, so lets fix it before its checked
    # This wont be a issue for the .100 release because we will never get there with the 13300 base
    if ($VersionNum == "13300.10") {
        $VersionNum = "13300.010";
    }

    # Simple spam/hack protection
    # We check the sha1sum length, and verify version is a number
    if (strlen($SerialNum) != "40" or !is_numeric($VersionNum)) {
        # Pretend no update exists just to throw them off
        echo "NoUpdate";
        exit();
    }

    # is it a test device?
    $IsTestDevice = TestDeviceCheck($DBcon, $DBprefix, $SerialNum);

    # Do we need a update?
    $UpdateCheckFun = UpdateCheck($DBcon, $DBprefix, $VersionNum, $IsTestDevice, $SerialNum, $ForceCheck);
    if ($UpdateCheckFun['available'] == true) {
        echo $UpdateCheckFun['link'];
        $DeviceUpdate = time();
        LogCall($DBcon, $DBprefix, $CheckTime, $SerialNum, $VersionNum, $IsTestDevice, "True");
    } else {
        echo "NoUpdate";
        $DeviceUpdate = null;
        LogCall($DBcon, $DBprefix, $CheckTime, $SerialNum, $VersionNum, $IsTestDevice, "False");
    }

    # Lets log this device
    if (mysqli_num_rows(mysqli_query($DBcon,
        "SELECT * FROM `$DBprefix-devices` WHERE `Serial`='$SerialNum';")) == 0) {
        CreateDevice($DBcon, $DBprefix, $SerialNum, $VersionNum, $CheckTime, $DeviceUpdate);
    } else {
        if ($DeviceUpdate != null) {
            UpdateDevice($DBcon, $DBprefix, $VersionNum, $CheckTime, $SerialNum, true, $DeviceUpdate);
        } else {
            UpdateDevice($DBcon, $DBprefix, $VersionNum, $CheckTime, $SerialNum, false, $DeviceUpdate);
        }
    }

} else {
    # what you doing?
    echo "InvalidRequest";
}
# Close all mysql connections we left open
mysqli_close($DBcon);
?>
