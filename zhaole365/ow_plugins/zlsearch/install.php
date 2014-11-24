<?php



BOL_LanguageService::getInstance()->addPrefix('zlsearch', '去找乐吧');

//installing language pack
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('zlareas')->getRootDir().'langs.zip', 'zlareas');

