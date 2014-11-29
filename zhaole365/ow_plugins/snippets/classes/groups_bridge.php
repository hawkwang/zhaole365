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
class SNIPPETS_CLASS_GroupsBridge
{
    
    const WIDGET_NAME = "groups";
    
    /**
     * Class instance
     *
     * @var SNIPPETS_CLASS_GroupsBridge
     */
    protected static $classInstance;

    /**
     * Returns class instance
     *
     * @return SNIPPETS_CLASS_GroupsBridge
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
        return OW::getPluginManager()->isPluginActive("groups");
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
            $snippet->setLabel($language->text("snippets", "snippet_groups_preview"));
            $snippet->setIconClass("ow_ic_files");
            $event->add($snippet);
            
            return;
        }
        
        // Privacy check
        $eventParams =  array(
            'action' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $exception )
        {
            return;
        }
                
        $service = GROUPS_BOL_Service::getInstance();
        $total = $service->findUserGroupListCount($userId);
        $list = $service->findUserGroupList($userId, 0, 3);

        if ( empty($list) )
        {
            return;
        }
        
        $images = array();
        foreach ( $list as $group )
        {
            $images[] = $service->getGroupImageUrl($group);
        }
        
        $url = OW::getRouter()->urlForRoute("groups-user-groups", array(
            "user" => BOL_UserService::getInstance()->getUserName($userId)
        ));
        
        $snippet->setImages($images);
        $snippet->setLabel($language->text("snippets", "snippet_groups", array(
            "count" => '<span class="ow_txt_value">' . $total . '</span>'
        )));
        $snippet->setUrl($url);
        
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