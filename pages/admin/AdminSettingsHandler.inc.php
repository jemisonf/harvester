<?php

/**
 * AdminSettingsHandler.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 *
 * Handle requests for changing site admin settings. 
 *
 * $Id$
 */

class AdminSettingsHandler extends AdminHandler {
	
	/**
	 * Display form to modify site settings.
	 */
	function settings() {
		parent::validate();
		parent::setupTemplate(true);
		
		import('admin.form.SiteSettingsForm');

		$settingsForm = &new SiteSettingsForm();
		$settingsForm->initData();
		$settingsForm->display();
	}
	
	/**
	 * Validate and save changes to site settings.
	 */
	function saveSettings() {
		parent::validate();
		parent::setupTemplate(true);
		
		import('admin.form.SiteSettingsForm');
		
		$settingsForm = &new SiteSettingsForm();
		$settingsForm->readInputData();
		
		if ($settingsForm->validate()) {
			$settingsForm->execute();
		
			$templateMgr = &TemplateManager::getManager();
			$templateMgr->assign(array(
				'currentUrl' => 'admin/settings',
				'pageTitle' => 'admin.siteSettings',
				'message' => 'common.changesSaved',
				'backLink' => Request::getPageUrl() . '/admin',
				'backLinkLabel' => 'admin.siteAdmin'
			));
			$templateMgr->display('common/message.tpl');
			
		} else {
			$settingsForm->display();
		}
	}
	
}

?>
