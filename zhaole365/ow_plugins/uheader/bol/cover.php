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
class UHEADER_BOL_Cover extends UHEADER_BOL_CoverBase
{
    const STATUS_ACTIVE = 'active';
    const STATUS_TMP = 'tmp';
    const STATUS_REMOVED = 'removed';

    public $userId;

    public $status;
    
    public $templateId = null;
}
