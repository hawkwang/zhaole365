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
 * @package uheader.components
 */
class UHEADER_CMP_Header extends OW_Component
{
    private $userId;

    /**
     *
     * @var UHEADER_BOL_Service
     */
    private $service;
    
    /**
     *
     * @var UHEADER_BOL_CoverBase
     */
    private $cover = null;
    private $hasCover = false;
    private $defaultTemplatesCount = 0;
    private $defaultTemplateMode = false;
    
    private $templateId = null;

    public function __construct( $userId )
    {
        parent::__construct();

        UHEADER_CLASS_Plugin::getInstance()->includeStatic();
        
        $this->userId = $userId;
        $this->service = UHEADER_BOL_Service::getInstance();
        
        $this->cover = $this->service->findCoverByUserId($this->userId, UHEADER_BOL_Cover::STATUS_ACTIVE);
        
        if ( $this->cover === null )
        {
            $removedCover = $this->service->findCoverByUserId($this->userId, UHEADER_BOL_Cover::STATUS_REMOVED);
            if ( $removedCover === null )
            {
                $template = $this->service->findDefaultTemplateForUser($userId);
                
                if ( $template !== null )
                {
                    $this->cover = $template->createCover($this->userId);
                    $this->templateId = $template->id;
                    $this->defaultTemplateMode = true;
                }
            }
        }
        else
        {
            $this->hasCover = true;
        }
        
        $this->defaultTemplatesCount = $this->service->findTemplatesCountForUser($this->userId, true);
    }

