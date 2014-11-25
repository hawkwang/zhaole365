<?php

final class ZLEVENT_BOL_EventService
{
    const USER_STATUS_YES = ZLEVENT_BOL_EventUserDao::VALUE_STATUS_YES;
    const USER_STATUS_MAYBE = ZLEVENT_BOL_EventUserDao::VALUE_STATUS_MAYBE;
    const USER_STATUS_NO = ZLEVENT_BOL_EventUserDao::VALUE_STATUS_NO;

    const CAN_INVITE_PARTICIPANT = ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_INVITE_PARTICIPANT;
    const CAN_INVITE_CREATOR = ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_INVITE_CREATOR;

    const CAN_VIEW_ANYBODY = ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_VIEW_ANYBODY;
    const CAN_VIEW_INVITATION_ONLY = ZLEVENT_BOL_EventDao::VALUE_WHO_CAN_VIEW_INVITATION_ONLY;

    const CONF_EVENT_USERS_COUNT = 'zlevent_users_count';
    const CONF_EVENT_USERS_COUNT_ON_PAGE = 'zlevent_users_count_on_page';
    const CONF_EVENTS_COUNT_ON_PAGE = 'zlevents_count_on_page';
    const CONF_WIDGET_EVENTS_COUNT = 'zlevents_widget_count';
    const CONF_WIDGET_EVENTS_COUNT_OPTION_LIST = 'zlevents_widget_count_select_set';
    const CONF_DASH_WIDGET_EVENTS_COUNT = 'zlevents_dash_widget_count';

    const EVENT_AFTER_EVENT_EDIT = 'zlevent_after_event_edit';
    const EVENT_ON_DELETE_EVENT = 'zlevent_on_delete_event';
    const EVENT_AFTER_DELETE_EVENT = 'zlevent_after_delete_event';
    const EVENT_ON_CREATE_EVENT = 'zlevent_on_create_event';
    const EVENT_ON_CHANGE_USER_STATUS = 'zlevent_on_change_user_status';
    const EVENT_AFTER_CREATE_EVENT = 'zlevent_after_create_event';
    
    const EVENT_BEFORE_EVENT_CREATE = 'zlevents.before_event_create';
    const EVENT_BEFORE_EVENT_EDIT = 'zlevents.before_event_edit';
    const EVENT_COLLECT_TOOLBAR = 'zlevents.collect_toolbar';
    
    const ENTITY_TYPE_TAG = 'zlevent_tag';

    /**
     * @var array
     */
    private $configs = array();
    
    private $eventLocationDao;
    private $eventGroupDao;
    /**
     * @var ZLEVENT_BOL_EventDao
     */
    private $eventDao;
    /**
     * @var ZLEVENT_BOL_EventUserDao
     */
    private $eventUserDao;
    /**
     * @var ZLEVENT_BOL_EventInviteDao
     */
    private $eventInviteDao;
    /**
     * Singleton instance.
     *
     * @var ZLEVENT_BOL_EventService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLEVENT_BOL_EventService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
    	$this->eventLocationDao = ZLEVENT_BOL_EventLocationDao::getInstance();
    	$this->eventGroupDao = ZLEVENT_BOL_EventGroupDao::getInstance();
    	$this->eventDao = ZLEVENT_BOL_EventDao::getInstance();
        $this->eventUserDao = ZLEVENT_BOL_EventUserDao::getInstance();
        $this->eventInviteDao = ZLEVENT_BOL_EventInviteDao::getInstance();

        $this->configs[self::CONF_EVENT_USERS_COUNT] = 10;
        $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE] = 15;
        $this->configs[self::CONF_DASH_WIDGET_EVENTS_COUNT] = 3;
        $this->configs[self::CONF_WIDGET_EVENTS_COUNT] = 3;
        $this->configs[self::CONF_EVENT_USERS_COUNT_ON_PAGE] = 30;
        $this->configs[self::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST] = array(3 => 3, 5 => 5, 10 => 10, 15 => 15, 20 => 20);
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Saves event dto.
     *
     * @param ZLEVENT_BOL_Event $event
     */
    public function saveEvent( ZLEVENT_BOL_Event $event )
    {
        $this->eventDao->save($event);
    }

