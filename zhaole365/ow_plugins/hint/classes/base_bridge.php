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
 * @package hint.classes
 */
class HINT_CLASS_BaseBridge
{
    /**
     * Class instance
     *
     * @var HINT_CLASS_BaseBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return HINT_CLASS_BaseBridge
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

    public function onCollectButtons( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $userId = $params["entityId"];
        $uniqId = uniqid("hint-af-");
        $label = OW::getLanguage()->text("hint", "button_view_profile_label");
        $url = BOL_UserService::getInstance()->getUserUrl($userId);

        $button = array(
            "key" => "view",
            "label" => $label,
            "attrs" => array("id" => $uniqId, "href" => $url, "target" => "_blank")
        );

        $event->add($button);
    }

    public function onCollectButtonsPreview( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("hint", "button_view_profile_label");

        $button = array(
            "key" => "view",
            "label" => $label,
            "attrs" => array("href" => "javascript://")
        );

        $event->add($button);
    }

    public function onCollectButtonsConfig( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }

        $label = OW::getLanguage()->text("hint", "button_view_profile_config");

        $active = HINT_BOL_Service::getInstance()->isActionActive(HINT_BOL_Service::ENTITY_TYPE_USER, "view");

        $button = array(
            "key" => "view",
            "active" => $active === null ? false : $active,
            "label" => $label
        );

        $event->add($button);
    }

    public function onHintRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != HINT_BOL_Service::ENTITY_TYPE_USER )
        {
            return;
        }
    }

    public function onQuery( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params["command"], array()) )
        {
            return;
        }

        $userId = $params["params"]['userId'];

        $info = null;
        $error = null;


        $event->setData(array(
            "info" => $info,
            "error" => $error
        ));
    }
    
    public function onCollectInfoConfigs( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        
        $params = $event->getParams();
        
        $event->add(array(
            "key" => "base-gender-age",
            "label" => $language->text("hint", "info-gender-age-label")
        ));
        
        $event->add(array(
            "key" => "base-activity",
            "label" => $language->text("hint", "info-activity-label")
        ));
        
        if ( $params["line"] != HINT_BOL_Service::INFO_LINE0 )
        {
            $event->add(array(
                "key" => "base-about",
                "label" => $language->text("hint", "info-about-label")
            ));
        }
        
        $event->add(array(
            "key" => "base-question",
            "label" => $language->text("hint", "info-question-label")
        ));
    }
    
    public function onInfoPreview( OW_Event $event )
    {
        $language = OW::getLanguage();
        
        $params = $event->getParams();
        
        switch ( $params["key"] )
        {
            case "base-gender-age":
                $event->setData($language->text("hint", "info-gender-age-preview"));
                break;
            
            case "base-activity":
                $event->setData($language->text("hint", "info-activity-preview"));
                break;
            
            case "base-about":
                $event->setData('<span class="ow_remark">' . $language->text("hint", "info-about-preview") . '</span>');
                break;
            
            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $questionLabel = BOL_QuestionService::getInstance()->getQuestionLang($params["question"]);
                    
                    if ( $params["line"] == HINT_BOL_Service::INFO_LINE2 )
                    {
                        $questionLabel = '<span class="ow_remark">' . $questionLabel . '</span>';
                    }
                    
                    $event->setData($questionLabel);
                }
                break;
        }
    }
    
    public function onInfoRender( OW_Event $event )
    {
        $params = $event->getParams();
        $entityType = $params["entityType"];
        $entityId = $params["entityId"];
        
        switch ( $params["key"] )
        {
            case "base-gender-age":
                $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($entityId), array("birthdate"));
                
                $ageStr = "";
                if ( !empty($questionData[$entityId]['birthdate']) )
                {
                    $date = UTIL_DateTime::parseDate($questionData[$entityId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                    $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                    $ageStr = $age . " " . OW::getLanguage()->text('base', 'questions_age_year_old');
                }
                
                $sex = $this->renderQuestion($entityId, "sex");
                $event->setData($sex . " " . $ageStr );
                break;
            
            case "base-about":
                $settings = BOL_ComponentEntityService::getInstance()->findSettingList("profile-BASE_CMP_AboutMeWidget", $entityId, array(
                    'content'
                ));

                $content = empty($settings['content']) ? null : UTIL_String::truncate($settings['content'], 100, '...');
                
                $event->setData('<span class="ow_remark ow_small">' . $content . '</span>');
                break;
            
            case "base-activity":
                // Check privacy permissions
                $eventParams = array(
                    'action' => 'base_view_my_presence_on_site',
                    'ownerId' => $entityId,
                    'viewerId' => OW::getUser()->getId()
                );
                try
                {
                    OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $e )
                {
                    break;
                }
                
                $isOnline = BOL_UserService::getInstance()->findOnlineUserById($entityId);
                
                if ( $isOnline )
                {
                    $event->setData(OW::getLanguage()->text("base", "activity_online"));
                }
                else 
                {
                    $user = BOL_UserService::getInstance()->findUserById($entityId);
                    $activity = UTIL_DateTime::formatDate($user->activityStamp);
                    
                    $event->setData(OW::getLanguage()->text("hint", "info-activity", array(
                        "activity" => $activity
                    )));
                }
                
                break;
            
            case "base-question":
                if ( !empty($params["question"]) )
                {
                    $renderedQuestion = $this->renderQuestion($entityId, $params["question"]);
                    
                    if ( $params["line"] == HINT_BOL_Service::INFO_LINE2 )
                    {
                        $renderedQuestion = '<span class="ow_remark">' . $renderedQuestion . '</span>';
                    }
                    
                    $event->setData($renderedQuestion);
                }
                break;
        }
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
            /*case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:
                
                if ( (int) $questionData[$userId][$question->name] === 1 )
                {
                    $questionData[$userId][$question['name']] = $language->text('base', 'questions_checkbox_value_true');
                }
                else
                {
                    $questionData[$userId][$question['name']] = $language->text('base', 'questions_checkbox_value_false');
                }

                break;*/

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

                // googlemap_location shortcut
                if ( $question->name == "googlemap_location" 
                        && !empty($questionData[$userId][$question->name]) 
                        && is_array($questionData[$userId][$question->name]) )
                {
                    $mapData = $questionData[$userId][$question->name];
                    $value = trim($mapData["address"]);
                }
                else
                {
                    $value = trim($questionData[$userId][$question->name]);
                }
                
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
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS, array($this, 'onCollectButtons'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_PREVIEW, array($this, 'onCollectButtonsPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_BUTTONS_CONFIG, array($this, 'onCollectButtonsConfig'));
        
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_COLLECT_INFO_CONFIG, array($this, 'onCollectInfoConfigs'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_PREVIEW, array($this, 'onInfoPreview'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_INFO_RENDER, array($this, 'onInfoRender'));

        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_HINT_RENDER, array($this, 'onHintRender'));
        OW::getEventManager()->bind(HINT_BOL_Service::EVENT_QUERY, array($this, 'onQuery'));
    }
}