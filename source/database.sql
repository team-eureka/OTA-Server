SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `ota-available_updates` (
  `ID` int(5) NOT NULL AUTO_INCREMENT,
  `Version` varchar(10) NOT NULL,
  `TestBuild` int(2) NOT NULL,
  `MinorUpdate` int(2) NOT NULL,
  `Requires` varchar(10) DEFAULT NULL,
  `Timestamp` int(12) NOT NULL,
  `DownloadURL` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `ota-devices` (
  `ID` int(6) NOT NULL AUTO_INCREMENT,
  `Serial` varchar(42) NOT NULL,
  `Version` varchar(10) NOT NULL,
  `First_Seen` varchar(32) NOT NULL,
  `Last_Update` varchar(32) NOT NULL,
  `Last_Seen` varchar(32) NOT NULL,
  `Count` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1666 ;

CREATE TABLE IF NOT EXISTS `ota-test_devices` (
  `ID` int(6) NOT NULL AUTO_INCREMENT,
  `user` varchar(24) NOT NULL,
  `Serial` varchar(42) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

CREATE TABLE IF NOT EXISTS `ota-update_checks` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Time` int(32) NOT NULL,
  `Serial` varchar(42) NOT NULL,
  `Device_Version` varchar(10) NOT NULL,
  `Test_Device` varchar(5) NOT NULL,
  `Update_Sent` varchar(5) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=190731 ;
