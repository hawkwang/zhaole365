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
 * @package gheader.components
 */
class GHEADER_CMP_CoverItem extends OW_Component
{
    public function __construct( $groupId )
    {
        parent::__construct();

        $cover = GHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId);
        
        if ( $cover === null )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $staticUrl = OW::getPluginManager()->getPlugin('gheader')->getStaticUrl();
        OW::getDocument()->addStyleSheet($staticUrl . 'gheader.css');

        $uniqId = uniqid('gheader-');
        $this->assign('uniqId', $uniqId);

        $js = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $uniqId, 'click',
            'OW.ajaxFloatBox("GHEADER_CMP_CoverView", [e.data.groupId], {
                layout: "empty",
                top: 50
            });
            return false;'
        , array('e'), array(
            'groupId' => $groupId
        ));

        OW::getDocument()->addOnloadScript($js);

        $tplCover = array();
        $tplCover["src"] = GHEADER_BOL_Service::getInstance()->getCoverUrl($cover);

        $tplCover['imageCss'] = $cover->getCssString();
        $tplCover["scale"] = $cover->getRatio();
        
        $this->assign("cover", $tplCover);
    }
}