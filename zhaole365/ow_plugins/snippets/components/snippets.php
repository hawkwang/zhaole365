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
class SNIPPETS_CMP_Snippets extends OW_Component
{
    protected $uniqId;
    protected $entityType;
    protected $entityId;
    protected $settings;
    
    protected $snippets = array();
    
    public $hasSnippets = false;

    public function __construct( $entityType, $entityId, $settings ) 
    {
        parent::__construct();
        
        $this->uniqId = uniqid("snippets-");
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        
        $this->settings = empty($settings) ? array(
            "hidden" => array(),
            "active" => array()
        ) : json_decode($settings, true);
        
        $this->snippets = $this->getSnippets();
        $this->hasSnippets = !empty($this->snippets);
    }
    
    private function initStatic()
    {
        $plugin = OW::getPluginManager()->getPlugin("snippets");
        $staticUrl = $plugin->getStaticUrl();
        
        OW::getDocument()->addScript($staticUrl . "script.js?" . $plugin->getDto()->build);
        OW::getDocument()->addStyleSheet($staticUrl . "style.css?" . $plugin->getDto()->build);
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array("SNIPPETS", "snippets"), array(
            $this->uniqId
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function getSnippets()
    {
        $snippets = SNIPPETS_BOL_Service::getInstance()->getSnippets($this->entityType, $this->entityId, $this->settings, false);
        
        return empty($snippets["active"]) ? array() : $snippets["active"];
    }
    
    public function onBeforeRender() {
        parent::onBeforeRender();
        
        $this->assign("uniqId", $this->uniqId);
        $this->assign("snippets", $this->snippets);
        
        $this->initStatic();
    }
}