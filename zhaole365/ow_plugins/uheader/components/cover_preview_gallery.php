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
class UHEADER_CMP_CoverPreviewGallery extends OW_Component
{
    const ITEM_WIDTH = 175;
    const CANVAS_WIDTH = 660;
    
    const CURRENT_WIDTH = 781;
    
    protected $userId;
    protected $uniqId;
    
    protected $tplId = null;
    protected $scale = 1;
    protected $tabKey;
    
    protected $dimensions = array();

    /**
     *
     * @var UHEADER_BOL_Service
     */
    protected $service;
    
    public function __construct( $userId, $tabKey, $dimensions, $forWidth = self::CURRENT_WIDTH )
    {
        parent::__construct();
        
        $this->userId = $userId;
        $this->service = UHEADER_BOL_Service::getInstance();
        
        $this->uniqId = uniqid("uheader-cg-");
        
        $this->tabKey = $tabKey;
                
        if ( !empty($forWidth) )
        {
            $this->scale = self::CANVAS_WIDTH / $forWidth;
        }
        
        $this->dimensions = $dimensions;
    }
    
    protected function getTplInfo( UHEADER_BOL_Template $template ) 
    {
        $canvas = $template->getCanvas(self::CANVAS_WIDTH);
        $previewCanvas = $template->getCanvas(self::ITEM_WIDTH);
        
        $css = $template->getCss();
        $cssStr = $template->getCssString();
        
        return array(
            "id" => $template->id,
            "default" => (bool) $template->default,
            "src" => $this->service->getTemplateUrl($template),
            "data" => $template->getSettings(),
            "css" => $css,
            "cssStr" => $cssStr,
            "canvas" => $canvas,
            "previewCss" => $css,
            "previewCssStr" => $cssStr,
            "previewCanvas" => $previewCanvas,
            
            "users" => rand(1, 100)
        );
    }
    
    public function getTemplateList()
    {
        $templateList = $this->service->findTemplateListForUserId($this->userId);
        $tplTemplates = array();
        
        foreach ( $templateList as $tpl )
        {
            $tplTemplates[] = $this->getTplInfo($tpl);
        }
        
        return $tplTemplates;
    }
    
    public function getConfig()
    {
        $config = array();
        $config['avatarSize'] = OW::getConfig()->getValue("base", "avatar_big_size") * $this->scale;
        $config['coverHeight'] = OW::getConfig()->getValue("uheader", "cover_height");
        $config['coverWidth'] = self::CANVAS_WIDTH;
        
        $config['previewHeight'] = self::ITEM_WIDTH / self::CANVAS_WIDTH * $config['coverHeight'];
        $config['previewWidth'] = self::ITEM_WIDTH;
        
        
        return $config;
    }
    
    public function getUser()
    {
        $user = array();
        
        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($this->userId, 2);
        $user['avatar'] =  $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);
        $user['displayName'] = BOL_UserService::getInstance()->getDisplayName($this->userId);
        
        return $user;
    }
    
    public function getInfoLines()
    {
        return array(
            UHEADER_BOL_Service::INFO_LINE1 => UHEADER_BOL_Service::getInstance()->getInfoLine($this->userId, UHEADER_BOL_Service::INFO_LINE1),
            UHEADER_BOL_Service::INFO_LINE2 => UHEADER_BOL_Service::getInstance()->getInfoLine($this->userId, UHEADER_BOL_Service::INFO_LINE2)
        );
    }
    
    public function initJs( $currentItem )
    {
        $settings = array(
            "rsp" => OW::getRouter()->urlFor("UHEADER_CTRL_Header", "rsp"),
            "current" => $currentItem
        );
        
        $js = UTIL_JsGenerator::composeJsString('UHEADER.GallerySwitcher.registerTab({$tabKey}, new UHEADER.TemplatePreviewGallery({$params}, _scope));', array(
            'params' => array(
                'userId' => $this->userId,
                'tabKey' => $this->tabKey,
                'settings' => $settings,
                'uniqId' => $this->uniqId,
                "dimensions" => $this->dimensions
            ),
            "tabKey" => $this->tabKey
        ));

        OW::getDocument()->addOnloadScript($js);
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->assign("uniqId", $this->uniqId);
        
        $tplList = $this->getTemplateList();
        $currentItem = $this->tplId === null || empty($tplList[$this->tplId]) ? reset($tplList) : $tplList[$this->tplId];
        
        $this->assign("list", $tplList);
        $this->assign("current", $currentItem);
        $this->assign("config", $this->getConfig());
        $this->assign("user", $this->getUser());
        $this->assign("dimensions", $this->dimensions);
        $this->assign("infoLines", $this->getInfoLines());
        
        $this->initJs($currentItem);
    }
}
