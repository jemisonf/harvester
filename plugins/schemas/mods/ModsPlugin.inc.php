<?php

/**
 * ModsPlugin.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * MODS schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

class ModsPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'ModsPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return Locale::translate('plugins.schemas.mods.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.mods.description');
	}

	function &getXMLHandler(&$harvester) {
		$this->import('ModsXMLHandler');
		$handler =& new ModsXMLHandler(&$harvester);
		return $handler;
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'identifier',
				'title',
				'subTitle',
				'partNumber',
				'partName',
				'nonSort',
				'namePart',
				'displayForm',
				'affiliation',
				'role',
				'roleTerm',
				'typeOfResource',
				'genre',
				'placeTerm',
				'publisher',
				'dateIssued',
				'dateCreated',
				'dateCaptured',
				'dateValid',
				'dateModified',
				'copyrightDate',
				'dateOther',
				'recordCreationDate',
				'recordChangeDate',
				'edition',
				'issuance',
				'frequency',
				'languageTerm',
				'form',
				'reformattingQuality',
				'internetMediaType',
				'extent',
				'digitalOrigin',
				'physicalDescriptionNote',
				'abstract',
				'tableOfContents',
				'targetAudience',
				'topic',
				'geographic',
				'temporal',
				'geographicCode',
				'continent',
				'country',
				'province',
				'region',
				'state',
				'territory',
				'county',
				'city',
				'island',
				'area',
				'scale',
				'projection',
				'coordinates',
				'occupation',
				'classification',
				'physicalLocation',
				'url',
				'accessCondition',
				'extension',
				'recordContentSource',
				'recordIdentifier',
				'recordOrigin',
				'languageOfCataloging',
				'note'
			);
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.description", $locale);
	}

	function getAuthorsAndTitle($entries) {
		$authors = array();
		$title = array();

		foreach ($entries as $name => $entry) {
			foreach ($entry as $entryId => $item) {
				if (isset($item['attributes']['nameAssocId'])) {
					$nameAssocId = $item['attributes']['nameAssocId'];
					switch ($name) {
						case 'roleTerm':
							switch ($item['attributes']['type']) {
								case 'text': $name = 'roleText'; break;
								case 'code': $name = 'roleCode'; break;
							}
							break;
					}
					if (isset($authors[$nameAssocId][$name])) {
						$authors[$nameAssocId][$name] .= '; ' . $item['value'];
					} else {
						$authors[$nameAssocId][$name] = $item['value'];
					}
				} elseif (isset($item['attributes']['titleAssocId'])) {
					if (!isset($title[$name])) {
						$title[$name] = $item['value'];
					} else {
						$title[$name] .= "\n" . $item['value'];
					}
				}
			}
		}
		return array($authors, $title);
	}

	/**
	 * Get the authors for the supplied record, if available; null otherwise
	 * @param $record object
	 * @param $entries array
	 * @return array
	 */
	function getAuthors(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);
		return $authors;
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getTitle(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);
		return $title;
	}

	/**
	 * Display a record summary.
	 */
	function displayRecordSummary(&$record) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('record', $record);

		$entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);

		$templateMgr->assign('title', $title);
		$templateMgr->assign('authors', $authors);
		$templateMgr->assign('url', $this->getUrl($record, $entries));

		$templateMgr->display($this->getTemplatePath() . 'summary.tpl', null);
	}

	/**
	 * Display a record.
	 */
	function displayRecord(&$record) {
		$templateMgr =& TemplateManager::getManager();

		$entries = $record->getEntries();
		list($authors, $title) = $this->getAuthorsAndTitle($entries);
		$archive =& $record->getArchive();

		list($version, $defineTermsContextId) = $this->getRtVersion($archive);
		if ($version) {
			$templateMgr->assign('sidebarTemplate', 'rt/rt.tpl');
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign('defineTermsContextId', $defineTermsContextId);
		}

		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign_by_ref('archive', $archive);
		$templateMgr->assign('title', $title);
		$templateMgr->assign('authors', $authors);
		$templateMgr->assign('entries', $entries);
		$templateMgr->display($this->getTemplatePath() . 'record.tpl', null);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getUrl(&$record, $entries) {
		if (is_array($entries['url'])) foreach ($entries['url'] as $entry) {
			if (preg_match('/^[a-z]+:\/\//', $entry['value'])) {
				return $entry['value'];
			}
		}
		return null;
	}

	function getFieldType($name) {
		switch ($name) {
			case 'dateIssued':
			case 'dateCreated':
			case 'dateCaptured':
			case 'dateValid':
			case 'dateModified':
			case 'copyrightDate':
			case 'dateOther':
			case 'recordCreationDate':
			case 'recordChangeDate':
				return FIELD_TYPE_DATE;
			case 'languageTerm':
				return FIELD_TYPE_SELECT;
			default:
				return FIELD_TYPE_STRING;
		}
	}

	/**
	 * Parse a date into a value suitable for indexing.
	 * @return int timestamp or string date, or null on failure
	 */
	function parseDate($fieldName, $value, $attributes = null) {
		if (String::strlen($value) == 4 && is_numeric($value)) {
			// It's a year by itself e.g. 1942; make it 1942-01-01
			$value .= '-01-01';
		}
		return parent::parseDate($fieldName, $value, $attributes);
	}

	/**
	 * Get the "importance" of this field. This is used to display subsets of the complete
	 * field list of a schema by importance.
	 * @param $name string
	 * @return int
	 */
	function getFieldImportance($name) {
		switch ($name) {
			case 'title':
			case 'namePart':
			case 'topic':
			case 'abstract':
			case 'note':
			case 'publisher':
			case 'dateCreated':
			case 'genre':
				return 1;
			default:
				return 0;
		}
	}

	/**
	 * Get a list of field importance levels supported by this plugin, in .
	 * @return array
	 */
	function getSupportedFieldImportance() {
		return array(0, 1);
	}
}

?>
