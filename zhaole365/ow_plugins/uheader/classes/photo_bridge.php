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
 * @package uheader.classes
 */
class UHEADER_CLASS_PhotoBridge
{
    /**
     * Class instance
     *
     * @var UHEADER_CLASS_PhotoBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_PhotoBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $isPluginActive = false;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private $defaultPhotoAlbumName = 'Cover Images';

    private $disabledEvents = array();

    public function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('photo');
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    private function triggerEvent( OW_Event $event )
    {
        if ( in_array($event->getName(), $this->disabledEvents) )
        {
            return $event;
        }

        return OW::getEventManager()->trigger($event);
    }

    private function callEvent( $eventName, $params )
    {
        if ( in_array($eventName, $this->disabledEvents) )
        {
            return null;
        }

        return OW::getEventManager()->call($eventName, $params);
    }

    public function isActive()
    {
        return $this->isPluginActive;
    }

    private function getAlbumName()
    {
        $albumName = OW::getLanguage()->text($this->plugin->getKey(), 'default_photo_album_name');

        return empty($albumName) ? $this->defaultPhotoAlbumName : $albumName;
    }

    private function getAlbum( $userId, $albumName, $entityType = "user", $entityId = null )
    {
        if ( !$this->isActive() ) return null;
        
        if ( empty($entityId) )
        {
            $entityId = $userId;
        }

        $album = OW::getEventManager()->call("photo.album_find", array(
            "userId" => $userId,
            "albumTitle" => $albumName
        ));
        
        if ( empty($album) )
        {
            $data = OW::getEventManager()->call("photo.album_add", array(
                "userId" => $userId,
                "name" => $albumName,
                "entityType" => $entityType,
                "entityId" => $entityId
            ));

            $albumId = $data["albumId"];
        }
        else
        {
            $albumId = $album["id"];
        }

        return $albumId;
    }

    public function pullPhoto( $photoId )
    {
        if ( !$this->isActive() ) return null;

        $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);

        if ( empty($photo) )
        {
            return null;
        }

        $source = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photoId, $photo->hash);

        $pluginfilesDir = $this->plugin->getPluginFilesDir();
        $dist = $pluginfilesDir . uniqid('tmp_') . '.jpg';

        if ( !OW::getStorage()->copyFileToLocalFS($source, $dist) )
        {
            return null;
        }

        return $dist;
    }

    public function addPhoto( $userId, $filePath, $title = "", $text = null, $addToFeed = true )
    {
        if ( !$this->isActive() ) return null;
        
        $description = empty($title) ? $text : $title;
        $description = empty($description) ? null : $description;
        
        if ( !OW::getUser()->isAuthorized('photo', 'upload') ) 
        {
            return null;
        }
                
        $albumName = $this->getAlbumName();
        
        $data = OW::getEventManager()->call("photo.add", array(
            "albumId" => $this->getAlbum($userId, $albumName),
            "path" => $filePath,
            "description" => $description,
            "addToFeed" => $addToFeed
        ));
        
        if ( empty($data["photoId"]) )
        {
            return null;
        }
        
        BOL_AuthorizationService::getInstance()->trackAction('photo', 'upload');
        
        $photoId = $data["photoId"];
        
        return $photoId;
    }

    public function findUserPhotos( $userId, $start, $offset )
    {
        if ( !$this->isActive() ) return null;

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photoDao = PHOTO_BOL_PhotoDao::getInstance();
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $query = 'SELECT p.* FROM ' . $photoDao->getTableName() . ' AS p
            INNER JOIN ' . $albumDao->getTableName() . ' AS a ON p.albumId=a.id
                WHERE a.userId=:u AND a.`entityType` = "user" AND p.status = "approved" ORDER BY p.addDatetime DESC
                    LIMIT :start, :offset';

        $list = OW::getDbo()->queryForList($query, array(
            'u' => $userId,
            'start' => $start,
            'offset' => $offset
        ));

        $out = array();
        foreach ( $list as $photo )
        {
            $id = $photo['id'];
            $out[$id] = array(
                'id' => $id,
                'thumb' => $photoService->getPhotoUrlByType($id, PHOTO_BOL_PhotoService::TYPE_SMALL, $photo["hash"]),
                'url' => $photoService->getPhotoUrlByType($id, PHOTO_BOL_PhotoService::TYPE_MAIN, $photo["hash"]),
                'path' => $photoService->getPhotoPath($id, $photo["hash"], PHOTO_BOL_PhotoService::TYPE_MAIN),
                'description' => $photo['description'],
                'permalink' => OW::getRouter()->urlForRoute('view_photo', array(
                    'id' => $id
                ))
            );

            $out[$id]['oembed'] = json_encode(array(
                'type' => 'photo',
                'url' => $out[$id]['url'],
                'href' => $out[$id]['permalink'],
                'description' => $out[$id]['description']
            ));
        }

        return $out;
    }
    
    public function getUserPhotoCount( $userId )
    {
        if ( !$this->isActive() ) return null;
        
        return PHOTO_BOL_PhotoService::getInstance()->countUserPhotos($userId);
    }
    

    public function findUserAlbums( $userId, $start, $offset )
    {
        if ( !$this->isActive() ) return null;

        $service = PHOTO_BOL_PhotoAlbumService::getInstance();
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setOrder('createDatetime DESC');
        $example->setLimitClause($start, $offset);

        $albumList = $albumDao->findListByExample($example);

        $albumListIds = array();
        foreach ( $albumList as $album )
        {
            $albumListIds[] = $album->id;
        }

        $covers = $service->getAlbumCoverForList($albumIdList);
        $out = array();

        foreach ( $albumList as $album )
        {
            $out[] = array(
                'id' => $album->id,
                'cover' => $covers[$album->id],
                'name' => $album->name
            );
        }

        return $out;
    }


    public function onCoverAdd( OW_Event $event )
    {
        if ( !OW::getConfig()->getValue('uheader', 'photo_share') )
        {
            return;
        }

        $params = $event->getParams();

        $coverPath = $params['path'];
        $userId = $params['userId'];

        $data = $event->getData();

        if ( !empty($data['photoId']) )
        {
            return;
        }

        $photoId = $this->addPhoto($userId, $coverPath, "", "", false);

        if ( $photoId === null )
        {
            return;
        }

        if ( $data !== null )
        {
            $data['photoId'] = $photoId;
        }

        $event->setData($data);
    }

    public function onCoverRemove( OW_Event $event )
    {

    }
    
    public function getUserPhotosUrl( $userId )
    {
        if ( !$this->isActive() )
        {
            return null;
        }
        
        $userName = BOL_UserService::getInstance()->getUserName($userId);
        
        return OW::getRouter()->urlForRoute("photo_user_albums", array(
            "user" => $userName
        ));
    }

    public function init()
    {
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverAdd'));
    }
}