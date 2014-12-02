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
class UHEADER_CLASS_NewsfeedBridge
{

    /**
     * Class instance
     *
     * @var UHEADER_CLASS_NewsfeedBridge
     */
    protected static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_NewsfeedBridge
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

    protected function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('newsfeed');
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    public function isActive()
    {
        return $this->isPluginActive;
    }

    protected function triggerAction( OW_Event $event, $add )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $userId = $params['userId'];
        $coverId = $params['id'];

        $eventParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'entityType' => UHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            'entityId' => $coverId,
            'userId' => $userId
        );

        $stringKey = $add
            ? 'uheader+newsfeed_add_cover'
            : 'uheader+newsfeed_change_cover';

        $eventData = array(
            'string' => array(
                "key" => $stringKey
            ),
            'cover' => $params,
            'photoId' => empty($data['photoId']) ? null : $data['photoId'],
            'add' => $add
        );

        $event = new OW_Event('feed.action',$eventParams, $eventData);
        OW::getEventManager()->trigger($event);
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

        $userId = $params['userId'];
        $coverId = $params['id'];

        if ( $params['status'] != 'active' )
        {
            return;
        }

        $event = new OW_Event('feed.delete_item', array(
            'entityType' => UHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            'entityId' => $coverId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        if ( !isset($data['content']) || !is_array($data['content']) )
        {
            $cover = UHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);
        
        	if (!empty($cover))
        	{
            	$cmp = new UHEADER_CMP_CoverItem($cover->userId);
            	$data['content'] = $cmp->render();
            }
        }

        $event->setData($data);
    }

    public function onEntityAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $data['content'] = array(
            "format" => "profile_cover",
            "vars" => array(
                "coverId" => $params['entityId'],
                "userId" => $params["userId"]
            )
        );
        
        $data['view'] = array(
            'iconClass' => 'ow_ic_picture'
        );

        $event->setData($data);
    }

    public function onCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('uheader', 'feed_content_label'),
            'activity' => '*:' . UHEADER_CLASS_CommentsBridge::ENTITY_TYPE
        ));
    }

    public function onCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('create:' . UHEADER_CLASS_CommentsBridge::ENTITY_TYPE, UHEADER_CLASS_PrivacyBridge::PRIVACY_ACTION));
    }

    public function onComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];
        $coverId = $params['entityId'];

        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($coverId);

        $string = null;

        if ( $cover->userId == $userId )
        {
            $string = array(
                "key" => 'uheader+activity_string_cover_comment_self'
            );
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($cover->userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($cover->userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = array(
                "key" => 'uheader+activity_string_cover_comment', 
                "vars" => array(
                    'user' => $userEmbed
                )
            );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $userId,
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $userId,
            'pluginKey' => $this->plugin->getKey()
        ), array(
            'string' => $string
        ) ));
    }
    
    public function deleteComment( OW_Event $e )
    {
        $params = $e->getParams();
        $commentId = $params['commentId'];
        $userId = $params['userId'];

        $event = new OW_Event('feed.delete_activity', array(
            'entityType' => $params['entityType'],
            'entityId' => $params['entityId'],
            'activityType' => 'comment',
            'activityId' => $userId
        ));
        OW::getEventManager()->trigger($event);
    }

    public function onLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != UHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];
        $coverId = $params['entityId'];

        $cover = UHEADER_BOL_Service::getInstance()->findCoverById($coverId);

        $string = null;

        if ( $cover->userId == $userId )
        {
            $string = array(
                "key" => 'uheader+activity_string_cover_like_self'
            );
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($cover->userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($cover->userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = array(
                "key" => 'uheader+activity_string_cover_like', 
                "vars" => array(
                    'user' => $userEmbed
                )
            );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $userId,
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $userId,
            'pluginKey' => $this->plugin->getKey()
        ), array(
            'string' => $string
        ) ));
    }

    public function onPluginsInit( OW_Event $event )
    {

    }
    
    public function onCollectFormats( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "name" => "profile_cover",
            "class" => "UHEADER_CLASS_CoverFormat"
        ));
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'onLike'));
        OW::getEventManager()->bind('feed.on_entity_add', array($this, 'onEntityAdd'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'onCollectConfigurableActivity'));
        OW::getEventManager()->bind('feed.collect_privacy', array($this, 'onCollectPrivacy'));
        OW::getEventManager()->bind('base_add_comment', array($this, 'onComment'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverChange'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onItemRender'));
    }
    
    public function init()
    {
        $this->genericInit();
        
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'onPluginsInit'));
        OW::getEventManager()->bind('feed.collect_formats', array($this, 'onCollectFormats'));
        OW::getEventManager()->bind('base_delete_comment', array($this, 'deleteComment'));
    }
}