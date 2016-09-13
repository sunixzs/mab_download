<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

// Static TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ( $_EXTKEY, 'Configuration/TypoScript', 'MAB Download' );

// Plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin ( $_EXTKEY, 'Download', 'Download' );

// TsConfig for Plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig ( 
		'
mod.wizards.newContentElement.wizardItems.special.elements.tx_mabdownload_download {
	icon = EXT:frontend/Resources/Public/Icons/ContentElementWizard/filelinks.gif
	title = Download
	description = Einfach ein paar Dateien zum Download präsentieren...
	tt_content_defValues.CType = mabdownload_download
}
mod.wizards.newContentElement.wizardItems.special.show := addToList(tx_mabdownload_download)
' );

