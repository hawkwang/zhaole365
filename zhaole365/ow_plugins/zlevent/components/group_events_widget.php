<?php

class ZLEVENT_CMP_GroupEventsWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;

        $eventService = ZLEVENT_BOL_EventService::getInstance();
        
        $groupId = (int) $paramsObj->additionalParamList['entityId'];
        
        //OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlevent')->getStaticCssUrl() . 'bootstrap.min.css');
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlevent')->getStaticJsUrl() . 'jquery-1.10.2.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY+10);
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlevent')->getStaticJsUrl() . 'bootstrap.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        $isAuthenticated = false;
        if ( OW::getUser()->isAuthenticated() )
        {
        	$isAuthenticated = true;
        }
        
        $this->assign('groupId', $groupId);
        $this->assign('isAuthenticated', $isAuthenticated);
        
        $latestEvents = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsByGroupId($groupId, 0, 5);
        if ( empty($latestEvents) )
        {
        	$this->assign('no_latestevents', true);
        }
        $this->assign('latestEvents', ZLEVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($latestEvents));
        
    }

    public static function getSettingList()
    {
        $eventConfigs = ZLEVENT_BOL_EventService::getInstance()->getConfigs();
        $settingList = array();
        $settingList['events_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('zlevent', 'cmp_widget_events_count'),
            'optionList' => $eventConfigs[ZLEVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST],
            'value' => $eventConfigs[ZLEVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT]
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => '群乐',
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}