    /**
     * Makes and saves event standard image and icon.
     *
     * @param string $imagePath
     * @param integer $imageId
     */
    public function saveEventImage( $imagePath, $imageId )
    {
        $storage = OW::getStorage();
        
        if ( $storage->fileExists($this->generateImagePath($imageId)) )
        {
            $storage->removeFile($this->generateImagePath($imageId));
            $storage->removeFile($this->generateImagePath($imageId, false));
        }

        $pluginfilesDir = Ow::getPluginManager()->getPlugin('zlevent')->getPluginFilesDir();

        $tmpImgPath = $pluginfilesDir . 'img_' .uniqid() . '.jpg';
        $tmpIconPath = $pluginfilesDir . 'icon_' . uniqid() . '.jpg';

        $image = new UTIL_Image($imagePath);
        $image->resizeImage(400, null)->saveImage($tmpImgPath)
            ->resizeImage(100, 100, true)->saveImage($tmpIconPath);

        unlink($imagePath);

        $storage->copyFile($tmpIconPath, $this->generateImagePath($imageId));
        $storage->copyFile($tmpImgPath,$this->generateImagePath($imageId, false));

        unlink($tmpImgPath);
        unlink($tmpIconPath);
    }
    
    public function saveEventImageFromUrl( $imageUrlPath, $imageId )
    {
    	$storage = OW::getStorage();
    
    	if ( $storage->fileExists($this->generateImagePath($imageId)) )
    	{
    		$storage->removeFile($this->generateImagePath($imageId));
    		$storage->removeFile($this->generateImagePath($imageId, false));
    	}
    
    	$pluginfilesDir = Ow::getPluginManager()->getPlugin('zlevent')->getPluginFilesDir();
    
    	$tmpImgPath = $pluginfilesDir . 'img_' .uniqid() . '.jpg';
    	$tmpIconPath = $pluginfilesDir . 'icon_' . uniqid() . '.jpg';
    
    	$image = new UTIL_Image($imageUrlPath);
    	$image->resizeImage(400, null)->saveImage($tmpImgPath)
    	->resizeImage(100, 100, true)->saveImage($tmpIconPath);
    
    	//unlink($imagePath);
    
    	$storage->copyFile($tmpIconPath, $this->generateImagePath($imageId));
    	$storage->copyFile($tmpImgPath,$this->generateImagePath($imageId, false));
    
    	unlink($tmpImgPath);
    	unlink($tmpIconPath);
    }

    /**
     * Deletes event.
     *
     * @param integer $eventId
     */
    public function deleteEvent( $eventId )
    {
        $eventDto = $this->eventDao->findById((int) $eventId);

        if ( $eventDto === null )
        {
            return;
        }
        
        $e = new OW_Event(self::EVENT_ON_DELETE_EVENT, array('eventId' => (int) $eventId));
        OW::getEventManager()->trigger($e);

        if( !empty($eventDto->image) )
        {
            $storage = OW::getStorage();
            $storage->removeFile($this->generateImagePath($eventDto->image));
            $storage->removeFile($this->generateImagePath($eventDto->image, false));
        }

        // 删除群乐地址
        $this->eventLocationDao->deleteByEventId($eventDto->getId());
        
        // 删除群乐乐群信息
        $this->eventGroupDao->deleteByEventId($eventDto->getId());
        
        $this->eventUserDao->deleteByEventId($eventDto->getId());
        $this->eventDao->deleteById($eventDto->getId());
        $this->eventInviteDao->deleteByEventId($eventDto->getId());
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('zlevent', $eventId);
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('zlevent-invitation', $eventId);
        
        $e = new OW_Event(self::EVENT_AFTER_DELETE_EVENT, array('eventId' => (int) $eventId));
        OW::getEventManager()->trigger($e);
        
    }

    /**
     * Returns event image and icon path.
     *
     * @param integer $imageId
     * @param boolean $icon
     * @return string
     */
    public function generateImagePath( $imageId, $icon = true )
    {
        $imagesDir = OW::getPluginManager()->getPlugin('zlevent')->getUserFilesDir();
        return $imagesDir . ( $icon ? 'zlevent_icon_' : 'zlevent_image_' ) . $imageId . '.jpg';
    }

    /**
     * Returns event image and icon url.
     * 
     * @param integer $imageId
     * @param boolean $icon
     * @return string
     */
    public function generateImageUrl( $imageId, $icon = true )
    {
        return OW::getStorage()->getFileUrl($this->generateImagePath($imageId, $icon));
    }

