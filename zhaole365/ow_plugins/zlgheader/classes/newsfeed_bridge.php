<?php

class ZLGHEADER_CLASS_NewsfeedBridge
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

    protected $isPluginActive = false;

    protected $plugin;

    protected function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('newsfeed');
        $this->plugin = OW::getPluginManager()->getPlugin('zlgheader');
    }

    public function isActive()
    {
        return $this->isPluginActive;
    }

    private function triggerAction( OW_Event $event, $add )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $coverId = $params['id'];
        
        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($coverId);
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        
        if ( empty($group) )
        {
            return;
        }
        
        $userId = $group->userId;

        $eventParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'entityType' => ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            'entityId' => $coverId,
            'userId' => $userId,
            'feedType' => 'zlgroups',
            'feedId' => $group->id,
            'visibility' => $this->getVisibility($group),
            'postOnUserFeed' => $this->getPostOnUserFeed($group)
        );

        $url = ZLGROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');
        $assignVars = array();
        $assignVars['group'] = '<a href="' . $url . '">' . $title . '</a>';
        
        $string = $add
            ? array( "key" => 'zlgheader+newsfeed_add_cover', "vars" => $assignVars)
            : array( "key" => 'zlgheader+newsfeed_change_cover', "vars" => $assignVars);

        $eventData = array(
            'string' => $string,
            'cover' => $params,
            'groupId' => $group->id,
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

        $groupId = $params['groupId'];
        $coverId = $params['id'];

        if ( $params['status'] != 'active' )
        {
            return;
        }

        $event = new OW_Event('feed.delete_item', array(
            'entityType' => ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            'entityId' => $coverId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( $params['action']['entityType'] != ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }
        
        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($params['action']['entityId']);
        $userId = OW::getUser()->getId();
        
        if ( !isset($data['content']) || !is_array($data['content']) )
        {
            $cmp = new ZLGHEADER_CMP_CoverItem($cover->groupId);
            $data['content'] = $cmp->render();
        }
        
        //Context
        
        if ( $params['feedType'] == 'groups' )
        {
            $data['context'] = null;
        }
        else
        {
            $service = ZLGROUPS_BOL_Service::getInstance();
            $group = $service->findGroupById($cover->groupId);
            
            if ( $group != null )
            {
                $url = $service->getGroupUrl($group);
                $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

                $data['context'] = array(
                    'label' => $title,
                    'url' => $url
                );
            }
        }

        $event->setData($data);
    }

    public function onEntityAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);
        
        $data['content'] = array(
            "format" => "zlgroup_cover",
            "vars" => array(
                "groupId" => $cover->groupId
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
            'label' => $language->text('zlgheader', 'feed_content_label'),
            'activity' => '*:' . ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE
        ));
    }

    public function onCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        /**
         * Uncomment to enable privacy in the plugin. It will use group plugin privacy action
         *  
        $event->add(array('create:' . ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE, ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
         */
    }

    public function onComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];
        $coverId = $params['entityId'];

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($coverId);

        if ( $cover === null )
        {
            return;
        }
        
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        
        $string = null;

        $url = ZLGROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');
        $groupEmbed = '<a href="' . $url . '">' . $title . '</a>';
        
        if ( $group->userId == $userId )
        {
            $string = array( 
                "key" => 'zlgheader+activity_string_cover_comment_self', 
                "vars" => array(
                    'group' => $groupEmbed
                )
            );
        }
        else
        {
            $string = array( 
                "key" => 'zlgheader+activity_string_cover_comment', 
                "vars" => array(
                    'group' => $groupEmbed
                )
            );
        }
        
        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $userId,
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $userId,
            'pluginKey' => $this->plugin->getKey(),
            'feedType' => 'zlgroups',
            'feedId' => $group->id,
            'visibility' => $this->getVisibility($group),
            'postOnUserFeed' => $this->getPostOnUserFeed($group)
        ), array(
            'string' => $string
        ) ));
    }

    public function onLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }

        $userId = $params['userId'];
        $coverId = $params['entityId'];

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($coverId);
        
        if ( $cover === null )
        {
            return;
        }
        
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);

        $string = null;
        
        $url = ZLGROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');
        $groupEmbed = '<a href="' . $url . '">' . $title . '</a>';

        if ( $group->userId == $userId )
        {
            $string = OW::getLanguage()->text('zlgheader', 'activity_string_cover_like_self', array(
                'group' => $groupEmbed
            ));
        }
        else
        {
            $string = OW::getLanguage()->text('zlgheader', 'activity_string_cover_like', array(
                'group' => $groupEmbed
            ));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $userId,
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $userId,
            'pluginKey' => $this->plugin->getKey(),
            'feedType' => 'zlgroups',
            'feedId' => $group->id,
            'visibility' => $this->getVisibility($group),
            'postOnUserFeed' => $this->getPostOnUserFeed($group)
        ), array(
            'string' => $string
        ) ));
    }
    
    public function onGroupEdit( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = (int) $params['groupId'];

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId);
        
        if ( $cover === null )
        {
            return;
        }
        
        $eventParams = array(
            'pluginKey' => $this->plugin->getKey(),
            'entityType' => ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            'entityId' => $cover->id
        );

        $event = new OW_Event('feed.action',$eventParams);
        OW::getEventManager()->trigger($event);
    }

    public function onActionUpdate( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( $params['entityType'] != ZLGHEADER_CLASS_CommentsBridge::ENTITY_TYPE )
        {
            return;
        }
        
        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverById($params['entityId']);
        
        if ( $cover === null )
        {
            return;
        }
        
        $data['params'] = empty($data['params']) ? array() : $data['params'];
        
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($cover->groupId);
        
        $data['params']['visibility'] = $this->getVisibility($group);
        $data['params']['postOnUserFeed'] = $this->getPostOnUserFeed($group);
         
        $event->setData($data);
    }
    
    private function getVisibility( ZLGROUPS_BOL_Group $group )
    {
        /*
         * Uncoment if the cover change event should be displayed on the index feed
         * 
         * $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        $visibility = $private
            ? 4 + 8 // Visible for autor (4) and current feed (8)
            : 15; // Visible for all (15)*/
        
        return 2 + 4 + 8; // Visible for follows(2), autor (4) and current feed (8)
    }
    
    private function getPostOnUserFeed( ZLGROUPS_BOL_Group $group )
    {
        /*
         * Uncoment if the cover change event should be displayed on the index feed
         * 
         return $group->whoCanView != GROUPS_BOL_Service::WCV_INVITE
         * 
         */
        
        return false;
    }
    
    public function onPluginsInit( OW_Event $event )
    {

    }

    public function onCollectFormats( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "name" => "zlgroup_cover",
            "class" => "ZLGHEADER_CLASS_CoverFormat"
        ));
    }
    
    public function genericInit()
    {
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'onLike'));
        OW::getEventManager()->bind('feed.on_entity_add', array($this, 'onEntityAdd'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'onCollectConfigurableActivity'));
        OW::getEventManager()->bind('feed.collect_privacy', array($this, 'onCollectPrivacy'));
        OW::getEventManager()->bind('base_add_comment', array($this, 'onComment'));
        
        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverChange'));
        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
        
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onItemRender'));
    }
    
    public function init()
    {
        
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'onPluginsInit'));
        OW::getEventManager()->bind('feed.collect_formats', array($this, 'onCollectFormats'));
        
        /**
         * Uncoment if the cover change event should be displayed on the index feed
         *
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_EDIT, array($this, 'onGroupEdit'));
        OW::getEventManager()->bind('feed.on_entity_update', array($this, 'onActionUpdate'));
         * 
         */
        
    }
}