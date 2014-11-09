<?php

class ZLGROUPS_CMP_InviteWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = $params->additionalParamList['entityId'];
        $userId = OW::getUser()->getId();
        $service = ZLGROUPS_BOL_Service::getInstance();

        if ( !$params->customizeMode && !$service->isCurrentUserInvite($groupId) )
        {
            $this->setVisible(false);

            return;
        }

        $users = null;

        if ( OW::getEventManager()->call('plugin.friends') )
        {
            $users = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
                'userId' => $userId,
                'count' => 100
            ));
        }

        if ( $users === null )
        {
            $users = array();
            $userDtos = BOL_UserService::getInstance()->findRecentlyActiveList(0, 100);

            foreach ( $userDtos as $u )
            {
                if ( $u->id != $userId )
                {
                    $users[] = $u->id;
                }
            }
        }

        $idList = array();

        if ( !empty($users) )
        {
            $groupUsers = $service->findGroupUserIdList($groupId);
            $invitedList = $service->findInvitedUserIdList($groupId, $userId);

            foreach ( $users as $uid )
            {
            	// 如果已经是乐群成员 或 已经在被邀请名单中， 则不处理
                if ( in_array($uid, $groupUsers) || in_array($uid, $invitedList) )
                {
                    continue;
                }

                $idList[] = $uid;
            }
        }

        $options = array(
            'groupId' => $groupId,
            'userList' => $idList,
            'floatBoxTitle' => OW::getLanguage()->text('zlgroups', 'invite_fb_title'),
            'inviteResponder' => OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'invite')
        );

        // 动态创建js脚本，用于
        $js = UTIL_JsGenerator::newInstance()->callFunction('ZLGROUPS_InitInviteButton', array($options));
        OW::getDocument()->addOnloadScript($js);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW_Language::getInstance()->text('zlgroups', 'widget_invite_button_title'),
            self::SETTING_ICON => self::ICON_BOOKMARK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}