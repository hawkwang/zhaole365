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
abstract class QUESTIONS_CMP_Feed extends OW_Component
{
    const ORDER_LATEST = 'latest';
    const ORDER_POPULAR = 'popular';

    const FEED_ALL = 'all';
    const FEED_MY = 'my';
    const FEED_FRIENDS = 'friends';

    protected $uniqId, $feedType, $userId, $startStamp, $count,
            $renderedQuestionIds = array(), $order;

    /**
     *
     * @var QUESTIONS_BOL_FeedService
     */
    protected $service;

    public function __construct( $startStamp, $userId, $count )
    {
        parent::__construct();

        $this->userId = $userId;
        $this->uniqId = uniqid('questionList');
        $this->startStamp = (int) $startStamp;
        $this->count = $count;

        $this->service = QUESTIONS_BOL_FeedService::getInstance();

        $template = OW::getPluginManager()->getPlugin('questions')->getCmpViewDir() . 'feed.html';
        $this->setTemplate($template);

        $this->order = $this->service->getDefaultOrder();
        $this->feedType = self::FEED_ALL;

        QUESTIONS_Plugin::getInstance()->addStatic();
    }

    public function setOrder( $order )
    {
        $this->order = $order;
    }

    public function setFeedType( $type )
    {
        $this->feedType = $type;
    }

    public function setRenderedQuestionIds( $questionIds )
    {
        $this->renderedQuestionIds = $questionIds;
    }

    abstract public function findFeed( $startStamp, $count, $questionIds, $order );
    abstract public function findActivity( $startStamp, $questionIds );
    abstract public function findFeedCount( $startStamp );

    public function getBubbleActivityList( $activityList )
    {
        $out = array();
        foreach ( $activityList as $questionId => $activity )
        {
            $out[$questionId] = reset($activity);
        }

        return $out;
    }

    public function getRenderedCount()
    {
        return count($this->renderedQuestionIds);
    }

    public function getRenderedQuestionIds()
    {
        return $this->renderedQuestionIds;
    }

    public function renderList()
    {
        $questions = $this->findFeed($this->startStamp, $this->count, $this->renderedQuestionIds, $this->order);

        $questionIds = array();
        foreach ( $questions as $item )
        {
            $questionIds[] = $item->id;
        }

        $activityList = $this->findActivity($this->startStamp, $questionIds);
        $buubleActivityList = $this->getBubbleActivityList($activityList);

        $this->renderedQuestionIds = array_merge($this->renderedQuestionIds, $questionIds);

        $cmp = new QUESTIONS_CMP_FeedList($questions, $buubleActivityList, $activityList);

        return $cmp->render();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('list', $this->renderList());

        $feedCount = $this->findFeedCount($this->startStamp);
        $renderedCount = $this->getRenderedCount();

        $viewMore = $renderedCount < $feedCount;
        $this->assign('viewMore', $viewMore);

        $this->assign('uniqId', $this->uniqId);

        $js = UTIL_JsGenerator::newInstance();

        $data = array(
            'viewMore' => $viewMore,
            'startStamp' => $this->startStamp,
            'userId' => $this->userId,
            'className' => get_class($this),
            'questionIds' => $this->renderedQuestionIds,
            'order' => $this->order,
            'feedType' => $this->feedType
        );

        $js->newObject('questionList', 'QUESTIONS_QuestionList', array($this->uniqId, $data));
        $js->callFunction(array('questionList', 'setResponder'), array(
            OW::getRouter()->urlFor('QUESTIONS_CTRL_List', 'rsp')
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}