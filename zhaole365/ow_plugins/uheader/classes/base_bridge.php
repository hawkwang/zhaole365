<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package uheader.classes
 */
class UHEADER_CLASS_BaseBridge
{
    /**
     * Class instance
     *
     * @var UHEADER_CLASS_BaseBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_BaseBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {

    }

    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        
        $event->add(array(
            "key" => "base-gender-age",
            "label" => $language->text("uheader", "info-gender-age-label")
        ));
        
        $event->add(array(
            "key" => "base-about",
            "label" => $language->text("uheader", "info-about-label")
        ));
        
        $event->add(array(
            "key" => "base-question",
            "label" => $language->text("uheader", "info-question-label")
        ));
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        
        $params = $event->getParams();
        
        switch ( $params["key"] )
        {
            case "base-gender-age":
                $event->setData($language->text("uheader", "info-gender-age-preview"));
                break;
            
            case "base-about":
                $event->setData($language->text("uheader", "info-about-preview"));
                break;
            
            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $questionLabel = BOL_QuestionService::getInstance()->getQuestionLang($params["question"]);
                    $event->setData($questionLabel);
                }
                break;
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        $out = null;
        
        switch ( $params["key"] )
        {
            case "base-gender-age":
                $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array("birthdate"));
                
                $ageStr = "";
                if ( !empty($questionData[$userId]['birthdate']) )
                {
                    $date = UTIL_DateTime::parseDate($questionData[$userId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                    $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                    $ageStr = $age . OW::getLanguage()->text('base', 'questions_age_year_old');
                }
                
                $sex = $this->renderQuestion($userId, "sex");
                $out = $sex . " " . $ageStr;
                break;
            
            case "base-about":
                $settings = BOL_ComponentEntityService::getInstance()->findSettingList("profile-BASE_CMP_AboutMeWidget", $userId, array(
                    'content'
                ));

                $out = empty($settings['content']) ? null : $settings['content'];
                break;
            
            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $out = $this->renderQuestion($userId, $params["question"]);
                }
                break;
        }
        
        $out = UTIL_String::truncate($out, 270, '...');
        
        $event->setData($out);
    }
    
    private function renderQuestion( $userId, $questionName )
    {
        $language = OW::getLanguage();
        
        $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array($questionName));
        if ( !isset($questionData[$userId][$questionName]) )
        {
            return null;
        }
        
        $question = BOL_QuestionService::getInstance()->findQuestionByName($questionName);
        
        switch ( $question->presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_DATE:

                $format = OW::getConfig()->getValue('base', 'date_field_format');

                $value = 0;

                switch ( $question->type )
                {
                    case BOL_QuestionService::QUESTION_VALUE_TYPE_DATETIME:

                        $date = UTIL_DateTime::parseDate($questionData[$userId][$question->name], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                        if ( isset($date) )
                        {
                            $format = OW::getConfig()->getValue('base', 'date_field_format');
                            $value = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
                        }

                        break;

                    case BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:

                        $value = (int)$questionData[$userId][$question->name];

                        break;
                }

                if ( $format === 'dmy' )
                {
                    $questionData[$userId][$question->name] = date("d/m/Y",$value) ;
                }
                else
                {
                    $questionData[$userId][$question->name] = date("m/d/Y", $value);
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:

                $date = UTIL_DateTime::parseDate($questionData[$userId][$question->name], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $questionData[$userId][$question->name] = UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']);

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_AGE:

                $date = UTIL_DateTime::parseDate($questionData[$userId][$question->name], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $questionData[$userId][$question->name] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']) . " " . $language->text('base', 'questions_age_year_old');

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_RANGE:

                $range = explode('-', $questionData[$userId][$question->name] );
                $questionData[$userId][$question->name] = $language->text('base', 'form_element_from') ." ". $range[0] ." ". $language->text('base', 'form_element_to') ." ". $range[1];

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
            case BOL_QuestionService::QUESTION_PRESENTATION_RADIO:
            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

                $value = "";
                $multicheckboxValue = (int) $questionData[$userId][$question->name];

                $questionValues = BOL_QuestionService::getInstance()->findQuestionValues($question->name);

                foreach( $questionValues as $val )
                {

                    /* @var $val BOL_QuestionValue */

                    if ( ( (int) $val->value ) & $multicheckboxValue )
                    {
                        if ( strlen($value) > 0 )
                        {
                            $value .= ', ';
                        }

                        $value .= $language->text('base', 'questions_question_' . $question->name . '_value_' . ($val->value));
                    }
                }

                if ( strlen($value) > 0 )
                {
                    $questionData[$userId][$question->name] = $value;
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_URL:
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXT:
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA:

                $value = trim($questionData[$userId][$question->name]);

                if ( strlen($value) > 0 )
                {
                    $questionData[$userId][$question->name] = UTIL_HtmlTag::autoLink(nl2br($value));
                }

                break;
                
            default :
                $questionData[$userId][$question->name] = null;
        }
        
        return $questionData[$userId][$question->name];
    }

    public function init()
    {
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));
    }
}