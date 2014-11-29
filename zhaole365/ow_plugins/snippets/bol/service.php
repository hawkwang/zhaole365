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
 * @package snippets.bol
 */
class SNIPPETS_BOL_Service
{

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return SNIPPETS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {
    
    }

    public function getSnippets( $entityType, $entityId, $order, $preview = false )
    {
        $event = new BASE_CLASS_EventCollector(SNIPPETS_CLASS_EventHandler::EVENT_COLLECT_SNIPPETS, array(
            "entityType" => $entityType,
            "entityId" => $entityId,
            "preview" => $preview
        ));
        
        OW::getEventManager()->trigger($event);
        
        $result = array(
            "hidden" => array(),
            "active" => array()
        );
                
        $snippets = array();
        
        foreach ( $event->getData() as $snippet )
        {
            /* @var $snippet SNIPPETS_CMP_Snippet */
            
            $snippet->setIsPreview($preview);
            
            $snippets[$snippet->getName()] = array(
                "html" => $snippet->render(),
                "name" => $snippet->getName()
            );
        }
        
        foreach ( $order["hidden"] as $name )
        {
            if ( !empty($snippets[$name]) )
            {
                $result["hidden"][] = $snippets[$name];
                unset($snippets[$name]);
            }
            
        }
        
        foreach ( $order["active"] as $name )
        {
            if ( !empty($snippets[$name]) )
            {
                $result["active"][] = $snippets[$name];
                unset($snippets[$name]);
            }
        }
        
        foreach ( $snippets as $snippet )
        {
            $result["active"][] = $snippet;
        }
        
        return $result;
    }
}