<?php

class ZLEVENT_CLASS_InvitationHandler
{
    const INVITATION_JOIN = 'zlevent-join';

    /**
     * Singleton instance.
     *
     * @var ZLEVENT_CLASS_InvitationHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLEVENT_CLASS_InvitationHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }



    private function __construct()
    {

    }

    public function onInvite( OW_Event $event )
    {
        $params = $event->getParams();

        $eventId = $params['eventId'];

        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($params['eventId']);
        $eventUrl = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $eventDto->id));

        $eventTitle = UTIL_String::truncate($eventDto->title, 100, '...');

        $userId = OW::getUser()->getId();
        $userDto = OW::getUser()->getUserObject();
        $userUrl = BOL_UserService::getInstance()->getUserUrlForUsername($userDto->username);
        $userDisplayName = BOL_UserService::getInstance()->getDisplayName($userId);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];

        $users = array($userId);

        $stringAssigns = array(
            'event' => '<a href="' . $eventUrl . '">' . $eventTitle . '</a>'
        );

        $stringAssigns['user1'] = '<a href="' . $userUrl . '">' . $userDisplayName . '</a>';

        $contentImage = null;

        if ( !empty($eventDto->image) )
        {
            $eventSrc = ZLEVENT_BOL_EventService::getInstance()->generateImageUrl($eventDto->image, true);

            $contentImage = array(
                    'src' => $eventSrc,
                    'url' => $eventUrl,
                    'title' => $eventTitle
                );
        }

        //$userCount = count($data['users']);
//        $userIds = array();
//        for ( $i = 0; $i < $userCount; $i++ )
//        {
//            $user = $data['users'][$i];
//            $stringAssigns['user' . ($i+1)] = '<a href="' . $user['url'] . '">' . $user['name'] . '</a>';
//
//            if ( $i >= 2 )
//            {
//                $userIds[] = $user['userId'];
//            }
//        }



        //$stringAssigns['otherUsers'] = '<a href="javascript://" onclick="OW.showUsers(' . json_encode($users) . ');">' .
        //OW::getLanguage()->text('zlevent', 'invitation_join_string_other_users', array( 'count' => count($users) )) . '</a>';

        // $languageKey = $userCount > 2 ? 'invitation_join_string_many' : 'invitation_join_string_' . $userCount;
        $languageKey = 'invitation_join_string_' . 1;

        $invitationEvent = new OW_Event('invitations.add', array(
            'pluginKey' => 'zlevent',
            'entityType' => self::INVITATION_JOIN,
            'entityId' => $eventDto->id,
            'userId' => $params['userId'],
            'time' => time(),
            'action' => 'zlevent-invitation'
        ), array(
            'string' => array(
                'key' => 'zlevent+'.$languageKey,
                'vars' => $stringAssigns
            ),
            'users' => $users,
            'avatar' => $avatar,
            'contentImage' => $contentImage
        ));


        OW::getEventManager()->trigger($invitationEvent);
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != self::INVITATION_JOIN )
        {
            return;
        }

        $eventId = (int) $params['entityId'];
        $data = $params['data'];

        $itemKey = $params['key'];
        
        $language = OW::getLanguage();

        $data['toolbar'] = array(
            array(
                'label' => $language->text('zlevent', 'accept_request'),
                'id'=> 'toolbar_accept_' . $itemKey
            ),
            array(
                'label' => $language->text('zlevent', 'ignore_request'),
                'id'=> 'toolbar_ignore_' . $itemKey
            )
        );

        $event->setData($data);

        $jsData = array(
            'eventId' => $eventId,
            'itemKey' => $itemKey
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#toolbar_ignore_$itemKey", 'click',
                'OW.Invitation.send("zlevents.ignore", e.data.eventId).removeItem(e.data.itemKey);',
        array('e'), $jsData);

        $js->jQueryEvent("#toolbar_accept_$itemKey", 'click',
                'OW.Invitation.send("zlevents.accept", e.data.eventId);
                 $("#toolbar_ignore_" + e.data.itemKey).hide();
                 $("#toolbar_accept_" + e.data.itemKey).hide();',
        array('e'), $jsData);

        OW::getDocument()->addOnloadScript($js->generateJs());
    }

    public function onEventDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $eventId = $params['eventId'];

        OW::getEventManager()->call('invitations.remove', array(
            'entityType' => 'zlevent',
            'entityId' => $eventId
        ));
        
        OW::getEventManager()->call('invitations.remove', array(
            'entityType' => self::INVITATION_JOIN,
            'entityId' => $eventId
        ));  
        
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'zlevent',
            'entityId' => $eventId
        ));
    }

    public function onCommand( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return 'auth faild';
        }

        $params = $event->getParams();

        if ( !in_array($params['command'], array('zlevents.accept', 'zlevents.ignore')) )
        {
            return 'wrong command';
        }

        $eventId = $params['data'];
        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($eventId);

        $userId = OW::getUser()->getId();
        $jsResponse = UTIL_JsGenerator::newInstance();
        $eventService = ZLEVENT_BOL_EventService::getInstance();

        if ( empty($eventDto) )
        {
            BOL_InvitationService::getInstance()->deleteInvitation(self::INVITATION_JOIN, $eventId, $userId);
            return 'empty Event Id';
        }

        if ( $params['command'] == 'zlevents.accept' )
        {
            $feedback = array('messageType' => 'error');
            $exit = false;
            $attendedStatus = 1;

            if ( $eventService->canUserView($eventId, $userId) )
            {
                $eventDto = $eventService->findEvent($eventId);

                if ( $eventDto->getEndTimeStamp() < time() )
                {
                    $eventService->deleteUserEventInvites((int)$eventId, $userId);
                    $jsResponse->callFunction(array('OW', 'error'), array( OW::getLanguage()->text('zlevent', 'user_status_updated') ));
                    $event->setData($jsResponse);
                    return;
                }

                $eventUser = $eventService->findEventUser($eventId, $userId);

                if ( $eventUser !== null && (int) $eventUser->getStatus() === (int) $attendedStatus )
                {
                    $jsResponse->callFunction(array('OW', 'error'), array( OW::getLanguage()->text('zlevent', 'user_status_not_changed_error') ));
                    $exit = true;
                }

                if ( $eventDto->getUserId() == OW::getUser()->getId() && (int) $attendedStatus == ZLEVENT_BOL_EventService::USER_STATUS_NO )
                {
                    $jsResponse->callFunction(array('OW', 'error'), array( OW::getLanguage()->text('zlevent', 'user_status_author_cant_leave_error') ));
                    $exit = true;
                }

                if ( !$exit )
                {
                    $eventUserDto = ZLEVENT_BOL_EventService::getInstance()->addEventUser($userId, $eventId, $attendedStatus );

                    if( !empty( $eventUserDto ) )
                    {
                        $e = new OW_Event(ZLEVENT_BOL_EventService::EVENT_ON_CHANGE_USER_STATUS, array('eventId' => $eventDto->id, 'userId' => $eventUserDto->userId));
                        OW::getEventManager()->trigger($e);

                        $jsResponse->callFunction(array('OW', 'info'), array( OW::getLanguage()->text('zlevent', 'user_status_updated') ));
                        BOL_InvitationService::getInstance()->deleteInvitation(self::INVITATION_JOIN, $eventId, $userId);
                    }
                    else
                    {
                        $jsResponse->callFunction(array('OW', 'error'), array( OW::getLanguage()->text('zlevent', 'user_status_update_error') ));
                    }
                }
            }
            else
            {
                $jsResponse->callFunction(array('OW', 'error'), array( OW::getLanguage()->text('zlevent', 'user_status_update_error') ));
            }
        }
        else if ( $params['command'] == 'zlevents.ignore' )
        {
            $eventService->deleteUserEventInvites((int)$eventId, $userId);
            $jsResponse->callFunction(array('OW', 'info'), array( OW::getLanguage()->text('zlevent', 'user_status_updated') ));
            BOL_InvitationService::getInstance()->deleteInvitation(self::INVITATION_JOIN, $eventId, $userId);
        }

        $event->setData($jsResponse);
    }

    public function init()
    {
        OW::getEventManager()->bind('zlevent.invite_user', array($this, 'onInvite'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_ON_DELETE_EVENT, array($this, 'onEventDelete'));

        OW::getEventManager()->bind('invitations.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind('invitations.on_command', array($this, 'onCommand'));
    }
}