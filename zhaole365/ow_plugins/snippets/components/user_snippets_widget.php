<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package snippets.components
 */
class SNIPPETS_CMP_UserSnippetsWidget extends BASE_CLASS_Widget
{
    private $userId;
    
    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $userId = (int) $params->additionalParamList['entityId'];
        
        $snippets = new SNIPPETS_CMP_Snippets(SNIPPETS_CLASS_EventHandler::ENTITY_TYPE_USER, $userId, $params->customParamList["snippets"]);
        
        if ( !$snippets->hasSnippets && !$params->customizeMode )
        {
            $this->setVisible(false);
        }
        else
        {
            $this->addComponent("snippets", $snippets);
        }
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('snippets', 'widget_user_title'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_FILES
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getSettingList()
    {
        $settings = array();
        
        $settings["snippets"] = array(
            "presentation" => self::PRESENTATION_CUSTOM,
            "display" => "custom",
            "name" => "snippets",
            "render" => array("SNIPPETS_CMP_UserSnippetsWidget", "renderSettings")
        );
        
        return $settings;
    }
    
    public static function renderSettings( $widgetName, $filedName, $value )
    {
        $placeData = self::getPlaceData();
        $userId = empty($placeData["entity"]) ? null : $placeData["entity"];

        $settings = new SNIPPETS_CMP_Settings(SNIPPETS_CLASS_EventHandler::ENTITY_TYPE_USER, $userId, $filedName, $value);
        
        return $settings->render();
    }
}