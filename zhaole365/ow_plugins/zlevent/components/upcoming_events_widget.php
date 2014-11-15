<?php

class ZLEVENT_CMP_UpcomingEventsWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;

        $eventService = ZLEVENT_BOL_EventService::getInstance();
        $events = $eventService->findPublicEvents(null, $params['events_count']);
        $count = $eventService->findPublicEventsCount();

        if ( ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('zlevent', 'add_event') ) && $count == 0 )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('events', $eventService->getListingDataWithToolbar($events));
        $this->assign('no_content_message', OW::getLanguage()->text('zlevent', 'no_index_events_label', array('url' => OW::getRouter()->urlForRoute('zlevent.add'))));

        if ( $eventService->findPublicEventsCount() > $params['events_count'] )
        {
            $toolbarArray = array(array('href' => OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => 'latest')), 'label' => OW::getLanguage()->text('zlevent', 'view_all_label')));
            $this->assign('toolbar', $toolbarArray);
        }
    }

    public static function getSettingList()
    {
        $eventConfigs = ZLEVENT_BOL_EventService::getInstance()->getConfigs();
        $settingList = array();
        $settingList['events_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('zlevent', 'cmp_widget_events_count'),
            'optionList' => $eventConfigs[EVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST],
            'value' => $eventConfigs[EVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT]
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlevent', 'up_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}