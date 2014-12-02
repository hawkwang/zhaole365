<?php

class ZLGHEADER_CMP_Header extends OW_Component
{
    private $groupId;

    private $service;

    private $groupService;

    private $group;

    public function __construct( $groupId )
    {
        parent::__construct();

        $this->groupId = $groupId;

        $urlStatic = OW::getPluginManager()->getPlugin('zlgheader')->getStaticUrl();
        OW::getDocument()->addScript($urlStatic . 'gheader.min.js?' . ZLGHEADER_Plugin::PLUGIN_BUILD);
        OW::getDocument()->addStyleSheet($urlStatic . 'gheader.min.css?' . ZLGHEADER_Plugin::PLUGIN_BUILD);

        OW::getLanguage()->addKeyForJs('zlgheader', 'delete_cover_confirmation');
        OW::getLanguage()->addKeyForJs('zlgheader', 'my_photos_title');

        $this->service = ZLGHEADER_BOL_Service::getInstance();
        $this->groupService = ZLGROUPS_BOL_Service::getInstance();
        $this->group = $this->groupService->findGroupById($this->groupId);
    }

    private function getGroupInfo()
    {
        static $groupInfo = array();

        if ( !empty($groupInfo) )
        {
            return $groupInfo;
        }

        $groupInfo['id'] = $this->group->id;
        $groupInfo['hasImage'] = !empty($this->group->imageHash);
        $groupInfo['image'] = $this->groupService->getGroupImageUrl($this->group);

        $groupInfo['title'] = htmlspecialchars($this->group->title);
        $groupInfo['description'] = $this->group->description;
        $groupInfo['url'] = $this->groupService->getGroupUrl($this->group);
        $groupInfo['time'] = UTIL_DateTime::formatDate($this->group->timeStamp);

        $privacyParams = array('action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $this->group->userId, 'viewerId' => OW::getUser()->getId());
        $event = new OW_Event('privacy_check_permission', $privacyParams);

        $showAdmin = false;
        
        try {
            OW::getEventManager()->trigger($event);
            $showAdmin = true;
        }
        catch ( RedirectException $e )
        {
            $showAdmin = false;
        }
        
        if ( $showAdmin )
        {
            $groupInfo['admin'] = array();
            $groupInfo['admin']['name'] = BOL_UserService::getInstance()->getDisplayName($this->group->userId);
            $groupInfo['admin']['url'] = BOL_UserService::getInstance()->getUserUrl($this->group->userId);
        }
        else
        {
            $groupInfo["admin"] = null;
        }
        
        return $groupInfo;
    }

    private function getConfig()
    {
        $config = array();
        $config['coverHeight'] = $this->service->getConfig($this->groupId, 'coverHeight');
        $config['avatarSize'] = 100;

        return $config;
    }

    /**
     *
     * @return BASE_CMP_ContextAction
     */
    private function getContextToolbar( $hasCover = false )
    {
        $language = OW::getLanguage();
        $permissions = $this->getPemissions();

        $contextActionMenu = new BASE_CMP_ContextAction();
        
        if ($hasCover)
        {
            $contextActionMenu->setClass("ow_photo_context_action");
        }

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('gheaderToolbar');
        $contextParentAction->setLabel('<span class="uh-toolbar-add-label">' . $language->text('zlgheader', 'set_covet_label') . '</span><span class="uh-toolbar-edit-label">' . $language->text('zlgheader', 'change_covet_label') . '</span>');
        $contextParentAction->setId('uh-toolbar-parent');
        //$contextParentAction->setClass('ow_ic_picture');

        $contextActionMenu->addAction($contextParentAction);

        if ( $permissions['add'] )
        {
            if ( ZLGHEADER_CLASS_PhotoBridge::getInstance()->isActive() )
            {
                $contextAction = new BASE_ContextAction();
                $contextAction->setParentKey($contextParentAction->getKey());
                $contextAction->setLabel($language->text('zlgheader', 'choose_from_photos_label'));
                $contextAction->setUrl('javascript://');
                $contextAction->setKey('uhChoose');
                $contextAction->setId('uhco-choose');
                $contextAction->setClass('uhco-item uhco-choose');
                $contextAction->setOrder(1);

                $contextActionMenu->addAction($contextAction);
            }

            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel('<div class="uh-fake-file"><div>' . $language->text('zlgheader', 'upload_label') . '</div><input type="file" name="file" id="uh-upload-cover" size="1" /></div>');
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhUpload');
            $contextAction->setClass('uhco-item uhco-upload');
            $contextAction->setOrder(2);

            $contextActionMenu->addAction($contextAction);
        }

        if ( $permissions['reposition'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text('zlgheader', 'reposition_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhReposition');
            $contextAction->setId('uhco-reposition');
            $contextAction->setClass('uhco-item uhco-reposition');
            $contextAction->setOrder(3);

            $contextActionMenu->addAction($contextAction);
        }

        if ( $permissions['delete'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text('zlgheader', 'remove_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhRemove');
            $contextAction->setId('uhco-remove');
            $contextAction->setClass('uhco-item uhco-remove');

            $contextAction->setOrder(4);

            $contextActionMenu->addAction($contextAction);
        }

        return $contextActionMenu;
    }

    public function getToolbar()
    {
        $toolbar = array();

        $groupInfo = $this->getGroupInfo();

        $js = UTIL_JsGenerator::newInstance()
            ->jQueryEvent('#groups_toolbar_flag', 'click', UTIL_JsGenerator::composeJsString('OW.flagContent({$entity}, {$id}, {$title}, {$href}, "zlgroups+flags", {$ownerId});',
                array(
                    'entity' => GROUPS_BOL_Service::WIDGET_PANEL_NAME,
                    'id' => $this->group->id,
                    'title' => $groupInfo['title'],
                    'href' => $groupInfo['url'],
                    'ownerId' => $this->group->userId
                )));

        OW::getDocument()->addOnloadScript($js, 1001);

        if ( $this->groupService->isCurrentUserCanEdit($this->group) )
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('zlgroups', 'edit_btn_label'),
                'href' => OW::getRouter()->urlForRoute('zlgroups-edit', array('groupId' => $this->groupId))
            );
        }

        if ( OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $this->group->userId )
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('base', 'flag'),
                'href' => 'javascript://',
                'id' => 'groups_toolbar_flag'
            );
        }

