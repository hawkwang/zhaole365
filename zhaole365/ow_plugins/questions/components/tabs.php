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
 * @package questions.components
 */
class QUESTIONS_CMP_Tabs extends OW_Component
{
    private $tabs = array();

    public function __construct()
    {
        parent::__construct();

        static $count = 0;
        $count++;

        $uniqId = 'gtabs-' . $count;

        QUESTIONS_Plugin::getInstance()->addStatic();

        $js = UTIL_JsGenerator::newInstance()->newObject('questionsTabs', 'QUESTIONS_Tabs', array($uniqId));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('uniqId', $uniqId);
    }
    
    public function focusOnInput( $focus = true )
    {
        // Pass
    }

    public function addTab( $label, OW_Component $cmp, $icon = 'ow_ic_files' )
    {
        $this->tabs[] = array(
            'label' => $label,
            'cmp' => $cmp,
            'icon' => $icon
        );
    }

    public function render()
    {
        $tplTabs = array();

        foreach ( $this->tabs as $item )
        {
            $tplTabs[] = array(
                'label' => $item['label'],
                'content' => $item['cmp']->render(),
                'icon' => $item['icon'],
                'active' => false
            );
        }

        $tplTabs[0]['active'] = true;

        $this->assign('tabs', $tplTabs);

        return parent::render();
    }
}