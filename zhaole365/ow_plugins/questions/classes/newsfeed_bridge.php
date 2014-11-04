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
class QUESTIONS_CLASS_NewsfeedBridge
{
    /**
     * Singleton instance.
     *
     * @var QUESTIONS_CLASS_NewsfeedBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return QUESTIONS_CLASS_NewsfeedBridge
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
     * @var QUESTIONS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = QUESTIONS_BOL_Service::getInstance();
    }

    //Event Handlers

    public function onStatusCmp( OW_Event $event )
    {
        $params = $event->getParams();
        $language = OW::getLanguage();

        $status = new NEWSFEED_CMP_UpdateStatus($params['feedAutoId'], $params['entityType'], $params['entityId'], $params['visibility']);

        if ( QUESTIONS_BOL_Service::getInstance()->isCurrentUserCanAsk() )
        {
            $tabs = new QUESTIONS_CMP_Tabs();
            $tabs->addTab($language->text('questions', 'newsfeed_status_tab'), $status, 'ow_ic_chat');
            $question = new QUESTIONS_CMP_NewsfeedQuestionAdd($params['feedAutoId'], $params['entityType'], $params['entityId'], $params['visibility']);
            $tabs->addTab($language->text('questions', 'newsfeed_question_tab'), $question, 'ow_ic_lens');

            $status = $tabs;
        }

        return $status;
    }

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != QUESTIONS_BOL_Service::ENTITY_TYPE )
        {
            return;
        }

        $language = OW::getLanguage();
        $configs = OW::getConfig()->getValues('questions');
        $questionId = $params['action']['entityId'];
        $userId = OW::getUser()->getId();

        $question = $this->service->findQuestion($questionId);
        $optionTotal = $this->service->findOptionCount($questionId);
        $answerCount = $this->service->findTotalAnswersCount($questionId);
        $postCount = BOL_CommentService::getInstance()->findCommentCount(QUESTIONS_BOL_Service::ENTITY_TYPE, $params['action']['entityId']);
        $userContext = array();

        $count = QUESTIONS_BOL_Service::DISPLAY_COUNT;
        if ( $optionTotal - $count < 2 )
        {
            $count = $optionTotal;
        }

        $cmp = new QUESTIONS_CMP_Answers($question, $optionTotal, array(0, $count));
        $cmp->setTotalAnswerCount($answerCount);

        if ( in_array($params['feedType'], array('user', 'my')) )
        {
            foreach ( $params['activity'] as $act )
            {
                if ( $act['activityType'] == 'answer' )
                {
                    $userContext[] = $act['userId'];
                }
            }

            $cmp->setUsersContext($userContext);
        }

        $lastActivity = $this->getBubbleActivity($params);

        $data['assign']['answers'] = $cmp->render();

        $questionUrl = OW::getRouter()->urlForRoute('questions-question', array(
            'qid' => $question->id
        ));

        $jsSelector = 'QUESTIONS_AnswerListCollection.' . $cmp->getUniqId();
        $allowPopups = !isset($configs['allow_popups']) || $configs['allow_popups'];

        $data['assign']['string'] = $data['string'] = $this->getItemString($question, $lastActivity, $jsSelector, $questionUrl);

        $data['features'] = array();

        $onClickStr = "window.location.href='$questionUrl'";

        if ( $configs['allow_comments'] )
        {
            if ( $allowPopups )
            {
                $onClickStr = "return {$jsSelector}.openQuestionDelegate(true);";
            }

            $data['features']["q-comments"] = array(
                'class' => 'q-' . $cmp->getUniqId() . '-status-comments',
                'iconClass' => 'ow_miniic_comment',
                'label' => $postCount,
                'onclick' => $onClickStr,
                'string' => null
            );
        }

        if ( $allowPopups )
        {
            $onClickStr = "return {$jsSelector}.openQuestionDelegate();";
        }

        $data['features']["q-votes"] = array(
            'class' => 'q-' . $cmp->getUniqId() . '-status-votes',
            'iconClass' => 'questions_miniicon_check',
            'label' => $answerCount,
            'onclick' => $onClickStr,
            'string' => null
        );

        if ( $configs['enable_follow'] )
        {
            $onClickStr = "OW.error('" . $language->text('questions', 'follow_not_allowed') . "')";
            $isFollowing = false;

            if ( $this->service->isCurrentUserCanInteract($question) )
            {
                $isFollowing = $this->service->isFollow($userId, $question->id);
                $onClickStr = $isFollowing
                    ? $jsSelector . '.unfollowQuestion();'
                    : $jsSelector . '.followQuestion();';
            }
            else if ( OW::getUser()->isAuthenticated() )
            {
                $isFollowing = $this->service->isFollow($userId, $question->id);

                if ( $isFollowing )
                {
                    $onClickStr = $jsSelector . '.unfollowQuestion();';
                }
            }

            $data['features']["q-follows"] = array(
                'class' => 'q-' . $cmp->getUniqId() . '-status-follows',
                'iconClass' => 'questions_miniic_follow',
                'label' => $this->service->findFollowsCount($question->id),
                'onclick' => $onClickStr,
                'active' => $isFollowing
            );
        }
        
        $settings = $question->getSettings();

        $selfContext = isset($settings['context']['type']) && $settings['context']['type'] == "user" && $settings['context']['id'] == $question->userId;
        
        if ( !$selfContext && isset($settings['context']) && $settings['context']['type'] != $params['feedType'] )
        {
            if ( !empty($settings['context']['url']) && !empty($settings['context']['label']) )
            {
                $data['context'] = $settings['context'];
            }
        }
        
        if ( $selfContext ) 
        {
            $data['context'] = null;
        }
        
        $event->setData($data);
    }

    private function getBubbleActivity( $params )
    {
        if ( !empty($params['lastActivity']) )
        {
            return $params['lastActivity'];
        }

        foreach ( $params['activity'] as $act ) //TODO: Back compatibility with 1.3.1
        {
            if ( !in_array($act['activityType'], array('subscribe')) )
            {
                return $act;
            }
        }

        return $params['createActivity'];
    }

    private function getItemString( $question, $bubbleActivity, $jsSelector, $questionUrl )
    {
        $activityType = $bubbleActivity['activityType'];

        $configs = OW::getConfig()->getValues('questions');
        $allowPopups = !isset($configs['allow_popups']) || $configs['allow_popups'];
        $onClickStr = $allowPopups ? 'onclick="return ' . $jsSelector . '.openQuestionDelegate();"' : '';

        $questionEmbed = '<a href="' . $questionUrl . '" ' . $onClickStr . '>' . $question->text . '</a>';

        if ( in_array($activityType, array(QUESTIONS_BOL_FeedService::ACTIVITY_CREATE, QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW)) )
        {
            return OW::getLanguage()->text('questions', 'item_text_' . $activityType, array(
                'question' => $questionEmbed
            ));
        }

        $buubleData = $bubbleActivity['data'];
        $with = '';
        if ( !empty($buubleData['text']) )
        {
            $text = UTIL_String::truncate($buubleData['text'], 50, '...');
            $with = '<a href="' . $questionUrl . '" ' . $onClickStr . '>' . $text . '</a>';
        }

        return OW::getLanguage()->text('questions', 'item_text_' . $activityType, array(
            'question' => $questionEmbed,
            'with' => $with
        ));
    }

    public function onEntityAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $language = OW::getLanguage();

        if ( $params['entityType'] != QUESTIONS_BOL_Service::ENTITY_TYPE )
        {
            return;
        }

        $questionId = (int) $params['entityId'];
        $question = QUESTIONS_BOL_Service::getInstance()->findQuestion($questionId);

        if ( $question === null )
        {
            return;
        }

        $questionUrl = OW::getRouter()->urlForRoute('questions-question', array(
            'qid' => $question->id
        ));

        $questionEmbed = '<a href="' . $questionUrl . '">' . $question->text . '</a>';
        $string = $language->text('questions', 'item_text_create', array(
            'question' => $questionEmbed
        ));

        $data = array_merge($data, array(
            'params' => array(
                'subscribe' => true
            ),
            'ownerId' => (int) $question->userId,
            'time' => (int) $question->timeStamp,
            'string' => $string,
            'content' => '[ph:answers]',
            'view' => array(
                'iconClass' => 'ow_ic_lens'
            ),
            'data' => array(
                'questionId' => $question->id
            )
        ));

        $event->setData($data);
    }

    public function onActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $siteActivity = true;

        if ( !$siteActivity )
        {
            if ( $params['entityType'] == QUESTIONS_BOL_Service::ENTITY_TYPE )
            {
                if ( $params['activityType'] == 'comment' )
                {
                    $data['params']['subscribe'] = false;
                }

                if ( !isset($params['visibility']) && !isset($data['params']['visibility']) )
                {
                    $data['params']['visibility'] = 15;
                }
                else if (isset($params['visibility']))
                {
                    $data['params']['visibility'] = $params['visibility'];
                }

                if ( $params['activityType'] != 'create' && intval($data['params']['visibility']) & 1 )
                {
                    $data['params']['visibility'] -= 1; // All visibility (15) instead of SITE Visibility (1)
                }
            }
        }

        $event->setData($data);
    }

    public function onAnswerAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $optionId = (int) $params['optionId'];

        $optionDto = QUESTIONS_BOL_Service::getInstance()->findOption($optionId);

        $id = (int) $params['id'];

        $activityParams = array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $optionDto->questionId,
            'pluginKey' => 'questions',
            'activityType' => QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER,
            'activityId' => $id,
            'userId' => $params['userId']
        );

        $activityData = array(
            'answerId' => $id,
            'optionId' => $optionId,
            'text' => $optionDto->text,
            'string' => '[ph:string]'
        );

        $event = new OW_Event('feed.activity', $activityParams, $activityData);
        OW::getEventManager()->trigger($event);
    }

    public function onAnswerRemove(  OW_Event $event )
    {
        $params = $event->getParams();

        $optionId = (int) $params['optionId'];
        $optionDto = QUESTIONS_BOL_Service::getInstance()->findOption($optionId);

        $activityParams = array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $optionDto->questionId,
            'activityType' => QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER,
            'activityId' => $params['id']
        );

        $event =new OW_Event('feed.delete_activity', $activityParams);
        OW::getEventManager()->trigger($event);
    }

    public function onFollowAdd( OW_Event $event )
    {
        $params = $event->getParams();

        $activityParams = array(
            'pluginKey' => 'questions',
            'userId' => $params['userId'],
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $params['questionId'],
            'activityId' => $params['userId'],
            'activityType' => 'subscribe',
            'visibility' => 14, // Visibility autor
            'time' => time()
        );

        $event =new OW_Event('feed.activity', $activityParams);
        OW::getEventManager()->trigger($event);

        $activityParams['activityType'] = QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW;
        $activityParams['activityId'] = $params['id'];
        $activityParams['subscribe'] = false;

        $activityData = array(
            'string' => '[ph:string]'
        );

        $event = new OW_Event('feed.activity', $activityParams, $activityData);
        OW::getEventManager()->trigger($event);
    }

    public function onFollowRemove( OW_Event $event )
    {
        $params = $event->getParams();

        $activityParams = array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $params['questionId'],
            'activityType' => 'subscribe',
            'activityId' => $params['userId']
        );

        $event =new OW_Event('feed.delete_activity', $activityParams);
        OW::getEventManager()->trigger($event);

        $activityParams['activityType'] = QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW;
        $activityParams['activityId'] = $params['id'];
        $activityParams['subscribe'] = false;

        $event =new OW_Event('feed.delete_activity', $activityParams);
        OW::getEventManager()->trigger($event);
    }

    public function onPostAdd( OW_Event $e )
        {
        $params = $e->getParams();

        $activityParams = array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => (int) $params['questionId'],
            'pluginKey' => 'questions',
            'activityType' => QUESTIONS_BOL_FeedService::ACTIVITY_POST,
            'activityId' => (int) $params['id'],
            'userId' => $params['userId']
        );

        $activityData = array(
            'text' => $params['text'],
            'string' => '[ph:string]'
        );

        $event = new OW_Event('feed.activity', $activityParams, $activityData);
        OW::getEventManager()->trigger($event);
        }

    public function onPostRemove( OW_Event $e )
    {
        $params = $e->getParams();

        $activityParams = array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $params['questionId'],
            'activityType' => QUESTIONS_BOL_FeedService::ACTIVITY_POST,
            'activityId' => $params['id']
        );

        $event =new OW_Event('feed.delete_activity', $activityParams);
        OW::getEventManager()->trigger($event);
    }

    public function onQuestionRemove( OW_Event $event )
    {
        $params = $event->getParams();

        $questionId = $params['id'];

        $event = new OW_Event('feed.delete_item', array(
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE,
            'entityId' => $questionId
        ));

        OW::getEventManager()->trigger($event);
    }

    public function configurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('questions', 'feed_content_label'),
            'activity' => '*:' . QUESTIONS_BOL_Service::ENTITY_TYPE
        ));
    }

    public function collectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('*:' . QUESTIONS_BOL_Service::ENTITY_TYPE, QUESTIONS_Plugin::PRIVACY_ACTION_VIEW_MY_QUESTIONS));
    }
}