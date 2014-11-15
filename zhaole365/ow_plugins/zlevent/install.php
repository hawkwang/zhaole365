<?php

OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "zlevent_invite` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "zlevent_item` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "zlevent_user` ");

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlevent_invite` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `displayInvitation` BOOL NOT NULL DEFAULT '1',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `inviteUnique` (`userId`,`inviterId`,`eventId`),
  KEY `userId` (`userId`),
  KEY `inviterId` (`inviterId`),
  KEY `eventId` (`eventId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlevent_item` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `location` text NOT NULL,
  `createTimeStamp` int(11) NOT NULL,
  `startTimeStamp` int(11) NOT NULL,
  `endTimeStamp` int(11) default NULL,
  `userId` int(11) NOT NULL,
  `whoCanView` tinyint(4) NOT NULL,
  `whoCanInvite` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `image` VARCHAR(32) default NULL,
  `endDateFlag` BOOL NOT NULL DEFAULT '0',
  `startTimeDisabled` BOOL NOT NULL DEFAULT '0',
  `endTimeDisabled` BOOL NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlevent_user` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `eventUser` (`eventId`,`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

// added by hawk,
// 活动地址
OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlevent_event_location` (
		`id` int(11) NOT NULL auto_increment,
		`eventId` int(11) NOT NULL,
		`locationId` int(11) NOT NULL,
		`location` text NOT NULL,
		PRIMARY KEY  (`id`),
		UNIQUE KEY `eventId` (`eventId`,`locationId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;");

// 活动隶属的乐群
OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "zlevent_event_group` (
		`id` int(11) NOT NULL auto_increment,
		`eventId` int(11) NOT NULL,
		`groupId` int(11) NOT NULL,
		PRIMARY KEY  (`id`),
		UNIQUE KEY `eventId` (`eventId`,`groupId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;");


// ended by hawk

$authorization = OW::getAuthorization();
$groupName = 'zlevent';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_event');
$authorization->addAction($groupName, 'view_event', true);
$authorization->addAction($groupName, 'add_comment');

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlevent')->getRootDir() . 'langs.zip', 'zlevent');