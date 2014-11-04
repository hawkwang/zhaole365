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
class QUESTIONS_BOL_FeedService
{
    const ACTIVITY_CREATE = 'create';
    const ACTIVITY_FOLLOW = 'follow';
    const ACTIVITY_ANSWER = 'answer';
    const ACTIVITY_POST = 'post';

    const PRIVACY_EVERYBODY = 'everybody';
    const PRIVACY_FRIENDS = 'friends_only';
    const PRIVACY_ONLY_ME = 'only_for_me';
    const PRIVACY_NOBODY = 'nobody';

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return QUESTIONS_BOL_FeedService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var QUESTIONS_BOL_ActivityDao
     */
    private $activityDao;

    /**
     *
     * @var QUESTIONS_BOL_QuestionDao
     */
    private $questionDao;

    private function __construct()
    {
        $this->activityDao = QUESTIONS_BOL_ActivityDao::getInstance();
        $this->questionDao = QUESTIONS_BOL_QuestionDao::getInstance();
    }

    public function saveActivity( QUESTIONS_BOL_Activity $activity )
    {
        $oldActivity = $this->findActivity($activity->questionId, $activity->activityType, $activity->activityId);

        if ( $oldActivity !== null )
        {
            $activity->id = $oldActivity->id;
        }

        $this->activityDao->save($activity);
    }

    public function findActivity( $questionId, $activityType, $activityId )
    {
        return $this->activityDao->findActivity($questionId, $activityType, $activityId);
    }

    public function findActivityByUserId( $userId )
    {
        return $this->activityDao->findByUserId($userId);
    }
    
    public function deleteActivity( $questionId, $activityType, $activityId )
    {
        $this->activityDao->deleteActivity($questionId, $activityType, $activityId);
    }


    public function findMainFeed( $startStamp, $count, $questionIds )
    {
        return $this->questionDao->findMainFeed($startStamp, $count, $questionIds);
    }

    public function findOrderedMainFeed( $startStamp, $count, $questionIds, $orderActivities )
    {
        return $this->questionDao->findOrderedMainFeed($startStamp, $count, $questionIds, $orderActivities);
    }

    public function findMainFeedCount( $startStamp )
    {
        return $this->questionDao->findMainFeedCount($startStamp);
    }

    public function findMainActivity( $startStamp, $questionIds )
    {
        return $this->fetchActivity($this->activityDao->findMainActivity($startStamp, $questionIds));
    }

    public function findMyFeed( $startStamp, $userId, $count, $questionIds )
    {
        return $this->questionDao->findMyFeed($startStamp, $userId, $count, $questionIds);
    }

    public function findOrderedMyFeed( $startStamp, $userId, $count, $questionIds, $orderActivities )
    {
        return $this->questionDao->findOrderedMyFeed($startStamp, $userId, $count, $questionIds, $orderActivities);
    }

    public function findMyFeedCount( $startStamp, $userId )
    {
        return $this->questionDao->findMyFeedCount($startStamp, $userId);
    }

    public function findMyActivity( $startStamp, $userId, $questionIds )
    {
        return $this->fetchActivity($this->activityDao->findMyActivity($startStamp, $questionIds, $userId));
    }


    public function findFriendsFeed( $startStamp, $userId, $count, $questionIds )
    {
        return $this->questionDao->findFriendsFeed($startStamp, $userId, $count, $questionIds);
    }

    public function findOrderedFriendsFeed( $startStamp, $userId, $count, $questionIds, $orderActivities )
    {
        return $this->questionDao->findOrderedFriendsFeed($startStamp, $userId, $count, $questionIds, $orderActivities);
    }

    public function findFriendsFeedCount( $startStamp, $userId )
    {
        return $this->questionDao->findFriendsFeedCount($startStamp, $userId);
    }

    public function findFriendsActivity( $startStamp, $userId, $questionIds )
    {
        return $this->fetchActivity($this->activityDao->findFriendsActivity($startStamp, $questionIds, $userId));
    }


    private function fetchActivity( $list )
    {
        $out = array();
        foreach ( $list as $item )
        {
            $out[$item->questionId][] = $item;
        }

        return $out;
    }

    private function fetchFeed( $feed )
    {
        $out = array();
        foreach ( $feed as $item )
        {
            $question = new QUESTIONS_BOL_Question();
            $question->id = (int) $item['qId'];
            $question->settings = $item['qSettings'];
            $question->text = $item['qText'];
            $question->timeStamp = (int) $item['qTimeStamp'];
            $question->userId = (int) $item['qUserId'];

            $activity = new QUESTIONS_BOL_Activity();
            $activity->id = (int) $item['aId'];
            $activity->activityType = $item['aActivityType'];
            $activity->activityId = (int) $item['aActivityId'];
            $activity->data = $item['aData'];
            $activity->privacy = $item['aPrivacy'];
            $activity->questionId = (int) $item['aQuestionId'];
            $activity->timeStamp = (int) $item['aTimeStamp'];
            $activity->userId = (int) $item['aUserId'];

            $out[] = array(
                'question' => $question,
                'activity' => $activity
            );
        }

        return $out;
    }

    public function setOrder( $feedType, $order, $userId )
    {
        setcookie('questions_list_order_' . $feedType, $order, time() + 3600 * 24 * 365, '/');
    }

    public function getOrder( $feedType, $userId )
    {
        $order = null;

        if ( !empty($_COOKIE['questions_list_order_' . $feedType]) )
        {
            $order = $_COOKIE['questions_list_order_' . $feedType];
        }

        return !empty($order) ? $order : $this->getDefaultOrder();
    }

    public function getDefaultOrder()
    {
        return OW::getConfig()->getValue(QUESTIONS_Plugin::PLUGIN_KEY, 'list_order');
    }

    public function setPrivacy( $userId, $privacy )
    {
        $this->activityDao->setPrivacy($userId, $privacy);
    }
}