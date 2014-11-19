<?php



/**
 * Main page
 *
 * @author Hawk Wang <zhaole365@yahoo.com>
 * @package ow_plugins.zlareas.controllers
 * @since 1.0
 */
class ZLSEARCHENGINE_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
    	$language = OW::getLanguage();
    	
    	$this->setPageHeading($language->text('zlsearchengine', 'admin_page_heading'));
    	$this->setPageHeadingIconClass('ow_ic_comment');
    	
    	$configSaveForm = new ConfigForm();
    	$this->addForm($configSaveForm);
    	
    	if ( OW::getRequest()->isPost() && $configSaveForm->isValid($_POST) )
    	{
    		$configSaveForm->process();
    		OW::getFeedback()->info($language->text('zlsearchengine', 'settings_updated'));
    		$this->redirect();
    	}
    }
	

	

	
}

/**
 * Save Configurations form class
 */
class ConfigForm extends Form
{

	/**
	 * Class constructor
	 *
	 */
	public function __construct()
	{
		parent::__construct('configSaveForm');

		$language = OW::getLanguage();

		$configs = OW::getConfig()->getValues('zlsearchengine');

		$element = new TextField('searchengine_url');
		$element->setValue($configs['searchengine_url']);

		$validator = new UrlValidator();
		//$validator->setErrorMessage($language->text('zlsearchengine', 'searchengine_url_invalid'));

		$element->addValidator($validator);
		$this->addElement($element);

		// submit
		$submit = new Submit('save');
		$submit->setValue($language->text('base', 'edit_button'));
		$this->addElement($submit);
	}

	/**
	 * Updates forum plugin configuration
	 *
	 * @return boolean
	 */
	public function process()
	{
		$values = $this->getValues();

		$searchengine_url = empty($values['searchengine_url']) ? '' : $values['searchengine_url'];

		$config = OW::getConfig();

		$config->saveConfig('zlsearchengine', 'searchengine_url', $searchengine_url);

		return array('result' => true);
	}
}