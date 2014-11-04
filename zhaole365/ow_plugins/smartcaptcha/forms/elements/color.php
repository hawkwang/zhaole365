<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Kairat Bakytow
 * @package ow_plugins.smartcaptcha.forms.elements
 * @since 1.0
 */
class SMARTCAPTCHA_FORMELEMENT_Color extends TextField
{
    public function renderInput( $params = null )
    {
        parent::renderInput( $params );
        
        $this->addAttribute( 'class', 'colorSelector' );
        
        return UTIL_HtmlTag::generateTag( 'div', $this->attributes, true, '<div></div>' );
    }
    
    public function getElementJs()
    {
        return 'var formElement = new SmartcaptchaColorField(' . json_encode( $this->getId() ) . ', ' . json_encode( $this->getName() ) . ',' . json_encode( $this->getValue() ) .');';
    }
}
