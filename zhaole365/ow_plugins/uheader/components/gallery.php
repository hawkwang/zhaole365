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
class UHEADER_CMP_Gallery extends OW_Component
{
    protected $userId;
    protected $uniqId;
    
    protected $options = array();
    
    public function __construct( $userId, $options )
    {
        parent::__construct();
        
        $this->userId = $userId;
        $this->uniqId = uniqid("uhg-");
        
        $this->options = $options;
    }
    
    protected function getDimensions()
    {
        $dimensions = array();
        $dimensions["height"] = $this->options["winHeight"] - 250;
        
        $dimensions["height"] = $dimensions["height"] < 400
                ? 400 : $dimensions["height"];
        
        $dimensions["height"] = $dimensions["height"] > 583
                ? 583 : $dimensions["height"];
        
        return $dimensions;
    }
    
    protected function getTabs()
    {
        $language = OW::getLanguage();
        $service = UHEADER_BOL_Service::getInstance();
        $photoBridge = UHEADER_CLASS_PhotoBridge::getInstance();
        
        $templatesCount = $service->findTemplatesCountForUser($this->userId);
        
        if ( $templatesCount > 0 || !$photoBridge->isActive() )
        {
            $activeTab = "gallery";
        }
        else 
        {
            $activeTab = "photos";
        }
        
        $tabs = array();
        
        if ( $templatesCount > 0 )
        {
            $tabKey = "gallery";
            
            $dimensions = $this->getDimensions();
            //$dimensions["height"] -= 45;
            
            if ( OW::getConfig()->getValue('uheader', 'tpl_view_mode') == "list" )
            {
                $coverGallery = new UHEADER_CMP_CoverGallery($this->userId, $tabKey, $dimensions);
            }
            else
            {
                $coverGallery = new UHEADER_CMP_CoverPreviewGallery($this->userId, $tabKey, $dimensions);
            }
            
            $coverGallery->assign("dimensions", $this->getDimensions());

            $tabs[] = array(
                "label" => $language->text("uheader", "gallery_tab_gallery"),
                "key" => $tabKey,
                "active" => $tabKey == $activeTab,
                "content" => $coverGallery->render()
            );
        }
        
        $tabKey = "photos";
        $photoList = new UHEADER_CMP_MyPhotos($this->userId, $tabKey);
        
        $tabs[] = array(
            "label" => $language->text("uheader", "gallery_tab_photos"),
            "key" => $tabKey,
            "active" => $tabKey == $activeTab,
            "content" => $photoList->render()
        );
        
        return $tabs;
    }


    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $options = array();
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array("UHEADER.GallerySwitcher", "init"), array($this->uniqId, $options));
        
        OW::getDocument()->addOnloadScript($js);
        
        $this->assign("uniqId", $this->uniqId);
        $this->assign("tabs", $this->getTabs());
        $this->assign("dimensions", $this->getDimensions());
    }
}
