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
class SNIPPETS_CMP_Snippet extends OW_Component
{
    const DISPLAY_TYPE_1 = 1;
    const DISPLAY_TYPE_3 = 3;
    const DISPLAY_TYPE_4 = 4;
    
    protected $isPreview = false;
    protected $entityId;
    
    protected $images = array();
    protected $label = "";
    protected $url = "#";

    protected $name;
    protected $displayType = null;
    protected $iconClass;

    public function __construct( $snippetName, $entityId = null )
    {
        parent::__construct();
        
        $this->entityId = $entityId;
        $this->name = $snippetName;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setIsPreview( $yes = true )
    {
        $this->isPreview = $yes;
    }
    
    public function setIconClass( $iconClass )
    {
        $this->iconClass = $iconClass;
    }

    public function setImages( $images )
    {
        $this->images = $images;
    }
    
    public function setDisplayType( $displayType )
    {
        $this->displayType = $displayType;
    }
    
    public function setLabel( $label )
    {
        $this->label = $label;
    }
    
    public function setUrl( $url )
    {
        $this->url = $url;
    }
    
    public function beforePreviewRender()
    {
        $plugin = OW::getPluginManager()->getPlugin("snippets");
        $template = OW::getAutoloader()->classToFilename(get_class($this), false);
        
        $this->setTemplate($plugin->getCmpViewDir() . $template . '_preview.html');
    }
    
    public function beforeContentRender()
    {
        $plugin = OW::getPluginManager()->getPlugin("snippets");
        $template = OW::getAutoloader()->classToFilename(get_class($this), false);
        
        $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        if ($this->isPreview)
        {
            $this->beforePreviewRender();
        } 
        else 
        {
            $this->beforeContentRender();
        }
    }
    
    public function render()
    {
        $this->assign("images", $this->images);
        $this->assign("url", $this->url);
        $this->assign("label", $this->label);
        $this->assign("iconClass", $this->iconClass);
        
        $autoDisplayType = count($this->images);
        $autoDisplayType = $autoDisplayType > 4 ? 4 : $autoDisplayType;
        
        $this->assign("displayType", $this->displayType!== null ? $this->displayType : $autoDisplayType);
        
        return parent::render();
    }
}