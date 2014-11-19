<?php

class ZLGROUPS_CTRL_Groups extends OW_ActionController
{

    private $service;

    public function __construct()
    {
        $this->service = ZLGROUPS_BOL_Service::getInstance();

        if ( !OW::getRequest()->isAjax() )
        {
            $mainMenuItem = OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::MAIN)->getElement('main_menu_list', 'zlgroups');
            if ( $mainMenuItem !== null )
            {
                $mainMenuItem->setActive(true);
            }
        }
    }

    // 乐群列表首页面action
    public function index()
    {
        $this->mostPopularList();
    }

    // 显示定制的乐群页面
    public function customize( $params )
    {
        $params['mode'] = 'customize';

        $this->view($params);
    }

    // 显示乐群页面
    public function view( $params )
    {
        $groupId = (int) $params['groupId'];

        if ( empty($groupId) )
        {
            throw new Redirect404Exception();
        }

        $groupDto = $this->service->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        OW::getDocument()->addMetaInfo('og:title', htmlspecialchars($groupDto->title), 'property');
        OW::getDocument()->addMetaInfo('og:description', htmlspecialchars($groupDto->description), 'property');
        OW::getDocument()->addMetaInfo('og:url', OW_URL_HOME . OW::getRequest()->getRequestUri(), 'property');
        OW::getDocument()->addMetaInfo('og:site_name', OW::getConfig()->getValue('base', 'site_name'), 'property');

        $language = OW::getLanguage();

        if ( !$this->service->isCurrentUserCanView($groupDto->userId) )
        {
            $this->assign('permissionMessage', $language->text('zlgroups', 'view_no_permission'));
            return;
        }

        $invite = $this->service->findInvite($groupDto->id, OW::getUser()->getId());

        if ( $invite !== null )
        {
            OW::getRegistry()->set('zlgroups.hide_console_invite_item', true);

            $this->service->markInviteAsViewed($groupDto->id, OW::getUser()->getId());
        }

        if ( $groupDto->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE && !OW::getUser()->isAuthorized('zlgroups') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                $this->redirect(OW::getRouter()->urlForRoute('zlgroups-private-group', array(
                    'groupId' => $groupDto->id
                )));
            }

            $user = $this->service->findUser($groupDto->id, OW::getUser()->getId());

            if ( $groupDto->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE && $invite === null && $user === null )
            {
                $this->redirect(OW::getRouter()->urlForRoute('zlgroups-private-group', array(
                    'groupId' => $groupDto->id
                )));
            }
        }

        OW::getDocument()->setTitle($language->text('zlgroups', 'view_page_title', array(
            'group_name' => strip_tags($groupDto->title)
        )));

        OW::getDocument()->setDescription($language->text('zlgroups', 'view_page_description', array(
            'description' => UTIL_String::truncate(strip_tags($groupDto->description), 200)
        )));

        $place = 'zlgroup';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('zlgroups-customize', array('mode' => 'customize', 'groupId' => $groupId)),
            'normal' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $groupId))
        );

        $componentAdminService = BOL_ComponentAdminService::getInstance();
        $componentEntityService = BOL_ComponentEntityService::getInstance();

        $userCustomizeAllowed = $componentAdminService->findPlace($place)->editableByUser;
        $ownerMode = $groupDto->userId == OW::getUser()->getId();
        $allowCustomize = $ownerMode || OW::getUser()->isAuthorized("zlgroups");

        $customize = !empty($params['mode']) && $params['mode'] == 'customize';

        if ( !( $userCustomizeAllowed && $allowCustomize ) && $customize )
        {
            $this->redirect($customizeUrls['normal']);
        }

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $schemeList = $componentAdminService->findSchemeList();
        $defaultScheme = $componentAdminService->findSchemeByPlace($place);
        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        if ( !$componentAdminService->isCacheExists($place) )
        {
            $state = array();
            $state['defaultComponents'] = $componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $componentAdminService->findAllSettingList();
            $state['defaultScheme'] = $defaultScheme;

            $componentAdminService->saveCache($place, $state);
        }

        $state = $componentAdminService->findCache($place);

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            if ( !$componentEntityService->isEntityCacheExists($place, $groupId) )
            {
                $entityCache = array();
                $entityCache['entityComponents'] = $componentEntityService->findPlaceComponentList($place, $groupId);
                $entityCache['entitySettings'] = $componentEntityService->findAllSettingList($groupId);
                $entityCache['entityPositions'] = $componentEntityService->findAllPositionList($place, $groupId);

                $componentEntityService->saveEntityCache($place, $groupId, $entityCache);
            }

            $entityCache = $componentEntityService->findEntityCache($place, $groupId);
            $entityComponents = $entityCache['entityComponents'];
            $entitySettings = $entityCache['entitySettings'];
            $entityPositions = $entityCache['entityPositions'];
        }
        else
        {
            $entityComponents = array();
            $entitySettings = array();
            $entityPositions = array();
        }

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $groupId, $defaultComponents, $customize, $template);
        $componentPanel->setAdditionalSettingList(array(
            'entityId' => $groupId,
            'entity' => 'zlgroups'
        ));

        if ( $allowCustomize )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setUserScheme($userScheme);
          } */

        if ( !empty($entityComponents) )
        {
            $componentPanel->setEntityComponentList($entityComponents);
        }

        if ( !empty($entityPositions) )
        {
            $componentPanel->setEntityPositionList($entityPositions);
        }

        if ( !empty($entitySettings) )
        {
            $componentPanel->setEntitySettingList($entitySettings);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    // 创建乐群action
    public function create()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $permissionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            
            throw new AuthorizationException($permissionStatus['msg']);
        }
        
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('zlgroups', 'create_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_new');
        OW::getDocument()->setTitle($language->text('zlgroups', 'create_page_title'));
        OW::getDocument()->setDescription($language->text('zlgroups', 'create_page_description'));
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        
        // FIXME
        $searcharea = '北京市';
        $this->assign('searcharea', $searcharea);

        $form = new ZLGROUPS_CreateGroupForm();

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $groupDto = $form->process();

            if ( empty($groupDto) )
            {
                $this->redirect();
            }

            // 将当前用户添加到乐群中
            $this->service->addUser($groupDto->id, OW::getUser()->getId());

            // 显示乐群创建成功的消息
            OW::getFeedback()->info($language->text('zlgroups', 'create_success_msg'));
            
            // TBD 
            // 1 － 邀请好友加入
            // 2 － 创建乐子
            $this->redirect($this->service->getGroupUrl($groupDto));
        }

        $this->addForm($form);
    }

    // 删除指定的乐群
    public function delete( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupDto = $this->service->findGroupById($params['groupId']);

        if ( empty($groupDto) )
        {
            throw new Redirect404Exception();
        }

        $isOwner = OW::getUser()->getId() == $groupDto->userId;
        $isModerator = OW::getUser()->isAuthorized('zlgroups');

        if ( !$isOwner && !$isModerator )
        {
            throw new Redirect403Exception();
        }

        $this->service->deleteGroup($groupDto->id);
        
        OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'delete_complete_msg'));

        $this->redirect(OW::getRouter()->urlForRoute('zlgroups-index'));
    }

    // 编辑指定的乐群
    public function edit( $params )
    {
        $groupId = (int) $params['groupId'];

        if ( empty($groupId) )
        {
            throw new Redirect404Exception();
        }

        $groupDto = $this->service->findGroupById($groupId);

        if ( !$this->service->isCurrentUserCanEdit($groupDto) )
        {
            throw new Redirect404Exception();
        }

        if ( $groupId === null )
        {
            throw new Redirect404Exception();
        }
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        
        // FIXME
        $searcharea = '北京市';
        $this->assign('searcharea', $searcharea);

        $form = new ZLGROUPS_EditGroupForm($groupDto);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if ( $form->process() )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'edit_success_msg'));
            }
            $this->redirect();
        }

        $this->addForm($form);

        $this->assign('imageUrl', empty($groupDto->imageHash) ? false : $this->service->getGroupImageUrl($groupDto));

        $deleteUrl = OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'delete', array('groupId' => $groupDto->id));
        $viewUrl = $this->service->getGroupUrl($groupDto);
        $lang = OW::getLanguage()->text('zlgroups', 'delete_confirm_msg');

        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');
        $js->jQueryEvent('#zlgroups-delete_btn', 'click', UTIL_JsGenerator::composeJsString(
                'if( confirm({$lang}) ) redirect({$url});', array('url' => $deleteUrl, 'lang' => $lang)));
        $js->jQueryEvent('#zlgroups-back_btn', 'click', UTIL_JsGenerator::composeJsString(
                'redirect({$url});', array('url' => $viewUrl)));

        OW::getDocument()->addOnloadScript($js);
    }

    // 当前用户加入指定乐群
    public function join( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();
        
        $groupDto = $this->service->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }
        
        if ( !$this->service->isCurrentUserCanView($groupDto->userId) )
        {
            throw new Redirect403Exception();
        }

        $invite = $this->service->findInvite($groupDto->id, $userId);

        if ( $invite !== null )
        {
            $this->service->markInviteAsViewed($groupDto->id, $userId);
        } 
        else if ( $groupDto->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE )
        {
            $this->redirect(OW::getRouter()->urlForRoute('zlgroups-private-group', array(
                'groupId' => $groupDto->id
            )));
        }
        
        ZLGROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);

        $redirectUrl = OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $groupId));
        OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'join_complete_message'));

        $this->redirect($redirectUrl);
    }

    // 拒绝邀请
    public function declineInvite( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();

        ZLGROUPS_BOL_Service::getInstance()->deleteInvite($groupId, $userId);

        $redirectUrl = OW::getRouter()->urlForRoute('zlgroups-invite-list');
        OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'invite_declined_message'));

        $this->redirect($redirectUrl);
    }

    // 退出乐群（自己）
    public function leave( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();

        ZLGROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userId);

        $redirectUrl = OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $groupId));
        OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'leave_complete_message'));

        $this->redirect($redirectUrl);
    }

    // 删除指定乐群的指定用户（moderator）
    public function deleteUser( $params )
    {
        if ( empty($params['groupId']) || empty($params['userId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        $isModerator = OW::getUser()->isAuthorized('zlgroups');

        if ( !$isModerator && $groupDto->userId != OW::getUser()->getId()  )
        {
            throw new Redirect403Exception();
        }

        $groupId = (int) $groupDto->id;
        $userId = $params['userId'];

        ZLGROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userId);

        //$redirectUrl = OW::getRouter()->urlForRoute('groups-user-list', array('groupId' => $groupId));

        OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'delete_user_success_message'));

        $redirectUri = urldecode($_GET['redirectUri']);
        $this->redirect(OW_URL_HOME . $redirectUri);
    }

    // 构造paging信息
    // page    - 第几个页面 
    // perPage - 每个页面的item个数
    // first   - item offset
    // count   - 每个页面的item个数 (limit)
    private function getPaging( $page, $perPage, $onPage )
    {
        $paging['page'] = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $paging['perPage'] = $perPage;

        $paging['first'] = ($paging['perPage'] - 1) * $paging['perPage'];
        $paging['count'] = $paging['perPage'];
    }

    // 根据page请求显示popular乐群列表
    public function mostPopularList()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('zlgroups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('zlgroups', 'popular_list_page_title'));
        OW::getDocument()->setDescription($language->text('zlgroups', 'popular_list_page_description'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        // 根据请求获得页面索引号$page
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        // 得到需要开始显示的乐群偏移量offset
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        // 得到乐群列表
        $dtoList = $this->service->findGroupList(ZLGROUPS_BOL_Service::LIST_MOST_POPULAR, $first, $count);
        // 得到所有该类型的乐群总数
        $listCount = $this->service->findGroupListCount(ZLGROUPS_BOL_Service::LIST_MOST_POPULAR);

        // 创建paging组件（component）
        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        // 创建菜单，并设置活动菜单项
        $menu = $this->getGroupListMenu();
        $menu->getElement('popular')->setActive(true);
        
        // 设置当前乐群列表类型变量listType,用于显示
        $this->assign('listType', 'popular');

        // 
        $this->displayGroupList($dtoList, $paging, $menu);
    }

    // 根据page请求显示latest乐群列表
    public function latestList()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('zlgroups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('zlgroups', 'latest_list_page_title'));
        OW::getDocument()->setDescription($language->text('zlgroups', 'latest_list_page_description'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findGroupList(ZLGROUPS_BOL_Service::LIST_LATEST, $first, $count);
        $listCount = $this->service->findGroupListCount(ZLGROUPS_BOL_Service::LIST_LATEST);

        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        $menu = $this->getGroupListMenu();
        $menu->getElement('latest')->setActive(true);
        $this->assign('listType', 'latest');

        $this->displayGroupList($dtoList, $paging, $menu);
    }

    // 根据page请求显示invite乐群列表
    public function inviteList()
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('zlgroups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('zlgroups', 'invite_list_page_title'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        OW::getRegistry()->set('zlgroups.hide_console_invite_item', true);

        $this->service->markAllInvitesAsViewed($userId);

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findInvitedGroups($userId, $first, $count);
        $listCount = $this->service->findInvitedGroupsCount($userId);

        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        $menu = $this->getGroupListMenu();
        $menu->getElement('invite')->setActive(true);
        $this->assign('listType', 'invite');

        $templatePath = OW::getPluginManager()->getPlugin('zlgroups')->getCtrlViewDir() . 'groups_list.html';

        $this->setTemplate($templatePath);

        $acceptUrls = array();
        $declineUrls = array();

        $out = array();

        foreach ( $dtoList as $group )
        {
            $acceptUrls[$group->id] = OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'join', array(
                'groupId' => $group->id
            ));

            $declineUrls[$group->id] = OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'declineInvite', array(
                'groupId' => $group->id
            ));
        }

        $acceptLabel = OW::getLanguage()->text('zlgroups', 'invite_accept_label');
        $declineLabel = OW::getLanguage()->text('zlgroups', 'invite_decline_label');

        foreach ( $dtoList as $item )
        {
            /* @var $item ZLGROUPS_BOL_Group */

            $userCount = ZLGROUPS_BOL_Service::getInstance()->findUserListCount($item->id);
            $title = strip_tags($item->title);

            $toolbar = array(
                array(
                    'label' => OW::getLanguage()->text('zlgroups', 'listing_users_label', array(
                        'count' => $userCount
                    ))
                ),

                array(
                    'href' => $acceptUrls[$item->id],
                    'label' => $acceptLabel
                ),

                array(
                    'href' => $declineUrls[$item->id],
                    'label' => $declineLabel
                )
            );

            $out[] = array(
                'id' => $item->id,
                'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $item->id)),
                'title' => $title,
                'imageTitle' => $title,
                'content' => strip_tags($item->description),
                'time' => UTIL_DateTime::formatDate($item->timeStamp),
                'imageSrc' => ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($item),
                'users' => $userCount,
                'toolbar' => $toolbar
            );
        }

        $this->addComponent('paging', $paging);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }
        else
        {
            $this->assign('menu', '');
        }

        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign("authMsg", json_encode($authStatus["msg"]));
                $this->assign("showCreate", true);
            }
            else 
            {
                $this->assign("showCreate", false);
            }
        }

        $this->assign('list', $out);
    }

    // 根据page请求显示我的乐群列表
    public function myGroupList()
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('zlgroups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('zlgroups', 'my_list_page_title'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findMyGroups($userId, $first, $count);
        $listCount = $this->service->findMyGroupsCount($userId);

        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        $menu = $this->getGroupListMenu();
        $menu->getElement('my')->setActive(true);
        $this->assign('listType', 'my');

        $this->displayGroupList($dtoList, $paging, $menu);
    }

    // 显示指定用户的乐群列表（采用paging）
    public function userGroupList( $params )
    {
        $userDto = BOL_UserService::getInstance()->findByUsername(trim($params['user']));

        if ( empty($userDto) )
        {
            throw new Redirect404Exception();
        }

        // privacy check
        // 得到被查看用户ID
        $userId = $userDto->id;
        // 得到当前用户ID
        $viewerId = OW::getUser()->getId();
        // 如果两者相等，说明是所有者
        $ownerMode = $userId == $viewerId;
        $modPermissions = OW::getUser()->isAuthorized('zlgroups');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new OW_Event('privacy_check_permission', $privacyParams);

            OW::getEventManager()->trigger($event);
        }

        $language = OW::getLanguage();
        OW::getDocument()->setTitle($language->text('zlgroups', 'user_groups_page_title'));
        OW::getDocument()->setDescription($language->text('zlgroups', 'user_groups_page_description'));
        OW::getDocument()->setHeading($language->text('zlgroups', 'user_group_list_heading', array(
                'userName' => BOL_UserService::getInstance()->getDisplayName($userDto->id)
            )));

        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findUserGroupList($userDto->id, $first, $count);
        $listCount = $this->service->findUserGroupListCount($userDto->id);

        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        $this->assign('hideCreateNew', true);
        
        $this->assign('listType', 'user');

        $this->displayGroupList($dtoList, $paging);
    }

    // 显示乐群列表，给定参数为 乐群列表 － paging组件 － 菜单
    private function displayGroupList( $list, $paging, $menu = null )
    {
        $templatePath = OW::getPluginManager()->getPlugin('zlgroups')->getCtrlViewDir() . 'groups_list.html';
        $this->setTemplate($templatePath);

        $out = array();

        foreach ( $list as $item )
        {
            /* @var $item ZLGROUPS_BOL_Group */

            $userCount = ZLGROUPS_BOL_Service::getInstance()->findUserListCount($item->id);
            $title = strip_tags($item->title);

            $toolbar = array(
                array(
                    'label' => OW::getLanguage()->text('zlgroups', 'listing_users_label', array(
                        'count' => $userCount
                    ))
                )
            );

            $out[] = array(
                'id' => $item->id,
                'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $item->id)),
                'title' => $title,
                'imageTitle' => $title,
                'content' => UTIL_String::truncate(strip_tags($item->description), 300, '...'),
                'time' => UTIL_DateTime::formatDate($item->timeStamp),
                'imageSrc' => ZLGROUPS_BOL_Service::getInstance()->getGroupImageUrl($item),
                'users' => $userCount,
                'toolbar' => $toolbar
            );
        }

        $this->addComponent('paging', $paging);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }
        else
        {
            $this->assign('menu', '');
        }

        $this->assign("showCreate", true);
        
        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign("authMsg", json_encode($authStatus["msg"]));
            }
            else 
            {
                $this->assign("showCreate", false);
            }
        }
        
        $this->assign('list', $out);
    }

    // 获得指定乐群的所有乐友列表
    public function userList( $params )
    {
        $groupId = (int) $params['groupId'];
        $groupDto = $this->service->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( $groupDto->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE && !OW::getUser()->isAuthorized('zlgroups') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                $this->redirect(OW::getRouter()->urlForRoute('zlgroups-private-group', array(
                    'groupId' => $groupDto->id
                )));
            }

            $invite = $this->service->findInvite($groupDto->id, OW::getUser()->getId());
            $user = $this->service->findUser($groupDto->id, OW::getUser()->getId());

            if ( $groupDto->whoCanView == ZLGROUPS_BOL_Service::WCV_INVITE && $invite === null && $user === null )
            {
                $this->redirect(OW::getRouter()->urlForRoute('zlgroups-private-group', array(
                    'groupId' => $groupDto->id
                )));
            }
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findUserList($groupId, $first, $count);
        $listCount = $this->service->findUserListCount($groupId);

        $listCmp = new ZLGROUPS_UserList($groupDto, $dtoList, $listCount, 20);  // TBU this component
        $this->addComponent('listCmp', $listCmp);
        $this->addComponent('groupBriefInfo', new ZLGROUPS_CMP_BriefInfo($groupId));
        
        $this->assign("groupId", $groupId);
    }

    // 创建乐群列表页菜单
    private function getGroupListMenu()
    {

        $language = OW::getLanguage();

        $items = array();

        $items[0] = new BASE_MenuItem();
        $items[0]->setLabel($language->text('zlgroups', 'group_list_menu_item_popular'))
            ->setKey('popular')
            ->setUrl(OW::getRouter()->urlForRoute('zlgroups-most-popular'))
            ->setOrder(1)
            ->setIconClass('ow_ic_comment');

        $items[1] = new BASE_MenuItem();
        $items[1]->setLabel($language->text('zlgroups', 'group_list_menu_item_latest'))
            ->setKey('latest')
            ->setUrl(OW::getRouter()->urlForRoute('zlgroups-latest'))
            ->setOrder(2)
            ->setIconClass('ow_ic_clock');


        if ( OW::getUser()->isAuthenticated() )
        {
            $items[2] = new BASE_MenuItem();
            $items[2]->setLabel($language->text('zlgroups', 'group_list_menu_item_my'))
                ->setKey('my')
                ->setUrl(OW::getRouter()->urlForRoute('zlgroups-my-list'))
                ->setOrder(3)
                ->setIconClass('ow_ic_files');

            $items[3] = new BASE_MenuItem();
            $items[3]->setLabel($language->text('zlgroups', 'group_list_menu_item_invite'))
                ->setKey('invite')
                ->setUrl(OW::getRouter()->urlForRoute('zlgroups-invite-list'))
                ->setOrder(4)
                ->setIconClass('ow_ic_bookmark');
        }

        return new BASE_CMP_ContentMenu($items);
    }

    public function follow()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $_GET['groupId'];

        $groupDto = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => ZLGROUPS_BOL_Service::ENTITY_TYPE_GROUP,
            'feedId' => $groupId
        );

        $title = UTIL_String::truncate(strip_tags($groupDto->title), 100, '...');

        switch ( $_GET['command'] )
        {
            case 'follow':
                OW::getEventManager()->call('feed.add_follow', $eventParams);
                OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'feed_follow_complete_msg', array('groupTitle' => $title)));
                break;

            case 'unfollow':
                OW::getEventManager()->call('feed.remove_follow', $eventParams);
                OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'feed_unfollow_complete_msg', array('groupTitle' => $title)));
                break;
        }

        $this->redirect(OW_URL_HOME . $_GET['backUri']);
    }

    // 邀请选中的用户加入乐群
    public function invite()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $respoce = array();

        $userIds = json_decode($_POST['userIdList']);
        $groupId = $_POST['groupId'];
        $allIdList = json_decode($_POST['allIdList']);

        $group = $this->service->findGroupById($groupId);

        $count = 0;
        foreach ( $userIds as $uid )
        {
            $this->service->inviteUser($group->id, $uid, $userId);

            $count++;
        }

        $respoce['messageType'] = 'info';
        $respoce['message'] = OW::getLanguage()->text('zlgroups', 'users_invite_success_message', array('count' => $count));
        $respoce['allIdList'] = array_diff($allIdList, $userIds);

        exit(json_encode($respoce));
    }

    public function privateGroup( $params )
    {
        $language = OW::getLanguage();

        $this->setPageTitle($language->text('zlgroups', 'private_page_title'));
        $this->setPageHeading($language->text('zlgroups', 'private_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        $groupId = $params['groupId'];
        $group = $this->service->findGroupById($groupId);

        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($group->userId));
        $displayName = BOL_UserService::getInstance()->getDisplayName($group->userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($group->userId);

        $this->assign('group', $group);
        $this->assign('avatar', $avatarList[$group->userId]);
        $this->assign('displayName', $displayName);
        $this->assign('userUrl', $userUrl);
        $this->assign('creator', $language->text('zlgroups', 'creator'));
    }
}

// Additional calsses

class ZLGROUPS_UserList extends BASE_CMP_Users
{
    /**
     *
     * @var ZLGROUPS_BOL_Group
     */
    protected $groupDto;

    public function __construct( ZLGROUPS_BOL_Group $groupDto, $list, $itemCount, $usersOnPage, $showOnline = true)
    {
        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
        $this->groupDto = $groupDto;
    }

    public function getContextMenu($userId)
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return null;
        }

        $isOwner = $this->groupDto->userId == OW::getUser()->getId();
        $isGroupModerator = OW::getUser()->isAuthorized('zlgroups');

        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('group_user_' . $userId); // TBU- 可能有错
        $contextActionMenu->addAction($contextParentAction);

        if ( ($isOwner || $isGroupModerator) && $userId != OW::getUser()->getId() )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setKey('delete_group_user');
            $contextAction->setLabel(OW::getLanguage()->text('zlgroups', 'delete_group_user_label'));

            if ( $this->groupDto->userId != $userId )
            {
                $callbackUri = OW::getRequest()->getRequestUri();
                $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('ZLGROUPS_CTRL_Groups', 'deleteUser', array(
                    'groupId' => $this->groupDto->id,
                    'userId' => $userId
                )), array(
                    'redirectUri' => urlencode($callbackUri)
                ));

                $contextAction->setUrl($deleteUrl);

                $contextAction->addAttribute('data-message', OW::getLanguage()->text('zlgroups', 'delete_group_user_confirmation'));
                $contextAction->addAttribute('onclick', "return confirm($(this).data().message)");
            }
            else
            {
                $contextAction->setUrl('javascript://');
                $contextAction->addAttribute('data-message', OW::getLanguage()->text('zlgroups', 'group_owner_delete_error'));
                $contextAction->addAttribute('onclick', "OW.error($(this).data().message); return false;");
            }

            $contextActionMenu->addAction($contextAction);
        }

        return $contextActionMenu;
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate !== null && $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex !== null && $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
        }

        return $fields;
    }
}