    private function getUserInfo()
    {
        $permissions = $this->getPemissions();
        $user = array();

        $user['id'] = $this->userId;
        
        $onlineUser = BOL_UserService::getInstance()->findOnlineUserById($this->userId);
        $user['isOnline'] = $onlineUser !== null;

        $avatarDto = BOL_AvatarService::getInstance()->findByUserId($this->userId);
        
        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($this->userId, 2, null, false, !$permissions['viewAvatar']);
        $user['avatar'] =  $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);
        $user["avatarApproved"] = $avatar ? $avatarDto->status == "active" : true;
        $user['avatarId'] = $avatarDto ? $avatarDto->id : false; // FIXME, 这是一个bug
        
        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($this->userId));

        $user['role'] = !empty($roles[$this->userId]) ? $roles[$this->userId] : null;

        $user['displayName'] = BOL_UserService::getInstance()->getDisplayName($this->userId);
        
        $user["photosUrl"] = UHEADER_CLASS_UavatarsBridge::getInstance()->hasHistory($this->userId)
            ? null
            : UHEADER_CLASS_PhotoBridge::getInstance()->getUserPhotosUrl($this->userId);
        
        return $user;
    }

    private function getConfig()
    {
        $config = array();

        $config['avatarSize'] = OW::getConfig()->getValue('base', 'avatar_big_size');
        $config['coverHeight'] = OW::getConfig()->getValue('uheader', 'cover_height');

        return $config;
    }

    /**
     *
     * @return BASE_CMP_ContextAction
     */
    private function getContextToolbar( $hasCover )
    {
        $language = OW::getLanguage();
        $permissions = $this->getPemissions();

        $contextActionMenu = new BASE_CMP_ContextAction();
        
        if ($hasCover)
        {
            $contextActionMenu->setClass("ow_photo_context_action");
        }
        
        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('uheaderToolbar');
        $contextParentAction->setLabel('<span class="uh-toolbar-add-label">' . $language->text('uheader', 'set_covet_label') . '</span><span class="uh-toolbar-edit-label">' . $language->text('uheader', 'change_covet_label') . '</span>');
        $contextParentAction->setId('uh-toolbar-parent');
        
        $contextActionMenu->addAction($contextParentAction);

        if ( $permissions['choose'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text("uheader", "choose_from_gallery_label"));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhGallery');
            $contextAction->setId('uhco-gallery');
            $contextAction->setClass('uhco-item uhco-gallery');
            $contextAction->setOrder(1);

            $contextActionMenu->addAction($contextAction);
        }
        
        if ( $permissions['add'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel('<div class="uh-fake-file"><div>' . $language->text('uheader', 'upload_label') . '</div><input type="file" name="file" id="uh-upload-cover" size="1" /></div>');
            //$contextAction->setLabel($language->text('uheader', 'upload_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhUpload');
            $contextAction->setId('uhco-upload');
            $contextAction->setClass('uhco-item uhco-upload');
            $contextAction->setOrder(2);

            $contextActionMenu->addAction($contextAction);
        }
        
        if ( $permissions['reposition'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text('uheader', 'reposition_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhReposition');
            $contextAction->setId('uhco-reposition');
            $contextAction->setClass('uhco-item uhco-reposition');
            $contextAction->setOrder(3);

            $contextActionMenu->addAction($contextAction);
        }
        
        if ( $permissions['stick'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text('uheader', 'stick_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhStick');
            $contextAction->setId('uhco-stick');
            $contextAction->setClass('uhco-item uhco-stick');

            $contextAction->setOrder(4);

            $contextActionMenu->addAction($contextAction);
        }

        if ( $this->defaultTemplatesCount > 0 )
        {
            if ( $permissions['restore'] )
            {
                $contextAction = new BASE_ContextAction();
                $contextAction->setParentKey($contextParentAction->getKey());
                $contextAction->setLabel($language->text("uheader", "restore_default_label"));
                $contextAction->setUrl('javascript://');
                $contextAction->setKey('uhRestoreDefault');
                $contextAction->setId('uhco-restore-default');
                $contextAction->setClass('uhco-item uhco-restore-default');
                $contextAction->setOrder(5);

                $contextActionMenu->addAction($contextAction);
            }
        }
        else if ( $permissions['delete'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text("uheader", "remove_label"));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhRemove');
            $contextAction->setId('uhco-remove');
            $contextAction->setClass('uhco-item uhco-remove');
            $contextAction->setOrder(5);

            $contextActionMenu->addAction($contextAction);
        }
                
        /*if ( $permissions['delete'] )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($language->text('uheader', 'remove_label'));
            $contextAction->setUrl('javascript://');
            $contextAction->setKey('uhRemove');
            $contextAction->setId('uhco-remove');
            $contextAction->setClass('uhco-item uhco-remove');

            $contextAction->setOrder(4);

            $contextActionMenu->addAction($contextAction);
        }*/
        
        return $contextActionMenu;
    }

    public function getPemissions()
    {
        static $permissions = null;
        
        if ( $permissions !== null )
        {
            return $permissions;
        }
        
        $permissions = array(
            "choose" => false,
            "stick" => false,
            'add' => false,
            'reposition' => false,
            'restore' => false,
            'delete' => false,
            'view' => false,
            'changeAvatar' => false,
            'moderation' => false,
            'viewAvatar' => false
        );

        $selfMode = $this->userId == OW::getUser()->getId();
        $moderationMode = OW::getUser()->isAuthorized('uheader');
        
        $credits = UHEADER_CLASS_CreditsBridge::getInstance()->credits;
        
        $choose = UHEADER_CLASS_PhotoBridge::getInstance()->isActive()
                || $this->service->findTemplatesCountForUser($this->userId) > 0;

        
        $permissions['changeAvatar'] = $selfMode;

        if ( $selfMode || $moderationMode )
        {
            $permissions['delete'] = true;
            $permissions['view'] = true;
            $permissions["restore"] = true;
        }

        if ( $selfMode && $credits->isAvaliable('add_cover') )
        {
            $permissions['reposition'] = true;
            $permissions['add'] = true;
            $permissions['choose'] = $choose;
            $permissions['stick'] = $this->defaultTemplatesCount > 1;
        }

        if ( !$permissions['view'] && OW::getUser()->isAuthorized('uheader', 'view_cover') )
        {
            $permissions['view'] = UHEADER_CLASS_PrivacyBridge::getInstance()->checkPrivacy($this->userId);
        }
        
        if ( $this->hasCover )
        {
            $permissions['controls'] = ($permissions['add']
                || $permissions['reposition']
                || $permissions['delete']
                || $permissions['restore']
                || $permissions['choose'])
                && $permissions['view'];
        }
        else
        {
            $permissions['controls'] = ($permissions['add']
                || $permissions['choose']
                || $permissions["stick"])
                && $permissions['view'];
        }

        
        $permissions['approveAvatar'] = OW::getUser()->isAuthorized('base');
        $permissions['viewAvatar'] = $selfMode || $moderationMode;
        
        return $permissions;
    }

    public function getCover()
    {
        $permissions = $this->getPemissions();

        /*@var $cover UHEADER_BOL_CoverBase */
        $cover = $permissions['view']
            ? $this->cover
            : null;
        
        if ( $cover === null )
        {
            return array(
                'hasCover' => false,
                'src' => null,
                'data' => array(),
                'css' => '',
                'scale' => 0,
                'tempateId' => $this->templateId,
                'defaultTempateId' => $this->templateId,
                "class" => "uh-cover-no-cover"
            );
        }
        
        $classList = array();
        $classList[] = "uh-cover-has-cover";
        if ( $this->templateId !== null )
        {
            $classList[] = "uh-is-default-template";
        }
        
        if ( $this->defaultTemplatesCount > 0 )
        {
            $classList[] = "uh-has-default-templates";
        }
        
        return array(
            'hasCover' => true,
            'src' => $this->service->getCoverUrl($cover),
            'data' => $cover->getSettings(),
            'css' => $cover->getCssString(),
            'scale' => $cover->getRatio(),
            'templateId' => $this->templateId,
            "templateMode" => $this->templateId !== null,
            "class" => implode(" ", $classList)
        );
    }
    
    public function getInfoLines()
    {
        return array(
            UHEADER_BOL_Service::INFO_LINE1 => UHEADER_BOL_Service::getInstance()->getInfoLine($this->userId, UHEADER_BOL_Service::INFO_LINE1),
            UHEADER_BOL_Service::INFO_LINE2 => UHEADER_BOL_Service::getInstance()->getInfoLine($this->userId, UHEADER_BOL_Service::INFO_LINE2)
        );
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $permissions = $this->getPemissions();

        $toolbar = new UHEADER_CMP_ActionToolbar($this->userId);
        $this->addComponent('actionToolbar', $toolbar);

        $cover = $this->getCover();
        $this->assign('cover', $cover);

        if ( $permissions['controls'] )
        {
            $contextToolbar = $this->getContextToolbar($cover["hasCover"]);
            $this->addComponent('contextToolbar', $contextToolbar);
        }

        $userInfo = $this->getUserInfo();
        $this->assign('user', $userInfo);
        $this->assign('config', $this->getConfig());
        $this->assign('info', $this->getInfoLines());
        
        $this->assign('permissions', $permissions);

        $options = array();

        if ( $permissions['view'] )
        {
            $options['userId'] = $this->userId;
            
            $options['cover'] = array(
                'uploader' => OW::getRouter()->urlFor('UHEADER_CTRL_Header', 'uploader'),
                'responder' => OW::getRouter()->urlFor('UHEADER_CTRL_Header', 'rsp'),
                'cover' => $cover,
                'userId' => $this->userId,
                'viewOnlyMode' => !$permissions['controls'],
                "templateId" => $this->templateId,
                "defaultTemplateMode" => $this->defaultTemplateMode
            );

            $options["avatar"] = array(
                "id" => $userInfo["avatarId"],
                "approveRsp" => OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')
            );
            
            $js = UTIL_JsGenerator::newInstance()->newObject(array('window', 'UHEADER_Header'), 'UHEADER.Header', array($options));

            OW::getDocument()->addOnloadScript($js);
        }
    }
}