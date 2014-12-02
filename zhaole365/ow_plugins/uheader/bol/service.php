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
 * @package uheader.bol
 */
class UHEADER_BOL_Service
{

    const EVENT_ADD = 'uheader.after_cover_add';
    const EVENT_UPDATE = 'uheader.after_cover_upddate';
    const EVENT_CHANGE = 'uheader.after_cover_change';
    const EVENT_REMOVE = 'uheader.after_cover_remove';
    
    const EVENT_INFO_RENDER = "uheader.info_line_render";
    const EVENT_COLLECT_INFO_CONFIG = 'uheader.collect_info_line_config';
    const EVENT_INFO_PREVIEW = 'uheader.info_line_preview';
    
    const INFO_LINE1 = "line1";
    const INFO_LINE2 = "line2";
    
    const TEMPLATE_ITEM_WIDTH = 175;

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var UHEADER_BOL_CoverDao
     */
    private $coverDao;
    
    /**
     *
     * @var UHEADER_BOL_TemplateDao
     */
    private $templateDao;

    /**
     *
     * @var UHEADER_BOL_TemplateRoleDao 
     */
    private $templateRoleDao;
    
    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private function __construct()
    {
        $this->coverDao = UHEADER_BOL_CoverDao::getInstance();
        $this->templateDao = UHEADER_BOL_TemplateDao::getInstance();
        $this->templateRoleDao = UHEADER_BOL_TemplateRoleDao::getInstance();
        
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    /**
     *
     * @param int $userId
     * @param type $status
     * @return UHEADER_BOL_Cover
     */
    public function findCoverByUserId( $userId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        return $this->coverDao->findByUserId($userId, $status);
    }
    
    /**
     *
     * @param int $id
     * @return UHEADER_BOL_Cover
     */
    public function findCoverById( $id )
    {
        return $this->coverDao->findById($id);
    }
    
    public function findCoverListByUserId( $userId, $status = null )
    {
        return $this->coverDao->findListByUserId($userId, $status);
    }

    public function saveCover( UHEADER_BOL_Cover $cover )
    {
        $this->coverDao->save($cover);
    }

    public function deleteCover( UHEADER_BOL_Cover $cover )
    {
        $coverPath = $this->getCoverPath($cover);

        $event = new OW_Event(UHEADER_BOL_Service::EVENT_REMOVE, array(
            'userId' => $cover->userId,
            'id' => $cover->id,
            'file' => $cover->file,
            'status' => $cover->status,
            'path' => $coverPath,
            'src' => $this->getCoverUrl($cover),
            'data' => $cover->getSettings(),
            'templateId' => $cover->templateId
        ));

        OW::getEventManager()->trigger($event);

        $this->coverDao->deleteById($cover->id);
        
        if ( $cover->templateId === null )
        {
            OW::getStorage()->removeFile($coverPath);
        }
    }

    public function deleteCoverByUserId( $userId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $cover = $this->findCoverByUserId($userId, $status);

        if ( $cover === null )
        {
            return;
        }

        $this->deleteCover($cover);
    }
    
    public function deleteAllUserCovers( $userId )
    {
        $covers = $this->findCoverListByUserId($userId);
        
        foreach ( $covers as $cover )
        {
            $this->deleteCover($cover);
        }
    }

    public function getCoverPath( UHEADER_BOL_CoverBase $cover )
    {
        return $this->plugin->getUserFilesDir() . $cover->file;
    }

    public function getCoverUrl( UHEADER_BOL_CoverBase $cover )
    {
        return OW::getStorage()->getFileUrl($this->getCoverPath($cover));
    }

    
    // Templates
    
    public function saveTemplate( UHEADER_BOL_Template $tpl )
    {
        return $this->templateDao->save($tpl);
    }
    
    public function getTemplatePath( UHEADER_BOL_Template $tpl )
    {
        return $this->getCoverPath($tpl);
    }

    public function getTemplateUrl( UHEADER_BOL_Template $tpl )
    {
        return $this->getCoverUrl($tpl);
    }
    
    public function getTemplateInfo( UHEADER_BOL_Template $template, $itemWidth = self::TEMPLATE_ITEM_WIDTH ) 
    {
        $canvas = $template->getCanvas();
        $previewCanvas = $template->getCanvas($itemWidth);
        
        $css = $template->getCss();
        $cssStr = $template->getCssString();
        $roles = $this->getRoleIdsForTemplateId($template->id);
        
        return array(
            "id" => $template->id,
            "default" => (bool) $template->default,
            "src" => $this->getTemplateUrl($template),
            "data" => $template->getSettings(),
            "css" => $css,
            "cssStr" => $cssStr,
            "canvas" => $canvas,
            "previewCss" => $css,
            "previewCssStr" => $cssStr,
            "previewCanvas" => $previewCanvas,
            
            "users" => $this->findTemplateUsageCount($template->id),
            "roles" => $roles,
            "url" => OW::getRouter()->urlForRoute("uheader-settings-gallery-item", array(
                "tplId" => $template->id
            ))
        );
    }
    
    private function getRoleListForUserId( $userId )
    {
        $roles = BOL_AuthorizationService::getInstance()->findUserRoleList($userId);
        $roleIds = array();
        
        foreach ( $roles as $role )
        {
            /*@var $role BOL_AuthorizationRole */
            $roleIds[] = $role->id;
        }
        
        return $roleIds;
    }
    
    /**
     * 
     * @param int $userId
     * @return UHEADER_BOL_Template
     */
    public function findDefaultTemplateForUser( $userId )
    {
        $roleIds = $this->getRoleListForUserId($userId);
        
        return $this->templateDao->findDefaultForRoleIds($roleIds);
    }
    
    public function findTemplatesCountForUser( $userId, $default = false ) 
    {
        $roleIds = $this->getRoleListForUserId($userId);
        return $this->templateDao->findCountForRoleIds($roleIds, $default);
    }
    
    public function findTemplateList( $roleIds = null )
    {
        if ( $roleIds === null )
        {
            return $this->templateDao->findListForAllUsers();
        }
        
        return $this->templateDao->findListForRoleIds($roleIds);
    }
    
    public function findTemplateListForUserId( $userId )
    {
        $roleIds = $this->getRoleListForUserId($userId);
        
        return $this->findTemplateList($roleIds);
    }
    
    public function findTemplateById( $templateId )
    {
        return $this->templateDao->findById($templateId);
    }
    
    public function findTemplateUsageCount( $templateId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        return $this->coverDao->findCountByTemplateId($templateId);
    }
    
    public function deleteTemplateById( $templateId )
    {
        $template = $this->findTemplateById($templateId);
        $this->templateRoleDao->deleteByTemplateId($templateId);
        $this->templateDao->deleteById($templateId);
        $this->deleteCoversByTemplateId($templateId);
        
        $tplPath = $this->getTemplatePath($template);
        
        OW::getStorage()->removeFile($tplPath);
    }
    
    public function deleteCoversByTemplateId( $templateId )
    {
        $covers = $this->coverDao->findByTemplateId($templateId);
        foreach ( $covers as $cover )
        {
            $this->deleteCover($cover);
        }
    }
    
    public function getRoleIdsForTemplateId( $templateId )
    {
        $tmpRoles = $this->templateRoleDao->findByTemplateId($templateId);
        
        $roleIds = array();
        if ( empty($tmpRoles) )
        {
            $allRoles = BOL_AuthorizationService::getInstance()->findNonGuestRoleList();
            
            foreach ( $allRoles as $role )
            {
                $roleIds[] = $role->id;
            }
        }
        else
        {
            foreach ( $tmpRoles as $role )
            {
                $roleIds[] = $role->roleId;
            }
        }
        
        return $roleIds;
    }
    
    public function findRoleIdsByTemplateId( $templateId )
    {
        $tmpRoles = $this->templateRoleDao->findByTemplateId($templateId);
        
        $roleIds = array();
        foreach ( $tmpRoles as $role )
        {
            $roleIds[] = $role->roleId;
        }

        return $roleIds;
    }
    
    public function saveRoleIdsForTemplateId( $templateId, $roleIds )
    {
        $allRoles = BOL_AuthorizationService::getInstance()->findNonGuestRoleList();
        $allRoleIds = array();
        foreach ( $allRoles as $role )
        {
            $allRoleIds[] = $role->id;
        }
        
        $this->templateRoleDao->deleteByTemplateId($templateId);
        
        if ( !array_diff($allRoleIds, $roleIds) ) {
            return;
        }
        
        foreach ( $roleIds as $roleId )
        {
            $tmpRoleDto = new UHEADER_BOL_TemplateRole();
            $tmpRoleDto->templateId = $templateId;
            $tmpRoleDto->roleId = $roleId;
            
            $this->templateRoleDao->save($tmpRoleDto);
        }
    }
    
    public function getConfig( $name )
    {
        return OW::getConfig()->getValue("uheader", $name);
    }

    public function saveConfig( $name, $value )
    {
        if ( OW::getConfig()->configExists("uheader", $name) )
        {
            OW::getConfig()->saveConfig("uheader", $name, $value);
        }
        else
        {
            OW::getConfig()->addConfig("uheader", $name, $value);
        }
    }
    
    public function getInfoLineSettings( $line )
    {
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_INFO_CONFIG, array(
            "line" => $line
        ));

        OW::getEventManager()->trigger($event);
        
        $out = array();
        
        foreach ( $event->getData() as $info )
        {
            if ( empty($info["key"]) )
            {
                continue;
            }

            $key = $info["key"];

            $out[$key] = $info;
        }

        return $out;
    }
    
    public function getInfoLinePreview( $key, $question, $line )
    {
        $event = new OW_Event(self::EVENT_INFO_PREVIEW, array(
            "key" => $key,
            "question" => $question,
            "line" => $line
        ));
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function getInfoLine( $userId, $line )
    {
        $infoConfig = $this->getInfoConfig($line);
        
        $key = empty($infoConfig["key"]) ? null : $infoConfig["key"];
        $question = empty($infoConfig["question"]) ? null : $infoConfig["question"];
        
        if ( $key != "base-question" )
        {
            $question = null;
        }
        
        $event = new OW_Event(self::EVENT_INFO_RENDER, array(
            "userId" => $userId,
            "key" => $key,
            "question" => $question,
            "line" => $line
        ));
        
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }
    
    public function saveInfoConfig( $line, $key, $question = null )
    {
        $value = array(
            "key" => $key,
            "question" => $question
        );
        
        $this->saveConfig("info_" . $line, json_encode($value));
    }
    
    public function getInfoConfig( $line )
    {
        $value = $this->getConfig("info_" . $line);
        
        if ( $value === null )
        {
            return null;
        }
        
        return json_decode($value, true);
    }
}