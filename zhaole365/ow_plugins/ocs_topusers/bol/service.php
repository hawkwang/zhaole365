<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Rate user service
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers.bol
 * @since 1.2.6
 */
final class OCSTOPUSERS_BOL_Service
{
    /**
     * Constructor.
     */
    private function __construct() { }
    
    /**
     * Singleton instance.
     *
     * @var OCSTOPUSERS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return OCSTOPUSERS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public static function sortArrayItemByDesc( $el1, $el2 )
    {
        if ( $el1['score'] === $el2['score'] )
        {
        	if ( $el1['rates'] === $el2['rates'] )
        	{
        		return 0;
        	}
        	
            return $el1['rates'] < $el2['rates'] ? 1 : -1;
        }

        return $el1['score'] < $el2['score'] ? 1 : -1;
    }

    public static function sortArrayItemByTimeDesc( $el1, $el2 )
    {
        if ( $el1['timeStamp'] === $el2['timeStamp'] )
        {
            return 0;
        }

        return $el1['timeStamp'] < $el2['timeStamp'] ? 1 : -1;
    }
    
    public function findList( $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$topRatedList = $this->findMostRatedEntityList('user_rates', $first, $limit);

        if ( !$topRatedList )
        {
            return array();
        }
        
        $userArr = BOL_UserService::getInstance()->findUserListByIdList(array_keys($topRatedList));

        $users = array();

        foreach ( $userArr as $key => $user )
        {
            $users[$key]['dto'] = $user;
            $users[$key]['score'] = $topRatedList[$user->id]['avgScore'];
            $users[$key]['rates'] = $topRatedList[$user->id]['ratesCount'];
        }
        
        usort($users, array('OCSTOPUSERS_BOL_Service', 'sortArrayItemByDesc'));
        
        return $users;
    }
    
    public function countUsers()
    {
    	return BOL_RateService::getInstance()->findMostRatedEntityCount('user_rates');
    }
    
    public function findRateUserList( $userId, $page, $limit )
    {
    	$rateDao = BOL_RateDao::getInstance();
    	$userDao = BOL_UserDao::getInstance();
    	
    	$limit = (int) $limit;
        $first = ( $page - 1 ) * $limit;
        
        $sql = "SELECT `r`.`score`, `r`.`userId`, `r`.`timeStamp`
            FROM `" . $rateDao->getTableName() . "` AS `r`
            INNER JOIN `" . $userDao->getTableName() . "` AS `u` ON (`u`.`id` = `r`.`userId`) 
            WHERE `entityId` = :entityId AND `entityType` = 'user_rates'
            ORDER BY `timeStamp` DESC
            LIMIT :first, :limit";
        
        $list = OW::getDbo()->queryForList($sql, array('entityId' => $userId, 'first' => $first, 'limit' => $limit));
        
        if ( !$list )
        {
        	return null;
        }
        
        $idList = array();
        $keyList = array();
        foreach ( $list as $rate )
        {
        	$keyList[$rate['userId']] = $rate;
        	array_push($idList, $rate['userId']);
        }
        
        $userArr = BOL_UserService::getInstance()->findUserListByIdList($idList);

        $users = array();
        foreach ( $userArr as $key => $user )
        {
            $users[$key]['dto'] = $user;
            $users[$key]['score'] = $keyList[$user->id]['score'];
            $users[$key]['timeStamp'] = $keyList[$user->id]['timeStamp'];
        }
        
        usort($users, array('OCSTOPUSERS_BOL_Service', 'sortArrayItemByTimeDesc'));

        return $users;
    }
    
    public function countRateUsers( $userId )
    {
        $rateDao = BOL_RateDao::getInstance();
        $userDao = BOL_UserDao::getInstance();
        
        $sql = "SELECT COUNT(*)
            FROM `" . $rateDao->getTableName() . "` AS `r`
            INNER JOIN `" . $userDao->getTableName() . "` AS `u` ON (`u`.`id` = `r`.`userId`) 
            WHERE `entityId` = :entityId AND `entityType` = 'user_rates'";
        
        return (int) OW::getDbo()->queryForColumn($sql, array('entityId' => $userId));
    }
    
    public function findMostRatedEntityList( $entityType, $first, $count )
    {
    	$rateDao = BOL_RateDao::getInstance();
    	
    	$query = "SELECT `" . BOL_RateDao::ENTITY_ID . "` AS `id`, COUNT(*) as `ratesCount`, AVG(`score`) as `avgScore`
            FROM " . $rateDao->getTableName() . "
                        WHERE `" . BOL_RateDao::ENTITY_TYPE . "` = :entityType AND `" . BOL_RateDao::ACTIVE . "` = 1
            GROUP BY `" . BOL_RateDao::ENTITY_ID . "`
                        ORDER BY `avgScore` DESC, `ratesCount` DESC
                        LIMIT :first, :count";

        $arr = OW::getDbo()->queryForList($query, array('entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count));
        
        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }
}