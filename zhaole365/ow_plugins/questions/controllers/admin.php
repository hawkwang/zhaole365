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
 * @package questions.controllers
 */
class QUESTIONS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function getMenu()
    {
        $item[0] = new BASE_MenuItem(array());

        $item[0]->setLabel(OW::getLanguage()->text('questions', 'admin_general_settings'));
        $item[0]->setIconClass('ow_ic_lens');

        $item[0]->setKey('1');

        $item[0]->setUrl(
            OW::getRouter()->urlForRoute('questions-admin-main')
        );

        $item[0]->setOrder(1);

        $item[1] = new BASE_MenuItem(array());

        $item[1]->setLabel(OW::getLanguage()->text('questions', 'admin_extended_version'));
        $item[1]->setIconClass('ow_ic_star q-extended-tab');
        $item[1]->setKey('2');
        $item[1]->setUrl(
            OW::getRouter()->urlForRoute('questions-upgrade')
        );

        $item[1]->setOrder(2);

        return new BASE_CMP_ContentMenu($item);
    }

    public function main()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('questions', 'admin_main_page_heading'));
        $this->setPageTitle($language->text('questions', 'admin_main_page_title'));
        $this->setPageHeadingIconClass('ow_ic_lens');

        $configs = OW::getConfig()->getValues('questions');
        $this->assign('configs', $configs);

        $form = new QUESTIONS_ConfigSaveForm($configs);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if ( $form->process($_POST) )
            {
                OW::getFeedback()->info($language->text('questions', 'admin_settings_updated'));
                $this->redirect(OW::getRouter()->urlForRoute('questions-admin-main'));
            }
        }

        $this->addComponent('menu', $this->getMenu());
    }
}

class QUESTIONS_ConfigSaveForm extends Form
{
    private $configs = array();

    public function __construct( $configs )
    {
        parent::__construct('QUESTIONS_ConfigSaveForm');

        $this->configs = $configs;

        $language = OW::getLanguage();

        $field = new CheckboxField('allow_comments');
        $field->setLabel($language->text('questions', 'admin_allow_comments_label'));
        $field->setValue($configs['allow_comments']);
        $this->addElement($field);

        $field = new Selectbox('list_order');
        foreach ( array(QUESTIONS_CMP_Feed::ORDER_LATEST, QUESTIONS_CMP_Feed::ORDER_POPULAR) as $v )
        {
            $field->addOption($v, $language->text('questions', 'feed_order_' . $v));
        }
        $field->setHasInvitation(false);
        $field->setLabel($language->text('questions', 'admin_list_order_label'));
        $field->setValue($configs['list_order']);
        $this->addElement($field);

        $field = new CheckboxField('enable_follow');
        $field->setLabel($language->text('questions', 'admin_enable_follow_label'));
        $field->setValue($configs['enable_follow']);
        $this->addElement($field);

        $field = new CheckboxField('allow_popups');
        $field->setLabel($language->text('questions', 'admin_allow_popups_label'));
        $field->setValue($configs['allow_popups']);
        $this->addElement($field);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('questions', 'admin_save_btn'));
        $this->addElement($submit);
    }

    public function process( $data )
    {
        $config = OW::getConfig();

        foreach ( $this->configs as $k => $v )
        {
            $element = $this->getElement($k);

            if ( $element !== null )
            {
                $v = $element->getValue();
                $config->saveConfig('questions', $k, $v === null ? 0 : $v);
            }
        }

        return true;
    }
}