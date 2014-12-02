<?php

class ZLGHEADER_CMP_MyPhotoList extends OW_Component
{
    public function __construct( $photos )
    {
        parent::__construct();

        $this->assign('photos', $photos);
    }
}
