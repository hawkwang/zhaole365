<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package questions.bol
 */
class QUESTIONS_BOL_ActivityDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_BOL_ActivityDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_BOL_ActivityDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'QUESTIONS_BOL_Activity';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'questions_activity';
    }

    /**
     *
     * @param int $questionId
     * @param string $activityType
     * @param int $activityId
     * @return QUESTIONS_BOL_Activity
     */
    public function findActivity( $questionId, $activityType, $activityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        $example->andFieldEqual('activityType', $activityType);
        $example->andFieldEqual('activityId', $activityId);

        return $this->findObjectByExample($example);
    }

    public function deleteActivity( $questionId, $activityType, $activityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        $example->andFieldEqual('activityType', $activityType);
        $example->andFieldEqual('activityId', $activityId);

        return $this->deleteByExample($example);
    }

    public function findMainActivity( $startStamp, $questionIds )
    {
        if ( empty($questionIds) )
        {
            return array();
        }

        $questionsIN = implode(',', $questionIds);

        $query = "SELECT a.* FROM " . $this->getTableName() . " a
            WHERE a.privacy=:pe AND a.timeStamp <= :ss AND a.questionId IN ($questionsIN)
            ORDER BY a.timeStamp DESC";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'pe' => QUESTIONS_BOL_FeedService::PRIVACY_EVERYBODY
        ));
    }

    public function findMyActivity( $startStamp, $questionIds, $userId )
    {
        if ( empty($questionIds) )
        {
            return array();
        }

        $questionsIN = implode(',', $questionIds);

        $query = "SELECT a.* FROM " . $this->getTableName() . " a
            WHERE a.privacy!=:pn AND a.timeStamp <= :ss AND a.questionId IN ($questionsIN)
            ORDER BY a.timeStamp DESC";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function findFriendsActivity( $startStamp, $questionIds, $userId )
    {
        if ( empty($questionIds) )
        {
            return array();
        }

        $friendsDao = FRIENDS_BOL_FriendshipDao::getInstance();

        $questionsIN = implode(',', $questionIds);

        $query = "SELECT a.* FROM " . $this->getTableName() . " a
            INNER JOIN " . $friendsDao->getTableName() . " f ON ( a.userId=f.userId OR a.userId=f.friendId ) AND f.status=:fs
            WHERE a.privacy!=:pn AND a.timeStamp <= :ss AND a.questionId IN ($questionsIN) AND ( f.userId =:u OR f.friendId=:u )
            ORDER BY a.timeStamp DESC";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'fs' => FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE,
            'u' => $userId,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function setPrivacy( $userId, $privacy )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET privacy=:p WHERE userId=:u';

        $this->dbo->query($query, array(
            'p' => $privacy,
            'u' => $userId
        ));
    }
    
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        
        return $this->findListByExample($example);
    }
}
