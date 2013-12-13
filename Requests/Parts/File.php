<?php

namespace Crackle\Requests\Parts {

	use \Exception;

	/**
	 * Represents a file to send with a request.
	 * @author George Brighton
	 */
	class File {

		/**
		 * The name of this file, including extension.
		 * @var string
		 */
		private $name;

		/**
		 * The MIME type of this file.
		 * @var string
		 */
		private $mimetype;

		/**
		 * The raw content of this file.
		 * @var string
		 */
		private $content;

		/**
		 * Get the name of this file.
		 * @return string		The name of this file, including extension.
		 */
		private final function getName() {
			return $this->name;
		}

		/**
		 * Set the name of this file.
		 * @param string $name		The name of this file, including extension.
		 */
		public final function setName($name) {
			$this->name = (string)$name;
		}

		/**
		 * Get the MIME type of this file.
		 * @return string		The MIME type of this file.
		 */
		private final function getMimetype() {
			return $this->mimetype;
		}

		/**
		 * Set the MIME type of this file.
		 * @param string $mimetype		The MIME type of this file.
		 */
		public final function setMimetype($mimetype) {
			$this->mimetype = (string)$mimetype;
		}

		/**
		 * Get the raw content of this file.
		 * @return string		The raw content of this file.
		 */
		private final function getContent() {
			return $this->content;
		}

		/**
		 * Set the raw content of this file.
		 * @param string $content		The raw content of this file.
		 */
		public final function setContent($content) {
			$this->content = (string)$content;
		}

		/**
		 * Initialise a new file object.
		 */
		public function __construct() {
			$this->setMimetype('application/octet-stream');
		}

		/**
		 * Create a file object from a path.
		 * @param string $path						The absolute or relative (to this script) path to the file.
		 * @throws Exception						If the path does not point to a valid, readable file.
		 * @return \Crackle\Requests\Parts\File		The created object.
		 */
		public static function factory($path) {
			$path = realpath($path);

			if(!is_file($path)) {
				throw new Exception('The path must be the path of a file.');
			}

			if(!is_readable($path)) {
				throw new Exception('The supplied file path cannot be read.');
			}

			$file = new File();
			$file->setName(basename($path));
			$file->setContent(file_get_contents($path));
			if(extension_loaded('fileinfo')) {
				$file->setMimetype(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path));
			}

			return $file;
		}

		/**
		 * Add this file to a multipart request.
		 * @param array $lines			The lines array to append to.
		 * @param string $name			The field name of this file within the request.
		 */
		public function appendPart(array &$lines, $name) {
			$lines[] = 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $this->getName() . '"';
			$lines[] = 'Content-Type: ' . $this->getMimetype();
			$lines[] = '';
			$lines[] = $this->getContent();
		}
	}
}