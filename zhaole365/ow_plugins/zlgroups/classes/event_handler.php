<?php

class ZLGROUPS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var ZLGROUPS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ZLGROUPS_CLASS_EventHandler
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
     *
     * @var ZLGROUPS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = ZLGROUPS_BOL_Service::getInstance();
    }
    
    // BASE_CMP_AddNewContent widget 构建时 fire 事件BASE_CMP_AddNewContent::EVENT_NAME，相关 event handler
    public function onAddNewContent( BASE_CLASS_EventCollector $event )
    {
        $uniqId = uniqid("zlgroups-create-");
        
        if (!ZLGROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            
            if ( $status['status'] != BOL_AuthorizationService::STATUS_PROMOTED )
            {
                return;
            }
            
            $script = UTIL_JsGenerator::composeJsString('$("#" + {$id}).click(function(){
                OW.authorizationLimitedFloatbox({$msg});
            });', array(
                "id" => $uniqId,
                "msg" => $status["msg"]
            ));
            OW::getDocument()->addOnloadScript($script);
        }
        
        $event->add(array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
            BASE_CMP_AddNewContent::DATA_KEY_ID => $uniqId,
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('zlgroups-create'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('zlgroups', 'add_new_label')
        ));
    }
    
    // 乐群删除前事件处理
    public function onBeforeGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = $params['groupId'];

        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        // 删除相关文件 （补充：隶属乐群的乐子）
        $fileName = ZLGROUPS_BOL_Service::getInstance()->getGroupImagePath($group);

        if ( $fileName !== null )
        {
            OW::getStorage()->removeFile($fileName);
        }
    }
    
    // 乐群删除后事件处理
    public function onAfterGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();

        $groupId = $params['groupId'];

        // 删除该乐群相关组件定制布局
        BOL_ComponentEntityService::getInstance()->onEntityDelete(ZLGROUPS_BOL_Service::WIDGET_PANEL_NAME, $groupId);
        
        // 删除该乐群相关评论
        BOL_CommentService::getInstance()->deleteEntityComments(ZLGROUPS_BOL_Service::ENTITY_TYPE_WAL, $groupId);
        
        // 删除该乐群相关标签
        // TBD

        // 删除该乐群相关flag，TBU－不清除咋用的
        BOL_FlagService::getInstance()->deleteByTypeAndEntityId(ZLGROUPS_BOL_Service::ENTITY_TYPE_GROUP, $groupId);

        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId
        )));
    }
    
    // 相应用户注销事件的eventhandler
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        ZLGROUPS_BOL_Service::getInstance()->onUserUnregister( $userId, !empty($params['deleteContent']) );
    }
    
    public function onForumCheckPermissions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] == 'zlgroups' )
        {
            $groupService = ZLGROUPS_BOL_Service::getInstance();

            if ( $params['action'] == 'edit_topic' )
            {
                $group = $groupService->findGroupById($params['entityId']);

                if ( $group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized($params['entity']) )
                {
                    $event->setData(true);
                }
            }
            else if ( $params['action'] == 'add_topic' )
            {
                if ( OW::getUser()->isAuthorized($params['entity'], 'add_topic') && $groupService->findUser($params['entityId'], OW::getUser()->getId()) )
                {
                    $event->setData(true);
                }
                else
                {

                    if ($groupService->findUser($params['entityId'], OW::getUser()->getId()))
                    {
                        $status = BOL_AuthorizationService::getInstance()->getActionStatus($params['entity'], 'add_topic');
                        if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                        {
                            $event->setData(true);
                            return;
                        }
                    }

                    $event->setData(false);
                }
            }
            else if ( $groupService->findUser($params['entityId'], OW::getUser()->getId()) )
            {
                $event->setData(true);
            }
            else
            {
                $event->setData(false);
            }
        }
    }
    
    public function onForumFindCaption( OW_Event $event )
    {

        $params = $event->getParams();
        if ( !isset($params['entity']) || !isset($params['entityId']) )
        {
            return;
        }

        if ( $params['entity'] == 'zlgroups' )
        {
            $component = new ZLGROUPS_CMP_BriefInfo($params['entityId']);
            $eventData['component'] = $component;
            $eventData['key'] = 'main_menu_list';
            $event->setData($eventData);
        }
    }
    
    public function onCollectAdminNotifications( BASE_CLASS_EventCollector $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('zlgroups', 'is_forum_connected');

        if ( $is_forum_connected && !OW::getPluginManager()->isPluginActive('forum') )
        {
            $language = OW::getLanguage();

            $event->add($language->text('zlgroups', 'error_forum_disconnected', array('url' => OW::getRouter()->urlForRoute('admin_plugins_installed'))));
        }
    }
    
    public function onForumUninstall( OW_Event $event )
    {
        $config = OW::getConfig();

        if ( $config->getValue('zlgroups', 'is_forum_connected') )
        {
            $event = new OW_Event('forum.delete_section', array('entity' => 'zlgroups'));
            OW::getEventManager()->trigger($event);

            $event = new OW_Event('forum.delete_widget');
            OW::getEventManager()->trigger($event);

            $config->saveConfig('zlgroups', 'is_forum_connected', 0);

            $actionId = BOL_AuthorizationActionDao::getInstance()->getIdByName('add_topic');

            BOL_AuthorizationService::getInstance()->deleteAction($actionId);
        }
    }
    
    public function onForumActivate( OW_Event $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('zlgroups', 'is_forum_connected');

        // Add latest topic widget if forum plugin is connected
        if ( $is_forum_connected )
        {
            $event->setData(array('forum_connected' => true, 'place' => 'zlgroup', 'section' => BOL_ComponentAdminService::SECTION_RIGHT));
        }
    }
    
    // 乐群创建完成事件处理
    public function onAfterGroupCreate( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = (int) $params['groupId'];

        // 动态信息显示
        $event = new OW_Event('feed.action', array(
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'zlgroups'
        ));

        OW::getEventManager()->trigger($event);
    }
    
    public function onFeedEntityAction( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE )
        {
            return;
        }

        $groupId = (int) $params['entityId'];
        $groupService = ZLGROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);

        if ( $group === null )
        {
            return;
        }

        $private = $group->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE;
        $visibility = $private
                ? 4 + 8 // Visible for autor (4) and current feed (8)
                : 15; // Visible for all (15)

        $content = array(
            "format" => "image_content",
            "vars" => array(
                "image" => $groupService->getGroupImageUrl($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG),
                "thumbnail" => $groupService->getGroupImageUrl($group),
                "title" => UTIL_String::truncate(strip_tags($group->title), 100, '...'),
                "description" => UTIL_String::truncate(strip_tags($group->description), 150, '...'),
                "url" => array( "routeName" => "zlgroups-view", "vars" => array('groupId' => $group->id)),
                "iconClass" => "ow_ic_group"
            )
        );

        $data = array(
            'params' => array(
                'feedType' => 'zlgroups',
                'feedId' => $groupId,
                'visibility' => $visibility,
                'postOnUserFeed' => !$private
            ),
            'ownerId' => $group->userId,
            'time' => (int) $group->timeStamp,
            'string' => array("key" => "zlgroups+feed_create_string"),
            'content' => $content,
            'view' => array(
                'iconClass' => 'ow_ic_files'
            )
        );

        $e->setData($data);
    }
    
    // 乐群编辑后事件处理
    public function onAfterGroupEdit( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $groupId = (int) $params['groupId'];

        $groupService = ZLGROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);
        
        // 获得乐群性质（公开还是私有）
        $private = $group->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE;

        // 更新论坛信息
        $event = new OW_Event('forum.edit_group', array('entity' => 'zlgroups', 'entityId'=>$groupId, 'name'=>$group->title, 'description'=>$group->description));
        OW::getEventManager()->trigger($event);

        // 更新动态信息
        $event = new OW_Event('feed.action', array(
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'zlgroups'
        ));

        OW::getEventManager()->trigger($event);

        // 如果是私有群，则删除非乐群成员的粉丝
        if ( $private )
        {
            $users = $groupService->findGroupUserIdList($groupId);
            $follows = OW::getEventManager()->call('feed.get_all_follows', array(
                'feedType' => 'zlgroups',
                'feedId' => $groupId
            ));

            foreach ( $follows as $follow )
            {
                if ( in_array($follow['userId'], $users) )
                {
                    continue;
                }

                OW::getEventManager()->call('feed.remove_follow', array(
                    'feedType' => 'zlgroups',
                    'feedId' => $groupId,
                    'userId' => $follow['userId']
                ));
            }
        }
    }
    
    // 乐群加入用户后事件处理，发事件以更新动态信息项
    public function onGroupUserJoin( OW_Event $e )
    {
        $params = $e->getParams();

        $groupId = (int) $params['groupId'];
        $userId = (int) $params['userId'];
        $groupUserId = (int) $params['groupUserId'];

        $groupService = ZLGROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);

        // 如果是owner就不处理
        if ( $group->userId == $userId )
        {
            return;
        }

        // 发事件以更新动态信息项
        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'zlgroups-join',
            'activityId' => $userId,
            'entityId' => $group->id,
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'userId' => $userId,
            'pluginKey' => 'zlgroups',
            'feedType' => 'zlgroups',
            'feedId' => $group->id
        ), array(
            'groupId' => $group->id,
            'userId' => $userId,
            'groupUserId' => $groupUserId,

            'string' => array("key" => 'zlgroups+user_join_activity_string'),
            'features' => array()
        )));

        $url = $groupService->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data = array(
            'time' => time(),
            'string' => array(
                "key" => 'zlgroups+feed_join_string',
                "vars" => array(
                    'groupTitle' => $title,
                    'groupUrl' => $url
                )
            ),
            'view' => array(
                'iconClass' => 'ow_ic_add'
            ),
            'data' => array(
                'joinUsersId' => $userId
            )
        );

        $event = new OW_Event('feed.action', array(
            'feedType' => 'zlgroups',
            'feedId' => $group->id,
            'entityType' => 'zlgroups-join',
            'entityId' => $groupUserId,
            'pluginKey' => 'zlgroups',
            'userId' => $userId,
            'visibility' => 8,
            'postOnUserFeed' => false
        ), $data);

        OW::getEventManager()->trigger($event);
    }

    // 乐群加入用户后事件处理，将用户加为follower
    public function onGroupUserJoinFeedAddFollow( OW_Event $event )
    {
    	$params = $event->getParams();
    
    	$groupId = $params['groupId'];
    	$userId = $params['userId'];
    
    	OW::getEventManager()->call('feed.add_follow', array(
    	'feedType' => 'zlgroups',
    	'feedId' => $groupId,
    	'userId' => $userId
    	));
    }    
    
    public function onFeedCollectWidgets( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'place' => 'zlgroup',
            'section' => BOL_ComponentService::SECTION_RIGHT,
            'order' => 0
        ));
    }
    
    public function onForumCollectWidgetPlaces( BASE_CLASS_EventCollector $e )
    {
        if ( OW::getConfig()->getValue('zlgroups', 'is_forum_connected') )
        {
            $e->add(array(
                'place' => 'zlgroup',
                'section' => BOL_ComponentService::SECTION_RIGHT,
                'order' => 0
            ));
        }
    }
    
    public function onFeedWidgetConstruct( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['feedType'] != 'zlgroups' )
        {
            return;
        }

        $data = $e->getData();

        if ( !OW::getUser()->isAuthorized('zlgroups', 'add_comment') )
        {
            $data['statusForm'] = false;
            $actionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'add_comment');
            
            if ( $actionStatus["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $data["statusMessage"] = $actionStatus["msg"];
            }
            
            $e->setData($data);

            return;
        }

        $groupId = (int) $params['feedId'];
        $userId = OW::getUser()->getId();

        $userDto = ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);

        $data['statusForm'] = $userDto !== null;

        $e->setData($data);
    }
    
    // 乐群介绍组件（ZLGROUPS_CMP_BriefInfoContent）构造工具栏的 event handler
    // 更新事件数据以更新toolbar信息（添加了“关注”或“取消关注”）
    public function onGroupToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $e->getParams();
        $backUri = OW::getRequest()->getRequestUri();

        if ( OW::getEventManager()->call('feed.is_inited') )
        {
            $url = OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'follow');

            $eventParams = array(
                'userId' => OW::getUser()->getId(),
                'feedType' => ZLGROUPS_BOL_Service::ENTITY_TYPE_GROUP,
                'feedId' => $params['groupId']
            );

            if ( !OW::getEventManager()->call('feed.is_follow', $eventParams) )
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('zlgroups', 'feed_group_follow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'follow'))
                ));
            }
            else
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('zlgroups', 'feed_group_unfollow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'unfollow'))
                ));
            }
        }
    }
    
    public function onAdsCollectEnabledPlugins( BASE_CLASS_EventCollector $event )
    {
        $event->add('zlgroups');
    }
    
    public function findAllGroupsUsers( OW_Event $e )
    {
        $out = ZLGROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        $e->setData($out);

        return $out;
    }
    
    public function onFeedCollectFollow( BASE_CLASS_EventCollector $e )
    {
        $groupUsers = ZLGROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        foreach ( $groupUsers as $groupId => $users )
        {
            foreach ( $users as $userId )
            {
                $e->add(array(
                    'feedType' => 'zlgroups',
                    'feedId' => $groupId,
                    'userId' => $userId
                ));
            }
        }
    }

    
    public function onFeedStatusAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'zlgroups-status' )
        {
            return;
        }

        $service = ZLGROUPS_BOL_Service::getInstance();
        $group = $service->findGroupById($params['feedId']);
        $url = $service->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data['context'] = array(
            'label' => $title,
            'url' => $url
        );

        $event->setData($data);
    }
    
    public function onFeedItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $actionUserId = $userId = (int) $data['action']['userId'];
        if ( OW::getUser()->isAuthenticated() && in_array($params['feedType'], array('zlgroups')) )
        {
            $groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($params['feedId']);
            $isGroupOwner = $groupDto->userId == OW::getUser()->getId();
            $isGroupModerator = OW::getUser()->isAuthorized('zlgroups');

            if ( $actionUserId != OW::getUser()->getId() && ($isGroupOwner || $isGroupModerator) )
            {
                $groupUserDto = ZLGROUPS_BOL_Service::getInstance()->findUser($groupDto->id, $actionUserId);
                if ( $groupUserDto !== null )
                {
                    $data['contextMenu'] = empty($data['contextMenu']) ? array() : $data['contextMenu'];


                    if ( $groupDto->userId == $userId )
                    {
                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('zlgroups', 'delete_group_user_label'),
                            'url' => 'javascript://',
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('zlgroups', 'group_owner_delete_error'),
                                'onclick' => 'OW.error($(this).data().message); return false;'
                            )
                        ));
                    }
                    else
                    {
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'deleteUser', array(
                            'groupId' => $groupDto->id,
                            'userId' => $userId
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));

                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('zlgroups', 'delete_group_user_label'),
                            'url' => $deleteUrl,
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('zlgroups', 'delete_group_user_confirmation'),
                                'onclick' => 'return confirm($(this).data().message);'
                            )
                        ));
                    }
                }
            }

            $canRemove = $isGroupOwner || $params['action']['userId'] == OW::getUser()->getId() || $isGroupModerator;

            if ( $canRemove )
            {
                array_unshift($data['contextMenu'], array(
                    'label' => OW::getLanguage()->text('zlgroups', 'delete_feed_item_label'),
                    'class' => 'newsfeed_remove_btn',
                    'attributes' => array(
                        'rel' => OW::getLanguage()->text('zlgroups', 'delete_feed_item_confirmation')
                    )
                ));
            }
        }

        $event->setData($data);
    }
    
    public function onFeedItemRenderContext( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $groupActions = array(
            'zlgroups-status'
        );

        if ( in_array($params['action']['entityType'], $groupActions) && $params['feedType'] == 'zlgroups' )
        {
            $data['context'] = null;
        }

        if ( $params['action']['entityType'] == 'forum-topic' && isset($data['contextFeedType'])
                && $data['contextFeedType'] == 'zlgroups' && $data['contextFeedType'] != $params['feedType'] )
        {
            $service = ZLGROUPS_BOL_Service::getInstance();
            $group = $service->findGroupById($data['contextFeedId']);
            $url = $service->getGroupUrl($group);
            $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

            $data['context'] = array(
                'label' => $title,
                'url' => $url
            );
        }

        $event->setData($data);
    }
    
    /*public function onFeedItemRenderContext( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( empty($data['contextFeedType']) )
        {
            return;
        }
        
        if ( $data['contextFeedType'] != "zlgroups" )
        {
            return;
        }
        
        if ( $params['feedType'] == "zlgroups" )
        {
            $data["context"] = null;
            $event->setData($data);
            
            return;
        }
        
        $service = ZLGROUPS_BOL_Service::getInstance();
        $group = $service->findGroupById($data['contextFeedId']);
        $url = $service->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data['context'] = array(
            'label' => $title,
            'url' => $url
        );

        $event->setData($data);
    }*/
    
    public function onFeedItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE || $params['feedType'] == 'zlgroups')
        {
            return;
        }

        $groupId = $params['action']['entityId'];
        $usersCount = ZLGROUPS_BOL_Service::getInstance()->findUserListCount($groupId);

        if ( $usersCount == 1 )
        {
            return;
        }

        $users = ZLGROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId, ZLGROUPS_BOL_Service::PRIVACY_EVERYBODY);
        $activityUserIds = array();

        foreach ( $params['activity'] as $activity )
        {
            if ( $activity['activityType'] == 'zlgroups-join')
            {
                $activityUserIds[] = $activity['data']['userId'];
            }
        }

        $lastUserId = reset($activityUserIds);
        $follows = array_intersect($activityUserIds, $users);
        $notFollows = array_diff($users, $activityUserIds);
        $idlist = array_merge($follows, $notFollows);

        $viewMoreUrl = null;
        
        if ( count($idlist) > 5 )
        {
            $viewMoreUrl = array("routeName" => "zlgroups-user-list", "vars" => array(
                "groupId" => $groupId
            ));
        }
        
        if ( is_array($data["content"])  )
        {
            $data["content"]["vars"]["userList"] = array(
                "label" => array(
                    "key" => "zlgroups+feed_activity_users",
                    "vars" => array(
                        "usersCount" => $usersCount
                    )
                ),
                "viewAllUrl" => $viewMoreUrl,
                "ids" => array_slice($idlist, 0, 5)
            );
        }
        else // Backward compatibility
        {
            $avatarList = new BASE_CMP_MiniAvatarUserList( array_slice($idlist, 0, 5) );
            $avatarList->setEmptyListNoRender(true);

            if ( count($idlist) > 5 )
            {
                $avatarList->setViewMoreUrl(OW::getRouter()->urlForRoute($viewMoreUrl["routeName"], $viewMoreUrl["vars"]));
            }

            $language = OW::getLanguage();
            $content = $avatarList->render();

            if ( $lastUserId )
            {
                $userName = BOL_UserService::getInstance()->getDisplayName($lastUserId);
                $userUrl = BOL_UserService::getInstance()->getUserUrl($lastUserId);
                $content .= $language->text('zlgroups', 'feed_activity_joined', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
            }

            $data['assign']['activity'] = array('template' => 'activity', 'vars' => array(
                'title' => $language->text('zlgroups', 'feed_activity_users', array('usersCount' => $usersCount)),
                'content' => $content
            ));
        }

        $event->setData($data);
    }
    
    // 设置访问权限 event handler
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'zlgroups' => array(  // 可能是错的。查看其他相应代码后感觉没错，可以不要key
                    'label' => $language->text('zlgroups', 'auth_group_label'),
                    'actions' => array(
                        'add_topic' => $language->text('zlgroups', 'auth_action_label_add_topic'),
                        'create' => $language->text('zlgroups', 'auth_action_label_create'),
                        'view' => $language->text('zlgroups', 'auth_action_label_view'),
                        'add_comment' => $language->text('zlgroups', 'auth_action_label_wall_post'),
                        'delete_comment_by_content_owner' => $language->text('zlgroups', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }
    
    public function onFeedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('zlgroups', 'feed_content_label'),
            'activity' => '*:' . ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE
        ));
    }

    
    public function onFeedCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('zlgroups-join:*', ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:zlgroups-join', ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:' . ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE, ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
    }
    
    public function onPrivacyChange( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]) )
        {
            return;
        }

        ZLGROUPS_BOL_Service::getInstance()->setGroupUserPrivacy($userId, $actionList[ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
        //ZLGROUPS_BOL_Service::getInstance()->setGroupsPrivacy($userId, $actionList[ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
    }

    public function onPrivacyCollectActions( BASE_CLASS_EventCollector $event )
    {
    	$language = OW::getLanguage();
    
    	$action = array(
    			'key' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS,
    			'pluginKey' => 'zlgroups',
    			'label' => $language->text('zlgroups', 'privacy_action_view_my_groups'),
    			'description' => '',
    			'defaultValue' => ZLGROUPS_BOL_Service::PRIVACY_EVERYBODY,
    			'sortOrder' => 1000
    	);
    
    	$event->add($action);
    }    
    
    // 用户加入前事件处理
    public function onBeforeUserJoin( OW_Event $event )
    {
        $data = $event->getData();
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $privacy = ZLGROUPS_BOL_Service::PRIVACY_EVERYBODY;

        $t = OW::getEventManager()->call('plugin.privacy.get_privacy', array(
            'ownerId' => $params['userId'],
            'action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS
        ));

        $data['privacy'] = empty($t) ? $privacy : $t;

        $event->setData($data);
    }
    
    public function onForumCanView( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] != 'zlgroups' )
        {
            return;
        }


        $groupId = $params['entityId'];
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        if ( empty($group) )
        {
            return;
        }

        $privateUrl = OW::getRouter()->urlForRoute('zlgroups-private-group', array(
            'groupId' => $group->id
        ));

        $canView = ZLGROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group->userId);

        if ( $group->whoCanView != ZLGROUPS_BOL_Service::WCV_INVITE )
        {
            $event->setData($canView);

            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException($privateUrl);
        }

        $isUser = ZLGROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId()) !== null;

        if ( !$isUser && !OW::getUser()->isAuthorized('zlgroups') )
        {
            throw new RedirectException($privateUrl);
        }
    }
    
    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $service = ZLGROUPS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();

        $zlgroupsCount = $service->findMyGroupsCount($userId);
        $invitesCount = $service->findUserInvitedGroupsCount($userId, true);

        if ( $zlgroupsCount > 0 || $invitesCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('zlgroups', 'my_groups'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('zlgroups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $zlgroupsCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('zlgroups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT => $invitesCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT_URL => OW::getRouter()->urlForRoute('zlgroups-invite-list')
            ));
        }
    }
    
    public function onAfterFeedCommentAdd( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE )
        {
            return;
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'userId' => $params['userId'],
            'pluginKey' => 'zlgroups'
        ), array(
            'string' => array(
                "key" => "zlgroups+comment_activity_string"
            ),
            'features' => array('comments')
        )));
    }
    
    // 清除乐群列表缓存
    public function cleanCache( OW_Event $event )
    {
        ZLGROUPS_BOL_Service::getInstance()->clearListingCache();
    }
    
    public function sosialSharingGetGroupInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'zlgroups' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(0, 'zlgroups', 'view') )
            {
                $event->setData($data);
                return;
            }

            $groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($params['entityId']);
            
            if ( !empty($groupDto) )
            {
                $data['display'] = $groupDto->whoCanView !==  ZLGROUPS_BOL_Service::WCV_INVITE;
            }
        }

        $event->setData($data);
    }
    
    // 用户退出乐群后事件处理
    public function afterUserLeave( OW_Event $event )
    {
        $params = $event->getParams();
        
        $eventParams = array(
            'userId' => $params["userId"],
            'feedType' => ZLGROUPS_BOL_Service::ENTITY_TYPE_GROUP,
            'feedId' => $params["groupId"]
        );
        
        // 将用户作为乐群的follower信息删除
        OW::getEventManager()->call('feed.remove_follow', $eventParams);
        
        // 删除feed中信息
        OW::getEventManager()->call("feed.delete_item", array(
            'entityType' => 'zlgroups-join',
            'entityId' => $params["groupUserId"]
        ));
        
        OW::getEventManager()->call("feed.delete_activity", array(
            'activityType' => 'zlgroups-join',
            'activityId' => $params["userId"],
            'entityId' => $params["groupId"],
            'entityType' => ZLGROUPS_BOL_Service::FEED_ENTITY_TYPE
        ));
    }

    
    public function genericInit()
    {
        $eventHandler = $this;
        
        // 建立admin.add_admin_notification事件和相应handler的关联
        OW::getEventManager()->bind('admin.add_admin_notification', array($eventHandler, "onCollectAdminNotifications"));
        
        // 乐群创建event handler
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "onAfterGroupCreate"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "cleanCache"));
        
        // 乐群删除event handler
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_ON_DELETE, array($eventHandler, "onBeforeGroupDelete"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "onAfterGroupDelete"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "cleanCache"));
        
        // 乐群编辑event handler
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "onAfterGroupEdit"));
        
        // 乐群用户加入 event handler
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_BEFORE_ADDED, array($eventHandler, "onBeforeUserJoin"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoin"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoinFeedAddFollow"));
        
        // 乐群用户退出 event handler
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_DELETED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(ZLGROUPS_BOL_Service::EVENT_USER_DELETED, array($eventHandler, "afterUserLeave"));
        
        // 乐群介绍组件（ZLGROUPS_CMP_BriefInfoContent）构造工具栏的 event handler
        OW::getEventManager()->bind('zlgroups.on_toolbar_collect', array($eventHandler, "onGroupToolbarCollect"));
        
        // 未发现何处触发此事件
        OW::getEventManager()->bind('zlgroups.get_all_group_users', array($eventHandler, "findAllGroupsUsers"));
        
        // newsfeed相关event handler
        OW::getEventManager()->bind('feed.on_entity_action', array($eventHandler, "onFeedEntityAction"));
        OW::getEventManager()->bind('feed.collect_follow', array($eventHandler, "onFeedCollectFollow"));
        OW::getEventManager()->bind('feed.collect_privacy', array($eventHandler, "onFeedCollectPrivacy"));
        OW::getEventManager()->bind('feed.on_entity_add', array($eventHandler, "onFeedStatusAdd"));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($eventHandler, "onFeedCollectConfigurableActivity"));
        OW::getEventManager()->bind('feed.after_comment_add', array($eventHandler, "onAfterFeedCommentAdd"));
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderActivity"));
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderContext"));
        OW::getEventManager()->bind('feed.collect_widgets', array($eventHandler, "onFeedCollectWidgets"));
        OW::getEventManager()->bind('feed.on_widget_construct', array($eventHandler, "onFeedWidgetConstruct"));
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRender"));        
        
        // plugin.privacy相关event handler
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($eventHandler, "onPrivacyCollectActions"));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($eventHandler, "onPrivacyChange"));
        
        // forum相关event handler
        OW::getEventManager()->bind('forum.check_permissions', array($eventHandler, "onForumCheckPermissions"));
        OW::getEventManager()->bind('forum.can_view', array($eventHandler, 'onForumCanView'));
        OW::getEventManager()->bind('forum.activate_plugin', array($eventHandler, "onForumActivate"));
        OW::getEventManager()->bind('forum.find_forum_caption', array($eventHandler, "onForumFindCaption"));
        OW::getEventManager()->bind('forum.uninstall_plugin', array($eventHandler, "onForumUninstall"));
        OW::getEventManager()->bind('forum.collect_widget_places', array($eventHandler, "onForumCollectWidgetPlaces"));
        
        // 用户销户 event handler
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($eventHandler, "onUserUnregister"));
        
        // 广告相关 event handler
        OW::getEventManager()->bind('ads.enabled_plugins', array($eventHandler, "onAdsCollectEnabledPlugins"));
        
        // 设置访问权限 event handler
        OW::getEventManager()->bind('admin.add_auth_labels', array($eventHandler, "onCollectAuthLabels"));
        
        // 建立base.add_quick_link事件和相应handler的关联
        OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($eventHandler, 'onCollectQuickLinks'));
        
        // 信用相关 event handler
        $credits = new ZLGROUPS_CLASS_Credits();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        OW::getEventManager()->bind('usercredits.get_action_key', array($credits, 'getActionKey'));
        
        // BASE_CMP_AddNewContent widget 构建时 fire 事件BASE_CMP_AddNewContent::EVENT_NAME，相关 event handler
        // 未发现BASE_CMP_AddNewContent widget 的构建？？？
        OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'onAddNewContent'));
        
    }
}