// 乐群表单
class ZLGROUPS_GroupForm extends Form
{
    public function __construct( $formName )
    {
        parent::__construct($formName);

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $language = OW::getLanguage();

        // 乐群标题项
        $field = new TextField('title');
        $field->setRequired(true);
        $field->setLabel($language->text('zlgroups', 'create_field_title_label'));
        $this->addElement($field);

        // 乐群描述项
        $field = new WysiwygTextarea('description');
        $field->setLabel($language->text('zlgroups', 'create_field_description_label'));
        $field->setRequired(true);
        $this->addElement($field);

        // 乐群LOGO项
        $field = new ZLGROUPS_Image('image');
        $field->setLabel($language->text('zlgroups', 'create_field_image_label'));
        $field->addValidator(new ZLGROUPS_ImageValidator());
        $this->addElement($field);

        // TBD - 乐群地址项
        // 乐群location项
        $field = new TextField('location');
        $field->setLabel($language->text('zlgroups', 'create_field_location_label'));
        $this->addElement($field);
        
        $field = new HiddenField('locationinfo');
        $field->addValidator(new ZLGROUPS_RequiredLoactionValidator());
        $this->addElement($field);
        
        // 谁可以查看乐群项
        $whoCanView = new RadioField('whoCanView');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                ZLGROUPS_BOL_Service::WCV_ANYONE => $language->text('zlgroups', 'form_who_can_view_anybody'),
                ZLGROUPS_BOL_Service::WCV_INVITE => $language->text('zlgroups', 'form_who_can_view_invite')
            )
        );
        $whoCanView->setLabel($language->text('zlgroups', 'form_who_can_view_label'));
        $this->addElement($whoCanView);

        // 谁可以邀请项
        $whoCanInvite = new RadioField('whoCanInvite');
        $whoCanInvite->setRequired();
        $whoCanInvite->addOptions(
            array(
                ZLGROUPS_BOL_Service::WCI_PARTICIPANT => $language->text('zlgroups', 'form_who_can_invite_participants'),
                ZLGROUPS_BOL_Service::WCI_CREATOR => $language->text('zlgroups', 'form_who_can_invite_creator')
            )
        );
        $whoCanInvite->setLabel($language->text('zlgroups', 'form_who_can_invite_label'));
        $this->addElement($whoCanInvite);
    }

    /**
     *
     * @param ZLGROUPS_BOL_Group $group
     * @return ZLGROUPS_BOL_Group
     */
    public function processGroup( ZLGROUPS_BOL_Group $group )
    {
        $values = $this->getValues();
        $service = ZLGROUPS_BOL_Service::getInstance();

        if ( $values['image'] )
        {
            if ( !empty($group->imageHash) )
            {
                OW::getStorage()->removeFile($service->getGroupImagePath($group));
                OW::getStorage()->removeFile($service->getGroupImagePath($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG));
            }

            $group->imageHash = uniqid();
        }

        $group->title = strip_tags($values['title']);
        $values['description'] = UTIL_HtmlTag::stripJs($values['description']);
        $values['description'] = UTIL_HtmlTag::stripTags($values['description'], array('frame'), array(), true);

        $group->description = $values['description'];
        $group->whoCanInvite = $values['whoCanInvite'];
        $group->whoCanView = $values['whoCanView'];

        $service->saveGroup($group);

        if ( !empty($values['image']) )
        {
            $this->saveImages($values['image'], $group);
        }
        
        // added by hawk
        // 更新乐群地址信息
        $location = $values['location'];
        $addressinfo = $values['locationinfo'];
        $address_details = ZLAREAS_CLASS_Utility::getInstance()->getAddressInfo($addressinfo);
        $service->saveLocation(
        		$group->id, 
        		$location, 
        		$address_details['formated_address'],
        		$address_details['province'],
        		$address_details['city'],
        		$address_details['district'],
        		$address_details['longitude'],
        		$address_details['latitude']
        );        

        return $group;
    }

    protected function saveImages( $postFile, ZLGROUPS_BOL_Group $group )
    {
        $service = ZLGROUPS_BOL_Service::getInstance();
        
        $smallFile = $service->getGroupImagePath($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_SMALL);
        $bigFile = $service->getGroupImagePath($group, ZLGROUPS_BOL_Service::IMAGE_SIZE_BIG);
        
        $tmpDir = OW::getPluginManager()->getPlugin('zlgroups')->getPluginFilesDir();
        $smallTmpFile = $tmpDir . uniqid('small_') . '.jpg';
        $bigTmpFile = $tmpDir . uniqid('big_') . '.jpg';

        $image = new UTIL_Image($postFile['tmp_name']);
        $image->resizeImage(ZLGROUPS_BOL_Service::IMAGE_WIDTH_BIG, null)
            ->saveImage($bigTmpFile)
            ->resizeImage(ZLGROUPS_BOL_Service::IMAGE_WIDTH_SMALL, ZLGROUPS_BOL_Service::IMAGE_WIDTH_SMALL, true)
            ->saveImage($smallTmpFile);

        try
        {
            OW::getStorage()->copyFile($smallTmpFile, $smallFile);
            OW::getStorage()->copyFile($bigTmpFile, $bigFile);
        }
        catch ( Exception $e ) {}

        unlink($smallTmpFile);
        unlink($bigTmpFile);
    }

    public function process()
    {

    }
}

