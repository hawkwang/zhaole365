<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * User list component
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests.components
 * @since 1.3.1
 */
class OCSGUESTS_CMP_Users extends BASE_CMP_Users
{
    private $guests;

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $guests )
    {
        $this->guests = $guests;

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $lang = OW::getLanguage();

        $fields = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {
            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }

            $fields[$uid][] = array(
                'label' => ' ',
                'value' => $this->guests[$uid]['last_visit']
            );
        }

        return $fields;
    }
}