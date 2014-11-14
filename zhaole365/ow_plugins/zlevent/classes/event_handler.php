<?php


class ZLEVENT_CLASS_EventHandler
{

    public function __construct()
    {
        
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'zlevent',
            'action' => 'zlevent-invitation',
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('zlevent', 'notifications_section_label'),
            'description' => OW::getLanguage()->text('zlevent', 'notifications_new_message'),
            'selected' => true
        ));

        $e->add(array(
            'section' => 'zlevent',
            'sectionIcon' => 'ow_ic_files',
            'sectionLabel' => OW::getLanguage()->text('zlevent', 'notifications_section_label'),
            'action' => 'zlevent-add_comment',
            'description' => OW::getLanguage()->text('zlevent', 'email_notification_comment_setting'),
            'selected' => true
        ));
    }

    public function onUserInvite( OW_Event $e )
    {
        $params = $e->getParams();

        OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventUserDao::CACHE_TAG_EVENT_USER_LIST . $params['eventId']));
    }

    /**
     * Add event entity to the newsfeed
     *
     * @param OW_Event $e
     */
    public function feedEntityAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'zlevent' )
        {
            return;
        }

        $eventService = ZLEVENT_BOL_EventService::getInstance();
        $event = $eventService->findEvent($params['entityId']);

        //    if ( $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY )
        //    {
        //        return;
        //    }
        //$url = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->getId()));
        //$thumb = $eventService->generateImageUrl($event->image, true);

        $title = UTIL_String::truncate(strip_tags($event->getTitle()), 100, "...");


        /* $data = array(
          'time' => $event->getCreateTimeStamp(),
          'ownerId' => $event->getUserId(),
          'string' => OW::getLanguage()->text('zlevent', 'feed_add_item_label'),
          'content' => '<div class="clearfix"><div class="ow_newsfeed_item_picture">
          <a href="' . $url . '"><img src="' . ( $event->getImage() ? $eventService->generateImageUrl($event->getImage(), true) : $eventService->generateDefaultImageUrl() ) . '" /></a>
          </div><div class="ow_newsfeed_item_content">
          <a class="ow_newsfeed_item_title" href="' . $url . '">' . $title . '</a><div class="ow_remark ow_smallmargin">' . UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...') . '</div><div class="ow_newsfeed_action_activity event_newsfeed_activity">[ph:activity]</div></div></div>',
          'view' => array(
          'iconClass' => 'ow_ic_calendar'
          )
          ); */

        $data = array(
            'time' => $event->getCreateTimeStamp(),
            'ownerId' => $event->getUserId(),
            'string' => array("key" => "zlevent+feed_add_item_label"),
            'content' => array(
                "format" => "image_content",
                "vars" => array(
                    "image" => ( $event->getImage() ? $eventService->generateImageUrl($event->getImage(), false) : $eventService->generateDefaultImageUrl() ),
                    "thumbnail" => ( $event->getImage() ? $eventService->generateImageUrl($event->getImage(), true) : $eventService->generateDefaultImageUrl() ),
                    "title" => $title,
                    "description" => UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...'),
                    "url" => array(
                        "routeName" => 'zlevent.view',
                        "vars" => array('eventId' => $event->getId())
                    ),
                    'iconClass' => 'ow_ic_event'
                )
            ),
            'view' => array(
                'iconClass' => 'ow_ic_calendar'
            ),
        );

        if ( $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY )
        {
            $data['params']['visibility'] = 14; // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
        }

        $e->setData($data);
    }

    public function afterEventEdit( OW_Event $e )
    {
        $params = $e->getParams();
        $eventId = (int) $params['eventId'];

        $eventService = ZLEVENT_BOL_EventService::getInstance();
        $event = $eventService->findEvent($eventId);

        //$url = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->getId()));
        // $thumb = $eventService->generateImageUrl($event->image, true);

        /* $data = array(
          'time' => $event->getCreateTimeStamp(),
          'ownerId' => $event->getUserId(),
          'string' => OW::getLanguage()->text('zlevent', 'feed_add_item_label'),
          'content' => '<div class="clearfix"><div class="ow_newsfeed_item_picture">
          <a href="' . $url . '"><img src="' . $thumb . '" /></a>
          </div><div class="ow_newsfeed_item_content">
          <a class="ow_newsfeed_item_title" href="' . $url . '">' . $event->getTitle() . '</a><div class="ow_remark ow_smallmargin">' . UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...') . '</div><div class="ow_newsfeed_action_activity event_newsfeed_activity">[ph:activity]</div></div></div>',
          'view' => array(
          'iconClass' => 'ow_ic_calendar'
          )
          ); */
        $title = UTIL_String::truncate(strip_tags($event->getTitle()), 100, "...");

        $data = array(
            'time' => $event->getCreateTimeStamp(),
            'ownerId' => $event->getUserId(),
            'string' => array("key" => "zlevent+feed_add_item_label"), //OW::getLanguage()->text('zlevent', 'feed_add_item_label'),
            'content' => array(
                "format" => "image_content",
                "vars" => array(
                    "image" => ( $event->getImage() ? $eventService->generateImageUrl($event->getImage(), false) : $eventService->generateDefaultImageUrl() ),
                    "thumbnail" => ( $event->getImage() ? $eventService->generateImageUrl($event->getImage(), true) : $eventService->generateDefaultImageUrl() ),
                    "title" => $title,
                    "description" => UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...'),
                    "url" => array(
                        "routeName" => 'zlevent.view',
                        "vars" => array('eventId' => $event->getId())
                    ),
                    'iconClass' => 'ow_ic_event'
                )
            ),
            'view' => array(
                'iconClass' => 'ow_ic_calendar'
            ),
        );

        if ( $event->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY )
        {
            $data['params']['visibility'] = 14; // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
        }

        $event = new OW_Event('feed.action', array(
                'entityType' => 'zlevent',
                'entityId' => $eventId,
                'pluginKey' => 'zlevent',
                'postOnUserFeed' => 1
                ), $data);
        
        OW::getEventManager()->trigger($event);
    }

    public function addNewContentItem( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('zlevent', 'add_event') )
        {
            return;
        }

        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_calendar',
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('zlevent.add'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('zlevent', 'add_new_link_label')
        );

        $event->add($resultArray);
    }

    public function adsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('zlevent');
    }

    public function isPluginActive( OW_Event $event )
    {
        $event->setData(true);
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'zlevent' => array(
                    'label' => $language->text('zlevent', 'auth_group_label'),
                    'actions' => array(
                        'add_event' => $language->text('zlevent', 'auth_action_label_add_event'),
                        'view_event' => $language->text('zlevent', 'auth_action_label_view_event'),
                        'add_comment' => $language->text('zlevent', 'auth_action_label_add_comment')
                    )
                )
            )
        );
    }

    public function onUserDelete( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['deleteContent']) )
        {
            return;
        }

        $userId = $params['userId'];

        ZLEVENT_BOL_EventService::getInstance()->deleteUserEvents($userId);
    }

    public function privacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'zlevent_view_attend_events',
            'pluginKey' => 'zlevent',
            'label' => $language->text('zlevent', 'privacy_action_view_attend_events'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function feedOnItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != 'zlevent' )
        {
            return;
        }

        $eventId = $params['action']['entityId'];
        $usersCount = ZLEVENT_BOL_EventService::getInstance()->findEventUsersCount($eventId, ZLEVENT_BOL_EventService::USER_STATUS_YES);

        if ( $usersCount == 1 )
        {
            return;
        }

        $users = ZLEVENT_BOL_EventService::getInstance()->findEventUsers($eventId, ZLEVENT_BOL_EventService::USER_STATUS_YES, null, 6);

        $userIds = array();

        foreach ( $users as $user )
        {
            $userIds[] = $user->getUserId();
        }

        $activityUserIds = array();

        foreach ( $params['activity'] as $activity )
        {
            if ( $activity['activityType'] == 'zlevent-join' )
            {
                $activityUserIds[] = $activity['data']['userId'];
            }
        }

        $lastUserId = reset($activityUserIds);
        $follows = array_intersect($activityUserIds, $userIds);
        $notFollows = array_diff($userIds, $activityUserIds);
        $idlist = array_merge($follows, $notFollows);
        $idlist = array_slice($idlist, 0, 5);
        //$avatarList = new BASE_CMP_MiniAvatarUserList();
        //$avatarList->setEmptyListNoRender(true);

        /* if ( count($idlist) > 5 )
          {
          $avatarList->setViewMoreUrl(OW::getRouter()->urlForRoute('zlevent.main_user_list', array('eventId' => $eventId)));
          } */

        $language = OW::getLanguage();

        //$avatarList = new BASE_CMP_MiniAvatarUserList($idlist);
        //$content = $avatarList->render();

        /* if ( $lastUserId )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($lastUserId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($lastUserId);
            $content = $language->text('zlevent', 'feed_activity_joined', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
        } */

        /* $data['assign']['activity'] = array('template' => 'activity',
          'vars' => array(
          'title' => $language->text('zlevent', 'feed_activity_users', array('usersCount' => $usersCount)),
          'content' => $content
          )); */

        $userListData = array( 'userList' => array(
            'label' => array( 'key' => 'zlevent+feed_activity_users', 'vars' => array('usersCount' => $usersCount) ),
            'ids' => $idlist
            ) );

        $data['content']['vars'] = array_merge($data['content']['vars'], $userListData);
        $event->setData($data);
    }

    public function feedOnCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('zlevent-join', 'zlevent_view_attend_events'));
    }

    public function feedOnCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('zlevent', 'feed_content_label'),
            'activity' => '*:event'  // 可能出错
        ));
    }

    public function onAddComment( OW_Event $e )
    {
        $params = $e->getParams();

        if ( empty($params['entityType']) || $params['entityType'] != 'zlevent' )
        {
            return;
        }

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];
        $event = ZLEVENT_BOL_EventService::getInstance()->findEvent($entityId);

        $eventUrl = OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $event->id));

        $eventImage = null;
        if ( !empty($event->image) )
        {
            $eventImage = ZLEVENT_BOL_EventService::getInstance()->generateImageUrl($event->image, true);
        }

        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($entityId);
        $ownerId = $eventDto->userId;
        $userName = BOL_UserService::getInstance()->getDisplayName($ownerId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($ownerId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        $string = array( "key" => 'zlevent+feed_activity_comment_string', 'vars' => array('user' => $userEmbed) ); // OW::getLanguage()->text('zlevent', 'feed_activity_comment_string', array('user' => $userEmbed));

        if ( !empty($eventDto) && $userId == $eventDto->userId )
        {
            $string = array( "key" => 'zlevent+feed_activity_own_comment_string' ); //OW::getLanguage()->text('zlevent', 'feed_activity_own_comment_string');
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $commentId,
                'entityId' => $entityId,
                'entityType' => $params['entityType'],
                'userId' => $userId,
                'pluginKey' => 'zlevent'
                ), array(
                'string' => $string,
                'line' => null
            )));