// 乐群创建表单
// TBD - 添加乐群地址
class ZLGROUPS_CreateGroupForm extends ZLGROUPS_GroupForm
{

    public function __construct()
    {
        parent::__construct('ZLGROUPS_CreateGroupForm');

        $this->getElement('title')->addValidator(new ZLGROUPS_UniqueValidator());

        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('zlgroups', 'create_submit_btn_label'));
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/ZLGROUPS_GroupForm#process()
     */
    public function process()
    {
        $groupDto = new ZLGROUPS_BOL_Group();
        $groupDto->timeStamp = time();
        $groupDto->userId = OW::getUser()->getId();

        $data = array();
        foreach ( $groupDto as $key => $value )
        {
            $data[$key] = $value;
        }

        $event = new OW_Event(ZLGROUPS_BOL_Service::EVENT_BEFORE_CREATE, array('groupId' => $groupDto->id), $data);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $k => $v )
        {
            $groupDto->$k = $v;
        }

        $group = $this->processGroup($groupDto);
        
        BOL_AuthorizationService::getInstance()->trackAction('zlgroups', 'create');

        $is_forum_connected = OW::getConfig()->getValue('zlgroups', 'is_forum_connected');
        // Add forum group
        if ( $is_forum_connected )
        {
            $event = new OW_Event('forum.create_group', array('entity' => 'zlgroups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
            OW::getEventManager()->trigger($event);
        }
        
        if ( $group )
        {
            $event = new OW_Event(ZLGROUPS_BOL_Service::EVENT_CREATE, array('groupId' => $groupDto->id));
            OW::getEventManager()->trigger($event);
        }

        return $group;
    }
}

// 乐群编辑表单
// TBD － 乐群地点
class ZLGROUPS_EditGroupForm extends ZLGROUPS_GroupForm
{
    /**
     *
     * @var ZLGROUPS_BOL_Group
     */
    private $groupDto;

