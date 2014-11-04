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
 *
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.smartcaptcha.bol
 * @since 1.0
 */
class SMARTCAPTCHA_BOL_QuestionsDto extends OW_Entity
{
    public $question;
    
    public function getQuestion()
    {
        return $this->question;
    }
    
    public function setQuestion( $value )
    {
        $this->question = $value;
        
        return $this;
    }
}