    /**
     * Returns default event image url.
     */
    public function generateDefaultImageUrl()
    {
        return OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'no-picture.png';
    }

    /**
     * Finds event by id.
     *
     * @param integer $id
     * @return ZLEVENT_BOL_Event
     */
    public function findEvent( $id )
    {
        return $this->eventDao->findById((int) $id);
    }

    /**
     * Returns event users with provided status.
     *
     * @param integer $eventId
     * @param integer $status
     * @return array<ZLEVENT_BOL_EventUser>
     */
    public function findEventUsers( $eventId, $status, $page, $usersCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $usersCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENT_USERS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventUserDao->findListByEventIdAndStatus($eventId, $status, $first, $count);
    }

    /**
     * Returns users count for provided event and status.
     *
     * @param integer $eventId
     * @param integer $status
     * @return integer
     */
    public function findEventUsersCount( $eventId, $status )
    {
        return (int) $this->eventUserDao->findUsersCountByEventIdAndStatus($eventId, $status);
    }

    /**
     * Saves event user objects.
     *
     * @param ZLEVENT_BOL_EventUser $eventUser
     */
    public function saveEventUser( ZLEVENT_BOL_EventUser $eventUser )
    {
        $this->eventUserDao->save($eventUser);
    }

    /**
     * Saves event user objects.
     *
     * @param ZLEVENT_BOL_EventUser $eventUser
     */
    public function addEventUser( $userId, $eventId, $status, $timestamp = null )
    {
        $statusList = array( ZLEVENT_BOL_EventUserDao::VALUE_STATUS_YES, ZLEVENT_BOL_EventUserDao::VALUE_STATUS_MAYBE, ZLEVENT_BOL_EventUserDao::VALUE_STATUS_NO );

        if( (int) $userId <= 0 || $eventId <=0 || !in_array($status, $statusList) )
        {
            return null;
        }

        $event = $this->findEvent($eventId);

        if( empty($event) )
        {
            return null;
        }

        if ( !isset($timestamp) )
        {
            $timestamp = time();
        }

        $eventUser = $this->findEventUser($eventId, $userId);

        if ( empty($eventUser) )
        {
            $eventUser = new ZLEVENT_BOL_EventUser();

            $eventUser->eventId = $eventId;
            $eventUser->userId = $userId;
            $eventUser->timeStamp = $timestamp;
        }

        $eventUser->status = $status;
        
        $this->eventUserDao->save($eventUser);

        return $eventUser;
    }

    /**
     * Finds event-user object.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return ZLEVENT_BOL_EventUser
     */
    public function findEventUser( $eventId, $userId )
    {
        return $this->eventUserDao->findObjectByEventIdAndUserId($eventId, $userId);
    }

    /**
     * Checks if user can view and join event.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return boolean
     */
    public function canUserView( $eventId, $userId )
    {
        $event = $this->eventDao->findById($eventId);
        /* @var $event ZLEVENT_BOL_Event */
        if ( $event === null )
        {
            return false;
        }

        $userEvent = $this->eventUserDao->findObjectByEventIdAndUserId($eventId, $userId);

        if ( $event->getWhoCanView() === self::CAN_VIEW_INVITATION_ONLY && $userEvent === null )
        {
            return false;
        }

        return true;
    }

    /**
     * Checks if user can invite to event.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return boolean
     */
    public function canUserInvite( $eventId, $userId )
    {
        $event = $this->eventDao->findById($eventId);
        /* @var $event ZLEVENT_BOL_Event */
        if ( $event === null || ( $event->getWhoCanInvite() == self::CAN_INVITE_CREATOR && $userId != $event->getUserId() ) )
        {
            return false;
        }

        $userEvent = $this->eventUserDao->findObjectByEventIdAndUserId($eventId, $userId);

        if ( $userEvent === null || $userEvent->getStatus() != self::USER_STATUS_YES )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns latest events list.
     *
     * @param integer $page
     * @return array<ZLEVENT_BOL_Event>
     */
    public function findPublicEvents( $page, $eventsCount = null, $past = false )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findPublicEvents($first, $count, $past);
    }

    /**
     * Returns latest events count.
     *
     * @return integer
     */
    public function findPublicEventsCount( $past = false )
    {
        return $this->eventDao->findPublicEventsCount($past);
    }