    public function __construct( ZLGROUPS_BOL_Group $group )
    {
        parent::__construct('ZLGROUPS_EditGroupForm');

        $this->groupDto = $group;

        $this->getElement('title')->setValue($group->title);
        $this->getElement('title')->addValidator(new ZLGROUPS_UniqueValidator($group->title));
        $this->getElement('description')->setValue($group->description);
        $this->getElement('whoCanView')->setValue($group->whoCanView);
        $this->getElement('whoCanInvite')->setValue($group->whoCanInvite);
        
        // added by hawk, for location
        $field = new HiddenField('origin_lng');
        $this->addElement($field);
        $field = new HiddenField('origin_lat');
        $this->addElement($field);        
        
        $detailedLocationInfo = ZLGROUPS_BOL_Service::getInstance()->findLocationDetailedInfoByGroupId($group->id);
        $this->getElement('origin_lng')->setValue($detailedLocationInfo['longitude']);
        $this->getElement('origin_lat')->setValue($detailedLocationInfo['latitude']);
        $this->getElement('location')->setValue($detailedLocationInfo['location']);
        $this->getElement('locationinfo')->setValue($detailedLocationInfo['locationinfo']);
        
        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('zlgroups', 'edit_submit_btn_label'));
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/ZLGROUPS_GroupForm#process()
     */
    public function process()
    {
        $result = $this->processGroup($this->groupDto);

        if ( $result )
        {
            $event = new OW_Event(ZLGROUPS_BOL_Service::EVENT_EDIT, array('groupId' => $this->groupDto->id));
            OW::getEventManager()->trigger($event);
        }

        return $result;
    }
}

// 乐群LOGO验证器
class ZLGROUPS_ImageValidator extends OW_Validator
{

