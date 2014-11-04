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
 * @package questions.components
 */
class QUESTIONS_CMP_FeedItem extends OW_Component
{
    private $activity = array();

    /**
     *
     * @var QUESTIONS_BOL_Question
     */
    private $question;

    /**
     *
     * @var QUESTIONS_BOL_Activity
     */
    private $bubbleActivity;

    private $uniqId, $lastItem = false;

    public function __construct( QUESTIONS_BOL_Question $question, QUESTIONS_BOL_Activity $bubbleActivity, $activity )
    {
        parent::__construct();

        $this->activity = $activity;
        $this->question = $question;
        $this->bubbleActivity = $bubbleActivity;
        $this->uniqId = uniqid('qi_' . $question->id . '_');
    }

    public function getUniqId()
    {
        return $this->uniqId;
    }

    public function setIsLastItem( $yes = true )
    {
        $this->lastItem = $yes;
    }

    private function getContextUserIds()
    {
        $out = array();

        foreach ( $this->activity as $activity )
        {
            $out[] = $activity->userId;
        }

        return $out;
    }

    /**
     *
     * @return QUESTIONS_BOL_Activity
     */
    public function getBubbleActivity()
    {
        return $this->bubbleActivity;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $language = OW::getLanguage();
        $configs = OW::getConfig()->getValues('questions');

        $optionTotal = QUESTIONS_BOL_Service::getInstance()->findOptionCount($this->question->id);
        $answerCount = QUESTIONS_BOL_Service::getInstance()->findTotalAnswersCount($this->question->id);
        $postCount = BOL_CommentService::getInstance()->findCommentCount(QUESTIONS_BOL_Service::ENTITY_TYPE, $this->question->id);

        $questionUrl = OW::getRouter()->urlForRoute('questions-question', array(
            'qid' => $this->question->id
        ));

        $count = QUESTIONS_BOL_Service::DISPLAY_COUNT;
        if ( $optionTotal - $count < 2 )
        {
            $count = $optionTotal;
        }

        $answers = new QUESTIONS_CMP_Answers($this->question, $optionTotal, array(0, $count));
        $answers->setTotalAnswerCount($answerCount);
        $answers->setUsersContext($this->getContextUserIds());

        $bubbleActivity = $this->getBubbleActivity();
        $jsSelector = 'QUESTIONS_AnswerListCollection.' . $answers->getUniqId();

        $text = $this->getItemString($bubbleActivity, $jsSelector, $questionUrl);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($bubbleActivity->userId));

        $allowPopups = !isset($configs['allow_popups']) || $configs['allow_popups'];

        $features = array();

        $onClickStr = "window.location.href='$questionUrl'";

        if ( $configs['allow_comments'] )
        {
            if ( $allowPopups )
            {
                $onClickStr = "return {$jsSelector}.openQuestionDelegate(true);";
            }

            $features[] = array(
                'class' => 'q-' . $answers->getUniqId() . '-status-comments',
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

        $features[] = array(
            'class' => 'q-' . $answers->getUniqId() . '-status-votes',
            'iconClass' => 'questions_miniicon_check',
            'label' => $answerCount,
            'onclick' => $onClickStr,
            'string' => null
        );

        if ( $configs['enable_follow'] )
        {
            $onClickStr = "OW.error('" . $language->text('questions', 'follow_not_allowed') . "')";
            $isFollowing = false;

            if ( QUESTIONS_BOL_Service::getInstance()->isCurrentUserCanInteract($this->question) )
            {
                $userId = OW::getUser()->getId();

                $isFollowing = QUESTIONS_BOL_Service::getInstance()->isFollow($userId, $this->question->id);
                $onClickStr = $isFollowing
                    ? $jsSelector . '.unfollowQuestion();'
                    : $jsSelector . '.followQuestion();';
            }
            else if ( OW::getUser()->isAuthenticated() )
            {
                $isFollowing = QUESTIONS_BOL_Service::getInstance()->isFollow($userId, $this->question->id);

                if ( $isFollowing )
                {
                    $onClickStr = $jsSelector . '.unfollowQuestion();';
                }
            }

            $features[] = array(
                'class' => 'q-' . $answers->getUniqId() . '-status-follows',
                'iconClass' => 'questions_miniic_follow',
                'label' => QUESTIONS_BOL_Service::getInstance()->findFollowsCount($this->question->id),
                'onclick' => $onClickStr,
                'active' => $isFollowing
            );
        }



        $settings = $this->question->getSettings();
        $context = empty($settings['context']['url']) || empty($settings['context']['label'])
            ? null
            : array(
                'url' => $settings['context']['url'],
                'label' => $settings['context']['label']
            );

        $tplQuestion = array(
            'questionId' => $this->question->id,
            'uniqId' => $this->getUniqId(),
            'text' => $text,
            'timeStamp' => UTIL_DateTime::formatDate($bubbleActivity->timeStamp),
            'lastItem' => $this->lastItem,
            'answers' => $answers->render(),
            'avatar' => $avatars[$bubbleActivity->userId],
            'settings' => $settings,
            'context' => $context,
            'features' => $features,
            'permalink' => $questionUrl
        );
        
        $event = new OW_Event(QUESTIONS_BOL_Service::EVENT_ON_LIST_ITEM_RENDER, array(
            "questionId" => $this->question->id,
            "questionDto" => $this->question,
            "text" => $text,
            "settings" => $settings,
            "uniqId" => $this->getUniqId()
        ), $tplQuestion);
        
        OW::getEventManager()->trigger($event);

        $this->assign('item', $event->getData());
    }

    private function getActivityList( $type = null, $userId = null )
    {
        $out = array();

        foreach ( $this->activity as $activity )
        {
            if ( $type !== null && $activity->activityType != $type )
            {
                continue;
            }

            if ( $userId !== null && $activity->userId != $userId )
            {
                continue;
            }

            $out[$activity->timeStamp] = $activity;
        }

        krsort($out);

        return $out;
    }

    private function getItemString( $bubbleActivity, $jsSelector, $questionUrl )
    {
        $activityType = $bubbleActivity->activityType;

        $configs = OW::getConfig()->getValues('questions');

        $allowPopups = !isset($configs['allow_popups']) || $configs['allow_popups'];
        $onClickStr = $allowPopups ? 'onclick="return ' . $jsSelector . '.openQuestionDelegate();"' : '';

        $questionEmbed = '<a href="' . $questionUrl . '" ' . $onClickStr . '>' . $this->question->text . '</a>';

        if ( in_array($activityType, array(QUESTIONS_BOL_FeedService::ACTIVITY_CREATE, QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW)) )
        {
            return OW::getLanguage()->text('questions', 'item_text_' . $bubbleActivity->activityType, array(
                'question' => $questionEmbed
            ));
        }

        $buubleData = $bubbleActivity->getData();
        $with = '';
        if ( !empty($buubleData['text']) )
        {
            $text = UTIL_String::truncate($buubleData['text'], 50, '...');
            $with = '<a href="' . $questionUrl . '" ' . $onClickStr . '>' . $text . '</a>';
        }

        return OW::getLanguage()->text('questions', 'item_text_' . $bubbleActivity->activityType, array(
            'question' => $questionEmbed,
            'with' => $with
        ));
    }
}