//    $string = OW::getLanguage()->text('zlevent', 'email_notification_comment', array(
//            'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
//            'userUrl' => BOL_UserService::getInstance()->getUserUrl($userId),
//            'url' => $eventUrl,
//            'title' => strip_tags($event->title)
//        ));
//
//    $params = array(
//        'plugin' => 'zlevent',
//        'action' => 'zlevent-add_comment',
//        'string' => $string,
//        'avatar' => $eventImage,
//        'content' => $comment->getMessage(),
//        'url' => $eventUrl,
//        'time' => time(),
//        'userId' => $userId
//    );

        if ( $userId != $event->userId )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, false, false);
            $avatar = $avatars[$userId];

            $contentImage = array();

            if ( !empty($eventImage) )
            {
                $contentImage = array('src' => $eventImage);
            }

            $event = new OW_Event('notifications.add', array(
                    'pluginKey' => 'zlevent',
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId'],
                    'action' => 'zlevent-add_comment',
                    'userId' => $event->userId,
                    'time' => time()
                    ), array(
                    'avatar' => $avatar,
                    'string' => array(
                        'key' => 'zlevent+email_notification_comment',
                        'vars' => array(
                            'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                            'userUrl' => BOL_UserService::getInstance()->getUserUrl($userId),
                            'url' => $eventUrl,
                            'title' => strip_tags($event->title)
                        )
                    ),
                    'url' => $eventUrl,
                    'contentImage' => $contentImage
                ));

            OW::getEventManager()->trigger($event);
        }
    }

    public function feedOnLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'zlevent' )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $entityId = $params['entityId'];

        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($entityId);
        $ownerId = $eventDto->userId;
        $userName = BOL_UserService::getInstance()->getDisplayName($ownerId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($ownerId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        $string = array( 'key' => 'zlevent+feed_activity_event_string_like', 'vars' => array('user' => $userEmbed) ); //OW::getLanguage()->text('zlevent', 'feed_activity_event_string_like', array('user' => $userEmbed));

        if ( !empty($eventDto) && $userId == $eventDto->userId )
        {
            //$string = OW::getLanguage()->text('zlevent', 'feed_activity_event_string_like_own');
            $string = array( 'key' => 'zlevent+feed_activity_event_string_like_own' );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'zlevent'
                ), array(
                'string' => $string,
                'line' => null
            )));
    }

    public function quickLinks( BASE_CLASS_EventCollector $event )
    {
        $service = ZLEVENT_BOL_EventService::getInstance();
        $userId = OW::getUser()->getId();

        $eventsCount = $service->findUserParticipatedEventsCount($userId);
        $invitesCount = $service->findUserInvitedEventsCount($userId);

        if ( $eventsCount > 0 || $invitesCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('zlevent', 'common_list_type_joined_label'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => 'joined')),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $eventsCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => 'joined')),
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT => $invitesCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT_URL => OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => 'invited'))
            ));
        }
    }

    public function onAddEvent( OW_Event $event )
    {
        OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventDao::CACHE_TAG_EVENT_LIST));
    }

    public function onDeleteEvent( OW_Event $event )
    {
        $params = $event->getParams();
        $eventId = !empty($params['eventId']) ? $params['eventId'] : null;

        OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventDao::CACHE_TAG_EVENT_LIST));

        if ( isset($eventId) )
        {
            OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventUserDao::CACHE_TAG_EVENT_USER_LIST . $eventId));
        }

        $event = new OW_Event('feed.delete_item', array('entityType' => 'zlevent', 'entityId' => $eventId));
        OW::getEventManager()->trigger($event);
    }

    public function onEditEvent( OW_Event $event )
    {
        OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventDao::CACHE_TAG_EVENT_LIST));
    }

    public function onChangeUserStatus( OW_Event $event )
    {
        $params = $event->getParams();
        $eventId = !empty($params['eventId']) ? $params['eventId'] : null;
        $userId = !empty($params['userId']) ? $params['userId'] : null;

        if ( !isset($eventId) )
        {
            return;
        }

        OW::getCacheManager()->clean(array(ZLEVENT_BOL_EventUserDao::CACHE_TAG_EVENT_USER_LIST . $eventId));

        if ( !isset($userId) )
        {
            return;
        }

        $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($eventId);

        $eventUser = ZLEVENT_BOL_EventService::getInstance()->findEventUser($eventId, $userId);

        if ( empty($eventDto) || empty($eventUser) )
        {
            return;
        }

        if ( $eventUser->getStatus() == ZLEVENT_BOL_EventService::USER_STATUS_YES )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($eventDto->getUserId());
            $userUrl = BOL_UserService::getInstance()->getUserUrl($eventDto->getUserId());
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                    'activityType' => 'zlevent-join',
                    'activityId' => $eventUser->getId(),
                    'entityId' => $eventDto->getId(),
                    'entityType' => 'zlevent',
                    'userId' => $eventUser->getUserId(),
                    'pluginKey' => 'zlevent'
                    ), array(
                    'eventId' => $eventDto->getId(),
                    'userId' => $eventUser->getUserId(),
                    'eventUserId' => $eventUser->getId(),
                    'string' => OW::getLanguage()->text('zlevent', 'feed_actiovity_attend_string', array('user' => $userEmbed)),
                    'feature' => array()
                )));
            
            OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'subscribe',
                'activityId' => $eventUser->getId(),
                'entityId' => $eventDto->getId(),
                'entityType' => 'zlevent',
                'userId' => $eventUser->getUserId(),
                'pluginKey' => 'zlevent'
                ), array()));
        }
    }

    public function sosialSharingGetEventInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'zlevent' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('zlevent', 'view_event') )
            {
                $event->setData($data);
                return;
            }

            $eventDto = ZLEVENT_BOL_EventService::getInstance()->findEvent($params['entityId']);
            
            if ( !empty($eventDto) )
            {
                $data['display'] = $eventDto->getWhoCanView() == ZLEVENT_BOL_EventService::CAN_VIEW_ANYBODY;
            }

            $event->setData($data);
        }
    }

    public function getContentMenu( OW_Event $event )
    {
        //$event->setData(ZLEVENT_BOL_EventService::getInstance()->getContentMenu());
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        OW::getEventManager()->bind('zlevent.invite_user', array($this, 'onUserInvite'));
        OW::getEventManager()->bind('feed.on_entity_add', array($this, 'feedEntityAdd'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_AFTER_EVENT_EDIT, array($this, 'afterEventEdit'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_AFTER_CREATE_EVENT, array($this, 'afterEventEdit'));
        OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'addNewContentItem'));
        OW::getEventManager()->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        OW::getEventManager()->bind('zlevent.is_plugin_active', array($this, 'isPluginActive'));

        $credits = new ZLEVENT_CLASS_Credits();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));

        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserDelete'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'privacyAddAction'));

        OW::getEventManager()->bind('feed.on_item_render', array($this, 'feedOnItemRenderActivity'));

        OW::getEventManager()->bind('feed.collect_privacy', array($this, 'feedOnCollectPrivacy'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'feedOnCollectConfigurableActivity'));
        OW::getEventManager()->bind('base_add_comment', array($this, 'onAddComment'));
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'feedOnLike'));
        OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'quickLinks'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_ON_CREATE_EVENT, array($this, 'onAddEvent'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_ON_DELETE_EVENT, array($this, 'onDeleteEvent'));
        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_AFTER_EVENT_EDIT, array($this, 'onEditEvent'));

        OW::getEventManager()->bind(ZLEVENT_BOL_EventService::EVENT_ON_CHANGE_USER_STATUS, array($this, 'onChangeUserStatus'));

        OW::getEventManager()->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetEventInfo'));
    }

    public function init()
    {
        ZLEVENT_CLASS_InvitationHandler::getInstance()->init();
        //OW::getEventManager()->bind('zlevent.get_content_menu', 'getContentMenu');
    }
}