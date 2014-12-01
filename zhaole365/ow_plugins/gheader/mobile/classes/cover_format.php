<?php

class GHEADER_MCLASS_CoverFormat extends NEWSFEED_CLASS_MobileFormat
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $groupId = $this->vars["groupId"];

        $cover = GHEADER_BOL_Service::getInstance()->findCoverByGroupId($groupId);
        
        if ( $cover === null )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $staticUrl = OW::getPluginManager()->getPlugin('gheader')->getStaticUrl();
        OW::getDocument()->addStyleSheet($staticUrl . 'gheader.css');

        $src = GHEADER_BOL_Service::getInstance()->getCoverUrl($cover);
        $this->assign('src', $src);

        $settings = $cover->getSettings();

        $canvasHeight = $settings['canvas']['height'];
        $canvasWidth = $settings['canvas']['width'];
        $css = $settings['css'];

        if ( !empty($settings['position']['top']) )
        {
            $css['top'] = $this->calcPercent($settings['position']['top'], $canvasHeight) . '%';
        }

        if ( !empty($settings['position']['left']) )
        {
            $css['left'] = $this->calcPercent($settings['position']['left'], $canvasWidth) . '%';
        }

        $cssStr = '';
        foreach ( $css as $k => $v )
        {
            $cssStr .= $k . ': ' . $v  . '; ';
        }

        $this->assign('imageCss', $cssStr);
        $this->assign("scale", $settings['canvas']['height'] / $settings['canvas']['width'] * 100);
    }

    private function scale( $x, $y, $toX )
    {
        return $y * $toX / $x;
    }
    
    protected function calcPercent($value, $max)
    {
        return $value * 100 / $max;
    }
}