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
class QUESTIONS_CMP_OptionList extends OW_Component
{

    /**
     *
     * @var QUESTIONS_BOL_Service
     */
    private $service;

    /**
     *
     * @var QUESTIONS_BOL_Question
     */
    private $questionDto;

    private $uniqId, $userId, $editable = true, $editMode = false, $usersContext = null, $answerCount = 0,
            $poll = false, $optionList = array(), $optionIdList = array(), $optionDtoList = array();

    public function __construct( array $optionList, $uniqId, $userId )
    {
        parent::__construct();

        $this->optionDtoList = $optionList;

        $this->service = QUESTIONS_BOL_Service::getInstance();
        $this->userId = $userId;
        $this->uniqId = $uniqId;
    }

    public function setIsPoll( $yes = true )
    {
        $this->poll = $yes;
    }

    public function setEditMode( $yes = true )
    {
            $this->editMode = $yes;
    }

    public function setEditable( $yes = true )
    {
        $this->editable = $yes;
    }

    public function setUsersContext( $userIds )
    {
        $this->usersContext = $userIds;
    }

    public function setAnswerCount( $count )
    {
        $this->answerCount = (int) $count;
    }

    public function initOption( $countList, $optionCount = null )
    {
        if ( empty($this->optionDtoList) )
        {
            return array();
        }

        foreach ( $this->optionDtoList as $opt )
        {
            $this->optionIdList[] = $opt->id;
            $cmp = new QUESTIONS_CMP_Answer($opt, $this->uniqId);
            $this->optionList[$opt->id] = $cmp;

            $cmp->setDisbled( !$this->editable );
            $cmp->setIsMultiple(!$this->poll);
        }

        $checkedOptions = array();
        $optionsState = array();
        $totalAnswers = $this->answerCount;

        //$countList = $this->service->findAnswersCount($this->optionIdList);
        $answerDtoList = $this->service->findUserAnswerList($this->userId, $this->optionIdList);

        foreach ( $answerDtoList as $item )
        {
            $checkedOptions[] = $item->optionId;
            $this->getOption($item->optionId)->setVoted();
        }

        $optionCount = empty($optionCount) ? count($this->optionDtoList) : $optionCount;

        foreach ( $this->optionDtoList as $optionDto )
        {
            $optionId = $optionDto->id;
            $checked = in_array($optionId, $checkedOptions);

            $voteCount = $countList[$optionId];
            $users = $this->service->findAnsweredUserIdList($optionId, $this->usersContext, $checked ? 4 : 3);

            $optionsState[] = array(
                'id' => $optionId,
                'users' => $users,
                'voteCount' => $voteCount,
                'checked' => $checked
            );

            $this->getOption($optionId)->setVoteCount($voteCount);
            $this->getOption($optionId)->setUsers($users);

            $canEdit = $optionDto->userId == $this->userId
                    && ( $voteCount == 0 || $voteCount == 1 && $checked );

            $canEdit = $this->editMode || $canEdit;
            $canEdit = $this->poll ? $canEdit && $optionCount > 2 : $canEdit;

            $this->getOption($optionId)->setEditMode($canEdit);

            if ($totalAnswers)
            {
                $this->getOption($optionId)->setPercents($voteCount * 100 / $totalAnswers);
            }
        }

        return $optionsState;
    }

    /**
     *
     * @param $optionId
     * @return QUESTIONS_CMP_Answer
     */
    public function getOption( $optionId )
    {
        return $this->optionList[$optionId];
    }

    public function getOptionList()
    {
        return $this->optionList;
    }

    public function onBeforeRender()
    {
        $list = array();
        foreach ( $this->optionIdList as $optionId )
        {
            if ( empty($this->optionList[$optionId]) )
            {
                continue;
            }

            $list[] = $this->getOption($optionId)->render();
        }

        $this->assign('list', $list);
    }
}