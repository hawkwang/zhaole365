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
class UHEADER_BOL_Template extends UHEADER_BOL_CoverBase
{
    public $default;
    
    /**
     * 
     * @param int $userId
     * @return UHEADER_BOL_Cover
     */
    public function createCover( $userId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $cover = new UHEADER_BOL_Cover();
        
        $cover->settings = $this->settings;
        $cover->file = $this->file;
        $cover->templateId = $this->id;
        $cover->timeStamp = time();
        $cover->status = $status;
        $cover->userId = $userId;
        
        return $cover;
    }
    
    public function getSrc()
    {
        return UHEADER_BOL_Service::getInstance()->getTemplateUrl($this);
    }
}
