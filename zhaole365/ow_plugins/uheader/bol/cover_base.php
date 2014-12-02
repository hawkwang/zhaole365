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
 * @package uheader.bol
 */

abstract class UHEADER_BOL_CoverBase extends OW_Entity
{
    public $file;

    public $settings = '{}';

    public $timeStamp;
    
    public function setSettings( $settings )
    {
        $this->settings = json_encode($settings);
    }

    public function getSettings()
    {
        if ( empty($this->settings) )
        {
            return array();
        }

        return json_decode($this->settings, true);
    }
    
    protected function makeCssStr( $css ) {
        $cssStr = '';
        foreach ( $css as $k => $v )
        {
            $cssStr .= $k . ': ' . $v  . '; ';
        }
        
        return $cssStr;
    }
    
    protected function calcPercent($value, $max)
    {
        return $value * 100 / $max;
    }
    
    public function getCss()
    {
        $data = $this->getSettings();

        $canvasHeight = $data['canvas']['height'];
        $canvasWidth = $data['canvas']['width'];
        
        $css = empty($data['css']) ? array() : $data['css'];

        if ( !empty($data['position']['top']) )
        {
            $css['top'] = $this->calcPercent($data['position']['top'], $canvasHeight) . '%';
        }

        if ( !empty($data['position']['left']) )
        {
            $css['left'] = $this->calcPercent($data['position']['left'], $canvasWidth) . '%';
        }
        
        return $css;
    }
    
    public function getCssString() 
    {
        $css = $this->getCss();
        
        return $this->makeCssStr($css);
    }
    
    public function getCanvas( $forWidth = null )
    {
        $settings = $this->getSettings();

        $canvasHeight = $settings['canvas']['height'];
        $canvasWidth = $settings['canvas']['width'];
        
        if ( $forWidth !== null )
        {
            $canvasHeight = $this->scale($canvasWidth, $canvasHeight, $forWidth);
            $canvasWidth = $forWidth;
        }
        
        return array(
            "width" => $canvasWidth,
            "height" => $canvasHeight
        );
    }
    
    public function getRatio()
    {
        $data = $this->getSettings();
        
        return $data['canvas']['height'] / $data['canvas']['width'] * 100;
    }
    
    private function scale( $x, $y, $toX )
    {
        return $y * $toX / $x;
    }
    
    public function getSrc()
    {
        return UHEADER_BOL_Service::getInstance()->getCoverUrl($this);
    }
}
