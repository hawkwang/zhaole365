<?php

class ZLEVENT_CMP_ProfilePageWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;
        $addParams = $paramsObj->additionalParamList;
        
        if ( empty($addParams['entityId']) || !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('zlevent', 'view_event') )
        {
            $this->setVisible(false);
            return;
        }
        else
        {
            $userId = $addParams['entityId'];
        }

        $eventParams =  array(
                'action' => 'zlevent_view_attend_events',
                'ownerId' => $userId,
                'viewerId' => OW::getUser()->getId()
            );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        $language = OW::getLanguage();
        $eventService = ZLEVENT_BOL_EventService::getInstance();

        $userEvents = $eventService->findUserParticipatedPublicEvents($userId, null, $params['events_count']);
        
        
        if ( empty($userEvents) )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('my_events', $eventService->getListingDataWithToolbar($userEvents));

        $toolbarArray = array();
        
        if ( $eventService->findUserParticipatedPublicEventsCount($userId) > $params['events_count'] )
        {
            $url = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => 'user-participated-events')), array('userId' => $userId));
            $toolbarArray = array(array('href' => $url, 'label' => $language->text('zlevent', 'view_all_label')));
        }

        $this->assign('toolbars', $toolbarArray);
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
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlevent', 'profile_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}