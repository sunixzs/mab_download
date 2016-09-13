<?php

namespace MAB\MabDownload\Controller;

/**
 *
 */
class DownloadController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 * @inject 
	 */
	protected $fileRepository;
	
	/**
	 * @var \TYPO3\CMS\Core\Resource\FileCollectionRepository
	 * @inject
	 */
	protected $fileCollectionRepository;
	
	/**
	 * File-Extensions, which could be used for creating thumbnails
	 * @var \array
	 */
	protected $thumbnailExtensions = [ 
			"png",
			"jpg",
			"jpeg",
			"gif" 
	];
	
	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$currentTtContent = $this->configurationManager->getContentObject ()->data;
		$files = [ ]; // array with all files
		              
		// Get files from field media
		if ($currentTtContent[ 'media' ]) {
			$ttContentUid = ($currentTtContent[ '_LOCALIZED_UID' ]) ? $currentTtContent[ '_LOCALIZED_UID' ] : $currentTtContent[ 'uid' ];
			$fileObjects = $this->fileRepository->findByRelation ( 'tt_content', 'media', $ttContentUid );
			
			if (count ( $fileObjects )) {
				$mediaFiles = [ ];
				foreach ( $fileObjects as $fileObject ) {
					$this->addFileReference ( $mediaFiles, $fileObject );
				}
				
				if (count ( $mediaFiles )) {
					if ($this->settings[ 'order' ] == "CType") {
						$this->sortFiles ( $mediaFiles, "CType", $this->settings[ "direction" ] );
					}
					
					$files = array_merge ( $files, $mediaFiles );
				}
			}
		}
		
		// Get files from field file_collections
		if ($currentTtContent[ 'file_collections' ]) {
			$fileCollectionUids = \TYPO3\CMS\Extbase\Utility\ArrayUtility::integerExplode ( ",", $currentTtContent[ 'file_collections' ] );
			
			if (count ( $fileCollectionUids )) {
				
				$fileCollectionFiles = [ ];
				foreach ( $fileCollectionUids as $fileCollectionUid ) {
					$fileCollection = $this->fileCollectionRepository->findByUid ( $fileCollectionUid );
					if ($fileCollection) {
						$fileCollection->loadContents ();
						$fileObjects = $fileCollection->getItems ();
						foreach ( $fileObjects as $fileObject ) {
							if ($fileObject instanceof \TYPO3\CMS\Core\Resource\FileReference) {
								$this->addFileReference ( $fileCollectionFiles, $fileObject );
							} else if ($fileObject instanceof \TYPO3\CMS\Core\Resource\File) {
								$this->addFile ( $fileCollectionFiles, $fileObject );
							}
						}
					}
				}
				
				if (count ( $fileCollectionFiles )) {
					if ($this->settings[ 'order' ] == "CType") {
						$this->sortFiles ( $fileCollectionFiles, "CType", $this->settings[ "direction" ] );
					}
					
					$files = array_merge ( $files, $fileCollectionFiles );
				}
			}
		}
		
		if (count ( $files )) {
			if ($this->settings[ 'order' ] != "CType") {
				$this->sortFiles ( $files, $this->settings[ 'order' ], $this->settings[ "direction" ] );
			}
		}
		
		$this->view->assignMultiple ( 
				[ 
						'data' => $currentTtContent,
						'files' => $files,
						'showMeta' => $this->settings[ 'size' ] || $this->settings[ 'suffix' ] || $this->settings[ 'mime' ] ? TRUE : FALSE 
				] );
	}
	
	/**
	 * 
	 * @param array $files
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileObject
	 */
	protected function addFileReference(&$files, $fileObject) {
		if (file_exists ( $fileObject->getOriginalFile ()->getPublicUrl () ) == FALSE) {
			return false;
		}
		
		$file = [ 
				'key' => $this->encrypt ( $fileObject->getOriginalFile ()->getUid (), $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SYS' ][ 'encryptionKey' ] ),
				'reference' => $fileObject->getReferenceProperties (),
				'original' => $fileObject->getOriginalFile ()->getProperties (),
				'publicUrl' => $fileObject->getOriginalFile ()->getPublicUrl () 
		];
		
		// Filename
		$file[ 'filename' ] = $file[ 'original' ][ 'name' ];
		
		// Title
		if ($file['reference']['title']) {
			$file[ 'title' ] = $file['reference']['title'];
		} else if ($file['original']['title']) {
			$file[ 'title' ] = $file['original']['title'];
		} else {
			$file[ 'title' ] = $file['original']['name'];
		}
		
		// Description
		if ($file['reference']['description']) {
			$file[ 'description' ] = $file['reference']['description'];
		} else {
			$file[ 'description' ] = $file['original']['description'];
		} 
		
		// Sorting (like in CType)
		$file[ 'sorting' ] = $file[ 'reference' ][ 'sorting_foreign' ];
		
		$visibleMetas = [ ];
		
		// Size
		$file[ 'size' ] = $file[ 'original' ][ 'size' ];
		$file[ 'humanReadableSize' ] = $this->fileSizeConvert ( $file[ 'original' ][ 'size' ] );
		if ($this->settings[ 'size' ]) {
			$visibleMetas[] = '<span class="size">' . $file[ 'humanReadableSize' ] . '</span>';
		}
		
		// Suffix / Extension
		$file[ 'suffix' ] = $file[ 'original' ][ 'extension' ];
		if ($this->settings[ 'suffix' ]) {
			$visibleMetas[] = '<span class="suffix">' . $file[ 'suffix' ] . '</span>';
		}
		
		// Mime
		$file[ 'mime' ] = $file[ 'original' ][ 'mime_type' ];
		if ($this->settings[ 'mime' ]) {
			$visibleMetas[] = '<span class="mime">' . $file[ 'mime' ] . '</span>';
		}
		
		$file[ 'metaList' ] = count ( $visibleMetas ) ? implode ( ", ", $visibleMetas ) : "";
		
		// Thumbnails
		$file[ 'createThumbnail' ] = ($this->settings[ 'thumbnail' ] && in_array ( strtolower ( $file[ 'suffix' ] ), $this->thumbnailExtensions )) ? TRUE : FALSE;
		
		if (array_key_exists ( strtolower ( $file[ 'suffix' ] ), ( array ) $this->settings[ 'icons' ] )) {
			$file[ 'icon' ] = $this->settings[ 'iconsPath' ] . $this->settings[ 'icons' ][ strtolower ( $file[ 'original' ][ 'extension' ] ) ];
		} else {
			$file[ 'icon' ] = $this->settings[ 'iconsPath' ] . $this->settings[ 'icons' ][ 'standard' ];
		}
		
		// add File
		$files[] = $file;
	}
	
	/**
	 *
	 * @param array $files
	 * @param \TYPO3\CMS\Core\Resource\File $fileObject
	 */
	protected function addFile(&$files, $fileObject) {
		if (file_exists ( $fileObject->getPublicUrl () ) == FALSE) {
			return false;
		}
		
		$file = [ 
				'key' => $this->encrypt ( $fileObject->getUid (), $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SYS' ][ 'encryptionKey' ] ),
				'original' => $fileObject->getProperties (),
				'publicUrl' => $fileObject->getPublicUrl () 
		];
		
		// Filename
		$file[ 'filename' ] = $file[ 'original' ][ 'name' ];
		
		// Title
		$file[ 'title' ] = ($file[ 'original' ][ 'title' ]) ? $file[ 'original' ][ 'title' ] : $file[ 'original' ][ 'name' ];
		
		$file[ 'description' ] = $file[ 'original' ][ 'description' ];
		
		// Sorting (like in CType)
		$file[ 'sorting' ] = $file[ 'original' ][ 'sorting_foreign' ];
		
		$visibleMetas = [ ];
		
		// Size
		$file[ 'size' ] = $file[ 'original' ][ 'size' ];
		$file[ 'humanReadableSize' ] = $this->fileSizeConvert ( $file[ 'original' ][ 'size' ] );
		if ($this->settings[ 'size' ]) {
			$visibleMetas[] = '<span class="size">' . $file[ 'humanReadableSize' ] . '</span>';
		}
		
		// Suffix / Extension
		$file[ 'suffix' ] = $file[ 'original' ][ 'extension' ];
		if ($this->settings[ 'suffix' ]) {
			$visibleMetas[] = '<span class="suffix">' . $file[ 'suffix' ] . '</span>';
		}
		
		// Mime
		$file[ 'mime' ] = $file[ 'original' ][ 'mime_type' ];
		if ($this->settings[ 'mime' ]) {
			$visibleMetas[] = '<span class="mime">' . $file[ 'mime' ] . '</span>';
		}
		
		$file[ 'metaList' ] = count ( $visibleMetas ) ? implode ( ", ", $visibleMetas ) : "";
		
		// Thumbnails
		$file[ 'createThumbnail' ] = ($this->settings[ 'thumbnail' ] && in_array ( strtolower ( $file[ 'suffix' ] ), $this->thumbnailExtensions )) ? TRUE : FALSE;
		
		if (array_key_exists ( strtolower ( $file[ 'suffix' ] ), ( array ) $this->settings[ 'icons' ] )) {
			$file[ 'icon' ] = $this->settings[ 'iconsPath' ] . $this->settings[ 'icons' ][ strtolower ( $file[ 'original' ][ 'extension' ] ) ];
		} else {
			$file[ 'icon' ] = $this->settings[ 'iconsPath' ] . $this->settings[ 'icons' ][ 'standard' ];
		}
		
		// add File
		$files[] = $file;
	}
	
	/**
	 * 
	 * @param array $files
	 * @param string $orderBy
	 * @param string $orderDirection
	 */
	protected function sortFiles(&$files, $orderBy = "title", $orderDirection = "asc") {
		$order = (strtolower ( $orderDirection ) == "asc") ? SORT_ASC : SORT_DESC;
		switch ($orderBy) {
			case "title" :
				$sort_col = array ();
				foreach ( $files as $key => $row ) {
					$sort_col[ $key ] = $row[ 'title' ];
				}
				array_multisort ( $sort_col, $order, SORT_STRING, $files );
				break;
			case "filesize" :
				$sort_col = array ();
				foreach ( $files as $key => $row ) {
					$sort_col[ $key ] = $row[ 'size' ];
				}
				array_multisort ( $sort_col, $order, SORT_NUMERIC, $files );
				break;
			case "CType" :
			default :
				$sort_col = array ();
				foreach ( $files as $key => $row ) {
					$sort_col[ $key ] = $row[ 'sorting' ];
				}
				array_multisort ( $sort_col, $order, SORT_NUMERIC, $files );
				break;
		}
	}
	
	/**
	 * Converts bytes into human readable file size.
	 *
	 * @param string $bytes
	 * @return string human readable file size (2,87 Мб)
	 * @author Mogilev Arseny
	 */
	protected static function fileSizeConvert($bytes) {
		$bytes = floatval ( $bytes );
		$arBytes = array (
				0 => array (
						"UNIT" => "TB",
						"VALUE" => pow ( 1024, 4 ) 
				),
				1 => array (
						"UNIT" => "GB",
						"VALUE" => pow ( 1024, 3 ) 
				),
				2 => array (
						"UNIT" => "MB",
						"VALUE" => pow ( 1024, 2 ) 
				),
				3 => array (
						"UNIT" => "KB",
						"VALUE" => 1024 
				),
				4 => array (
						"UNIT" => "Byte",
						"VALUE" => 1 
				) 
		);
		
		foreach ( $arBytes as $arItem ) {
			if ($bytes >= $arItem[ "VALUE" ]) {
				$result = $bytes / $arItem[ "VALUE" ];
				$result = str_replace ( ".", ",", strval ( round ( $result, 2 ) ) ) . " " . $arItem[ "UNIT" ];
				break;
			}
		}
		return $result;
	}
	
	/**
	 * action download
	 *
	 * @return void
	 */
	public function downloadAction() {
		if (! $this->request->hasArgument ( "file" )) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( "404 - File not found" );
		}
		
		try {
			$decryptedUid = $this->decrypt ( $this->request->getArgument ( "file" ), $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SYS' ][ 'encryptionKey' ] );
			if (is_numeric ( $decryptedUid )) {
				$fileObject = $this->fileRepository->findByUid ( ( integer ) $decryptedUid );
			}
		} catch ( \Exception $e ) {
		}
		
		if (! $fileObject) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( "404 - File not found" );
		}
		
		if (is_file ( $fileObject->getPublicUrl () ) == false) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( "404 - File not found" );
		}
		
		if ($fileObject->getStorage ()->isOnline () == false) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( "404 - File not found" );
		}
		
		// set some vars for download
		$fileName = ($fileObject->getName ()) ? $fileObject->getName () : "unknown.txt";
		$filePath = PATH_site . $fileObject->getPublicUrl ();
		$speed = 1024 * 1024;
		set_time_limit ( 0 );
		ignore_user_abort ( false );
		
		while ( ob_get_level () > 0 ) {
			ob_end_clean ();
		}
		
		// open file
		$fileHandle = @fopen ( $filePath, 'rb' );
		if (is_resource ( $fileHandle ) === false) {
			header ( 'HTTP/1.1 404 Not Found' );
			die ( "404 - File not found" );
		}
		
		// send header
		header ( 'Expires: 0' );
		header ( 'Pragma: public' );
		header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header ( 'Content-Type: application/octet-stream' );
		header ( 'Content-Length: ' . sprintf ( '%u', filesize ( $filePath ) ) );
		header ( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
		header ( 'Content-Transfer-Encoding: binary' );
		
		while ( feof ( $fileHandle ) !== true ) {
			echo fread ( $fileHandle, $speed );
			
			while ( ob_get_level () > 0 ) {
				ob_end_flush ();
			}
			
			flush ();
			sleep ( 1 );
		}
		
		fclose ( $fileHandle );
		die ();
	}
	
	/**
	 * Returns an encrypted & utf8-encoded
	 */
	function encrypt($string, $key) {
		$result = '';
		for($i = 0; $i < strlen ( $string ); $i ++) {
			$char = substr ( $string, $i, 1 );
			$keychar = substr ( $key, ($i % strlen ( $key )) - 1, 1 );
			$char = chr ( ord ( $char ) + ord ( $keychar ) );
			$result .= $char;
		}
		return base64_encode ( $result );
	}
	
	/**
	 * Returns decrypted original string
	 */
	function decrypt($string, $key) {
		$result = '';
		$string = base64_decode ( $string );
		for($i = 0; $i < strlen ( $string ); $i ++) {
			$char = substr ( $string, $i, 1 );
			$keychar = substr ( $key, ($i % strlen ( $key )) - 1, 1 );
			$char = chr ( ord ( $char ) - ord ( $keychar ) );
			$result .= $char;
		}
		return $result;
	}
	
	/**
	 * A Wrapper for the extbase debugger.
	 *
	 * @param mixed $variable The value to dump
	 * @param string $title optional custom title for the debug output
	 * @param integer $maxDepth Sets the max recursion depth of the dump. De- or increase the number according to your needs and memory limit.
	 * @param boolean $plainText If TRUE, the dump is in plain text, if FALSE the debug output is in HTML format.
	 * @param boolean $ansiColors If TRUE (default), ANSI color codes is added to the output, if FALSE the debug output not colored.
	 * @param boolean $return if TRUE, the dump is returned for custom post-processing (e.g. embed in custom HTML). If FALSE (default), the dump is directly displayed.
	 * @param array $blacklistedClassNames An array of class names (RegEx) to be filtered. Default is an array of some common class names.
	 * @param array $blacklistedPropertyNames An array of property names and/or array keys (RegEx) to be filtered. Default is an array of some common property names.
	 * @return string if $return is TRUE, the dump is returned. By default, the dump is directly displayed, and nothing is returned.
	 * @api
	 */
	static public function debug($variable, $title = NULL, $maxDepth = 8, $plainText = FALSE, $ansiColors = TRUE, $return = FALSE, $blacklistedClassNames = NULL, $blacklistedPropertyNames = NULL) {
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump ( $variable, $title, $maxDepth, $plainText, $ansiColors, $return, $blacklistedClassNames, $blacklistedPropertyNames );
	}
}
?>