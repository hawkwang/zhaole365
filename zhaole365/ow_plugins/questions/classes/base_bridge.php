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
 * @package questions.classes
 */
class QUESTIONS_CLASS_BaseBridge
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_CLASS_BaseBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_CLASS_BaseBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
        
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        // Delete Questions        
        $questions = QUESTIONS_BOL_Service::getInstance()->findQuestionsByUserId($userId);
        foreach ( $questions as $question )
        {
            QUESTIONS_BOL_Service::getInstance()->deleteQuestion($question->id);
        }
        
        // Delete Answers
        $answers = QUESTIONS_BOL_Service::getInstance()->findAnswersByUserId($userId);
        foreach ( $answers as $answer )
        {
            QUESTIONS_BOL_Service::getInstance()->removeAnswerById($answer->id);
        }
        
        // Delete Follows
        $follows = QUESTIONS_BOL_Service::getInstance()->findFollowsByUserId($userId);
        foreach ( $follows as $follow )
        {
            QUESTIONS_BOL_Service::getInstance()->removeFollow($follow->userId, $follow->questionId);
        }
        
        // Delete Activity
        $activityList = QUESTIONS_BOL_FeedService::getInstance()->findActivityByUserId($userId);
        foreach ( $activityList as $activity )
        {
            /* @var $activity QUESTIONS_BOL_Activity */
            QUESTIONS_BOL_FeedService::getInstance()->deleteActivity($activity->questionId, $activity->activityType, $activity->activityId);
        }
    }
    
    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, "onUserUnregister"));
    }
}