        $event = new BASE_CLASS_EventCollector('zlgroups.on_toolbar_collect', array('groupId' => $this->groupId));
        OW::getEventManager()->trigger($event);

        foreach ( $event->getData() as $item )
        {
            $toolbar[] = $item;
        }

        return $toolbar;
    }
    
    public function getTabs()
    {
        $language = OW::getLanguage();
        
        $tabs = new ZLGHEADER_CMP_Tabs();
                  
        $tab = new BASE_MenuItem();
        $tab->setKey('index');
        $tab->setLabel($language->text('zlgheader', 'tab_index'));
        $tab->setUrl($this->groupService->getGroupUrl($this->group));
        $tab->setIconClass('ow_ic_info');
        $tab->setOrder(1);
        $tabs->addElement($tab);
        
        $tab = new BASE_MenuItem();
        $tab->setKey('users');
        $tab->setLabel($language->text('zlgheader', 'tab_users'));
        $tab->setUrl(OW::getRouter()->urlForRoute('zlgroups-user-list', array('groupId' => $this->groupId)));
        $tab->setIconClass('ow_ic_user');
        $tab->setOrder(2);
        $tabs->addElement($tab);
                
        $event = new BASE_CLASS_EventCollector("zlgheader.collect_tabs", array(
            "groupId" => $this->groupId
        ));
        
        OW::getEventManager()->trigger($event);
                
        foreach ( $event->getData() as $item )
        {
            $tabs->addElement($item);
        }
        
        return $tabs;
    }

    public function getPemissions()
    {
        $permissions = array(
            'add' => false,
            'reposition' => false,
            'delete' => false,
            'view' => false
        );

        $selfMode = $this->group->userId == OW::getUser()->getId();
        $moderationMode = OW::getUser()->isAuthorized('zlgheader');

        if ( $selfMode || $moderationMode )
        {
            $permissions['delete'] = true;
            $permissions['view'] = true;
        }

        $credits = ZLGHEADER_CLASS_CreditsBridge::getInstance()->credits;
        
        if ( $selfMode && $credits->isAvaliable('add_cover') )
        {
            $permissions['reposition'] = true;
            $permissions['add'] = true;
        }

        if ( !$permissions['view'] && OW::getUser()->isAuthorized('zlgheader', 'view_cover') )
        {
            $permissions['view'] = true;
        }

        $permissions['controls'] = ($permissions['add']
            || $permissions['reposition']
            || $permissions['delete'])
            && $permissions['view'];

        $permissions['moderation'] = !$selfMode && $moderationMode;

        return $permissions;
    }

    public function getCover()
    {
        $permissions = $this->getPemissions();

        $cover = $permissions['view']
            ? $this->service->findCoverByGroupId($this->groupId)
            : null;

        if ( $cover === null )
        {
            return array(
                'hasCover' => false,
                'src' => null,
                'data' => array(),
                'css' => '',
                'scale' => 0,
                "class" => "uh-cover-no-cover"
            );
        }

        $classList = array();
        $classList[] = "uh-cover-has-cover";
        
        return array(
            'hasCover' => true,
            'src' => $this->service->getCoverUrl($cover),
            'data' => $cover->getSettings(),
            'css' => $cover->getCssString(),
            'scale' => $cover->getRatio(),
            "class" => implode(" ", $classList)
        );
        
        /*
        $data = $cover->getSettings();

        $css = empty($data['css']) ? array() : $data['css'];

        if ( !empty($data['position']['top']) )
        {
            $css['top'] = $data['position']['top'] . 'px;';
        }

        if ( !empty($data['position']['left']) )
        {
            $css['left'] = $data['position']['left'] . 'px;';
        }

        $cssStr = '';
        foreach ( $css as $k => $v )
        {
            $cssStr .= $k . ': ' . $v  . '; ';
        }

        return array(
            'hasCover' => true,
            'src' => $this->service->getCoverUrl($cover),
            'data' => $data,
            'css' => $cssStr
        );*/
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $permissions = $this->getPemissions();

        $cover = $this->getCover();
        $this->assign('cover', $cover);

        $this->addComponent("tabs", $this->getTabs());
        
        $permissions['controls'] = !$cover['hasCover'] && $permissions['add']
            || $cover['hasCover'] && $permissions['delete'];

        if ( $permissions['controls'] )
        {
            $contextToolbar = $this->getContextToolbar($cover['hasCover']);
            $this->addComponent('contextToolbar', $contextToolbar);
        }

        $this->assign('group', $this->getGroupInfo());
        $this->assign('config', $this->getConfig());
        $this->assign('toolbar', $this->getToolbar());

        $this->assign('permissions', $permissions);

        $options = array();

        if ( $permissions['view'] )
        {
            $options['userId'] = $this->group->userId;
            $options['groupId'] = $this->groupId;

            $options['cover'] = array(
                'uploader' => OW::getRouter()->urlFor('ZLGHEADER_CTRL_Header', 'uploader'),
                'responder' => OW::getRouter()->urlFor('ZLGHEADER_CTRL_Header', 'rsp'),
                'cover' => $cover,
                'groupId' => $this->groupId,
                'userId' => $this->group->userId,
                'viewOnlyMode' => !$permissions['controls']
            );

            $js = UTIL_JsGenerator::newInstance()->newObject(array('window', 'ZLGHEADER_Header'), 'ZLGHEADER.Header', array($options));

            OW::getDocument()->addOnloadScript($js);
        }
    }
}