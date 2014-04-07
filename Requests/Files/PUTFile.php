<?php

namespace Crackle\Requests\Files {

	use \Exception;

	/**
	 * Represents a file that can be attached to a PUT request.
	 * @author George Brighton
	 */
	class PUTFile extends File {

		/**
		 * A stream resource pointing to this file.
		 * @var resource
		 */
		private $stream;

		/**
		 * The size of this file (bytes).
		 * @var int
		 */
		private $size;

		/**
		 * Get a stream resource pointing to this file.
		 * @return resource A stream resource pointing to this file.
		 */
		private final function getStream() {
			return $this->stream;
		}

		/**
		 * Set the stream resource pointing to this file.
		 * @param resource $stream The stream resource pointing to this file.
		 */
		private final function setStream($stream) {
			$this->clear(); // close any existing stream
			$this->stream = $stream;
		}

		/**
		 * Get the size of this file (bytes).
		 * @return int The size of this file (bytes).
		 */
		private final function getSize() {
			return $this->size;
		}

		/**
		 * Set the size of this file (bytes).
		 * @param int $size The size of this file (bytes).
		 */
		private final function setSize($size) {
			$this->size = (int)$size;
		}

		/**
		 * Initialise a new PUTFile.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Disposes of this PUTFile object.
		 */
		public function __destruct() {
			$this->clear();
		}

		/**
		 * Set the content of this file.
		 * @param string $content The new content to set.
		 * @throws \Exception If a new memory stream couldn't be opened.
		 * @see \Crackle\Requests\Parts\Files\File::setContent()
		 */
		public function setContent($content) {
			$stream = fopen('php://temp/maxmemory:1048576', 'w'); // 1 MiB
			if(!$stream) {
				throw new Exception('Failed to open temporary memory data.');
			}

			fwrite($stream, $content);
			fseek($stream, 0);

			$this->setStream($stream);
			$this->setSize(strlen($content));
		}

		/**
		 * Add this file to a request.
		 * @param resource $handle The handle to add this file to.
		 */
		public function addTo($handle) {
			curl_setopt_array($handle, array(
					CURLOPT_INFILE => $this->getStream(),
					CURLOPT_INFILESIZE => $this->getSize()));
		}

		/**
		 * Get a file object representing a file at a location.
		 * @param string $path The absolute or relative (to this script) path to the file.
		 * @return \Crackle\Requests\Parts\Files\POSTFile The created file sobject.
		 */
		public static function factory($path) {
			$file = parent::factory($path);
			$file->setStream(fopen($path, 'r')); // readability check done in parent
			$file->setSize(filesize($path));
			return $file;
		}

		/**
		 * Closes the content stream of this file.
		 * Called when replacing the stream or disposing of this object.
		 */
		private function clear() {
			if($this->getStream() !== null) {
				fclose($this->getStream());
			}
		}
	}
}