<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package uheader.controllers
 */
class UHEADER_CTRL_Templates extends ADMIN_CTRL_Abstract
{
    const CANVAS_WIDTH = 660;
    const ITEM_WIDTH = UHEADER_BOL_Service::TEMPLATE_ITEM_WIDTH;
    
    
    /**
     *
     * @var UHEADER_BOL_Service
     */
    private $service;
    
    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private $config = array();
    
    public function __construct()
    {
        parent::__construct();

        $this->service = UHEADER_BOL_Service::getInstance();
        $this->plugin = OW::getPluginManager()->getPlugin("uheader");
        
        //66 - for 1000px themes
        //84 - for 780px themes
        $scale = (OW::getThemeManager()->getSelectedTheme()->getDto()->sidebarPosition == "none" ? 69 : 88) / 100;
        
        $this->config = OW::getConfig()->getValues("uheader");
        $this->config["avatar_big_size"] = OW::getConfig()->getValue("base", "avatar_big_size") * $scale;
        
        $this->config["cover_height"] *= $scale;
    }
    
    public function index( $params )
    {
        $document = OW::getDocument();
        
        $menu = UHEADER_CTRL_Admin::getMenu();
        $menu->getElement("gallery")->setActive(true);
        $this->addComponent("menu", $menu);
        
        $tplId = empty($params["tplId"]) ? null : $params["tplId"];
        $roleId = empty($_GET["role"]) ? null : $_GET["role"];
        
        UHEADER_CLASS_Plugin::getInstance()->includeStatic();
        UHEADER_CLASS_Plugin::getInstance()->includeStaticFile("admin.css");
        
        $uniqId = uniqid("uheader-cg-");

        if ( empty($roleId) && !empty($tplId) )
        {
            $roles = $this->service->findRoleIdsByTemplateId($tplId);
            if ( !empty($roles) )
            {
                $roleId = reset($roles);
            }
        }
        
        $roleIds = $roleId === null ? null : array($roleId);
        
        $list = $this->service->findTemplateList($roleIds);
        $tplList = array();
        foreach ( $list as $template )
        {
            /*@var $template UHEADER_BOL_Template */
            $tplList[$template->id] = $this->service->getTemplateInfo($template);
        }
        
        $currentItem = $tplId === null || empty($tplList[$tplId]) ? reset($tplList) : $tplList[$tplId];
        
        $this->assign("list", $tplList);
        $this->assign("current", $currentItem);
        
        $this->assign("uploader", OW::getRouter()->urlFor(__CLASS__, "templateUploader"));
        $this->assign("uniqId", $uniqId);
        
        $config = array();
        $config['avatarSize'] = $this->config['avatar_big_size'];
        $config['coverHeight'] = $this->config["cover_height"];
        
        $config['previewHeight'] = self::ITEM_WIDTH / self::CANVAS_WIDTH * $config['coverHeight'];
        
        
        $this->assign("config", $config);
        
        $this->addComponent("contextToolbar", $this->getContextToolbar());
        
        $settings = array(
            "rsp" => OW::getRouter()->urlFor(__CLASS__, "rsp"),
            "current" => $currentItem
        );
        
        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array("UHEADER", "activeGallery"), "UHEADER.AdminGallery", array(
            $uniqId, $settings
        ));
        
        $document->addOnloadScript($js);
        
        $roles = BOL_AuthorizationService::getInstance()->findNonGuestRoleList();
        $tplRoles = array();
        
