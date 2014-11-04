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
class QUESTIONS_CMP_Question extends OW_Component
{
    public function __construct( $questionId, $userContext = null, $count = null, $options = null )
    {
        parent::__construct();

        $language = OW::getLanguage();

        $configs = OW::getConfig()->getValues('questions');
        $count = empty($count) ? QUESTIONS_BOL_Service::MORE_DISPLAY_COUNT : $count;

        $uniqId = uniqid('question_');
        $this->assign('uniqId', $uniqId);

        $service = QUESTIONS_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();
        $question = $service->findQuestion($questionId);

        if ( empty($question) )
        {
            $this->assign('noQuestion', true);

            return;
        }

        $settings = $question->getSettings();

        $isPoll = !$settings['allowAddOprions'];
        $optionTotal = $service->findOptionCount($questionId);
        $answerCount = $service->findTotalAnswersCount($questionId);
        $postCount = BOL_CommentService::getInstance()->findCommentCount('question', $questionId);
        $isAutor = $question->userId == $userId;

        if ( $optionTotal - $count < 10 )
        {
            $count = $optionTotal;
        }

        $limit = $count ? array(0, $count) : null;

        $answers = new QUESTIONS_CMP_Answers($question, $optionTotal, $limit);
        $answers->setExpandedView();
        $answers->setSettings($options);


        if ( isset($options['inPopup']) && $options['inPopup'] === true )
        {
            $answers->setInPopupMode();
        }

        if ( isset($options['loadStatic']) && $options['loadStatic'] === false )
        {
            $answers->setDoNotLoadStatic();
        }

        $editable = $service->isCurrentUserCanInteract($question);
        $answers->setEditable($editable && $service->isCurrentUserCanAnswer($question));

        if ( $userContext !== null )
        {
            $answers->setUsersContext($userContext);
        }

        $answers->showAddNew();
        $this->addComponent('answers', $answers);

        $followsCount = $service->findFollowsCount($question->id, $userContext, array($question->userId));

        $statusCmp = new QUESTIONS_CMP_QuestionStatus($answers->getUniqId(), $postCount, $answerCount, $followsCount);
        $plugin = OW::getPluginManager()->getPlugin('questions');
        $statusCmp->setTemplate($plugin->getCmpViewDir() . 'question_static_status.html');
        $this->addComponent('questionStatus', $statusCmp);

        $tplQuestion = array(
            'text' => nl2br($question->text)
        );
        
        $event = new OW_Event(QUESTIONS_BOL_Service::EVENT_ON_QUESTION_RENDER, array(
            "questionId" => $question->id,
            "questionDto" => $question,
            "text" => $question->text,
            "settings" => $settings,
            "uniqId" => $uniqId
        ), $tplQuestion);
        
        OW::getEventManager()->trigger($event);
        
        $this->assign('question', $event->getData());

        $js = UTIL_JsGenerator::newInstance()->newObject('question', 'QUESTIONS_Question', array($uniqId, $question->id));

        if ( $configs['allow_comments'] )
        {
            $commentsParams = new BASE_CommentsParams('questions', QUESTIONS_BOL_Service::ENTITY_TYPE);
            $commentsParams->setEntityId($question->id);
            $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_TOP_FORM_WITH_PAGING);
            $commentsParams->setCommentCountOnPage(5);
            $commentsParams->setOwnerId($question->userId);

            $commentsParams->setAddComment($editable);

            $commentCmp = new BASE_CMP_Comments($commentsParams);
            //$commentTemplate = OW::getPluginManager()->getPlugin('questions')->getCmpViewDir() . 'comments.html';
            //$commentCmp->setTemplate($commentTemplate);

            $this->addComponent('comments', $commentCmp);

            if ( !empty($options['focusToPost']) )
            {
                $js->addScript('question.focusOnPostInput()');
            }
        }

        $jsSelector = 'QUESTIONS_AnswerListCollection.' . $answers->getUniqId();

        $js->addScript('question.setAnswerList(' . $jsSelector . ');');

        if ( !empty($options['relation']) )
        {
            $js->addScript($jsSelector . '.setRelation("' . $options['relation'] . '");');
        }

        $js->equateVarables(array('QUESTIONS_QuestionColletction', $uniqId), 'question');
        OW::getDocument()->addOnloadScript($js);

        $toolbar = array();

        if ( $service->isCurrentUserCanInteract($question) )
        {
            if ( $configs['enable_follow'] )
            {
                $this->assign('follow', array(
                    'isFollow' => $service->isFollow($userId, $question->id),
                    'followId' => $answers->getUniqId() . '-follow',
                    'unfollowId' => $answers->getUniqId() . '-unfollow',
                    'followClick' => $jsSelector . '.followQuestion()',
                    'unfollowClick' => $jsSelector . '.unfollowQuestion()'
                ));

                /*$followLabel = $language->text('questions', 'toolbar_follow_btn');
                $unfollowLabel = $language->text('questions', 'toolbar_unfollow_btn');

                if ( $service->isFollow($userId, $question->id) )
                {
                    $toolbar[] = array(
                        'label' => '<a id="' . $answers->getUniqId() . '-unfollow" href="javascript://" onclick="' .$jsSelector . '.unfollowQuestion()">' . $unfollowLabel . '</a>
                                    <a id="' . $answers->getUniqId() . '-follow" href="javascript://" style="display: none;" onclick="' .$jsSelector . '.followQuestion()">' . $followLabel . '</a>'
                    );
                }
                else
                {
                    $toolbar[] = array(
                        'label' => '<a id="' . $answers->getUniqId() . '-unfollow" href="javascript://" style="display: none;" onclick="' .$jsSelector . '.unfollowQuestion()">' . $unfollowLabel . '</a>
                                    <a id="' . $answers->getUniqId() . '-follow" href="javascript://" onclick="' .$jsSelector . '.followQuestion()">' . $followLabel . '</a>'
                    );
                }*/
            }
        }

        if ( $isPoll )
        {
            $list = $service->findUserAnswerListByQuestionId($userId, $question->id);

            if ( count($list) )
            {
                $toolbar[] = array(
                    'label' => '<a id="' . $answers->getUniqId() . '-unvote" href="javascript://" onclick="' .$jsSelector . '.unvote()">' .
                        $language->text('questions', 'toolbar_unvote_btn') . '</a>'
                );
            }
        }

        if ( $service->isCurrentUserCanEdit($question) )
        {
            $condEmbed = "confirm('" . $language->text('questions', 'delete_question_confirm') . "')";
            $toolbar[] = array(
                'label' => '<a href="javascript://" onclick="if(' . $condEmbed . ') ' .$jsSelector . '.deleteQuestion();">' .
                        $language->text('questions', 'toolbar_delete_btn') . '</a>'
            );
        }

        $userData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($question->userId));
        $questionInfo = array(
            'avatar' => $userData[$question->userId],
            'profileUrl' => $userData[$question->userId]['url'],
            'displayName' => $userData[$question->userId]['title'],
            'content' => '',
            'toolbar' => $toolbar,
            'date' => UTIL_DateTime::formatDate($question->timeStamp)
        );

        $this->assign('questionInfo', $questionInfo);
    }
}