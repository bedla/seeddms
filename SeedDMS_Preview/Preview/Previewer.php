<?php
/**
 * Implementation of preview documents
 *
 * @category   DMS
 * @package    SeedDMS_Preview
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include some files
 */
require_once("ExecPreviewer.php");
require_once("PhpApiPreviewer.php");


/**
 * Class for managing creation of preview images for documents.
 *
 * @category   DMS
 * @package    SeedDMS_Preview
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: @package_version@
 */
abstract class SeedDMS_Preview_Previewer {
	/**
	 * @var string $cacheDir location in the file system where all the
	 *      cached data like thumbnails are located. This should be an
	 *      absolute path.
	 * @access public
	 */
	public $previewDir;

	/**
	 * @var integer $width maximum width/height of resized image
	 * @access protected
	 */
	protected $width;

	public static function create($previewDir, $usePhpApi, $width=40) {
		if ($usePhpApi) {
			return new SeedDMS_Preview_PhpApiPreviewer($previewDir, $width);
		} else {
			return new SeedDMS_Preview_ExecPreviewer($previewDir, $width);
		}
	}

	private function __construct($previewDir, $width) {
		if(!is_dir($previewDir)) {
			if (!SeedDMS_Core_File::makeDir($previewDir)) {
				$this->previewDir = '';
			} else {
				$this->previewDir = $previewDir;
			}
		} else {
			$this->previewDir = $previewDir;
		}
		$this->width = intval($width);
	}

	/**
	 * Retrieve the physical filename of the preview image on disk
	 *
	 * @param object $object document content or document file
	 * @param integer $width width of preview image
	 * @return string file name of preview image
	 */
	protected function getFileName($object, $width) { /* }}} */
		$document = $object->getDocument();
		$dir = $this->previewDir.(SeedDMS_Preview_Previewer::strEndsWith($this->previewDir, '/') ? '' : '/').$document->getDir();
		switch(get_class($object)) {
			case "SeedDMS_Core_DocumentContent":
				$target = $dir.'p'.$object->getVersion().'-'.$width.'.png';
				break;
			case "SeedDMS_Core_DocumentFile":
				$target = $dir.'f'.$object->getID().'-'.$width.'.png';
				break;
			default:
				return false;
		}
		return $target;
	} /* }}} */

	public function createPreview($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		$document = $object->getDocument();
		$dir = $this->previewDir.'/'.$document->getDir();
		if(!is_dir($dir)) {
			if (!SeedDMS_Core_File::makeDir($dir)) {
				return false;
			}
		}
		$file = $document->_dms->contentDir.$object->getPath();
		if(!file_exists($file))
			return false;
		$target = $this->getFileName($object, $width);
		if($target !== false && (!file_exists($target) || filectime($target) < $object->getDate())) {
			$this->internalCreatePreview($object->getMimeType(), $width, $file, $target);
			return true;
		}
		return true;

	} /* }}} */

	protected abstract function internalCreatePreview($mimeType, $width, $file, $target);

	public function hasPreview($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;
		$target = $this->getFileName($object, $width);
		if($target !== false && file_exists($target) && filectime($target) >= $object->getDate()) {
			return true;
		}
		return false;
	} /* }}} */

	public function getPreview($object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;

		$target = $this->getFileName($object, $width);
		if($target && file_exists($target)) {
			readfile($target);
		}
	} /* }}} */

	public function deletePreview($document, $object, $width=0) { /* {{{ */
		if($width == 0)
			$width = $this->width;
		else
			$width = intval($width);
		if(!$this->previewDir)
			return false;

		$target = $this->getFileName($object, $width);
	} /* }}} */

	private static function strEndsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		} else {
			return (substr($haystack, -$length) === $needle);
		}
	}
}
?>
