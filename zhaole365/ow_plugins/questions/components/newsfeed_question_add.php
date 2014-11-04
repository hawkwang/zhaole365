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

require_once OW_DIR_CORE . 'form_element.php';

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package questions.components
 */
class QUESTIONS_CMP_NewsfeedQuestionAdd extends QUESTIONS_CMP_QuestionAdd
{
    private $feedAutoId, $feedType, $feedId, $actionVisibility;

    public function __construct($feedAutoId, $feedType, $feedId, $actionVisibility = null)
    {
        parent::__construct();

        $this->feedAutoId = $feedAutoId;
        $this->feedType = $feedType;
        $this->feedId = $feedId;
        $this->actionVisibility = $actionVisibility;
    }

    public function initForm()
    {
       return new QUESTIONS_NewsfeedAddForm($this->feedAutoId, $this->feedType, $this->feedId, $this->actionVisibility);
    }
}

class QUESTIONS_NewsfeedAddForm extends QUESTIONS_AddForm
{
    private $feedAutoId;

    public function __construct($feedAutoId, $feedType, $feedId, $actionVisibility)
    {
        $this->feedAutoId = $feedAutoId;

        parent::__construct();

        $field = new HiddenField('feedType');
        $field->setValue($feedType);
        $this->addElement($field);

        $field = new HiddenField('feedId');
        $field->setValue($feedId);
        $this->addElement($field);

        $field = new HiddenField('visibility');
        $field->setValue($actionVisibility);
        $this->addElement($field);

        $this->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('QUESTIONS_CTRL_Questions', 'newsfeedAdd')) );
    }

    public function initJsResponder()
    {

        //ow_newsfeed_status_input
        $js = UTIL_JsGenerator::composeJsString('
        OW.bind("questions.tabs_changed", function(tab){
            var status = this.$(".ow_newsfeed_status_input");
            var sVal = status.hasClass("invitation") ? "" : status.val();
            var qVal = owForms["questions_add"].getElement("question").getValue();

            tab.newTab.find("textarea").focus();

            if ( !status.hasClass("invitation") && sVal && !qVal )
            {
                owForms["questions_add"].getElement("question").setValue(sVal);
                $(owForms["questions_add"].getElement("question").input).triggerHandler("keyup");
            }
        });
        owForms["questions_add"].bind( "success", function( r )
        {
            var form = owForms["questions_add"];

            if ( r )
            {
                if ( r.questionId )
                {
                    form.getElement("answers").resetValue();
                    form.getElement("question").resetValue();
                    QUTILS.addInvitation(form.getElement("question").input);
                    OW.trigger("questions.after_question_add", [r]);

                    window.ow_newsfeed_feed_list[{$autoId}].loadNewItem({"entityType": {$entityType}, "entityId": r.questionId}, false);
                }

                if ( r.warning )
            {
                    OW.warning(r.warning);
                }
            }
            else
            {
                OW.error({$errorMessage});
            }
        });', array(
            'autoId' => $this->feedAutoId,
            'errorMessage' => OW::getLanguage()->text('base', 'form_validate_common_error_message'),
            'entityType' => QUESTIONS_BOL_Service::ENTITY_TYPE
        ));

        OW::getDocument()->addOnloadScript( $js );
    }
}
