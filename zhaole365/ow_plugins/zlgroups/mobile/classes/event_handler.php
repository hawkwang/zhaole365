<?php


class ZLGROUPS_MCLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var ZLGROUPS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return ZLGROUPS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function onInvitationCommand( OW_Event $event )
    {
        $params = $event->getParams();

        $result = array('result' => false);
        if ( !in_array($params['command'], array('zlgroups.accept', 'zlgroups.ignore')) )
        {
            return;
        }

        $groupId = $params['data'];
        $userId = OW::getUser()->getId();

        if ( $params['command'] == 'zlgroups.accept' )
        {
            ZLGROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);
            $result = array('result' => true, 'msg' => OW::getLanguage()->text('zlgroups', 'join_complete_message'));
        }
        else if ( $params['command'] == 'zlgroups.ignore' )
        {
            ZLGROUPS_BOL_Service::getInstance()->deleteInvite($groupId, $userId);
        }

        $event->setData($result);
    }

    public function onInvitationsItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] == 'zlgroup-join' )
        {
            $data = $params['data'];
            $data['string']['vars']['group'] = strip_tags($data['string']['vars']['group']);
            $data['acceptCommand'] = 'zlgroups.accept';
            $data['declineCommand'] = 'zlgroups.ignore';
            $event->setData($data);
        }
    }
    
    public function onFeedItemRenderDisableActions( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( !in_array($params["action"]["entityType"], array( ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE, "zlgroups-join", "zlgroups-status" )) )
        {
            return;
        }
        
        $data = $event->getData();
        
        $data["disabled"] = true;
        
        $event->setData($data);
    }
}