<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$widgetService = BOL_ComponentAdminService::getInstance();

try 
{
    $widget = $widgetService->addWidget('BASE_CMP_UserAvatarWidget', false);
    $widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);

    $widgetService->deleteWidget('UHEADER_CMP_HeaderWidget');
}
catch ( Exception $e ) {}

