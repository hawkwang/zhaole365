<?php

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'zlevent.main_menu_route', 'zlevent', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

//将所有即将发生的活动widget放置在index页面左面部分
$widget = BOL_ComponentAdminService::getInstance()->addWidget('ZLEVENT_CMP_UpcomingEventsWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

//将ZLEVENT_CMP_ProfilePageWidget放置在profile页面左面部分
$widget = BOL_ComponentAdminService::getInstance()->addWidget('ZLEVENT_CMP_ProfilePageWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new ZLEVENT_CLASS_Credits();
$credits->triggerCreditActionsAdd();

