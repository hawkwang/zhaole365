<?php

class ZLEVENT_MCLASS_EventHandler
{

    private static $classInstance;

    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function setInvitationData( OW_Event $event )
    {
        $params = $event->getParams();
        if ( $params['entityType'] == 'zlevent-join' )
        {
            $data = $params['data'];
            $data['string']['vars']['event'] = strip_tags($data['string']['vars']['event']);
            $data['acceptCommand'] = 'zlevents.accept';
            $data['declineCommand'] = 'zlevents.ignore';
            $event->setData($data);
        }
    }

    public function onCommand( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $event->getParams();

        if ( !in_array($params['command'], array('zlevents.accept', 'zlevents.ignore')) )
        {
            return;
        }

        $eventId = $params['data'];
        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($eventId);

        $userId = OW::getUser()->getId();
        $eventService = ZLEVENT_BOL_EventService::getInstance();

        if ( empty($eventDto) )
        {
            BOL_InvitationService::getInstance()->deleteInvitation(
                ZLEVENT_CLASS_InvitationHandler::INVITATION_JOIN, $eventId, $userId
            );

            return;
        }

        $lang = OW::getLanguage();
        $result = array('result' => false);

        if ( $params['command'] == 'zlevents.accept' )
        {
            $exit = false;
            $attendedStatus = 1;

            if ( $eventService->canUserView($eventId, $userId) )
            {
                $eventDto = $eventService->findEvent($eventId);

                if ( $eventDto->getEndTimeStamp() < time() )
                {
                    $eventService->deleteUserEventInvites((int)$eventId, $userId);
                    $result['msg'] = $lang->text('zlevent', 'user_status_updated');
                    $event->setData($result);

                    return;
                }

                $eventUser = $eventService->findEventUser($eventId, $userId);

                if ( $eventUser !== null && (int) $eventUser->getStatus() === (int) $attendedStatus )
                {
                    $result['msg'] = $lang->text('zlevent', 'user_status_not_changed_error');
                    $exit = true;
                }

                if ( $eventDto->getUserId() == OW::getUser()->getId() && (int) $attendedStatus == ZLEVENT_BOL_EventService::USER_STATUS_NO )
                {
                    $result['msg'] = $lang->text('zlevent', 'user_status_author_cant_leave_error');
                    $exit = true;
                }

                if ( !$exit )
                {
                    $eventUserDto = ZLEVENT_BOL_EventService::getInstance()->addEventUser($userId, $eventId, $attendedStatus);

                    if ( !empty( $eventUserDto ) )
                    {
                        $e = new OW_Event(
                            ZLEVENT_BOL_EventService::EVENT_ON_CHANGE_USER_STATUS,
                            array('eventId' => $eventDto->id, 'userId' => $eventUserDto->userId)
                        );
                        OW::getEventManager()->trigger($e);

                        $result = array('result' => true, 'msg' => $lang->text('zlevent', 'user_status_updated'));
                        BOL_InvitationService::getInstance()->deleteInvitation(
                            ZLEVENT_CLASS_InvitationHandler::INVITATION_JOIN, $eventId, $userId
                        );
                    }
                    else
                    {
                        $result['msg'] = $lang->text('zlevent', 'user_status_update_error');
                    }
                }
            }
            else
            {
                $result['msg'] = $lang->text('zlevent', 'user_status_update_error');
            }
        }
        else if ( $params['command'] == 'zlevents.ignore' )
        {
            $eventService->deleteUserEventInvites((int)$eventId, $userId);
            $result = array('result' => true, 'msg' => $lang->text('zlevent', 'user_status_updated'));
            BOL_InvitationService::getInstance()->deleteInvitation(
                ZLEVENT_CLASS_InvitationHandler::INVITATION_JOIN, $eventId, $userId
            );
        }

        $event->setData($result);
    }

    public function init()
    {
        $em = OW::getEventManager();
        $em->bind('mobile.invitations.on_item_render', array($this, 'setInvitationData'));
        $em->bind('invitations.on_command', array($this, 'onCommand'));
        $em->bind('feed.on_item_render', array($this, 'onFeedItemRenderDisableActions'));
    }

    public function onFeedItemRenderDisableActions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["action"]["entityType"] != 'zlevent' )
        {
            return;
        }

        $data = $event->getData();

        $data["disabled"] = true;

        $event->setData($data);
    }
}