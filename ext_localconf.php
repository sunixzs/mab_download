<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin ( 'MAB.' . $_EXTKEY, 'Download', [ 
		'Download' => 'list,download' 
], [ 
		'Download' => 'download' 
], \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT );
