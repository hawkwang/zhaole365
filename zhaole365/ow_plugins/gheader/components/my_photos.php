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
 * @package gheader.components
 */
class GHEADER_CMP_MyPhotos extends OW_Component
{


    /**
     *
     * @var GHEADER_CLASS_PhotoBridge
     */
    private $bridge;
    private $userId;

    private $windowHeight = null;

    public function __construct( $params = null )
    {
        parent::__construct();

        $this->bridge = GHEADER_CLASS_PhotoBridge::getInstance();

        if ( !$this->bridge->isActive() )
        {
            $this->setVisible(false);

            return;
        }

        if ( !empty($params['windowHeight']) )
        {
            $this->windowHeight = $params['windowHeight'];
        }

        $this->userId = OW::getUser()->getId();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $count = 28;

        $photos = $this->bridge->findUserPhotos($this->userId, 0, $count);

        $height = $this->windowHeight - 250;
        $height = $height > 650 ? 650 : $height;

        if ( empty($photos) )
        {
            $height = null;
        }
        else
        {
            $this->addComponent('photoList', new GHEADER_CMP_MyPhotoList($photos));
        }

        $this->assign('height', $height);

        $js = UTIL_JsGenerator::composeJsString('var photoSelector = new GHEADER.PhotoSelector({$params}, _scope)', array(
            'params' => array(
                'responder' => OW::getRouter()->urlFor('GHEADER_CTRL_Header', 'rsp'),
                'userId' => $this->userId,
                'listFull' => count($photos) < $count
            )
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}
