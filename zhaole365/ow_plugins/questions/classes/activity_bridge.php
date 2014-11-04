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
 * @package questions.classes
 */
class QUESTIONS_CLASS_ActivityBridge
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_CLASS_ActivityBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_CLASS_ActivityBridge
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
     *
     * @var QUESTIONS_BOL_FeedService
     */
    private $service;

    private function __construct()
    {
        $this->service = QUESTIONS_BOL_FeedService::getInstance();
    }

    public function onQuestionAdd( OW_Event $e )
    {
        $params = $e->getParams();

        $activity = new QUESTIONS_BOL_Activity();
        $activity->questionId = (int) $params['id'];
        $activity->activityType = QUESTIONS_BOL_FeedService::ACTIVITY_CREATE;
        $activity->activityId = (int) $params['id'];
        $activity->userId = (int) $params['userId'];
        $activity->privacy = $params['privacy'];
        $activity->timeStamp = time();

        $this->service->saveActivity($activity);
    }

    public function onQuestionRemove( OW_Event $e )
    {
        $params = $e->getParams();
        $this->service->deleteActivity($params['id'], QUESTIONS_BOL_FeedService::ACTIVITY_CREATE, $params['id']);
    }

    public function onAnswerAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $option = QUESTIONS_BOL_Service::getInstance()->findOption($params['optionId']);

        if ( $option === null )
        {
            return;
        }

        $activity = new QUESTIONS_BOL_Activity();
        $activity->questionId = $option->questionId;
        $activity->activityType = QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER;
        $activity->activityId = (int) $params['id'];
        $activity->userId = (int) $params['userId'];
        $activity->timeStamp = time();
        $activity->setData(array(
            'text' => $option->text,
            'optionId' => $option->id
        ));

        $this->service->saveActivity($activity);
    }

    public function onAnswerRemove( OW_Event $e )
    {
        $params = $e->getParams();
        $option = QUESTIONS_BOL_Service::getInstance()->findOption($params['optionId']);

        if ( $option === null )
        {
            return;
        }

        $this->service->deleteActivity($option->questionId, QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER, $params['id']);
    }

    public function onFollowAdd( OW_Event $e )
    {
        $params = $e->getParams();

        $activity = new QUESTIONS_BOL_Activity();
        $activity->questionId = (int) $params['questionId'];
        $activity->activityType = QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW;
        $activity->activityId = (int) $params['userId'];
        $activity->userId = (int) $params['userId'];
        $activity->timeStamp = time();

        $this->service->saveActivity($activity);
    }

    public function onFollowRemove( OW_Event $e )
    {
        $params = $e->getParams();
        $this->service->deleteActivity($params['questionId'], QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW, $params['userId']);
    }

    public function onPostAdd( OW_Event $e )
    {
        $params = $e->getParams();

        $activity = new QUESTIONS_BOL_Activity();
        $activity->questionId = (int) $params['questionId'];
        $activity->activityType = QUESTIONS_BOL_FeedService::ACTIVITY_POST;
        $activity->activityId = (int) $params['id'];
        $activity->userId = (int) $params['userId'];
        $activity->timeStamp = time();

        $activity->setData(array(
            'text' => $params['text']
        ));

        $this->service->saveActivity($activity);
    }

    public function onPostRemove( OW_Event $e )
    {
        $params = $e->getParams();
        $this->service->deleteActivity($params['questionId'], QUESTIONS_BOL_FeedService::ACTIVITY_POST, $params['id']);
    }
}