<?php

namespace Crackle\Requests {

	require_once('Request.php');

	require_once('../Exceptions/ValidationException.php');

	use \Crackle\Exceptions\ValidationException;

	use \CURLFile;

	/**
	 * Represents an HTTP request sent using the POST method.
	 * @author George Brighton
	 */
	class POSTRequest extends Request {

		/**
		 * Files to send with this request.
		 * @var array[string]
		 */
		private $files;

		/**
		 * Get the files to send with this request.
		 * @return array[string]		The files to send with this request.
		 */
		private final function getFiles() {
			return $this->files;
		}

		/**
		 * Set the files to send with this request.
		 * @param array[string] $files		The files to send with this request.
		 */
		private final function setFiles(array $files) {
			$this->files = $files;
		}

		/**
		 * Add a new file to this request.
		 * @param string $name			The name of this file. Cannot conflict with other files or fields.
		 * @param string $path			The absolute path of the file.
		 */
		public final function addFile($name, $path) {
			$this->files[$name] = $path;
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
		 */
		public function finalise() {
			$this->validate();
			$this->buildRequest();
			parent::finalise();
		}

		/**
		 * Checks this request for consistency.
		 * @throws ValidationException a problem is discovered.
		 */
		private function validate() {
			$fields = array_map(function ($pair) {
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
					CURLOPT_HTTPHEADER => array(
							'Content-Length: ' . strlen($content),
							'Expect: 100-continue',
							'Content-Type: multipart/form-data; boundary=' . $boundary),
					CURLOPT_POSTFIELDS => $content));
		}

		/**
		 * Builds an array containing all key/value pairs and files that need to be sent in this request.
		 * @return array[array[mixed]]
		 */
		private function collapse() {
			$fields = array();
			foreach ($this->getFields() as $pair) {
				$fields[] = $pair->toArray();
			}
			foreach ($this->getFiles() as $name => $path) {
				$fields[] = array(
						$name,
						'@' . $path); // file paths are identified with a '@' prefix to the value
			}
			return $fields;
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
			foreach ($this->collapse() as $field) {
				list($name, $value) = $field;
				if (strpos($value, '@') === 0) {
					$matches = array();
					if (preg_match('/^@(.*?)$/', $value, $matches)) {
						$lines[] = '--' . $boundary;
						$lines[] = 'Content-Disposition: form-data; name="' . $name . '"; filename="' . basename($matches[1]) . '"';
						$lines[] = 'Content-Type: application/octet-stream';
						$lines[] = '';
						$lines[] = file_get_contents($matches[1]);
					}
				}
				else {
					$lines[] = '--' . $boundary;
					$lines[] = 'Content-Disposition: form-data; name="' . $name . '"';
					$lines[] = '';
					$lines[] = $value;
				}
			}

			$lines[] = '--' . $boundary . '--';
			$lines[] = '';

			return implode("\r\n", $lines);
		}
	}
}