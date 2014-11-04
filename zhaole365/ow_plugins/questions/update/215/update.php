<?php

if ( !Updater::getConfigService()->configExists('questions', 'allow_popups') )
{
    Updater::getConfigService()->addConfig('questions', 'allow_popups', 1);
}

if ( Updater::getConfigService()->configExists('questions', 'ev_page_visited') )
{
    Updater::getConfigService()->saveConfig('questions', 'ev_page_visited', 0);
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'questions');
