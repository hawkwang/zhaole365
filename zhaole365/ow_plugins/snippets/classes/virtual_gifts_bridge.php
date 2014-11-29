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
 * @package snippets.classes
 */
class SNIPPETS_CLASS_VirtualGiftsBridge
{
    
    const WIDGET_NAME = "virtual_gifts";
    
    /**
     * Class instance
     *
     * @var SNIPPETS_CLASS_VirtualGiftsBridge
     */
    protected static $classInstance;

    /**
     * Returns class instance
     *
     * @return SNIPPETS_CLASS_VirtualGiftsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        
    }
    
    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive("virtualgifts");
    }
    
    public function collectSnippets( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != SNIPPETS_CLASS_EventHandler::ENTITY_TYPE_USER )
        {
            return;
        }
        
        $userId = $params["entityId"];
        $preview = $params["preview"];
        
        $snippet = new SNIPPETS_CMP_Snippet(self::WIDGET_NAME, $userId);
        
        if ( $preview )
        {
            $snippet->setLabel($language->text("snippets", "snippet_virtual_gifts_preview"));
            $snippet->setIconClass("ow_ic_birthday");
            $event->add($snippet);
            
            return;
        }
        
        $service = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();
        
        $total = $service->countUserReceivedGifts($userId);
        $list = $service->findUserReceivedGifts($userId, 1, 4);

        if ( empty($list) )
        {
            return;
        }
        
        $images = array();
        foreach ( $list as $gift )
        {
            $images[] = $gift["imageUrl"];
        }
        
        $dispslayType = count($images) > 1 ? SNIPPETS_CMP_Snippet::DISPLAY_TYPE_4 : SNIPPETS_CMP_Snippet::DISPLAY_TYPE_1;
        
        $url = OW::getRouter()->urlForRoute('virtual_gifts_user_list', array(
            "userName" => BOL_UserService::getInstance()->getUserName($userId)
        ));
        
        $snippet->setImages($images);
        $snippet->setLabel($language->text("snippets", "snippet_virtual_gifts", array(
            "count" => '<span class="ow_txt_value">' . $total . '</span>'
        )));
        $snippet->setUrl($url);
        $snippet->setDisplayType($dispslayType);
        
        $event->add($snippet);
    }
    
    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
        
        OW::getEventManager()->bind(SNIPPETS_CLASS_EventHandler::EVENT_COLLECT_SNIPPETS, array($this, "collectSnippets"));
    }
}