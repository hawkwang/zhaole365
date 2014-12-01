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
 * @package gheader.controllers
 */
class GHEADER_CTRL_Header extends OW_ActionController
{
    /**
     *
     * @var GHEADER_BOL_Service
     */
    private $service;

    /**
     *
     * @var GROUPS_BOL_Service
     */
    private $groupService;

    public function __construct()
    {
        parent::__construct();

        $this->service = GHEADER_BOL_Service::getInstance();
        $this->groupService = GROUPS_BOL_Service::getInstance();
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

        $attachSelector = 'window.parent.GHEADER.CORE.ObjectRegistry[' . json_encode($uniqId) . ']';

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
            $error = $language->text('gheader', 'upload_file_extension_is_not_allowed');
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
            $error = $language->text('gheader', 'validation_error_image_size', array(
                'minWidth' => $minWidth,
                'minHeight' => $minHeight
            ));

            throw new InvalidArgumentException($error);
        }
    }

    private function uploadCover( $file, $query )
    {
        $groupId = $query['groupId'];

        $this->checkCredits();

        $coverDefaultHeight = $this->service->getConfig($groupId, 'coverHeight');
        
        $canvasWidth = $query['width'];
        $canvasHeight = $query['height'] < $coverDefaultHeight ? $coverDefaultHeight : $query['height'];

        $this->validateFile($file);

        $pluginfilesDir = OW::getPluginManager()->getPlugin('gheader')->getPluginFilesDir();
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

        $cover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_TMP);

        if ( $cover === null )
        {
            $cover = new GHEADER_BOL_Cover();
        }

        $extension = UTIL_File::getExtension($file['name']);
        $cover->file = uniqid('cover-' . $groupId . '-') . '.' . $extension;
        $cover->groupId = $groupId;
        $cover->status = GHEADER_BOL_Cover::STATUS_TMP;
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

    private function checkCredits()
    {
        $error = GHEADER_CLASS_CreditsBridge::getInstance()->credits->getErrorMessage(GHEADER_CLASS_Credits::ACTION_ADD);
        
        if ( !empty($error) )
        {
            throw new InvalidArgumentException($error);
        }
    }
    
    public function addFromPhotos( $query )
    {
        $photoId = $query['photoId'];
        $groupId = $query['groupId'];

        $this->checkCredits();

        $sourcePath = GHEADER_CLASS_PhotoBridge::getInstance()->pullPhoto($photoId);

        if ( $sourcePath === null )
        {
            throw new InvalidArgumentException("The requested photo wasn't found");
        }

        $coverDefaultHeight = $this->service->getConfig($groupId, 'coverHeight');
        
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

        $this->validateImage($coverImage, $canvasWidth, $canvasHeight );

        $cover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_TMP);

        if ( $cover === null )
        {
            $cover = new GHEADER_BOL_Cover();
        }

        $extension = UTIL_File::getExtension($sourcePath);
        $cover->file = uniqid('cover-' . $groupId . '-') . '.' . $extension;
        $cover->groupId = $groupId;
        $cover->status = GHEADER_BOL_Cover::STATUS_TMP;
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
        $groupId = (int) $coverData['groupId'];
        $eventName = null;

        $tmpCover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_TMP);
        $activeCover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_ACTIVE);
        $cover = $tmpCover;

        if ( $cover === null )
        {
            $eventName = GHEADER_BOL_Service::EVENT_UPDATE;
            $cover = $activeCover;
        }
        else
        {
            if ($activeCover === null)
            {
                $eventName = GHEADER_BOL_Service::EVENT_ADD;
            }
            else
            {
                $eventName = GHEADER_BOL_Service::EVENT_CHANGE;
                $this->service->deleteCover($activeCover);
            }
        }

        $data = $cover->getSettings();

        $cover->setSettings(array_merge($data, $coverData));
        $cover->status = GHEADER_BOL_Cover::STATUS_ACTIVE;

        $this->service->saveCover($cover);

        $src = $this->service->getCoverUrl($cover);

        $event = new OW_Event($eventName, array(
            'groupId' => $groupId,
            'id' => $cover->id,
            'file' => $cover->file,
            'path' => $this->service->getCoverPath($cover),
            'src' => $src,
            'data' => $cover->getSettings()
        ), $cover->getSettings());

        OW::getEventManager()->trigger($event);

        $cover->setSettings($event->getData());
        $this->service->saveCover($cover);

        $message = OW::getLanguage()->text('gheader', 'cover_save_success');

        return array(
            'message' => $message,
            'src' => $src,
            'data' => $cover->getSettings()
        );
    }

    public function cancelChanges( $coverData )
    {
        $groupId = (int) $coverData['groupId'];

        $this->service->deleteCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_TMP);

        $cover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_ACTIVE);

        if ( $cover !== null )
        {
            return array(
                'src' => $this->service->getCoverUrl($cover),
                'data' => $cover->getSettings()
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

    public function removeCover( $coverData )
    {
        $groupId = (int) $coverData['groupId'];

        $cover = $this->service->findCoverByGroupId($groupId, GHEADER_BOL_Cover::STATUS_ACTIVE);
        $this->service->deleteCover($cover);

        $message = OW::getLanguage()->text('gheader', 'cover_remove_success');

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


    public function loadMorePhotos( $query )
    {
        $count = 20;

        $start = $query['offset'];
        $listFull = false;
        $userId = OW::getUser()->getId();

        $photos = GHEADER_CLASS_PhotoBridge::getInstance()->findUserPhotos($userId, $start, $count);
        $photoCount = count($photos);

        $listFull = $photoCount < $count;

        $list = new GHEADER_CMP_MyPhotoList($photos);

        return array(
            'listFull' => $listFull,
            'list' => $list->render()
        );
    }
}