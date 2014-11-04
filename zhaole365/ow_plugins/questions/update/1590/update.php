<?php

if ( Updater::getConfigService()->configExists('questions', 'ev_page_visited') )
{
    Updater::getConfigService()->saveConfig('questions', 'ev_page_visited', 0);
}

try {
    $action = Updater::getAuthorizationService()->findAction("questions", "delete_comment_by_content_owner");
    if ( !empty($action) )
    {
        Updater::getAuthorizationService()->deleteAction($action->id);
    }
} catch (Exception $e) {}