    public function __construct()
    {

    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( empty($value) )
        {
            return true;
        }

        $realName = $value['name'];
        $tmpName = $value['tmp_name'];

        switch ( false )
        {
            case is_uploaded_file($tmpName):
                $this->setErrorMessage(OW::getLanguage()->text('zlgroups', 'errors_image_upload'));
                return false;

            case UTIL_File::validateImage($realName):
                $this->setErrorMessage(OW::getLanguage()->text('zlgroups', 'errors_image_invalid'));
                return false;
        }

        return true;
    }
}

// 乐群LOGO控件
class ZLGROUPS_Image extends FileField
{

    public function getValue()
    {
        return empty($_FILES[$this->getName()]['tmp_name']) ? null : $_FILES[$this->getName()];
    }
}

// 乐群地址验证器
// class ZLGROUPS_LoactionValidator extends OW_Validator
// {	
// 	public function __construct()
// 	{
// 		$errorMessage = OW::getLanguage()->text('zlgroups', 'errors_location');
	
// 		if ( empty($errorMessage) )
// 		{
// 			$errorMessage = 'Required Validator Error!';
// 		}
	
// 		$this->setErrorMessage($errorMessage);
// 	}
	
// 	public function isValid( $value )
// 	{
// 		if ( is_array($value) )
// 		{
// 			if ( sizeof($value) === 0 )
// 			{
// 				return false;
// 			}
// 		}
// 		else if ( $value === null || mb_strlen(trim($value)) === 0 )
// 		{
// 			return false;
// 		}
	
// 		return true;
// 	}
// }

class ZLGROUPS_RequiredLoactionValidator extends OW_Validator
{
	/**
	 * Constructor.
	 *
	 * @param array $params
	 */
	public function __construct()
	{
		$errorMessage = OW::getLanguage()->text('zlgroups', 'errors_location');

		if ( empty($errorMessage) )
		{
			$errorMessage = 'Required Validator Error!';
		}

		$this->setErrorMessage($errorMessage);
	}

	/**
	 * @see OW_Validator::isValid()
	 *
	 * @param mixed $value
	 */
	public function isValid( $value )
	{
		if ( is_array($value) )
		{
			if ( sizeof($value) === 0 )
			{
				return false;
			}
		}
		else if ( $value === null || mb_strlen(trim($value)) === 0 )
		{
			return false;
		}

		return true;
	}

	/**
	 * @see OW_Validator::getJsValidator()
	 *
	 * @return string
	 */
	public function getJsValidator()
	{
		return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
	}
}

// 乐群标题唯一性验证器
class ZLGROUPS_UniqueValidator extends OW_Validator
{
    private $exception;

    public function __construct( $exception = null )
    {
        $this->setErrorMessage(OW::getLanguage()->text('zlgroups', 'group_already_exists'));

        $this->exception = $exception;
    }

    public function isValid( $value )
    {
        if ( !empty($this->exception) && trim($this->exception) == trim($value) )
        {
            return true;
        }

        $dto = ZLGROUPS_BOL_Service::getInstance()->findByTitle($value);

        if ( $dto === null )
        {
            return true;
        }

        return false;
    }
}
