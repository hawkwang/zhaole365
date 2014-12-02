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
 * @package uheader.classes
 */
class UHEADER_CLASS_NotificationsBridge
{
    /**
     * Class instance
     *
     * @var UHEADER_CLASS_NotificationsBridge
     */
    protected static $classInstance;

    const ACTION_COMMENT = 'ucover_comment';
    const ACTION_LIKE = 'ucover_like';

    const TYPE_LIKE = 'ucover_like';
    const TYPE_COMMENT = 'ucover_comment';

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_NotificationsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected $isPluginActive = false;

    /**
     *
     * @var OW_Plugin
     */
    protected $plugin;

    public function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('notifications');
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params['entityType'], array(self::TYPE_COMMENT, self::TYPE_LIKE)) )
        {
            return;
        }

        $data = $event->getData();
        $data['url'] = 'javascript://';

        $userId = intval($params['entityId']);
        $uniqId = $params['key'];

        $js = UTIL_JsGenerator::newInstance();

        $js->jQueryEvent("#" . $uniqId, 'click',
                'if ( !$(e.target).is("a") ) { OW.ajaxFloatBox("UHEADER_CMP_CoverView", [e.data.userId], {layout: "empty", top: 50 }); return false; }',
        array('e'), array(
            'userId' => $userId
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

        if ( $userCount == 2 )
        {
            $data['string']['key'] = $params['entityType'] == self::TYPE_COMMENT
                ? 'uheader+notifications_comment_2'
                : 'uheader+notifications_like_2';

            $data['string']['vars'] = array(
                'user1' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>',
                'user2' => '<a href="' . $users[1]['userUrl'] . '">' . $users[1]['userName'] . '</a>'
            );
        }

        if ( $userCount > 2 )
        {
            $data['string']['key'] = 'uheader+notifications_comment_many';

            $data['string']['key'] = $params['entityType'] == self::TYPE_COMMENT
                ? 'uheader+notifications_comment_many'
                : 'uheader+notifications_like_many';

            $data['string']['vars'] = array(
                'user1' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>',
                'user2' => '<a href="' . $users[1]['userUrl'] . '">' . $users[1]['userName'] . '</a>',
                'otherCount' => $userCount - 1
            );
        }

        $data['users'] = $users;

        $event->setData($data);
    }

    public function onComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = OW::getUser()->getId();

        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);

        if ( $cover->userId == $userId )
        {
            return;
        }

        $userUrl = BOL_UserService::getInstance()->getUserUrl($cover->userId);
        $comment = BOL_CommentService::getInstance()->findComment($params['commentId']);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $previewImage = null;

        $notificationParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'action' => self::ACTION_COMMENT,
            'entityType' => self::TYPE_COMMENT,
            'entityId' => $cover->userId,
            'userId' => $cover->userId,
            'time' => time()
        );

        $users = array();
        $users[] = array(
            'userId' => $userId,
            'userName' => $avatar['title'],
            'userUrl' => $avatar['url']
        );

        $notificationData = array(
            'string' => array(
                'key' => 'uheader+notifications_comment_1',
                'vars' => array(
                    'user' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>'
                 )
            ),
            'users' => $users,
            'userIds' => array($userId),
            'avatar' => $avatar,
            'content' => $comment->getMessage(),
            'contentImage' => $previewImage,
            'url' => $userUrl,
            "coverId" => $cover->id
        );

        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);
    }

    public function onLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = OW::getUser()->getId();

        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);

        if ( $cover->userId == $userId )
        {
            return;
        }

        $userUrl = BOL_UserService::getInstance()->getUserUrl($cover->userId);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $previewImage = null;

        $notificationParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'action' => self::ACTION_LIKE,
            'entityType' => self::TYPE_LIKE,
            'entityId' => $cover->userId,
            'userId' => $cover->userId,
            'time' => time()
        );

        $users = array();
        $users[] = array(
            'userId' => $userId,
            'userName' => $avatar['title'],
            'userUrl' => $avatar['url']
        );

        $notificationData = array(
            'string' => array(
                'key' => 'uheader+notifications_like_1',
                'vars' => array(
                    'user' => '<a href="' . $users[0]['userUrl'] . '">' . $users[0]['userName'] . '</a>'
                )
            ),
            'users' => $users,
            'userIds' => array($userId),
            'avatar' => $avatar,
            'contentImage' => $previewImage,
            'url' => $userUrl,
            "coverId" => $cover->id
        );

        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);
    }

    public function onCoverRemove( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        $event = new OW_Event('notifications.remove', array(
            'entityType' => self::TYPE_LIKE,
            'entityId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        $event = new OW_Event('notifications.remove', array(
            'entityType' => self::TYPE_COMMENT,
            'entityId' => $userId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onCollectActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => $this->plugin->getKey(),
            'action' => self::ACTION_COMMENT,
            'sectionIcon' => 'ow_ic_picture',
            'sectionLabel' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_section_label'),
            'description' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_setting_comment'),
            'selected' => true
        ));

        if ( UHEADER_CLASS_NewsfeedBridge::getInstance()->isActive() )
        {
            $e->add(array(
                'section' => $this->plugin->getKey(),
                'action' => self::ACTION_LIKE,
                'sectionIcon' => 'ow_ic_picture',
                'sectionLabel' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_section_label'),
                'description' => OW::getLanguage()->text($this->plugin->getKey(), 'notifications_setting_like'),
                'selected' => true
            ));
        }
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('base_add_comment', array($this, 'onComment'));
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'onLike'));
        OW::getEventManager()->bind('notifications.collect_actions', array($this, 'onCollectActions'));
        OW::getEventManager()->bind('notifications.on_dublicate', array($this, 'onDublicate'));
    }
    
    public function init()
    {
        $this->genericInit();
        
        OW::getEventManager()->bind('notifications.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
    }
}