<?php


$plugin = OW::getPluginManager()->getPlugin('zlgroups');

$dbPrefix = OW_DB_PREFIX;

//安装“乐群”表，“乐群用户”表，“乐群邀请”表
$sql =
    <<<EOT
CREATE TABLE IF NOT EXISTS `{$dbPrefix}zlgroups_group` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `imageHash` varchar(32) default NULL,
  `timeStamp` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `privacy` varchar(100) NOT NULL default 'everybody',
  `whoCanView` varchar(100) NOT NULL default 'anyone',
  `whoCanInvite` varchar(100) NOT NULL default 'participant',
  PRIMARY KEY  (`id`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `whoCanView` (`whoCanView`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE `{$dbPrefix}zlgroups_group_location` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `locationId` int(11) NOT NULL,
  `location` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `groupId` (`groupId`,`locationId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE `{$dbPrefix}zlgroups_group_user` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `privacy` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `groupId` (`groupId`,`userId`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


CREATE TABLE `{$dbPrefix}zlgroups_invite` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `inviteUniq` (`groupId`,`userId`,`inviterId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `groupId` (`groupId`),
  KEY `viewed` (`viewed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		
EOT;

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('zlgroups', 'zlgroups-admin-widget-panel');
OW::getPluginManager()->addUninstallRouteName('zlgroups', 'zlgroups-admin-uninstall');

// Add widgets
$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('ZLGROUPS_CMP_JoinButtonWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $widgetService->addWidget('ZLGROUPS_CMP_BriefInfoWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('ZLGROUPS_CMP_UserListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $widgetService->addWidget('ZLGROUPS_CMP_LeaveButtonWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $widgetService->addWidget('ZLGROUPS_CMP_WallWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
//$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

$widget = $widgetService->addWidget('ZLGROUPS_CMP_InviteWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, 'zlgroup');
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

$widget = $widgetService->addWidget('BASE_CMP_CustomHtmlWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');

$widget = $widgetService->addWidget('BASE_CMP_RssWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');

// 设置语言包
BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'zlgroups');

// 设置访问权限
$authorization = OW::getAuthorization();
$groupName = 'zlgroups';
$authorization->addGroup($groupName);

$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'create');
$authorization->addAction($groupName, 'view', true);

// 设置乐群论坛关联配置
$config = OW::getConfig();

if ( !$config->configExists('zlgroups', 'is_forum_connected') )
{
    OW::getConfig()->addConfig('zlgroups', 'is_forum_connected', 0, 'Add Forum to zlgroups plugin');
}