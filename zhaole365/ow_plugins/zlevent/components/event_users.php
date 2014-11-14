<?php

class ZLEVENT_CMP_EventUsers extends OW_Component
{
    private $eventService;
    private $userLists;
    private $userListMenu;
    
    /**
     * @return Constructor.
     */
    public function __construct( $eventId )
    {
        parent::__construct();

        $this->eventService = ZLEVENT_BOL_EventService::getInstance();

        $event = $this->eventService->findEvent($eventId);

        if ( $event === null )
        {
            $this->setVisible(false);
        }

        // event users info
        $this->addUserList($event, ZLEVENT_BOL_EventService::USER_STATUS_YES);
        $this->addUserList($event, ZLEVENT_BOL_EventService::USER_STATUS_MAYBE);
        $this->addUserList($event, ZLEVENT_BOL_EventService::USER_STATUS_NO);
        $this->assign('userLists', $this->userLists);
        $this->addComponent('userListMenu', new BASE_CMP_WidgetMenu($this->userListMenu));
    }

    private function addUserList( ZLEVENT_BOL_Event $event, $status )
    {
        $language = OW::getLanguage();
        $listTypes = $this->eventService->getUserListsArray();
        $serviceConfigs = $this->eventService->getConfigs();
        $userList = $this->eventService->findEventUsers($event->getId(), $status, null, $serviceConfigs[ZLEVENT_BOL_EventService::CONF_EVENT_USERS_COUNT]);
        $usersCount = $this->eventService->findEventUsersCount($event->getId(), $status);

        $idList = array();

        /* @var $eventUser ZLEVENT_BOL_EventUser */
        foreach ( $userList as $eventUser )
        {
            $idList[] = $eventUser->getUserId();
        }

        $usersCmp = new BASE_CMP_AvatarUserList($idList);

        $linkId = UTIL_HtmlTag::generateAutoId('link');
        $contId = UTIL_HtmlTag::generateAutoId('cont');

        $this->userLists[] = array(
            'contId' => $contId,
            'cmp' => $usersCmp->render(),
            'bottomLinkEnable' => ($usersCount > $serviceConfigs[ZLEVENT_BOL_EventService::CONF_EVENT_USERS_COUNT]),
            'toolbarArray' => array(
                array(
                    'label' => $language->text('zlevent', 'avatar_user_list_bottom_link_label', array('count' => $usersCount)),
                    'href' => OW::getRouter()->urlForRoute('zlevent.user_list', array('eventId' => $event->getId(), 'list' => $listTypes[(int) $status]))
                )
            )
        );

        $this->userListMenu[] = array(
            'label' => $language->text('zlevent', 'avatar_user_list_link_label_' . $status),
            'id' => $linkId,
            'contId' => $contId,
            'active' => ( sizeof($this->userListMenu) < 1 ? true : false )
        );
    }
}