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
class QUESTIONS_CMP_Answer extends OW_Component
{
    /**
     *
     * @var QUESTIONS_BOL_Service
     */
    private $service;

    /**
     *
     * @var QUESTIONS_BOL_Option
     */
    private $option;

    private $voted = false, $voteCount = 0, $percents = 0, $userIds = array(), $multiple = true,
		$disabled = false, $editMode = false;

    public function __construct( QUESTIONS_BOL_Option $opt, $uniqId)
    {
        parent::__construct();

        $this->option = $opt;

        $this->assign('questionUniqId', $uniqId);
    }

    public function setEditMode( $yes = true )
    {
            $this->editMode = $yes;
    }

    public function setVoteCount( $voteCount )
    {
        $this->voteCount = $voteCount;
    }

    public function setPercents( $percents )
    {
        $this->percents = $percents;
    }

    public function setVoted( $voted = true )
    {
        $this->voted = (bool) $voted;
    }

    public function setDisbled( $disabled = true )
    {
        $this->disabled = (bool) $disabled;
    }

    public function setUsers( $users )
    {
        $this->userIds = $users;
    }

    public function setIsMultiple( $multiple = true )
    {
        $this->multiple = $multiple;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $tplOption = array();
        $tplOption['id'] = $this->option->id;
        $tplOption['text'] = $this->option->text;
        $tplOption['count'] = $this->voteCount;
        $tplOption['percents'] = $this->percents;
        $tplOption['voted'] = $this->voted;
        $tplOption['multiple'] = $this->multiple;
        $tplOption['disabled'] = $this->disabled;
        $tplOption['editMode'] = $this->editMode;

        $avatarList = new QUESTIONS_CMP_Avatars( $this->userIds, $this->voteCount );

        $tplOption['users'] = $avatarList->render();
        $this->assign('option', $tplOption);
    }

}