<?php

class ZLGHEADER_CMP_CoverItem extends OW_Component
{
    public function __construct( $groupId )
    {
        parent::__construct();

        $cover = ZLGHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId);
        
        if ( $cover === null )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $staticUrl = OW::getPluginManager()->getPlugin('zlgheader')->getStaticUrl();
        OW::getDocument()->addStyleSheet($staticUrl . 'gheader.css');

        $uniqId = uniqid('gheader-');
        $this->assign('uniqId', $uniqId);

        $js = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $uniqId, 'click',
            'OW.ajaxFloatBox("ZLGHEADER_CMP_CoverView", [e.data.groupId], {
                layout: "empty",
                top: 50
            });
            return false;'
        , array('e'), array(
            'groupId' => $groupId
        ));

        OW::getDocument()->addOnloadScript($js);

        $tplCover = array();
        $tplCover["src"] = ZLGHEADER_BOL_Service::getInstance()->getCoverUrl($cover);

        $tplCover['imageCss'] = $cover->getCssString();
        $tplCover["scale"] = $cover->getRatio();
        
        $this->assign("cover", $tplCover);
    }
}