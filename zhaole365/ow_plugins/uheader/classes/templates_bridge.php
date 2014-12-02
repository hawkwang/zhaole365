<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package uheader.classes
 */
class UHEADER_CLASS_TemplatesBridge
{
    const CANVAS_WIDTH = 660;
    
    /**
     * Class instance
     *
     * @var UHEADER_CLASS_TemplatesBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_TemplatesBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $config = array();
    
    /**
     *
     * @var UHEADER_BOL_Service
     */
    private $service;
    
    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private function __construct()
    {
        $this->service = UHEADER_BOL_Service::getInstance();
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
        
        //66 - for 1000px themes
        //84 - for 780px themes
        $scale = (OW::getThemeManager()->getSelectedTheme()->getDto()->sidebarPosition == "none" ? 69 : 88) / 100;
        
        $this->config = OW::getConfig()->getValues("uheader");
        $this->config["cover_height"] *= $scale;
    }
    
    public function addTemplate( $fileName, $roleIds = null, $default = false )
    {
        $canvasWidth = self::CANVAS_WIDTH;
        $canvasHeight = $this->config['cover_height'];

        $coverImage = new UTIL_Image($fileName);
        $imageHeight = $coverImage->getHeight();
        $imageWidth = $coverImage->getWidth();

        $css = array(
            'width' => 'auto',
            'height' => 'auto'
        );

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;

        if ( $tmp >= $canvasHeight )
        {
            $css['width'] = '100%';
        }
        else
        {
            $css['height'] = '100%';
        }

        $template = new UHEADER_BOL_Template();
        $extension = UTIL_File::getExtension($fileName);
        $template->file = uniqid('template-') . '.' . $extension;
        $template->default = $default;
        $template->timeStamp = time();

        $dimensions = array(
            'height' => $imageHeight,
            'width' => $imageWidth
        );

        $template->setSettings(array(
            'dimensions' => $dimensions,
            'css' => $css,
            'canvas' => array(
                'width' => $canvasWidth,
                'height' => $canvasHeight
            ),
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        ));

        $this->service->saveTemplate($template);
        
        if ( $roleIds !== null )
        {
            $this->service->saveRoleIdsForTemplateId($template->id, $roleIds);
        }

        $templatePath = $this->service->getTemplatePath($template);
        OW::getStorage()->copyFile($fileName, $templatePath);
    }
    
    public function addBuiltInCovers( $defaults = true )
    {
        $covers = array("forest.jpg", "bridge.jpg", "grass.jpg", "landscape.jpg", "sea.jpg", "sky.jpg", "feathers.jpg", "abstract-lines.jpg", "abstract-stripes.jpg", "line.jpg");
        $defaultCovers = $defaults ? array("grass.jpg", "bridge.jpg", "forest.jpg") : array();
        foreach ( $covers as $fileName )
        {
            $filePath = $this->plugin->getRootDir() . "covers" . DS . $fileName;
            $this->addTemplate($filePath, null, in_array($fileName, $defaultCovers));
        }
    }
}