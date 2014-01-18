<?php

namespace Crackle\Requests {

	use \Crackle\Requests\Parts\Files\POSTFile;
	use \Crackle\Exceptions\ValidationException;

	use \CURLFile;

	/**
	 * Represents an HTTP request sent using the POST method.
	 * @author George Brighton
	 */
	class POSTRequest extends Request {

		/**
		 * Files to send with this request.
		 * @var array[\Crackle\Requests\Parts\Files\POSTFile]
		 */
		private $files;

		/**
		 * Get the files to send with this request.
		 * @return array[\Crackle\Requests\Parts\Files\POSTFile]		The files to send with this request.
		 */
		private final function getFiles() {
			return $this->files;
		}

		/**
		 * Set the files to send with this request.
		 * @param array[\Crackle\Requests\Parts\Files\POSTFile] $files		The files to send with this request.
		 */
		private final function setFiles(array $files) {
			$this->files = $files;
		}

		/**
		 * Add a new file to this request.
		 * @param string $name								The name of this file. Any file previously assigned to this name will be overwritten.
		 * @param \Cracke\Requests\Parts\Files\POSTFile		The file to add.
		 */
		public final function addFile($name, POSTFile $file) {
			$this->files[$name] = $file;
		}

		/**
		 * Initialise a new HTTP POST request.
		 * @param string $url			An optional URL to initialise with.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
			$this->setFiles(array());
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 * @see \Crackle\Requests\Request::finalise()
		 */
		public function finalise() {
			parent::finalise();
			$this->buildRequest();
		}

		/**
		 * Checks this request for consistency.
		 * @throws ValidationException a problem is discovered.
		 */
		protected function validate() {
			$fields = array_map(function($pair) {
				return $pair->getKey();
			}, $this->getFields());
			$files = array_keys($this->getFiles());
			$duplicates = array_intersect($fields, $files);

			if (!empty($duplicates)) {
				throw new ValidationException('The following field(s) have been set as both POST fields and file uploads: ' . implode(', ', $duplicates));
			}
		}

		/**
		 * Creates the content of this POST request.
		 * Adapted from Beau Simensen's function on GitHub: https://gist.github.com/simensen/288242
		 */
		private function buildRequest() {
			$boundary = self::generateBoundary();
			$content = $this->buildContent($boundary);

			curl_setopt_array($this->getHandle(), array(
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $content));

			$headers = $this->getHeaders();
			$headers->set('Expect', '100-continue');
			$headers->set('Content-Length', strlen($content));
			$headers->set('Content-Type', 'multipart/form-data; boundary=' . $boundary);
		}

		/**
		 * Creates a boundary to divide parts of the request.
		 * @return string			The generated boundary.
		 */
		private static function generateBoundary() {
			$algorithms = hash_algos();
			$preferences = array('sha1', 'md5');
			foreach($preferences as $algorithm) {
				if(in_array($algorithm, $algorithms)) {
					return '----------------------------' . substr(hash('sha1', 'crackle' . microtime()), 0, 12);
				}
			}
			return '----------------------------' . substr(hash($algorithms[0], 'crackle' . microtime()), 0, 12);
		}

		/**
		 * Builds the lines of the request content.
		 * @param string $boundary			The boundary to use to divide parts.
		 * @return string					The created body.
		 */
		private function buildContent($boundary) {
			$lines = array();

			// add fields
			foreach($this->getFields() as $kvp) {
				$lines[] = '--' . $boundary;
				$lines[] = 'Content-Disposition: form-data; name="' . $kvp->getKey() . '"';
				$lines[] = '';
				$lines[] = $kvp->getValue();
			}

			// add files
			foreach($this->getFiles() as $name => $file) {
				$lines[] = '--' . $boundary;
				$file->appendPart($lines, $name);
			}

			$lines[] = '--' . $boundary . '--';
			$lines[] = '';

			return implode("\r\n", $lines);
		}
	}
}