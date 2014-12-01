<?php

class GHEADER_Plugin
{
    const PLUGIN_BUILD = "1655";
    
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GHEADER_Plugin
     */
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

    /**
     *
     * @return OW_Plugin
     */
    public function getPlugin()
    {
        return OW::getPluginManager()->getPlugin('gheader');
    }

    public function isAvaliable()
    {
        return OW::getPluginManager()->isPluginActive('groups');
    }

    public function onAddAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'gheader' => array(
                    'label' => $language->text('gheader', 'auth_group_label'),
                    'actions' => array(
                        'view_cover' => $language->text('gheader', 'auth_action_view_cover'),
                        'add_cover' => $language->text('gheader', 'auth_action_add_cover'),
                        'add_comment' => $language->text('gheader', 'auth_action_label_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('gheader', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }

    public function onAddAdminNotifications( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();
        $e->add($language->text('gheader', 'admin_plugin_required_notification', array(
            'coverUrl' => OW::getRouter()->urlForRoute('gheader-settings-page'),
            'groupsUrl' => 'http://www.oxwall.org/store/item/36'
        )));
    }

    public function genericInit()
    {
        GHEADER_CLASS_NewsfeedBridge::getInstance()->genericInit();
    }
    
    public function mobileInit()
    {
        $this->genericInit();
        
        GHEADER_MCLASS_NewsfeedBridge::getInstance()->init();
    }
    
    public function fullInit()
    {
        //FULL INIT

        $this->genericInit();
        
        //OVerwrite
        require_once $this->getPlugin()->getRootDir() . 'overwrite' . DS . 'components' . DS . 'brief_info.php';

        // Bridges
        GHEADER_CLASS_PhotoBridge::getInstance()->init();
        GHEADER_CLASS_NewsfeedBridge::getInstance()->init();
        GHEADER_CLASS_NotificationsBridge::getInstance()->init();
        GHEADER_CLASS_CommentsBridge::getInstance()->init();
        GHEADER_CLASS_CreditsBridge::getInstance()->init();
        GHEADER_CLASS_ForumBridge::getInstance()->init();

        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onAddAuthLabels'));
    }

    public function shortInit()
    {
        OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'onAddAdminNotifications'));
    }



    public function fullActivate()
    {
        $widgetService = BOL_ComponentAdminService::getInstance();

        $widget = $widgetService->addWidget('GHEADER_CMP_HeaderWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
        
        $infoWidget = $widgetService->addWidget('GHEADER_CMP_InfoWidget', false);
        $infoPlaceWidget = $widgetService->addWidgetToPlace($infoWidget, 'group');

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
            $uniqName = 'group-GROUPS_CMP_BriefInfoWidget';
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
        $widgetService->deleteWidget('GHEADER_CMP_HeaderWidget');
        $widgetService->deleteWidget('GHEADER_CMP_InfoWidget');

        $widget = $widgetService->addWidget('GROUPS_CMP_BriefInfoWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');

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
        $widgetService->deleteWidget('GHEADER_CMP_HeaderWidget');
    }
}