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
    $widget = $widgetService->addWidget('UHEADER_CMP_HeaderWidget', false);
    $widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_TOP, 0);

    $widgetService->deleteWidget('BASE_CMP_UserAvatarWidget');
}
catch ( Exception $e ) {}


require_once dirname(__FILE__) . DS . 'classes' . DS . 'credits.php';

$credits = new UHEADER_CLASS_Credits();
$credits->triggerCreditActionsAdd();