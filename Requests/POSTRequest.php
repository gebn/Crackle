<?php

namespace Crackle\Requests {

	require_once('Request.php');

	require_once('../Exceptions/ValidationException.php');

	use \Crackle\Exceptions\ValidationException;

	/**
	 * Represents an HTTP request sent using the POST method.
	 * @author George Brighton
	 */
	class POSTRequest extends Request {

		/**
		 * Files to send with this request.
		 * @var array
		 */
		private $files;

		/**
		 * Get the files to send with this request.
		 * @return array		The files to send with this request.
		 */
		private final function getFiles() {
			return $this->files;
		}

		/**
		 * Set the files to send with this request.
		 * @param array $files		The files to send with this request.
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
			curl_setopt_array($this->getHandle(), array(
					CURLOPT_POST => true,
					CURLOPT_HTTPHEADER => array('Content-type: multipart/form-data'),
					CURLOPT_POSTFIELDS => $this->buildPOSTFields()));
			parent::finalise();
		}

		/**
		 * Create the array of POST fields and files.
		 * FIXME N.B. PHP cURL doesn't support sending fields with duplicate names alongside files.
		 * @return array			An array that can be passed to cURL as CURLOPT_POSTFIELDS.
		 */
		private final function buildPOSTFields() {
			$fields = array();
			foreach($this->getFields() as $field) {
				$fields[$field->getKey()] = $field->getValue();
			}
			foreach($this->getfiles() as $name => $path) {
				$fields[$name] = '@' . $path;
			}
			return $fields;
		}

		/**
		 * Checks this request for consistency.
		 * @throws ValidationException			If a problem is discovered.
		 */
		protected function validate() {
			$fields = array_map(function($pair) {
						return $pair->getKey();
					}, $this->getFields());
			$files = array_keys($this->getFiles());
			$duplicates = array_intersect($fields, $files);

			if(!empty($duplicates)) {
				throw new ValidationException('The following field(s) have been set as both POST fields and file uploads: ' . implode(', ', $duplicates));
			}
		}
	}
}