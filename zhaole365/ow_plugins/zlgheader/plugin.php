<?php

class ZLGHEADER_Plugin
{
    const PLUGIN_BUILD = "1";
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getPlugin()
    {
        return OW::getPluginManager()->getPlugin('zlgheader');
    }

    public function isAvaliable()
    {
        return OW::getPluginManager()->isPluginActive('zlgroups');
    }

    public function onAddAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'zlgheader' => array(
                    'label' => $language->text('zlgheader', 'auth_group_label'),
                    'actions' => array(
                        'view_cover' => $language->text('zlgheader', 'auth_action_view_cover'),
                        'add_cover' => $language->text('zlgheader', 'auth_action_add_cover'),
                        'add_comment' => $language->text('zlgheader', 'auth_action_label_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('zlgheader', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }

    public function onAddAdminNotifications( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();
        $e->add($language->text('zlgheader', 'admin_plugin_required_notification', array(
            'coverUrl' => OW::getRouter()->urlForRoute('zlgheader-settings-page'),
            'zlgroupsUrl' => 'www.zhaole365.com'
        )));
    }

    public function genericInit()
    {
        ZLGHEADER_CLASS_NewsfeedBridge::getInstance()->genericInit();
    }
    
    public function mobileInit()
    {
        $this->genericInit();
        
        ZLGHEADER_MCLASS_NewsfeedBridge::getInstance()->init();
    }
    
    public function fullInit()
    {
        //FULL INIT

        $this->genericInit();
        
        //OVerwrite
        require_once $this->getPlugin()->getRootDir() . 'overwrite' . DS . 'components' . DS . 'brief_info.php';

        // Bridges
        ZLGHEADER_CLASS_PhotoBridge::getInstance()->init();
        ZLGHEADER_CLASS_NewsfeedBridge::getInstance()->init();
        ZLGHEADER_CLASS_NotificationsBridge::getInstance()->init();
        ZLGHEADER_CLASS_CommentsBridge::getInstance()->init();
        ZLGHEADER_CLASS_CreditsBridge::getInstance()->init();
        ZLGHEADER_CLASS_ForumBridge::getInstance()->init();

        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onAddAuthLabels'));
    }

    public function shortInit()
    {
        OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'onAddAdminNotifications'));
    }



    public function fullActivate()
    {
        $widgetService = BOL_ComponentAdminService::getInstance();

        $widget = $widgetService->addWidget('ZLGHEADER_CMP_HeaderWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');
        
        $infoWidget = $widgetService->addWidget('ZLGHEADER_CMP_InfoWidget', false);
        $infoPlaceWidget = $widgetService->addWidgetToPlace($infoWidget, 'zlgroup');

        try
        {
            $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP, 0);
            $widgetService->addWidgetToPosition($infoPlaceWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);
        }
        catch ( Exception $e )
        {
            // Log
        }

        try
        {
            $uniqName = 'zlgroup-ZLGROUPS_CMP_BriefInfoWidget';
            BOL_ComponentPositionDao::getInstance()->deleteByUniqName($uniqName);
            BOL_ComponentEntityPositionDao::getInstance()->deleteAllByUniqName($uniqName);
        }
        catch ( Exception $e )
        {
            // Log
        }
    }

    public function shortActivate()
    {

    }

    public function fullDeactivate()
    {
        $widgetService = BOL_ComponentAdminService::getInstance();
        $widgetService->deleteWidget('ZLGHEADER_CMP_HeaderWidget');
        $widgetService->deleteWidget('ZLGHEADER_CMP_InfoWidget');

        $widget = $widgetService->addWidget('ZLGROUPS_CMP_BriefInfoWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'zlgroup');

        try
        {
            $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP, 0);
        }
        catch ( Exception $e )
        {
            // Log
        }
    }

    public function shortDeactivate()
    {
        $widgetService = BOL_ComponentAdminService::getInstance();
        $widgetService->deleteWidget('ZLGHEADER_CMP_HeaderWidget');
    }
}