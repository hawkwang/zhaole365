<?php


$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    $widgets = $widgetService->findPlaceComponentList(ZLGROUPS_BOL_Service::WIDGET_PANEL_NAME);
    foreach ( $widgets as $widget )
    {
	$widgetService->deleteWidgetPlace($widget['uniqName']);
    }
}
catch ( Exception $e ) {}

BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_JoinButtonWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_BriefInfoWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_UserListWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_WallWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_LeaveButtonWidget');

if ( OW::getConfig()->getValue('zlgroups', 'is_forum_connected') )
{
    $event = new OW_Event('forum.delete_section', array('entity' => 'zlgroups'));
    OW::getEventManager()->trigger($event);

    $event = new OW_Event('forum.delete_widget');
    OW::getEventManager()->trigger($event);
}

$dbPrefix = OW_DB_PREFIX;

$sql =
    <<<EOT
DELETE FROM `{$dbPrefix}base_place` WHERE `name`='zlgroup';
EOT;

OW::getDbo()->query($sql);