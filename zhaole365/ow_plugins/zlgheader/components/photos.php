<?php

class ZLGHEADER_CMP_Photos extends ZLGHEADER_CMP_ImageListWidget
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