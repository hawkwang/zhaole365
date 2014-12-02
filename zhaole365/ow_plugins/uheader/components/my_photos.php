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
class UHEADER_CMP_MyPhotos extends OW_Component
{
    /**
     *
     * @var UHEADER_CLASS_PhotoBridge
     */
    private $bridge;
    private $userId;
    private $tabKey;

    public function __construct( $userId, $tabKey )
    {
        parent::__construct();

        $this->bridge = UHEADER_CLASS_PhotoBridge::getInstance();
        $this->userId = $userId;
        $this->tabKey = $tabKey;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $count = 42;

        $photos = $this->bridge->findUserPhotos($this->userId, 0, $count);
        $this->addComponent('photoList', new UHEADER_CMP_MyPhotoList($photos));
        
        $js = UTIL_JsGenerator::composeJsString('UHEADER.GallerySwitcher.registerTab({$tabKey}, new UHEADER.PhotoSelector({$params}, _scope));', array(
            'params' => array(
                'responder' => OW::getRouter()->urlFor('UHEADER_CTRL_Header', 'rsp'),
                'userId' => $this->userId,
                'tabKey' => $this->tabKey,
                'listFull' => count($photos) < $count
            ),
            "tabKey" => $this->tabKey
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}
