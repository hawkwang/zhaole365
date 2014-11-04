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
class QUESTIONS_BOL_QuestionDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_BOL_QuestionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_BOL_QuestionDao
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
        return 'QUESTIONS_BOL_Question';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'questions_question';
    }

    public function findMainFeed( $startStamp, $count, $questionIds )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();

        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $activityDao->getTableName() . " c ON a.questionId=c.questionId AND c.activityType=:ac
            WHERE a.timeStamp <= :ss AND c.privacy=:pe AND a.privacy=:pe AND $in
            GROUP BY q.id
            ORDER BY MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'ac' => QUESTIONS_BOL_FeedService::ACTIVITY_CREATE,
            'pe' => QUESTIONS_BOL_FeedService::PRIVACY_EVERYBODY,
            'c' => (int) $count
        ));
    }

    public function findOrderedMainFeed( $startStamp, $count, $questionIds, $orderActivities )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();


        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';
        $orderWhere = '1';
        if ( !empty($orderActivities) )
        {
            $orderActivities[] = QUESTIONS_BOL_FeedService::ACTIVITY_CREATE;
            $orderWhere = "a.activityType IN ('" . implode("','", $orderActivities) . "')";
        }

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $activityDao->getTableName() . " c ON a.questionId=c.questionId AND c.activityType=:ac
            WHERE a.timeStamp <= :ss AND c.privacy=:pe AND a.privacy=:pe AND $in AND $orderWhere
            GROUP BY q.id
            ORDER BY COUNT(a.id) DESC, MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'ac' => QUESTIONS_BOL_FeedService::ACTIVITY_CREATE,
            'pe' => QUESTIONS_BOL_FeedService::PRIVACY_EVERYBODY,
            'c' => (int) $count
        ));
    }

    public function findMainFeedCount( $startStamp )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();

        $query = "SELECT COUNT(DISTINCT q.id) FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $activityDao->getTableName() . " c ON a.questionId=c.questionId AND c.activityType=:ac
            WHERE a.timeStamp <= :ss AND c.privacy=:pe AND a.privacy=:pe";

        return $this->dbo->queryForColumn($query, array(
            'ss' => $startStamp,
            'ac' => QUESTIONS_BOL_FeedService::ACTIVITY_CREATE,
            'pe' => QUESTIONS_BOL_FeedService::PRIVACY_EVERYBODY
        ));
    }

    public function findMyFeed( $startStamp, $userId, $count, $questionIds )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $followDao = QUESTIONS_BOL_FollowDao::getInstance();

        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            LEFT JOIN " . $followDao->getTableName() . " f ON q.id=f.questionId
            WHERE a.privacy!=:pn AND a.timeStamp <= :ss AND $in
                AND ( f.userId=:u OR a.userId=:u )

            GROUP BY q.id
            ORDER BY MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'u' => $userId,
            'c' => (int) $count,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function findOrderedMyFeed( $startStamp, $userId, $count, $questionIds, $orderActivities )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $followDao = QUESTIONS_BOL_FollowDao::getInstance();

        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';
        $orderWhere = '1';
        if ( !empty($orderActivities) )
        {
            $orderActivities[] = QUESTIONS_BOL_FeedService::ACTIVITY_CREATE;
            $orderWhere = "a.activityType IN ('" . implode("','", $orderActivities) . "')";
        }

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            LEFT JOIN " . $followDao->getTableName() . " f ON q.id=f.questionId
            WHERE a.privacy!=:pn AND a.timeStamp <= :ss AND $in AND $orderWhere
                AND ( f.userId=:u OR a.userId=:u )

            GROUP BY q.id
            ORDER BY COUNT(a.id) DESC, MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'u' => $userId,
            'c' => (int) $count,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function findMyFeedCount( $startStamp, $userId )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $followDao = QUESTIONS_BOL_FollowDao::getInstance();

        $query = "SELECT COUNT(DISTINCT q.id) FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            LEFT JOIN " . $followDao->getTableName() . " f ON q.id=f.questionId
            WHERE a.privacy!=:pn AND a.timeStamp <= :ss
                AND f.userId=:u
                OR a.userId=:u";

        return $this->dbo->queryForColumn($query, array(
            'ss' => $startStamp,
            'u' => $userId,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }


    public function findFriendsFeed( $startStamp, $userId, $count, $questionIds )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $friendsDao = FRIENDS_BOL_FriendshipDao::getInstance();

        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $friendsDao->getTableName() . " f ON ( a.userId=f.userId OR a.userId=f.friendId ) AND f.status=:fs
            WHERE a.timeStamp <= :ss AND $in
                AND ( f.userId =:u OR f.friendId=:u )
                AND a.userId!=:u
                AND a.privacy!=:pn

            GROUP BY q.id
            ORDER BY MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'u' => $userId,
            'fs' => FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE,
            'c' => (int) $count,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function findOrderedFriendsFeed( $startStamp, $userId, $count, $questionIds, $orderActivities )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $friendsDao = FRIENDS_BOL_FriendshipDao::getInstance();

        $in = empty($questionIds) ? '1' : 'q.id NOT IN (' . implode(',', $questionIds) . ')';
        $orderWhere = '1';
        if ( !empty($orderActivities) )
        {
            $orderActivities[] = QUESTIONS_BOL_FeedService::ACTIVITY_CREATE;
            $orderWhere = "a.activityType IN ('" . implode("','", $orderActivities) . "')";
        }

        $query = "SELECT q.*
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $friendsDao->getTableName() . " f ON ( a.userId=f.userId OR a.userId=f.friendId ) AND f.status=:fs
            WHERE a.timeStamp <= :ss AND $in AND $orderWhere
                AND ( f.userId =:u OR f.friendId=:u )
                AND a.userId!=:u
                AND a.userId!=:pn

            GROUP BY q.id
            ORDER BY COUNT(a.id) DESC, MAX(a.timeStamp) DESC
            LIMIT :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ss' => $startStamp,
            'u' => $userId,
            'fs' => FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE,
            'c' => (int) $count,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }

    public function findFriendsFeedCount( $startStamp, $userId )
    {
        $activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $friendsDao = FRIENDS_BOL_FriendshipDao::getInstance();

        $query = "SELECT COUNT(DISTINCT q.id)
            FROM " . $this->getTableName() . " q
            INNER JOIN " . $activityDao->getTableName() . " a ON q.id=a.questionId
            INNER JOIN " . $friendsDao->getTableName() . " f ON ( a.userId=f.userId OR a.userId=f.friendId ) AND f.status=:fs
            WHERE a.timeStamp <= :ss
                AND ( f.userId =:u OR f.friendId=:u )
                AND a.userId!=:u
                AND a.userId!=:pn";

        return $this->dbo->queryForColumn($query, array(
            'fs' => FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE,
            'ss' => $startStamp,
            'u' => $userId,
            'pn' => QUESTIONS_BOL_FeedService::PRIVACY_NOBODY
        ));
    }
    
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        
        return $this->findListByExample($example);
    }

}