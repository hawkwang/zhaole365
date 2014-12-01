<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.classes
 */
class GHEADER_CLASS_NotificationsBridge
{
    /**
     * Class instance
     *
     * @var GHEADER_CLASS_NotificationsBridge
     */
    private static $classInstance;

    const ACTION_COMMENT = 'gcover_comment';
    const ACTION_LIKE = 'gcover_like';
    const ACTION_ADD = 'gcover_add';

    const TYPE_LIKE = 'gcover_like';
    const TYPE_ADD = 'gcover_add';
    const TYPE_COMMENT = 'gcover_comment';

    /**
     * Returns class instance
     *
     * @return GHEADER_CLASS_NotificationsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $isPluginActive = false;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    public function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('notifications');
        $this->plugin = OW::getPluginManager()->getPlugin('gheader');
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params['entityType'], array(self::TYPE_COMMENT, self::TYPE_LIKE, self::TYPE_ADD)) )
        {
            return;
        }

        $data = $event->getData();
        $data['url'] = 'javascript://';

        $groupId = intval($params['entityId']);
        $uniqId = $params['key'];

        $js = UTIL_JsGenerator::newInstance();

        $js->jQueryEvent("#" . $uniqId, 'click',
                'OW.ajaxFloatBox("GHEADER_CMP_CoverView", [e.data.groupId], {layout: "empty", top: 50 }); return false;',
        array('e'), array(
            'groupId' => $groupId
        ));

        OW::getDocument()->addOnloadScript($js->generateJs());

        $event->setData($data);
    }

    public function onDublicate( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params['entityType'], array(self::TYPE_COMMENT, self::TYPE_LIKE)) )
        {
            return;
        }

        $data = $event->getData();
        $oldData = $params['oldData'];

        if ( in_array($data['userIds'][0], $oldData['userIds']) )
        {
            $event->setData($oldData);

            return;
        }

        $data['userIds'] = array_merge($oldData['userIds'], $data['userIds']);


        $users = $oldData['users'];
        array_unshift($users, $data['users'][0]);
        $userCount = count($users);

        $data['string']['vars'] = empty($data['string']['vars']) 
            ? array()
            : $data['string']['vars'];
        
        if ( $userCount == 2 )
        {
            $data['string']['key'] = $params['entityType'] == self::TYPE_COMMENT
                ? 'gheader+notifications_comment_2'
                : 'gheader+notifications_like_2';

            $data['string']['vars']['user1'] = '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>';
            $data['string']['vars']['user2'] = '<a href="' . $users[1]['userUrl'] . '">' . $users[1]['userName'] . '</a>';
        }

        if ( $userCount > 2 )
        {
            $data['string']['key'] = $params['entityType'] == self::TYPE_COMMENT
                ? 'gheader+notifications_comment_many'
                : 'gheader+notifications_like_many';

            $data['string']['vars']['user1'] = '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>';
            $data['string']['vars']['user2'] = '<a href="' . $users[1]['userUrl'] . '">' . $users[1]['userName'] . '</a>';
            $data['string']['vars']['otherCount'] = $userCount - 1;
        }

        $data['users'] = $users;

        $event->setData($data);
    }

    public function onComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != GHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];

        $cover = GHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);
        if ( empty($cover) )
        {
            return;
        }
        
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        if ( empty($group) )
        {
            return;
        }
        
        $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $groupTitle = UTIL_String::truncate($group->title, 100, '...');
        $groupImage = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
        
        $comment = BOL_CommentService::getInstance()->findComment($params['commentId']);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        
        $previewImage = array(
            'src' => $groupImage,
            'url' => $groupUrl
        );

        $notificationParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'action' => self::ACTION_COMMENT,
            'entityType' => self::TYPE_COMMENT,
            'entityId' => $cover->groupId,
            'userId' => null,
            'time' => time()
        );

        $users = array();
        $users[] = array(
            'userId' => $userId,
            'userName' => $avatar['title'],
            'userUrl' => $avatar['url']
        );

        $groupEmbed = '<a href="' . $groupUrl . '">' . $groupTitle . '</a>';
        
        $notificationData = array(
            'string' => array(
                'key' => 'gheader+notifications_comment_1',
                'vars' => array(
                    'user' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>',
                    'group' => $groupEmbed
                 )
            ),
            'users' => $users,
            'userIds' => array($userId),
            'avatar' => $avatar,
            'content' => $comment->getMessage(),
            'contentImage' => $previewImage,
            'url' => $groupUrl
        );

        $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);

        foreach ( $userIds as $uid )
        {
            if ( $uid == $userId )
            {
                continue;
            }

            $notificationParams['userId'] = $uid;

            $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
            OW::getEventManager()->trigger($event);
        }
    }

    public function onLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != GHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];

        $cover = GHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);
        if ( empty($cover) )
        {
            return;
        }
        
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        if ( empty($group) )
        {
            return;
        }
        
        $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $groupTitle = UTIL_String::truncate($group->title, 100, '...');
        $groupImage = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
        
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $previewImage = array(
            'src' => $groupImage,
            'url' => $groupUrl
        );

        $notificationParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'action' => self::ACTION_LIKE,
            'entityType' => self::TYPE_LIKE,
            'entityId' => $cover->groupId,
            'userId' => null,
            'time' => time()
        );

        $users = array();
        $users[] = array(
            'userId' => $userId,
            'userName' => $avatar['title'],
            'userUrl' => $avatar['url']
        );

        $groupEmbed = '<a href="' . $groupUrl . '">' . $groupTitle . '</a>';
        
        $notificationData = array(
            'string' => array(
                'key' => 'gheader+notifications_like_1',
                'vars' => array(
                    'user' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>',
                    'group' => $groupEmbed
                )
            ),
            'users' => $users,
            'userIds' => array($userId),
            'avatar' => $avatar,
            'contentImage' => $previewImage,
            'url' => $groupUrl
        );

        $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);

        foreach ( $userIds as $uid )
        {
            if ( $uid == $userId )
            {
                continue;
            }

            $notificationParams['userId'] = $uid;

            $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
            OW::getEventManager()->trigger($event);
        }
    }

    
    private function triggerAction( OW_Event $event, $add )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $coverId = $params['id'];
        
        $cover = GHEADER_BOL_Service::getInstance()->findCoverById($coverId);
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        
        if ( empty($group) )
        {
            return;
        }
        
        $userId = $group->userId;

        $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $groupTitle = UTIL_String::truncate($group->title, 100, '...');
        $groupImage = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
        
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $previewImage = array(
            'src' => $groupImage,
            'url' => $groupUrl
        );

        $notificationParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'action' => self::ACTION_ADD,
            'entityType' => self::TYPE_ADD,
            'entityId' => $cover->groupId,
            'userId' => null,
            'time' => time()
        );

        $groupEmbed = '<a href="' . $groupUrl . '">' . $groupTitle . '</a>';
        
        $notificationData = array(
            'string' => array(
                'key' => $add ? 'gheader+notifications_cover_add' : 'gheader+notifications_cover_change',
                'vars' => array(
                    'user' => '<a href="' . $avatar['url'] . '">' . $avatar['title'] . '</a>',
                    'group' => $groupEmbed
                )
            ),
            'avatar' => $avatar,
            'contentImage' => $previewImage,
            'url' => $groupUrl
        );

        $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);

        foreach ( $userIds as $uid )
        {
            if ( $uid == $userId )
            {
                continue;
            }

            $notificationParams['userId'] = $uid;

            $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
            OW::getEventManager()->trigger($event);
        }
    }
    
    public function onCoverAdd( OW_Event $event )
    {
        $this->triggerAction($event, true);
    }

    public function onCoverChange( OW_Event $event )
    {
        $this->triggerAction($event, false);
    }
    
    
    public function onCoverRemove( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = $params['groupId'];

        $event = new OW_Event('notifications.remove', array(
            'entityType' => self::TYPE_LIKE,
            'entityId' => $groupId
        ));

        OW::getEventManager()->trigger($event);

        $event = new OW_Event('notifications.remove', array(
            'entityType' => self::TYPE_COMMENT,
            'entityId' => $groupId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onCollectActions( BASE_CLASS_EventCollector $e )
    {
        $sectionLabel = OW::getLanguage()->text('groups', 'email_notification_section_label');
        $sectionIcon = 'ow_ic_files';
        
        $e->add(array(
            'section' => 'groups',
            'action' => self::ACTION_COMMENT,
            'sectionIcon' => $sectionIcon,
            'sectionLabel' => $sectionLabel,
            'description' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_setting_comment'),
            'selected' => true
        ));
        
        $e->add(array(
            'section' => 'groups',
            'action' => self::ACTION_ADD,
            'sectionIcon' => $sectionIcon,
            'sectionLabel' => $sectionLabel,
            'description' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_setting_add'),
            'selected' => true
        ));

        if ( GHEADER_CLASS_NewsfeedBridge::getInstance()->isActive() )
        {
            $e->add(array(
                'section' => 'groups',
                'action' => self::ACTION_LIKE,
                'sectionIcon' => $sectionIcon,
                'sectionLabel' => $sectionLabel,
                'description' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_setting_like'),
                'selected' => true
            ));
        }
    }

    public function init()
    {
        OW::getEventManager()->bind('notifications.collect_actions', array($this, 'onCollectActions'));
        OW::getEventManager()->bind('notifications.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind('notifications.on_dublicate', array($this, 'onDublicate'));

        OW::getEventManager()->bind('base_add_comment', array($this, 'onComment'));
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'onLike'));

        OW::getEventManager()->bind(GHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
        OW::getEventManager()->bind(GHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(GHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverChange'));
    }
}