        foreach ( $roles as $role )
        {
            /* @var $role BOL_AuthorizationRole */
            $roleLabel = BOL_AuthorizationService::getInstance()->getRoleLabel($role->name);
            $tplRoles[] = array(
                "id" => $role->id,
                "selected" => $roleId == $role->id,
                "label" => $roleLabel,
                "url" => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute("uheader-settings-gallery"), array(
                    "role" => $role->id
                ))
            );
        }
        $this->assign("currentRoleId", $roleId);
        $this->assign("roleList", $tplRoles);
        $this->assign("allListUrl", OW::getRouter()->urlForRoute("uheader-settings-gallery"));
        
        $this->assign("previewWidth", self::ITEM_WIDTH);
        
        $this->initInfoLines();
        
        $this->assign("pluginUrl", UHEADER_CTRL_Admin::PLUGIN_STORE_URL);
    }
    
    private function findQuestions()
    {
        $ignorePresentations = array(BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX);
        
        $questions = BOL_QuestionService::getInstance()->findAllQuestions();
        
        $out = array();
        
        foreach ( $questions as $question )
        {
            /* @var $question BOL_Question */
            
            if ( !$question->onView || in_array($question->presentation, $ignorePresentations) )
            {
                continue;
            }
            
            $out[$question->name] = BOL_QuestionService::getInstance()->getQuestionLang($question->name);
        }
        
        return $out;
    }
    
    private function getInfoLine( $line )
    {
        $info = $this->service->getInfoConfig($line);
        $lineOptions = $this->service->getInfoLineSettings($line);
        
        $questionOptions = $this->findQuestions();

        $field = array();
        $field["name"] = "info_" . $line;
        $field["id"] = $line . "_id";
        $field["options"] = array();
        foreach ( $lineOptions as $option )
        {
            $field["options"][$option["key"]] = $option["label"];
        }
        
        $field["selected"] = empty($info["key"]) ? null : $info["key"];

        $questionField = array();
        $questionField["name"] = "info_" . $line . "_question";
        $questionField["id"] = $line . "_q";
        $questionField["options"] = $questionOptions;
        $questionField["selected"] = empty($info["question"]) ? null : $info["question"];
        
        $field["question"] = $questionField;
        
        $field["preview"] = null;
        if ( $field["selected"] !== null )
        {
            $field["preview"] = $this->service->getInfoLinePreview($field["selected"], $field["question"]["selected"], $line);
        }
        
        return $field;
    }
    
    private function initInfoLines()
    {
        $infoLines = array(
            UHEADER_BOL_Service::INFO_LINE1 => $this->getInfoLine(UHEADER_BOL_Service::INFO_LINE1),
            UHEADER_BOL_Service::INFO_LINE2 => $this->getInfoLine(UHEADER_BOL_Service::INFO_LINE2)
        );
        
        $this->assign("infoLines", $infoLines);
    }
    
    /**
     *
     * @return BASE_CMP_ContextAction
     */
    private function getContextToolbar()
    {
        $language = OW::getLanguage();
        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextActionMenu->setClass("ow_photo_context_action");

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('uheaderToolbar');
        $contextParentAction->setLabel($language->text("uheader", "admin_edit_cover_label"));
        $contextParentAction->setId('uh-toolbar-parent');

        $contextActionMenu->addAction($contextParentAction);
        
        $contextAction = new BASE_ContextAction();
        $contextAction->setParentKey($contextParentAction->getKey());
        $contextAction->setLabel($language->text('uheader', 'reposition_label'));
        $contextAction->setUrl('javascript://');
        $contextAction->setKey('uhReposition');
        $contextAction->setId('uhco-reposition');
        $contextAction->setClass('uhco-item uhco-reposition');
        $contextAction->setOrder(1);

        $contextActionMenu->addAction($contextAction);

        $contextAction = new BASE_ContextAction();
        $contextAction->setParentKey($contextParentAction->getKey());
        $contextAction->setLabel($language->text('uheader', 'remove_label'));
        $contextAction->setUrl('javascript://');
        $contextAction->setKey('uhRemove');
        $contextAction->setId('uhco-remove');
        $contextAction->setClass('uhco-item uhco-remove');

        $contextAction->setOrder(2);

        $contextActionMenu->addAction($contextAction);
        

        return $contextActionMenu;
    }
    
    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_POST['command']);
        $query = json_decode($_POST['params'], true);

        try
        {
            $response = call_user_func(array($this, $command), $query);
        }
        catch ( InvalidArgumentException $e )
        {
            $response = array(
                'type' => 'error',
                'error' => $e->getMessage()
            );
        }

        $response = empty($response) ? array() : $response;
        echo json_encode($response);
        exit;
    }
    
    private function loadTemplate( $params )
    {
        $tpl = $this->service->findTemplateById($params["id"]);
        $cover = $this->service->getTemplateInfo($tpl);

        return array(
            "cover" => $cover
        );
    }
    
    private function saveInfoLines( $params )
    {
        $info[UHEADER_BOL_Service::INFO_LINE1] = null;
        $info[UHEADER_BOL_Service::INFO_LINE2] = null;
        
        $line1key = empty($params[UHEADER_BOL_Service::INFO_LINE1]["key"]) ? null : $params[UHEADER_BOL_Service::INFO_LINE1]["key"];
        $line1question = empty($params[UHEADER_BOL_Service::INFO_LINE1]["question"]) ? null : $params[UHEADER_BOL_Service::INFO_LINE1]["question"];
        if ( $line1key !== null )
        {
            $info[UHEADER_BOL_Service::INFO_LINE1] = $this->service->getInfoLinePreview($line1key, $line1question, UHEADER_BOL_Service::INFO_LINE1);
        }
        
        $this->service->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE1, $line1key, $line1question);
        
        $line2key = empty($params[UHEADER_BOL_Service::INFO_LINE2]["key"]) ? null : $params[UHEADER_BOL_Service::INFO_LINE2]["key"];
        $line2question = empty($params[UHEADER_BOL_Service::INFO_LINE2]["question"]) ? null : $params[UHEADER_BOL_Service::INFO_LINE2]["question"];
        if ( $line2key !== null )
        {
            $info[UHEADER_BOL_Service::INFO_LINE2] = $this->service->getInfoLinePreview($line2key, $line2question, UHEADER_BOL_Service::INFO_LINE2);
        }
        
        $this->service->saveInfoConfig(UHEADER_BOL_Service::INFO_LINE2, $line2key, $line2question);
        
        return array(
            "infoLines" => $info
        );
    }
    
    public function saveReposition( $coverData )
    {
        $tplId = (int) $coverData['tplId'];
        $tpl = $this->service->findTemplateById($tplId);
        $data = $tpl->getSettings();
        $tpl->setSettings(array_merge($data, $coverData));
        $this->service->saveTemplate($tpl);
        
        return array(
            "cover" => $this->service->getTemplateInfo($tpl)
        );
    }

    public function cancelReposition( $coverData )
    {
        $tplId = (int) $coverData['tplId'];
        $tpl = $this->service->findTemplateById($tplId);
        
        return array(
            "cover" => $this->service->getTemplateInfo($tpl)
        );
    }

    public function removeTemplate( $coverData )
    {
        $tplId = (int) $coverData['tplId'];

        $this->service->deleteTemplateById($tplId);

        $message = OW::getLanguage()->text('uheader', 'template_remove_success');

        return array(
            'message' => $message
        );
    }
    
    public function saveInfo( $coverData )
    {
        $info = $coverData["info"];
        $tplId = (int) $coverData['tplId'];
        
        
        $tpl = $this->service->findTemplateById($tplId);
        /* @var UHEADER_BOL_Template $tpl */
        $tpl->default = $info["default"];

        $this->service->saveTemplate($tpl);
        
        $error = null;
        if ( empty($info["roles"]) )
        {
            $error = OW::getLanguage()->text("uheader", "template_no_roles_selected_error");
        }
        else
        {
            $this->service->saveRoleIdsForTemplateId($tplId, $info["roles"]);
        }
        
        
        return array(
            "cover" => $this->service->getTemplateInfo($tpl),
            "warning" => $error
        );
    }
    
    public function templateUploader()
    {
        $roleId = empty($_POST["roleId"]) ? null : $_POST["roleId"];
        
        if ( empty($_FILES["templates"]) )
        {
            OW::getFeedback()->warning(OW::getLanguage()->text("uheader", "template_upload_max_post_error"));
            
            $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute("uheader-settings-gallery"), array(
                "role" => $roleId
            )));
        }
        
        $FILES = $_FILES["templates"];
        
        $invalidList = array();
        $fileIndex = 0;
        $uploadedFiles = 0;
        
        foreach ($FILES["name"] as $fileName)
        {
            if ( !UTIL_File::validateImage($fileName) )
            {
                $invalidList[] = $fileName;
                continue;
            }
            
            $tplData = array(
                "name" => $FILES["name"][$fileIndex],
                "type" => $FILES["type"][$fileIndex],
                "tmp_name" => $FILES["tmp_name"][$fileIndex],
                "size" => $FILES["size"][$fileIndex],
                "error" => $FILES["error"][$fileIndex]
            );
            
            $fileIndex++;
            
            try 
            {
                $this->uploadTemplate($tplData);
                $this->addTemplate($tplData, $roleId);
                $uploadedFiles++;
            }
            catch ( InvalidArgumentException $e )
            {
                OW::getFeedback()->error($e->getMessage());
            }
        }
        
        if ( $fileIndex > 1 )
        {
            if ( $uploadedFiles == 0 )
            {
                OW::getFeedback()->warning(OW::getLanguage()->text("uheader", "template_upload_no_uploaded_warning"));
            }
            else if ( $uploadedFiles < $fileIndex  )
            {
                OW::getFeedback()->warning(OW::getLanguage()->text("uheader", "template_upload_some_not_uploaded_warning"));
            }
        }
        
        $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute("uheader-settings-gallery"), array(
            "role" => $roleId
        )));
    }
    
    private function uploadTemplate( $file )
    {
        $language = OW::getLanguage();
        $error = false;

        if ( !is_uploaded_file($file['tmp_name']) )
        {
            $error = $language->text('base', 'upload_file_fail');
        }
        else if ( $file['error'] != UPLOAD_ERR_OK )
        {
            switch ( $file['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }
        }

        if ( $error !== false )
        {
            throw new InvalidArgumentException($error);
        }
    }
    
    private function validateFile( $file )
    {
        $language = OW::getLanguage();

        if ( !UTIL_File::validateImage($file['name']) )
        {
            $error = $language->text('uheader', 'upload_file_extension_is_not_allowed');
            throw new InvalidArgumentException($error);
        }

        if ( (int) $file['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024 )
        {
            $error = $language->text('base', 'upload_file_max_upload_filesize_error');
            throw new InvalidArgumentException($error);
        }
    }

    private function validateImage( UTIL_Image $image )
    {
        $canvasHeight = $this->config['cover_height'];
        $minWidth = 300;
        $minHeight = round($canvasHeight / 2);

        $width = $image->getWidth();
        $height = $image->getHeight();

        if ( $width < $minWidth || $height < $minHeight )
        {
            $error = OW::getLanguage()->text('uheader', 'validation_error_image_size', array(
                'minWidth' => $minWidth,
                'minHeight' => $minHeight
            ));

            throw new InvalidArgumentException($error);
        }
    }

    private function addTemplate( $file, $roleId = null )
    {
        $canvasWidth = self::CANVAS_WIDTH;
        $canvasHeight = $this->config['cover_height'];

        $this->validateFile($file);

        $pluginfilesDir = OW::getPluginManager()->getPlugin('uheader')->getPluginFilesDir();
        $tmpTplPath = $pluginfilesDir . uniqid('tmp_') . '.jpg';

        if ( !move_uploaded_file($file['tmp_name'], $tmpTplPath) )
        {
            throw new InvalidArgumentException('Moving uploaded file faild');
        }

        $coverImage = new UTIL_Image($tmpTplPath);
        $imageHeight = $coverImage->getHeight();
        $imageWidth = $coverImage->getWidth();

        $css = array(
            'width' => 'auto',
            'height' => 'auto'
        );

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;

        if ( $tmp >= $canvasHeight )
        {
            $css['width'] = '100%';
        }
        else
        {
            $css['height'] = '100%';
        }

        $this->validateImage($coverImage, $canvasWidth, $canvasHeight );

        $template = new UHEADER_BOL_Template();
        $extension = UTIL_File::getExtension($file['name']);
        $template->file = uniqid('template-') . '.' . $extension;
        $template->default = false;
        $template->timeStamp = time();

        $dimensions = array(
            'height' => $imageHeight,
            'width' => $imageWidth
        );

        $template->setSettings(array(
            'dimensions' => $dimensions,
            'css' => $css,
            'canvas' => array(
                'width' => $canvasWidth,
                'height' => $canvasHeight
            ),
            'position' => array(
                'top' => 0,
                'left' => 0
            )
        ));

        $this->service->saveTemplate($template);
        
        if ( $roleId !== null )
        {
            $this->service->saveRoleIdsForTemplateId($template->id, array($roleId));
        }

        $templatePath = $this->service->getTemplatePath($template);
        OW::getStorage()->copyFile($tmpTplPath, $templatePath);
        @unlink($tmpTplPath);
    }
}