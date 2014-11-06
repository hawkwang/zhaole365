<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag.bol
 * @since 1.0
 */

final class LOCATIONTAG_BOL_LocationService
{
    const JQUERY_LOAD_PRIORITY = 100000002;
    
    const SESSION_VAR_ENTITY_LIST = 'googlelocation_userlist_session_var';
    const ENTITY_TYPE_STATUS_UPDATE = 'user-status';

    /**
     * @var LOCATIONTAG_BOL_LocationDao
     */
    private $locationDao;
    /**
     * Class instance
     *
     * @var LOCATIONTAG_BOL_LocationService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->locationDao = LOCATIONTAG_BOL_LocationDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return LOCATIONTAG_BOL_LocationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function save( LOCATIONTAG_BOL_Location $dto )
    {
        $this->locationDao->save($dto);
    }

    public function addLocation( $entityId, $entityType, $address, $json )
    {
        if ( empty($json) || empty($entityType) || empty($entityId) || empty($address) )
        {
            return;
        }

        $data = json_decode($json, true);

        $country = '';

        foreach ( $data['result']['address_components'] as $item )
        {
            if ( !empty($item['types']) )
            {
                foreach ( $item['types'] as $type )
                {
                    if ( $type == 'country' )
                    {
                        $country = !empty($item['short_name']) ? $item['short_name'] : '';
                        break;
                    }
                }
            }

            if ( !empty($country) )
            {
                break;
            }
        }
        
        $dto = new LOCATIONTAG_BOL_Location();
        $dto->entityId = (int)$entityId;
        $dto->entityType = $entityType;
        $dto->countryCode = $country;
        $dto->address = $address;
        $dto->lat = !empty($data['lat']) ? $data['lat'] : 0;
        $dto->lng = !empty($data['lng']) ? $data['lng'] : 0;
        $dto->southWestLat = !empty($data['southWestLat']) ? $data['southWestLat'] : 0;
        $dto->southWestLng = !empty($data['southWestLng']) ? $data['southWestLng'] : 0;
        $dto->northEastLat = !empty($data['northEastLat']) ? $data['northEastLat'] : 0;
        $dto->northEastLng = !empty($data['northEastLng']) ? $data['northEastLng'] : 0;
        $dto->json = $json;
        
        $this->save($dto);
    }
    public function getAllLocationsForEntityType( $entityType )
    {
        return $this->locationDao->getAllLocationsForEntityType($entityType);
    }

    public function getLanguageCode()
    {
        $tag = BOL_LanguageService::getInstance()->getCurrent()->getTag();
        $matches = array();
        preg_match("/^([a-zA-Z]{2})-[a-zA-Z]{2}.*$/", $tag, $matches);
        $language = 'en';

        if ( !empty($matches[1]) )
        {
            $language = mb_strtolower($matches[1]);
        }

        return $language;
    }

    public function findByEntityIdAndEntityType( $entityId, $entityType )
    {
        return $this->locationDao->findEntityIdAndEntityType($entityId, $entityType);
    }

    public function deleteByEntityIdAndEntityType( $entityId, $entityType )
    {
        return $this->locationDao->deleteByEntityIdAndEntityType($entityId, $entityType);
    }
    
    public function findById( $id )
    {
        return $this->locationDao->findById($id);
    }
}