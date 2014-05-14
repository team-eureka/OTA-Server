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

#Get vars
include ("./variables.php");

if (!$DBcon) {
    $UpdatedCount['Total'] = 0;
    $DeviceCount = 0;
    $TestDeviceCount = 0;
    $TimeCall['Total'] = 0;
    $AssocCall['Total_Count'] = 0;
} else {

    # Latest Rom Ver
    $RomVerCall = mysqli_fetch_array(mysqli_query($DBcon,
        "SELECT `Version` FROM `$DBprefix-available_updates` WHERE `TestBuild`=\"0\" ORDER BY `ID` DESC LIMIT 1;"));
    $RomVer = $RomVerCall['Version'];

    # How many devices do we have? use to count up ID
    $DeviceCount = mysqli_num_rows(mysqli_query($DBcon,
        "SELECT * FROM `$DBprefix-devices` WHERE 1 ORDER BY `ID`;"));

    # And Test Devices?
    $TestDeviceCount = mysqli_num_rows(mysqli_query($DBcon,
        "SELECT * FROM `$DBprefix-test_devices` WHERE 1 ORDER BY `ID`;"));

    # Total # of calls?
    $DBCallforCalls = mysqli_query($DBcon,
        "SELECT SUM(Count) AS Total_Count FROM `$DBprefix-devices`");
    $AssocCall = mysqli_fetch_assoc($DBCallforCalls);

    # Total up to date (based on latest ver)
    $DBUpdatedCountCall = mysqli_query($DBcon,
        "SELECT COUNT(*) AS Total FROM `$DBprefix-devices` WHERE `Version`>=\"$RomVer\"");
    $UpdatedCount = mysqli_fetch_assoc($DBUpdatedCountCall);

    # Total calls in last 24 hours
    $DayAgo = strtotime("-1 day");
    $DBTimeCall = mysqli_query($DBcon,
        "SELECT COUNT(*) AS Total FROM `$DBprefix-devices` WHERE `Last_Seen`>=\"$DayAgo\"");
    $TimeCall = mysqli_fetch_assoc($DBTimeCall);

    # Goodbye
    mysqli_close($DBcon);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Team Eureka OTA Server</title>
</head>
<body>
<h1>Welcome to the Team Eureka OTA Update Server</h1>
<h4>Latest Build: <?php echo $RomVer; ?></h4>
<h4>Number of devices on the latest Build: <?php echo $UpdatedCount['Total']; ?></h4>
<h4>Number of devices using Team Eureka OTA: <?php echo $DeviceCount; ?></h4>
<!-- Hello stranger, this is commented out because most people don't care, so yeah -->
<!-- <h4>Number of test devices registered: <?php echo $TestDeviceCount; ?></h4> -->
<h4>Number of update checks in the last day: <?php echo $TimeCall['Total']; ?></h4>
<h4>Total number of update checks processed: <?php echo $AssocCall['Total_Count']; ?></h4>
<h2>FAQ:</h2>
<ul>
  <li>How often does my chromecast for updates?</li>
    <ul><li>On boot, and then every 20 hours after that. It is completely silent and automatic.</li></ul>
    <br />
  <li>So what does it do when a update is available?</li>
    <ul><li>If a update is available, it will automatically download the update in the background, and when finished and verified, it will reboot into FlashCast Recovery to flash the update. The entire process of flashing should take no longer than 10 minutes.</li></ul>
    <br />
  <li>Does it send my devices serial number?</li>
    <ul><li>No. What it does send is a SHA1 copy of your serial number (which is undecryptable), and your current version number. The reason I need the SHA1 sum is to help with rolling updates to prevent excessive network loads on my end, as well as to flag select devices as "test" devices for testing builds.</li></ul>
    <br />
  <li>Can I disable this service?</li>
    <ul><li>Yes, if you would like to disable this feature and you are on a 13300 based build, then just flash <a href="http://pdl.team-eureka.com/ota/disable/eureka_image.zip">THIS</a> file using FlashCast. For builds based on 14975 and up, you can disable OTA updates from the web panel.</li></ul>
    <br />
  <li>Is the source code available?</li>
    <ul><li>The source code for the software script and custom recovery image are availabe on my <a href="https://github.com/team-eureka/PwnedCast-OTA">GitHub</a>.</li></ul>
    <br />
  <li>Is the source code for this website available?</li>
    <ul><li>The code that runs our backend will not be public until we feel we have ironed out all of the possible security flaws. Once we feel its safe enough for public release, we will upload it to a github repo. Until then, you can contact ddggttff3 with questions on how it works.</li></ul>
    <br />
</ul>
<p>If you have any questions, feel free to PM me on XDA at ddggttff3, or message me on reddit at riptide_wave</p>
<!-- Trust me, I know this site looks like crap. Want to make somthing nicer for me? Email me at chris(at)servernetworktech(dot)com! -->
</body>
</html>

