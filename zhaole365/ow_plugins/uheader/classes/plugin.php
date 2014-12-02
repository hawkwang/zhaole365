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
 * @package uheader
 */
class UHEADER_CLASS_Plugin
{

    /**
     * Class instance
     *
     * @var UHEADER_CLASS_Plugin
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_Plugin
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    public function __construct()
    {
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }
    
    public function includeStaticFile($file)
    {
        $document = OW::getDocument();
        $staticUrl = $this->plugin->getStaticUrl();
        $ext = UTIL_File::getExtension($file);
        $file .= "?" . $this->plugin->getDto()->build;
        
        switch ( $ext )
        {
            case "css":
                $document->addStyleSheet($staticUrl . $file);
                break;
            
            case "js":
                $document->addScript($staticUrl . $file);
                break;
        }
       
    }
    
    public function includeStatic()
    {
        $this->includeStaticFile("uheader.js");
        $this->includeStaticFile("uheader.css");
        
        OW::getLanguage()->addKeyForJs('uheader', 'delete_cover_confirmation');
        OW::getLanguage()->addKeyForJs('uheader', 'restore_cover_confirmation');
        OW::getLanguage()->addKeyForJs('uheader', 'my_photos_title');
        OW::getLanguage()->addKeyForJs('uheader', 'admin_delete_cover_confirmation');
        OW::getLanguage()->addKeyForJs('base', 'avatar_change');
        OW::getLanguage()->addKeyForJs('base', 'avatar_has_been_approved');
    }
}