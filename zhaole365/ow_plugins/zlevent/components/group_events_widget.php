<?php

class ZLEVENT_CMP_GroupEventsWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $plugin = OW::getPluginManager()->getPlugin('zlevent');
        $this->assign('staticurl', $plugin->getStaticUrl());
        
        $params = $paramsObj->customParamList;

        $eventService = ZLEVENT_BOL_EventService::getInstance();
        
        $groupId = (int) $paramsObj->additionalParamList['entityId'];
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlevent')->getStaticCssUrl() . 'group_events_widget.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlevent')->getStaticJsUrl() . 'group_events_widget.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        $isAuthenticated = false;
        if ( OW::getUser()->isAuthenticated() )
        {
        	$isAuthenticated = true;
        }
        
        $this->assign('groupId', $groupId);
        $this->assign('isAuthenticated', $isAuthenticated);
        
        $offset = 0;
        $limit = 5;
        
        // 最近群乐
        $latestEvents = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsByGroupId($groupId, $offset, $limit);
        $total_latestEvents = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsCountByGroupId($groupId);
        if ( empty($latestEvents) )
        {
        	$this->assign('no_latestevents', true);
        }
        $this->assign('latestEvents', ZLEVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($latestEvents));
        $this->assign('total_latestEvents', $total_latestEvents);
        $next_offset_latestEvents = $offset + $limit;
        $this->assign('offset_latestEvents', $next_offset_latestEvents);
        $this->assign('limit_latestEvents', $limit);
        $hasmore_latestEvents = true;
        if( $next_offset_latestEvents >= $total_latestEvents )
        	$hasmore_latestEvents = false;
        $this->assign('hasmore_latestEvents', $hasmore_latestEvents);
        $baseurl = OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'ajaxLatestResponder');
        $this->assign('baseurl_latestEvents', $baseurl);
        // end 
        
        // 历史群乐
        $historyEvents = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsByGroupId($groupId, $offset, $limit, true);
        $total_historyEvents = ZLEVENT_BOL_EventService::getInstance()->findPublicEventsCountByGroupId($groupId, true);
        if ( empty($historyEvents) )
        {
        	$this->assign('no_historyEvents', true);
        }
        $this->assign('historyEvents', ZLEVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($historyEvents));
        $this->assign('total_historyEvents', $total_historyEvents);
        $next_offset_historyEvents = $offset + $limit;
        $this->assign('offset_historyEvents', $next_offset_historyEvents);
        $this->assign('limit_historyEvents', $limit);
        $hasmore_historyEvents = true;
        if( $next_offset_historyEvents >= $total_historyEvents )
        	$hasmore_historyEvents = false;
        $this->assign('hasmore_historyEvents', $hasmore_historyEvents);
        $baseurl = OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'ajaxHistoryResponder');
        $this->assign('baseurl_historyEvents', $baseurl);
        // end   

        $userId = OW::getUser()->getId();
        // 我的群乐
        $myEvents = ZLEVENT_BOL_EventService::getInstance()->findUserParticipatedGroupEvents($groupId, $userId, $offset, $limit);
        $total_myEvents = 0;
        if(isset($myEvents))
        	$total_myEvents = count($myEvents);

        $this->assign('myEvents', ZLEVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($myEvents));
        $this->assign('total_myEvents', $total_myEvents);
        $next_offset_myEvents = $offset + $limit;
        $this->assign('offset_myEvents', $next_offset_myEvents);
        $this->assign('limit_myEvents', $limit);
        $hasmore_myEvents = true;
        if( $next_offset_myEvents >= $total_myEvents )
        	$hasmore_myEvents = false;
        $this->assign('hasmore_myEvents', $hasmore_myEvents);
        $baseurl = OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'ajaxMyResponder');
        $this->assign('baseurl_myEvents', $baseurl);
        // end
        
        // 邀请群乐
        $inviteEvents = ZLEVENT_BOL_EventService::getInstance()->findUserInvitedGroupEvents($groupId, $userId, $offset, $limit);
        $total_inviteEvents = 0;
        if(isset($inviteEvents))
        	$total_inviteEvents = count($inviteEvents);
        
        $this->assign('inviteEvents', ZLEVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($inviteEvents));
        $this->assign('total_inviteEvents', $total_inviteEvents);
        $next_offset_inviteEvents = $offset + $limit;
        $this->assign('offset_inviteEvents', $next_offset_inviteEvents);
        $this->assign('limit_inviteEvents', $limit);
        $hasmore_inviteEvents = true;
        if( $next_offset_inviteEvents >= $total_inviteEvents )
        	$hasmore_inviteEvents = false;
        $this->assign('hasmore_inviteEvents', $hasmore_inviteEvents);
        $baseurl = OW::getRouter()->urlFor('ZLEVENT_CTRL_Base', 'ajaxInviteResponder');
        $this->assign('baseurl_inviteEvents', $baseurl);
        // end
        
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