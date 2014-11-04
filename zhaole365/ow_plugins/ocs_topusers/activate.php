<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /activate.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers
 * @since 1.2.6
 */
$cmpService = BOL_ComponentAdminService::getInstance();

$widget = $cmpService->addWidget('OCSTOPUSERS_CMP_RateUserWidget');
$placeWidget = $cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
$cmpService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $cmpService->addWidget('OCSTOPUSERS_CMP_IndexWidget');
$placeWidget = $cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$cmpService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $cmpService->addWidget('OCSTOPUSERS_CMP_DashboardWidget');
$placeWidget = $cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$cmpService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);