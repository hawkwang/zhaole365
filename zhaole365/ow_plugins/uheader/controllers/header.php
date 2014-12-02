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
 * @package uheader.controllers
 */
class UHEADER_CTRL_Header extends OW_ActionController
{
    /**
     *
     * @var UHEADER_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = UHEADER_BOL_Service::getInstance();
    }

    public function uploader()
    {
        $language = OW::getLanguage();
        $error = false;

        $uniqId = $_POST['uniqId'];

        if ( empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name']) )
        {
            $error = $language->text('base', 'upload_file_fail');
        }
        else if ( $_FILES['file']['error'] != UPLOAD_ERR_OK )
        {
            switch ( $_FILES['file']['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }
        }


        if ( $error !== false )
        {
            $response = array(
                'type' => 'error',
                'error' => $error,
                'result' => empty($_FILES['file']) ? false : $_FILES['file']
            );
        }
        else
        {
            $query = json_decode($_POST['query'], true);
            $file = $_FILES['file'];

            try
            {
                $response = $this->uploadCover($file, $query);
            }
            catch ( InvalidArgumentException $e )
            {
                $response = array(
                    'type' => 'error',
                    'error' => $e->getMessage(),
                    'result' => $file
                );
            }
        }

        $attachSelector = 'window.parent.UHEADER.CORE.ObjectRegistry[' . json_encode($uniqId) . ']';

        $out = '<html><head><script>
            ' . $attachSelector . '.uploadComplete(' . json_encode($response) . ');
        </script></head><body></body></html>';

        echo $out;
        exit;
    }

    private function validateFile( $file )
    {
        $language = OW::getLanguage();

        if ( !UTIL_File::validateImage($file['name']) )
        {
            $error = $language->text('uheader', 'upload_file_extension_is_not_allowed');
            throw new InvalidArgumentException($error);
        }

        if ( (int) $file['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024 )
        {
            $error = $language->text('base', 'upload_file_max_upload_filesize_error');
            throw new InvalidArgumentException($error);
        }
    }

    private function validateImage( UTIL_Image $image, $canvasWidth, $canvasHeight )
    {
        $minWidth = 300;
        $minHeight = round($canvasHeight / 2);
        $language = OW::getLanguage();

        $width = $image->getWidth();
        $height = $image->getHeight();

        if ( $width < $minWidth || $height < $minHeight )
        {
            $error = $language->text('uheader', 'validation_error_image_size', array(
                'minWidth' => $minWidth,
                'minHeight' => $minHeight
            ));

            throw new InvalidArgumentException($error);
        }
    }

    private function checkCredits()
    {
        $error = UHEADER_CLASS_CreditsBridge::getInstance()->credits->getErrorMessage(UHEADER_CLASS_Credits::ACTION_ADD);
        
        if ( !empty($error) )
        {
            throw new InvalidArgumentException($error);
        }
    }
    
    private function uploadCover( $file, $query )
    {
        $userId = OW::getUser()->getId();

        $this->checkCredits();

        $coverDefaultHeight = OW::getConfig()->getValue('uheader', 'cover_height');
        
        $canvasWidth = $query['width'];
        $canvasHeight = $query['height'] < $coverDefaultHeight ? $coverDefaultHeight : $query['height'];
        
        $this->validateFile($file);

        $pluginfilesDir = OW::getPluginManager()->getPlugin('uheader')->getPluginFilesDir();
        $tmpCoverPath = $pluginfilesDir . uniqid('tmp_') . '.jpg';

        if ( !move_uploaded_file($file['tmp_name'], $tmpCoverPath) )
        {
            throw new InvalidArgumentException('Moving uploaded file faild');
        }

        $coverImage = new UTIL_Image($tmpCoverPath);
        $imageHeight = $coverImage->getHeight();
        $imageWidth = $coverImage->getWidth();

        $css = array(
            'width' => 'auto',
            'height' => 'auto'
        );

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;

        if ( $tmp >= $canvasHeight )
        {
            $css['width'] = '100%';
        }
        else
        {
            $css['height'] = '100%';
        }

        $this->validateImage($coverImage, $canvasWidth, $canvasHeight );


        // Saving the cover
        $cover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_TMP);

        if ( $cover === null )
        {
            $cover = new UHEADER_BOL_Cover();
        }

        $extension = UTIL_File::getExtension($file['name']);
        $cover->file = uniqid('cover-' . $userId . '-') . '.' . $extension;
        $cover->userId = $userId;
        $cover->status = UHEADER_BOL_Cover::STATUS_TMP;
        $cover->timeStamp = time();

        $dimensions = array(
            'height' => $imageHeight,
            'width' => $imageWidth
        );

        $cover->setSettings(array(
            'dimensions' => $dimensions,
            'css' => $css,
            'canvas' => array(
                'width' => $canvasWidth,
                'height' => $canvasHeight
            ),
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        ));

        $this->service->saveCover($cover);

        $coverPath = $this->service->getCoverPath($cover);
        OW::getStorage()->copyFile($tmpCoverPath, $coverPath);
        @unlink($tmpCoverPath);

        $coverUrl = $this->service->getCoverUrl($cover);

        return array(
            'src' => $coverUrl,
            'data' => $cover->getSettings(),
            'ratio' => $cover->getRatio()
        );
    }

    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_POST['command']);
        $query = json_decode($_POST['params'], true);

        try
        {
            $response = call_user_func(array($this, $command), $query);
        }
        catch ( InvalidArgumentException $e )
        {
            $response = array(
                'type' => 'error',
                'error' => $e->getMessage()
            );
        }

        $response = empty($response) ? array() : $response;
        echo json_encode($response);
        exit;
    }

    public function addFromPhotos( $query )
    {
        $photoId = $query['photoId'];
        $userId = OW::getUser()->getId();

        $this->checkCredits();

        $sourcePath = UHEADER_CLASS_PhotoBridge::getInstance()->pullPhoto($photoId);

        if ( $sourcePath === null )
        {
            throw new InvalidArgumentException("The requested photo wasn't find");
        }

        $coverDefaultHeight = OW::getConfig()->getValue('uheader', 'cover_height');
        
        $canvasWidth = $query['width'];
        $canvasHeight = $query['height'] < $coverDefaultHeight ? $coverDefaultHeight : $query['height'];

        $coverImage = new UTIL_Image($sourcePath);
        $imageHeight = $coverImage->getHeight();
        $imageWidth = $coverImage->getWidth();

        $css = array(
            'width' => 'auto',
            'height' => 'auto'
        );

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;

        if ( $tmp >= $canvasHeight )
        {
            $css['width'] = '100%';
        }
        else
        {
            $css['height'] = '100%';
        }

        $this->validateImage($coverImage, $canvasWidth, $canvasHeight);

        // Saving the cover
        $cover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_TMP);

        if ( $cover === null )
        {
            $cover = new UHEADER_BOL_Cover();
        }

        $extension = UTIL_File::getExtension($sourcePath);
        $cover->file = uniqid('cover-' . $userId . '-') . '.' . $extension;
        $cover->userId = $userId;
        $cover->status = UHEADER_BOL_Cover::STATUS_TMP;
        $cover->timeStamp = time();

        $dimensions = array(
            'height' => $imageHeight,
            'width' => $imageWidth
        );

        $cover->setSettings(array(
            'photoId' => $photoId,
            'dimensions' => $dimensions,
            'css' => $css,
            'canvas' => array(
                'width' => $canvasWidth,
                'height' => $canvasHeight
            ),
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        ));

        $this->service->saveCover($cover);
        $coverPath = $this->service->getCoverPath($cover);

        OW::getStorage()->copyFile($sourcePath, $coverPath);
        @unlink($sourcePath);

        $coverUrl = $this->service->getCoverUrl($cover);

        return array(
            'src' => $coverUrl,
            'data' => $cover->getSettings(),
            'ratio' => $cover->getRatio(),
            "mode" => "reposition"
        );
    }


    public function saveCover( $coverData )
    {
        $userId = (int) $coverData['userId'];
        $template = null;

        $tmpCover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_TMP);
        
        if ( $tmpCover === null && !empty($coverData["templateId"]) )
        {
            $template = $this->service->findTemplateById($coverData["templateId"]);
            $tmpCover = $template->createCover($userId);
        }
        
        $activeCover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_ACTIVE);
        $cover = $tmpCover;

        if ( $cover === null )
        {
            $eventName = UHEADER_BOL_Service::EVENT_UPDATE;
            $cover = $activeCover;
        }
        else
        {
            if ($activeCover === null)
            {
                $eventName = UHEADER_BOL_Service::EVENT_ADD;
            }
            else
            {
                $eventName = UHEADER_BOL_Service::EVENT_CHANGE;
                $this->service->deleteCover($activeCover);
            }
        }

        $data = $cover->getSettings();

        $cover->setSettings(array_merge($data, $coverData));
        $cover->status = UHEADER_BOL_Cover::STATUS_ACTIVE;

        $this->service->saveCover($cover);
        $src = $cover->getSrc();

        $event = new OW_Event($eventName, array(
            'userId' => $userId,
            'id' => $cover->id,
            'file' => $cover->file,
            'path' => $this->service->getCoverPath($cover),
            'src' => $src,
            'data' => $cover->getSettings()
        ), $cover->getSettings());

        OW::getEventManager()->trigger($event);

        $cover->setSettings($event->getData());
        $this->service->saveCover($cover);

        $message = OW::getLanguage()->text('uheader', 'cover_save_success');

        return array(
            'message' => $message,
            'src' => $src,
            'data' => $cover->getSettings(),
            'ratio' => $cover->getRatio()
        );
    }

    public function cancelChanges( $coverData )
    {
        $userId = (int) $coverData['userId'];

        $this->service->deleteCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_TMP);
        $cover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_ACTIVE);
        
        $templateId = null;
        
        if ( $cover === null && !empty($coverData["defaultTemplateId"]) )
        {
            $cover = $this->service->findTemplateById($coverData["defaultTemplateId"]);
            $templateId = $coverData["defaultTemplateId"];
        }
        
        if ( $cover !== null )
        {
            return array(
                'src' => $cover->getSrc(),
                'data' => $cover->getSettings(),
                "templateId" => $templateId,
                "defaultTemplateMode" => $templateId !== null,
                'ratio' => $cover->getRatio()
            );
        }

        return array(
            'src' => null,
            'data' => array(
                'position' => array(
                    'top' => 0,
                    'left' => 0
                ),

                'css' => null
            )
        );
    }

    public function swotchToBlank( $coverData )
    {
        $userId = (int) $coverData['userId'];
        
        $this->service->deleteCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_REMOVED);
         
        if ( empty($coverData['templateId']) )
        {
            $cover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_ACTIVE);
        }
        else
        {
            $template = $this->service->findTemplateById($coverData['templateId']);
            $cover = $template->createCover($userId);
        }

        $cover->status = UHEADER_BOL_Cover::STATUS_REMOVED;
        $this->service->saveCover($cover);
        
        $message = OW::getLanguage()->text('uheader', 'cover_switch_to_blank_success');

        return array(
            'message' => $message,
            'src' => null,
            'data' => array(
                'position' => array(
                    'top' => 0,
                    'left' => 0
                )
            )
        );
    }
    
    public function removeCover( $coverData )
    {
        $userId = (int) $coverData['userId'];
        
        $this->service->deleteAllUserCovers($userId);

        $data = array(
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        );
        
        $message = OW::getLanguage()->text('uheader', 'cover_remove_success');

        return array(
            'message' => $message,
            'src' => null,
            'data' => $data,
            'ratio' => null
        );
    }
    
    public function switchToDefaultTemplates( $coverData )
    {
        $userId = (int) $coverData['userId'];
        
        $this->service->deleteAllUserCovers($userId);
        $template = $this->service->findDefaultTemplateForUser($userId);

        $data = array(
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        );
        
        $src = null;
        $ratio = null;
        $templateId = null;
        
        if ( $template !== null )
        {
            $data = $template->getSettings();
            $src = $template->getSrc();
            $ratio = $template->getRatio();
            $templateId = $template->id;
        }
        
        $message = OW::getLanguage()->text('uheader', 'cover_restore_success');

        return array(
            'message' => $message,
            'src' => $src,
            'data' => $data,
            "defaultTemplateMode" => $templateId !== null,
            "templateId" => $templateId,
            'ratio' => $ratio
        );
    }
    
    public function stickTemplate( $coverData )
    {
        $userId = (int) $coverData['userId'];
        $templateId = (int) $coverData['templateId'];
        
        $response = $this->chooseTemplate(array(
            "userId" => $userId,
            "templateId" => $templateId
        ));
        
        $response["message"] = OW::getLanguage()->text("uheader", "cover_stick_message");
        
        return $response;
    }
    
    public function chooseTemplate( $params )
    {
        $userId = (int) $params['userId'];
        
        $this->checkCredits();
        
        $templateId = (int) $params['templateId'];
        $reposition = false;//(bool) $params['reposition'];
        
        $template = $this->service->findTemplateById($templateId);
        
        if ( $template === null )
        {
            return array(
                "error" => "Template was not found!"
            );
        }
        
        $status = $reposition ? UHEADER_BOL_Cover::STATUS_TMP : UHEADER_BOL_Cover::STATUS_ACTIVE;
        $cover = $template->createCover($userId, $status);
        
        $eventName = null;
        
        if ( !$reposition )
        {
            $activeCover = $this->service->findCoverByUserId($userId, UHEADER_BOL_Cover::STATUS_ACTIVE);
            $eventName = $activeCover === null 
                    ? UHEADER_BOL_Service::EVENT_ADD
                    : UHEADER_BOL_Service::EVENT_CHANGE;
        }
        
        $this->service->deleteCoverByUserId($userId, $status);
        $this->service->saveCover($cover);
        
        if ( $eventName !== null )
        {
            $src = $cover->getSrc();

            $event = new OW_Event($eventName, array(
                'userId' => $userId,
                'id' => $cover->id,
                'file' => $cover->file,
                'path' => $this->service->getCoverPath($cover),
                'src' => $src,
                'data' => $cover->getSettings()
            ), $cover->getSettings());

            OW::getEventManager()->trigger($event);
        }

        return array(
            'src' => $cover->getSrc(),
            'data' => $cover->getSettings(),
            'ratio' => $cover->getRatio(),
            "mode" => $reposition ? "reposition" : "view"
        );
    }

    public function loadMorePhotos( $query )
    {
        $count = 20;

        $start = $query['offset'];
        $userId = OW::getUser()->getId();

        $photos = UHEADER_CLASS_PhotoBridge::getInstance()->findUserPhotos($userId, $start, $count);
        $photoCount = count($photos);

        $listFull = $photoCount < $count;

        $list = new UHEADER_CMP_MyPhotoList($photos);

        return array(
            'listFull' => $listFull,
            'list' => $list->render()
        );
    }
    
    private function loadTemplate( $params )
    {
        $tpl = $this->service->findTemplateById($params["id"]);
        $cover = $this->service->getTemplateInfo($tpl);

        return array(
            "cover" => $cover
        );
    }
    
    public function test()
    {
        UHEADER_CLASS_Plugin::getInstance()->includeStatic();
        
        /*$gallery = new UHEADER_CMP_CoverPreviewGallery(OW::getUser()->getId());
        $this->addComponent("gallery", $gallery);*/
        
        OW::getDocument()->addOnloadScript("OW.ajaxFloatBox('UHEADER_CMP_CoverPreviewGallery', [" . OW::getUser()->getId() . ", 781])");
    }
}