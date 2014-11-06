<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.google_maps_location.classes
 * @since 1.0
 */

class LOCATIONTAG_CLASS_EventHandler
{
    protected $jsLibAdded = false;
    
    public function __construct()
    {
        
    }
    
    function onBeforeContentAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( !empty($_POST['location_tag_data']) && !empty($data['entityType']) && !empty($data['entityId']) )
        {
            $locationData = json_decode($_POST['location_tag_data'], true);

            if( !empty($locationData['value']) )
            {
                LOCATIONTAG_BOL_LocationService::getInstance()->addLocation($data['entityId'], $data['entityType'], $locationData['value'], $_POST['location_tag_data']);
            }
        }
    }
    
    function onAfterStatusUpdate( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( !empty($_POST['location_tag_data']) && !empty($data['statusId']) )
        {
            $locationData = json_decode($_POST['location_tag_data'], true);

            if( !empty($locationData['value']) )
            {
                LOCATIONTAG_BOL_LocationService::getInstance()->addLocation($data['statusId'], 'user-status', $locationData['value'], $_POST['location_tag_data']);
            }
        }
    }
    
    function addJsLib( OW_Event $e )
    {        
        /* if ( OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            return;
        } */
        if ( !$this->jsLibAdded )
        {
            $languageCode = LOCATIONTAG_BOL_LocationService::getInstance()->getLanguageCode();

            $key = Ow::getConfig()->getValue('locationtag', 'api_key');

            if ( !empty($key) )
            {
                $key = '&key=' . $key;
            }
            
            if ( OW::getPluginManager()->isPluginActive('googlelocation') )
            {
                $key = Ow::getConfig()->getValue('googlelocation', 'api_key');

                if ( !empty($key) )
                {
                    $key = '&key=' . $key;
                }
            }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

            $baseJsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
            OW::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'jquery.js', null, LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'jquery.migrate.js', null, LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'jquery.ui.js', null, LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY);
            
//             OW::getDocument()->addScript($protocol.'maps.google.com/maps/api/js?sensor=false' . trim($key) . '&language=' . $languageCode);
            OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
            
            OW::getDocument()->addOnloadScript('if( !window.map )
                {
                    window.map = {};
                }');
            
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'location_tag_1.js', 'text/javascript', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
            //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'location_tag_baidu.js', 'text/javascript', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
            
            
            $this->jsLibAdded = 1;
        }
    }
    
    function onItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( !empty($data['content']) && !empty($params['action']['entityId'])  )
        {
            $entityId = $params['action']['entityId'];
            
            /* @var $location LOCATIONTAG_BOL_Location */
            $location = LOCATIONTAG_BOL_LocationService::getInstance()->findByEntityIdAndEntityType($entityId, $params['action']['entityType']);

            if ( !empty($location->json) && is_array($data['content']) )
            {
                $address =  OW::getLanguage()->text('locationtag', 'address' , array('address' => $location->address, 'url' => OW::getRouter()->urlForRoute('locationtag_map', array('tagId' => $location->id)) ) );
                
                if ( empty($data['content']) )
                {
                    $data['content'] = array();
                }
                
                if ( empty($data['content']['vars']) )
                {
                    $data['content']['vars'] = array();
                }
                
                $data['content']['vars']['location'] = '
                        <div>
                            <div class="locationtag_address_icon_div">
                                    <span class="locationtag_address_icon ic_locationtag_pin"></span>
                            </div>
                            <div class="location_tag_label_div ow_smallmargin">
                                                <span class="location_tag_label">'. $address .'</span>
                            </div>
                        </div>';
                
                $event->setData($data);
            }
        }

    }
    
    function afterRenderFormat( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( !empty($params['vars']['location'])   )
        {
            if ( empty($data) )
            {
                $data = "";
            }
            
            $data .= $params['vars']['location'];
            $event->setData($data);
        }

    }

    function renderButton( OW_Event $event )
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('locationtag')->getStaticCssUrl() . 'location.css');
        
        $bridge = new LOCATIONTAG_CLASS_StatusUpdateBridge();
        $bridge->addButton();
    }
    
    function renderMobileButton( OW_Event $event )
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('locationtag')->getStaticCssUrl() . 'mobile_location.css');
        
        $bridge = new LOCATIONTAG_CLASS_StatusUpdateBridge();
        $bridge->addMobileButton();
    }
    
    public function genericInit()
    {
        OW::getEventManager()->bind('feed.after_status_update', array($this, 'onAfterStatusUpdate'));
        OW::getEventManager()->bind('feed.before_content_add', array($this, 'onBeforeContentAdd'));
        OW::getEventManager()->bind('locationtag.add_js_lib', array($this, 'addJsLib'));
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onItemRenderActivity'));
        //OW::getEventManager()->bind('feed.before_render_format', array($this, 'onItemRenderActivity'));
        OW::getEventManager()->bind("feed.after_render_format", array($this, "afterRenderFormat"), 9999999999 );
        OW::getEventManager()->bind('feed.get_status_update_cmp', array($this, 'addJsLib'));
        
    }

    public function init()
    {
        OW::getEventManager()->bind('feed.get_status_update_cmp', array($this, 'renderButton'));

    }
    
    public function mobileInit()
    {
        OW::getEventManager()->bind('feed.get_status_update_cmp', array($this, 'renderMobileButton'));
    }
}