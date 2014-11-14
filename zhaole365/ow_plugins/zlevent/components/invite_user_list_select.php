<?php


class ZLEVENT_CMP_InviteUserListSelect extends BASE_CMP_AvatarUserListSelect
{
    public function __construct( $eventId )
    {
        $count = 100;
        $friendList = null;
        
        if ( OW::getEventManager()->call('plugin.friends') )
        {
            $count = 1000;
            $friendList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => OW::getUser()->getId()));

            if ( empty($friendList) || !is_array($friendList) )
            {
                $count = 100;
                $friendList = array();
            }
        }
        
        $idList = ZLEVENT_BOL_EventService::getInstance()->findUserListForInvite((int)$eventId, 0, $count, $friendList);
        $this->setTemplate( OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'avatar_user_list_select.html' );
        
        parent::__construct($idList);
    }
}
