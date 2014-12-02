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
 * @package uheader.components
 */
class UHEADER_CMP_Photos extends UHEADER_CMP_ImageListWidget
{
    public function __construct( $userId )
    {
        $photos = PHOTO_BOL_PhotoService::getInstance()->findPhotoList('latest', 1, 2);

        if ( empty($photos) )
        {
            $this->setVisible(false);

            return;
        }

        $items = array();
        foreach ($photos as $item)
        {
            $items[] = array(
                'src' => $item['url']
            );
        }

        parent::__construct($items, 'Photos');
    }
}