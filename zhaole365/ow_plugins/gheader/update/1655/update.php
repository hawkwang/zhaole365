<?php

$widgetService = UPDATE_WidgetService::getInstance();

try
{
    $infoWidget = $widgetService->addWidget('GHEADER_CMP_InfoWidget', false);
    $infoPlaceWidget = $widgetService->addWidgetToPlace($infoWidget, 'group');
    $widgetService->addWidgetToPosition($infoPlaceWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);
}
catch ( Exception $e )
{
    // Log
}

$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'gheader');