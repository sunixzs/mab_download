<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

$tempColumns = array (
		'tx_mabdownload_file' => array (
				'exclude' => 1,
				'label' => 'Dateien',
				'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig ( 'tx_mabdownload_file', 
						array (
								'minitems' => 0,
								'maxitems' => 500,
								'appearance' => array (
										'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:media.addFileReference',
										'headerThumbnail' => "=" 
								),
								'foreign_types' => array (
										\TYPO3\CMS\Core\Resource\File::FILETYPE_UNKNOWN => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										),
										\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										),
										\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										),
										\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										),
										\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										),
										\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array (
												'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;tx_mabdownload_download,
											--palette--;;filePalette
										' 
										) 
								) 
						), "", $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'BE' ][ 'fileExtensions' ][ 'webspace' ][ 'deny' ] ) 
		) 
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns ( 'tt_content', $tempColumns, 1 );
// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette( 'tt_content', 'mabdownload_download', 'tx_mabdownload_file' );

// add flexform
$GLOBALS[ 'TCA' ][ 'tt_content' ][ 'columns' ][ 'pi_flexform' ][ 'config' ][ 'ds' ][ ",mabdownload_download" ] = 'FILE:EXT:mab_download/Configuration/FlexForms/flexform_download.xml';


// build own palette for the files
$GLOBALS[ 'TCA' ][ 'tt_content' ][ 'palettes' ][ 'tx_mabdownload' ] = array (
		'canNotCollapse' => TRUE,
		'showitem' => '
			media;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.ALT.uploads_formlabel,
			--linebreak--,
			file_collections;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:file_collections.ALT.uploads_formlabel,
			--linebreak--,
			pi_flexform
		' 
);

#$GLOBALS[ 'TCA' ][ 'tt_content' ][ 'types' ][ 'mabdownload_download' ][ 'showitem' ] = '
#	--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
#	--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.header;header,rowDescription,
#	--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media;tx_mabdownload,
#	--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
#	layout;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:layout_formlabel,
#	--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
#	--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
#	hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden,
#	--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
#	--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
#';


// copy uploads showitem
$ceShowitems = \TYPO3\CMS\Extbase\Utility\ArrayUtility::trimExplode ( ",", $GLOBALS[ 'TCA' ][ 'tt_content' ][ 'types' ][ 'uploads' ][ 'showitem' ], true );
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.header;header,rowDescription,
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media;uploads,
#--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
#layout;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:layout_formlabel,
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.uploads_layout;uploadslayout,
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
#--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
#hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden,
#--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
#--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended

// replace and remove some entries
$tmpShowitems = [];
foreach ($ceShowitems as $showitem) {
	if ($showitem == "--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media;uploads") {
		$tmpShowitems[] = "--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media;tx_mabdownload";
	} else if ($showitem == "--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.uploads_layout;uploadslayout") {
		continue;
	} else {
		$tmpShowitems[] = $showitem;
	}
}
unset($ceShowitems);

// set modified showitem
$GLOBALS[ 'TCA' ][ 'tt_content' ][ 'types' ][ 'mabdownload_download' ][ 'showitem' ] = implode(",", $tmpShowitems);


