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
class QUESTIONS_CMP_QuestionAdd extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        if ( !QUESTIONS_BOL_Service::getInstance()->isCurrentUserCanAsk() )
        {
            $this->setVisible(false);

            return;
        }

        $template = OW::getPluginManager()->getPlugin('questions')->getCmpViewDir() . 'question_add.html';
        $this->setTemplate($template);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $uniqId = uniqid('questionAdd');
        $this->assign('uniqId', $uniqId);

        $config = OW::getConfig()->getValues(QUESTIONS_Plugin::PLUGIN_KEY);

        $this->assign('configs', $config);

        $form = $this->initForm();
        $this->addForm($form);

        QUESTIONS_Plugin::getInstance()->addStatic();

        $js = UTIL_JsGenerator::newInstance()->newObject('questionsAdd', 'QUESTIONS_QuestionAdd', array($uniqId, $form->getName(), array(
            'maxQuestionLength' => 500,
            'minQuestionLength' => 3,
            'maxAnswerLength' => 150
        )));
        
        OW::getDocument()->addOnloadScript($js);

    }

    public function initForm()
    {
        return new QUESTIONS_AddForm();
    }
}

class QUESTIONS_AddForm extends Form
{
    public function __construct()
    {
        parent::__construct('questions_add');

        $language = OW::getLanguage();

        $this->setAjax();
        $this->setAjaxResetOnSuccess(false);

        $field = new Textarea('question');
        $field->addAttribute('maxlength', 500);
        $field->setRequired();
        $field->setHasInvitation(true);
        $field->setInvitation( $language->text('questions', 'question_add_text_inv') );
        $field->addAttribute( "inv", $language->text('questions', 'question_add_text_inv') );
        
        $this->addElement($field);

        $field = new CheckboxField('allowAddOprions');
        $field->addAttribute('checked');
        $field->setLabel( $language->text('questions', 'question_add_allow_add_opt') );
        $this->addElement($field);

        $field = new QUESTIONS_OptionsField('answers');
        $field->setHasInvitation(true);
        $field->setInvitation( $language->text('questions', 'question_add_option_inv') );
        $this->addElement($field);


        $submit = new Submit('save');
        $submit->setValue($language->text('questions', 'question_add_save'));
        $this->addElement($submit);

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getLanguage()->addKeyForJs('questions', 'feedback_question_empty');
            OW::getLanguage()->addKeyForJs('questions', 'feedback_question_min_length');
            OW::getLanguage()->addKeyForJs('questions', 'feedback_question_max_length');
            OW::getLanguage()->addKeyForJs('questions', 'feedback_question_two_apt_required');
            OW::getLanguage()->addKeyForJs('questions', 'feedback_question_dublicate_option');
            OW::getLanguage()->addKeyForJs('questions', 'feedback_option_max_length');

            $this->initJsResponder();
        }

        $this->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('QUESTIONS_CTRL_List', 'addQuestion')) );
    }

    public function initJsResponder()
    {
        $js = UTIL_JsGenerator::composeJsString(' owForms["questions_add"].bind( "success", function( r )
        {
            var form = owForms["questions_add"];
            if ( r.reset !== false )
            {
                form.getElement("answers").resetValue();
                form.getElement("question").resetValue();
                QUTILS.addInvitation(form.getElement("question").input);

                OW.trigger("questions.after_question_add", [r]);
            }

            if ( r )
            {
                window.QUESTIONS_ListObject.ajaxSuccess(r);
            }

        });');

        OW::getDocument()->addOnloadScript( $js );
    }
}

/**
 * Form element: TextField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class QUESTIONS_OptionsField extends InvitationFormElement
{
    private $itemIds = array();

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = array() )
    {
        $value = $this->getValue();
        $countValue = empty($value) ? 3 : count($value) + 1;
        $count = $countValue > 3 ? $countValue : 3;
        $content = $this->renderItem(-1, true);

        for ( $i=0; $i < $count; $i++ )
        {
            $content .= $this->renderItem($i);
        }

        return UTIL_HtmlTag::generateTag('div', array_merge($this->attributes, $params), true, $content);
    }

    private function renderItem( $index, $proto = false )
    {
        $value = $this->getValue();

        $inputAttrs = array(
            'type' => 'text',
            'maxlength' => 150,
            'name' => $this->getName() . '[]',
            'class' => 'mt-item-input',
            'value' => empty($value[$index]) ? '' : $value[$index]
        );

        $contAttrs = array(
            'class' => 'mt-item ow_smallmargin'
        );

        if ( $proto )
        {
            $inputAttrs['value'] = '';
            $contAttrs['style'] = 'display: none;';
        }

        if ( $this->getHasInvitation() && empty($inputAttrs['value']) )
        {
            $inputAttrs['value'] = $this->invitation;
            $inputAttrs['class'] .= ' invitation';
        }

        $input = UTIL_HtmlTag::generateTag('input', $inputAttrs);

        return UTIL_HtmlTag::generateTag('div', $contAttrs, true, $input);
    }

    public function getElementJs() {

        $js = UTIL_JsGenerator::newInstance()->newObject('formElement', 'QUESTIONS_AnswersField', array(
            $this->getId(), $this->getName(), ($this->getHasInvitation() ? $this->getInvitation() : false)
        ));

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
             $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return  $js;
    }
}
