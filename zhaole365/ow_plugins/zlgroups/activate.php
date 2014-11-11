<?php

//将乐群添加到首菜单中
$navigation = OW::getNavigation();
$navigation->addMenuItem(
    OW_Navigation::MAIN,
    'zlgroups-index',
    'zlgroups',
    'main_menu_list',
    OW_Navigation::VISIBLE_FOR_ALL);


//准备添加乐群相关的widget
$widgetService = BOL_ComponentAdminService::getInstance();

//将用户所有乐群widget放置在profile页面左面部分
$widget = $widgetService->addWidget('ZLGROUPS_CMP_UserGroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

/*$widget = $widgetService->addWidget('GROUPS_CMP_UserGroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT);*/

//将所有乐群widget放置在index页面左面部分
$widget = $widgetService->addWidget('ZLGROUPS_CMP_GroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

$event = new OW_Event('feed.install_widget', array(
    'place' => 'zlgroup',
    'section' => BOL_ComponentService::SECTION_RIGHT,
    'order' => 0
));
OW::getEventManager()->trigger($event);

if ( OW::getConfig()->getValue('zlgroups', 'is_forum_connected') )
{
    $event = new OW_Event('forum.install_widget', array(
        'place' => 'zlgroup',
        'section' => BOL_ComponentService::SECTION_RIGHT,
        'order' => 0
    ));
    OW::getEventManager()->trigger($event);

    if ( !OW::getConfig()->configExists('zlgroups', 'restore_groups_forum') )
    {
        OW::getConfig()->addConfig('zlgroups', 'restore_groups_forum', 1);
    }

}

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new ZLGROUPS_CLASS_Credits();
$credits->triggerCreditActionsAdd();