    /**
     * Invites user to event.
     *
     * @param integer $eventId
     * @param integer $userId
     * @param integer $inviterId
     *
     * @return ZLEVENT_BOL_EventInvite
     */
    public function inviteUser( $eventId, $userId, $inviterId )
    {
        $event = $this->findEvent($eventId);

        if ( $event === null )
        {
            return false;
        }

        $eventInvite = new ZLEVENT_BOL_EventInvite();
        $eventInvite->setEventId($eventId);
        $eventInvite->setInviterId($inviterId);
        $eventInvite->setUserId($userId);
        $eventInvite->setTimeStamp(time());
        $eventInvite->setDisplayInvitation(true);

        $this->eventInviteDao->save($eventInvite);

        return $eventInvite;
    }

    /**
     * Returns event invitation for user.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return ZLEVENT_BOL_EventInvite
     */
    public function findEventInvite( $eventId, $userId )
    {
        return $this->eventInviteDao->findObjectByUserIdAndEventId($eventId, $userId);
    }

    /**
     * Finds events for user
     *
     * @param integer $userId
     * @return array
     */
    public function findUserEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserCreatedEvents($userId, $first, $count);
    }

    /**
     * Returns user created events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUserEventsCount( $userId )
    {
        return $this->eventDao->findUserCreatedEventsCount($userId);
    }

    /**
     * Returns list of user participating events.
     *
     * @param integer $userId
     * @param integer $page
     * @param integer $count
     * @return array
     */
    public function findUserParticipatedEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserEventsWithStatus($userId, self::USER_STATUS_YES, $first, $count);
    }

    /**
     * Returns user participated events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUserParticipatedEventsCount( $userId )
    {
        return $this->eventDao->findUserEventsCountWithStatus($userId, self::USER_STATUS_YES);
    }

    /**
     * Returns list of user participating public events.
     *
     * @param integer $userId
     * @param integer $page
     * @param integer $count
     * @return array
     */
    public function findUserParticipatedPublicEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findPublicUserEventsWithStatus($userId, self::USER_STATUS_YES, $first, $count);
    }

    /**
     * Returns user participated public events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUserParticipatedPublicEventsCount( $userId )
    {
        return $this->eventDao->findPublicUserEventsCountWithStatus($userId, self::USER_STATUS_YES);
    }

    /**
     * Returns user participated public events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function hideInvitationByUserId( $userId )
    {
        return $this->eventInviteDao->hideInvitationByUserId($userId);
    }

    /**
     * Prepares data for ipc listing.
     *
     * @param array<ZLEVENT_BOL_Event> $events
     * @return array
     */
    public function getListingData( array $events )
    {
        $resultArray = array();

        /* @var $eventItem ZLEVENT_BOL_Event */
        foreach ( $events as $eventItem )
        {
            $title = UTIL_String::truncate(strip_tags($eventItem->getTitle()), 80, "...") ;
            $content = UTIL_String::truncate(strip_tags($eventItem->getDescription()), 100, "...");
            
            $resultArray[$eventItem->getId()] = array(
                'content' => $content,
                'title' => $title,
                'eventUrl' => OW::getRouter()->urlForRoute('zlevent.view', array('eventId' => $eventItem->getId())),
                'imageSrc' => ( $eventItem->getImage() ? $this->generateImageUrl($eventItem->getImage(), true) : $this->generateDefaultImageUrl() ),
                'imageTitle' => $title
            );
        }

        return $resultArray;
    }

    /**
     * Prepares data for ipc listing with toolbar.
     *
     * @param array<ZLEVENT_BOL_Event> $events
     * @return array
     */
    public function getListingDataWithToolbar( array $events, $toolbarList = array() )
    {
        $resultArray = $this->getListingData($events);
        $userService = BOL_UserService::getInstance();

        $idArray = array();

        /* @var $event ZLEVENT_BOL_Event */
        foreach ( $events as $event )
        {
            $idArray[] = $event->getUserId();
        }

        $usernames = $userService->getDisplayNamesForList($idArray);
        $urls = $userService->getUserUrlsForList($idArray);

        $language = OW::getLanguage();
        /* @var $eventItem ZLEVENT_BOL_Event */
        foreach ( $events as $eventItem )
        {
            $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => $usernames[$eventItem->getUserId()], 'href' => $urls[$eventItem->getUserId()], 'class' => 'ow_icon_control ow_ic_user');
            $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => UTIL_DateTime::formatSimpleDate($eventItem->getStartTimeStamp(),$eventItem->getStartTimeDisable()), 'class' => 'ow_ipc_date');

            if ( !empty($toolbarList[$eventItem->getId()]) )
            {
                $resultArray[$eventItem->getId()]['toolbar'] = array_merge($resultArray[$eventItem->getId()]['toolbar'], $toolbarList[$eventItem->getId()]);
            }
            
            /* if( !empty($isInviteList) )
            {
                $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => $language->text('zlevent', 'ignore_request'),'href' => 'zlevent.invite_acept');
                $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => $language->text('zlevent', 'accept_request'),'href' => 'zlevent.invite_decline');
            }*/
        }
        //printVar($resultArray);
        return $resultArray;
    }

    // 得到用户参加类型列表数组
    public function getUserListsArray()
    {
        return array(
            self::USER_STATUS_YES => 'yes',
            self::USER_STATUS_MAYBE => 'maybe',
            self::USER_STATUS_NO => 'no'
        );
    }


    // 根据page和数量得到指定用户被邀请的活动
    public function findUserInvitedEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserInvitedEvents($userId, $first, $count);
    }

	// 根据page和数量得到指定用户被邀请的活动数
    public function findUserInvitedEventsCount( $userId )
    {
        return $this->eventDao->findUserInvitedEventsCount($userId);
    }

    /**
     * Returns displayed user invited events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findDisplayedUserInvitationCount( $userId )
    {
        return $this->eventDao->findDisplayedUserInvitationCount($userId);
    }

    /**
     * Deletes all event invites for provided user.
     *
     * @param integer $eventId
     * @param integer $userId
     */
    public function deleteUserEventInvites( $eventId, $userId )
    {
        $this->eventInviteDao->deleteByUserIdAndEventId($eventId, $userId);
    }

    /**
     * Deletes all user events.
     *
     * @param integer $userId
     */
    public function deleteUserEvents( $userId )
    {
        $events = $this->eventDao->findAllUserEvents($userId);

        /* @var $event ZLEVENT_BOL_Event */
        foreach ( $events as $event )
        {
            $this->deleteEvent($event->getId());
        }
    }

    /**
     * returns invited userId list.
     *
     * @param integer $eventId
     */
    public function findInviteUserListByEventId( $eventId )
    {
        $inviteList = $this->eventInviteDao->findInviteListByEventId($eventId);

        $userList = array();

        foreach ( $inviteList as $invite )
        {
            $userList[] = $invite->userId;
        }

        return $userList;
    }

    public function findUserListForInvite( $eventId, $first, $count, $friendList = array() )
    {
         return $this->eventInviteDao->findUserListForInvite($eventId, $first, $count, $friendList );
    }
    
    // 构造活动列表首页内容菜单
    public function getContentMenu()
    {
        $menuItems = array();

        if ( OW::getUser()->isAuthenticated() )
        {
            $listNames = array(
                'invited' => array('iconClass' => 'ow_ic_bookmark'),
                'joined' => array('iconClass' => 'ow_ic_friends'),
                'past' => array('iconClass' => 'ow_ic_reply'),
                'latest' => array('iconClass' => 'ow_ic_calendar')
            );
        }
        else
        {
            $listNames = array(
                'past' => array('iconClass' => 'ow_ic_reply'),
                'latest' => array('iconClass' => 'ow_ic_calendar')
            );
        }
        
        foreach ( $listNames as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listKey);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('zlevent.view_event_list', array('list' => $listKey)));
            $menuItem->setLabel(OW::getLanguage()->text('zlevent', 'common_list_type_' . $listKey . '_label'));
            $menuItem->setIconClass($listArr['iconClass']);
            $menuItems[] = $menuItem;
        }
        
        $event = new BASE_CLASS_EventCollector('zlevent.add_content_menu_item');
        OW::getEventManager()->getInstance()->trigger($event);
        
        $data = $event->getData();
        
        if ( !empty($data) && is_array($data) )
        {
            $menuItems = array_merge($menuItems, $data);
        }
        
        return new BASE_CMP_ContentMenu($menuItems);
    }
    
    public function clearEventInvitations( $eventId )
    {        
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('zlevent', (int)$eventId);
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('zlevent-invitation', (int)$eventId);
        BOL_InvitationService::getInstance()->deleteInvitationByEntity(ZLEVENT_CLASS_InvitationHandler::INVITATION_JOIN, (int)$eventId);

        $this->eventInviteDao->deleteByEventId($eventId);
    }
    
    public function findCronExpiredEvents( $first, $count )
    {
        return $this->eventDao->findExpiredEventsForCronJobs($first, $count);
    }
    
    public function findByIdList( $idList )
    {
        return $this->eventDao->findByIdList($idList);
    }
    
    // added by hawk
    // 群乐地址相关操作
    public function saveLocation($eventid, $location, $formated_address, $province, $city, $district, $longitude, $latitude)
    {
    	// 删除已有乐群地址信息
    	$this->eventLocationDao->deleteByEventId($eventid);
    	 
    	// 根据$formated_address获得地址信息，如果不存在，则创建相应地址信息
    	$formated_address_info = ZLAREAS_BOL_LocationService::getInstance()->findLocationByAddress($formated_address);
    	if($formated_address_info==null)
    	{
    		ZLAREAS_BOL_LocationService::getInstance()->addDetailedLocation($formated_address, $province, $city, $district, $longitude, $latitude, $location);
    	}
    	$formated_address_info = ZLAREAS_BOL_LocationService::getInstance()->findLocationByAddress($formated_address);
    	 
    	// 建立关联关系
    	$eventlocation = new ZLEVENT_BOL_EventLocation();
    	$eventlocation->eventId = $eventid;
    	$eventlocation->locationId = $formated_address_info->id;
    	$eventlocation->location = $location;
    	$this->eventLocationDao->save($eventlocation);
    }
    
    public function findLocationDetailedInfoByEventId($eventid)
    {
    	$locationdetails = array();
    	 
    	$eventlocation = $this->eventLocationDao->findByEventId($eventid);
    	$locationdetails['location'] = $eventlocation->location;
    	 
    	$locationid = $eventlocation->locationId;
    	$location = ZLAREAS_BOL_LocationService::getInstance()->findById($locationid);
    	 
    	$locationdetails['formated_address'] = $location->address;
    	$locationdetails['longitude'] = $location->longitude;
    	$locationdetails['latitude'] = $location->latitude;
    	 
    	$areacode = $location->areacode;
    	$area = ZLAREAS_BOL_Service::getInstance()->findByAreacode($areacode);
    	$locationdetails['province'] = $area->province;
    	$locationdetails['city'] = $area->city;
    	$locationdetails['area'] = $area->area;
    	$locationdetails['areacode'] = $areacode;
    	 
    	$locationdetails['locationinfo'] =  $locationdetails['formated_address'] . '||'
    			. $locationdetails['province'] . '||' . $locationdetails['city'] . '||' . $locationdetails['area']
    			. '||' . $locationdetails['longitude'] . '||' . $locationdetails['latitude'];
    	 
    	return $locationdetails;
    }
    
    // 和乐群有关操作
    public function saveEventGroup($eventId, $groupId)
    {
    	$eventGroup = new ZLEVENT_BOL_EventGroup();
    	$eventGroup->eventId = $eventId;
    	$eventGroup->groupId = $groupId;
    	$this->eventGroupDao->save($eventGroup);
    }
    
    public function findGroupByEventId($eventId)
    {
    	$eventgroup = $this->eventGroupDao->findByEventId($eventId);
    	if($eventgroup==null)
    		return null;
    	
    	$group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($eventgroup->groupId);
    	return $group;
    }
    
    public function deleteEventGroupByEventId($eventId)
    {
    	$this->eventGroupDao->deleteByEventId($eventId);
    }

    public function deleteEventGroupByGroupId($groupId)
    {
    	$this->eventGroupDao->deleteByGroupId($groupId);
    }
    
    // 删除所有隶属该乐群的群乐纪录
    public function deleteEventsByGroupId($groupId)
    {
    	// get eventid list
    	$eventGroupList = $this->eventGroupDao->findByGroupId($groupId);
    	
    	// delete each event
    	foreach ( $eventGroupList as $eventGroup)
			$this->deleteEvent($eventGroup->eventId);
    }
    
    public function findPublicEventsCountByGroupId($groupId, $past = false )
    {
    	return $this->eventDao->findPublicEventsCountByGroupId($groupId, $past);
    }
    
    public function findLatestEventByGroupId( $groupId, $past = false )
    {
    	return $this->eventDao->findLatestEventByGroupId($groupId, $past);
    }
    
}