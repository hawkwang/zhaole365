<?php

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'uheader');

$sql = array();

$sql[] = "ALTER TABLE `" . OW_DB_PREFIX . "uheader_cover` ADD `templateId` int(11) DEFAULT NULL";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "uheader_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  `default` int(11) NOT NULL DEFAULT '0',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "uheader_template_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `templateId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `templateId` (`templateId`,`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

foreach ( $sql as $query )
{
    Updater::getDbo()->query($query);
}

Updater::getConfigService()->addConfig('uheader', 'tpl_view_mode', 'list', 'Cover gallery view mode');

try {
    $plugin = OW::getPluginManager()->getPlugin("uheader");
    $basePlugin = OW::getPluginManager()->getPlugin("base");
    
    spl_autoload_register(array('OW_Autoload', 'autoload'));
    
    OW::getAutoloader()->addPackagePointer("UHEADER_BOL", $plugin->getBolDir());
    OW::getAutoloader()->addPackagePointer("UHEADER_CLASS", $plugin->getClassesDir());
    OW::getAutoloader()->addPackagePointer("BASE_CLASS", $basePlugin->getClassesDir());
} catch (Exception $e) {}

try {
    //Add default templates
    $templatesBridge = UHEADER_CLASS_TemplatesBridge::getInstance();
    $templatesBridge->addBuiltInCovers();
} catch (Exception $e) {}

try {
    UHEADER_BOL_Service::getInstance()->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE1, "base-gender-age");
    UHEADER_BOL_Service::getInstance()->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE2, "base-about");
} catch (Exception $e) {}