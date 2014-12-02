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
class UHEADER_CMP_CoverGallery extends OW_Component
{
    const ITEM_WIDTH = 266;
    
    protected $userId;
    protected $tabKey;
    protected $dimensions;
    
    /**
     *
     * @var UHEADER_BOL_Service
     */
    protected $service;
    
    public function __construct( $userId, $tabKey, $dimensions )
    {
        parent::__construct();
        
        $this->userId = $userId;
        $this->tabKey = $tabKey;
        $this->service = UHEADER_BOL_Service::getInstance();
        $this->dimensions = $dimensions;
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $templateList = $this->service->findTemplateListForUserId($this->userId);
        $tplTemplates = array();
        
        foreach ( $templateList as $tpl )
        {
            /*@var $tpl UHEADER_BOL_Template */
            
            $settings = $tpl->getSettings();
            
            $template = array(
                "id" => $tpl->id,
                "src" => $this->service->getTemplateUrl($tpl),
                "css" => $tpl->getCssString(),
                "canvas" => $tpl->getCanvas(self::ITEM_WIDTH)
            );
            
            $tplTemplates[] = $template;
        }
        
        $this->assign("templates", $tplTemplates);
        $this->assign("dimensions", $this->dimensions);
        
        $js = UTIL_JsGenerator::composeJsString('UHEADER.GallerySwitcher.registerTab({$tabKey}, new UHEADER.TemplateGallery({$params}, _scope));', array(
            'params' => array(
                'userId' => $this->userId,
                'tabKey' => $this->tabKey,
                "dimensions" => $this->dimensions
            ),
            "tabKey" => $this->tabKey
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}
