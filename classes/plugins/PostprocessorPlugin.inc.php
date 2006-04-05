<?php

/**
 * PostprocessorPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Abstract class for preprocessor plugins; these are invoked after an
 * entry is stored but before it is indexed.
 *
 * $Id$
 */

class PostprocessorPlugin extends Plugin {
	function PostprocessorPlugin() {
		parent::Plugin();
	}

	function register($category, $path) {
		$result = parent::register($category, $path);
		if ($result) {
			HookRegistry::register('SchemaPlugin::indexRecord', array(&$this, '_postprocessEntry'));
		}
		return $result;
	}

	function _postprocessEntry($hookName, &$args) {
		$archive =& $args[0];
		$record =& $args[1];
		$field =& $args[2];
		$value =& $args[3];
		$attributes =& $args[4];
		return $this->postprocessEntry($archive, $record, $field, $value, $attributes);
	}

	/**
	 * Get the symbolic name of this plugin. Should be unique within
	 * the category.
	 */
	function getName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		fatalError('ABSTRACT CLASS');
	}

	function postprocessEntry(&$archive, &$record, &$field, &$value, &$attributes) {
		fatalError('ABSTRACT CLASS');
	}
}

?>