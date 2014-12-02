<?php

class ZLGHEADER_CMP_MyPhotos extends OW_Component
{

    private $bridge;
    private $userId;

    private $windowHeight = null;

    public function __construct( $params = null )
    {
        parent::__construct();

        $this->bridge = ZLGHEADER_CLASS_PhotoBridge::getInstance();

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
            $this->addComponent('photoList', new ZLGHEADER_CMP_MyPhotoList($photos));
        }

        $this->assign('height', $height);

        $js = UTIL_JsGenerator::composeJsString('var photoSelector = new ZLGHEADER.PhotoSelector({$params}, _scope)', array(
            'params' => array(
                'responder' => OW::getRouter()->urlFor('ZLGHEADER_CTRL_Header', 'rsp'),
                'userId' => $this->userId,
                'listFull' => count($photos) < $count
            )
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}
