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
class SMARTCAPTCHA_BOL_AnswerDto extends OW_Entity
{
    public $idQuestion;
    
    public function getIdQuestion()
    {
        return (int)$this->idQuestion;
    }
    
    public function setIdQuestion( $idQuestion )
    {
        $this->idQuestion = (int)$idQuestion;
        
        return $this;
    }

    public $answer;
    
    public function getAnswer()
    {
        return $this->answer;
    }
    
    public function setAnswer( $value )
    {
        $this->answer = $value;
        
        return $this;
    }
}
