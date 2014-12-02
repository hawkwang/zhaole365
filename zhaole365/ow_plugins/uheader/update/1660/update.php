<?php

try {
    $action = Updater::getAuthorizationService()->findAction("uheader", "delete_comment_by_content_owner");
    if ( !empty($action) )
    {
        Updater::getAuthorizationService()->deleteAction($action->id);
    }
} catch (Exception $e) {}


Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'uheader');
