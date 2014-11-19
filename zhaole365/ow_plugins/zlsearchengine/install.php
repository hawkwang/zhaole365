<?php



BOL_LanguageService::getInstance()->addPrefix('zlsearchengine', '找乐搜索引擎');

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlsearchengine')->getRootDir().'langs.zip', 'zlsearchengine');

//adding admin settings page
OW::getPluginManager()->addPluginSettingsRouteName('zlsearchengine', 'zlsearchengine.admin');

//
if ( !OW::getConfig()->configExists('zlsearchengine', 'searchengine_url') )
{
	OW::getConfig()->addConfig('zlsearchengine', 'searchengine_url', '', '搜索引擎WEB服务地址');
}
