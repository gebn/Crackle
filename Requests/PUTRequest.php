<?php

namespace Crackle\Requests {

	use \Crackle\Requests\Parts\Files\PUTFile;

	use \Exception;

	/**
	 * Represents an HTTP request sent using the PUT method.
	 * @author George Brighton
	 */
	class PUTRequest extends GETRequest {

		/**
		 * The file to send in this request.
		 * @var \Crackle\Requests\Parts\Files\PUTFile
		 */
		private $file;

		/**
		 * Get the file to send in this request.
		 * @return \Crackle\Requests\Parts\Files\PUTFile		The file to send in this request.
		 */
		private final function getFile() {
			return $this->file;
		}

		/**
		 * Set the file to send in this request.
		 * @param \Crackle\Requests\Parts\Files\PUTFile $file		The file to send in this request.
		 */
		public final function setFile(PUTFile $file) {
			$this->file = $file;
		}

		/**
		 * Initialise a new HTTP PUT request.
		 * @param string $url			An optional URL to initialise with.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 * @see \Crackle\Requests\Request::finalise()
		 */
		public function finalise() {
			parent::finalise();
			curl_setopt($this->getHandle(), CURLOPT_PUT, true);
			$this->getFile()->addTo($this->getHandle());
		}

		/**
		 * Checks this request for errors before it is sent.
		 * @throws \Exception		If an issue is found.
		 * @see \Crackle\Requests\Request::validate()
		 */
		protected function validate() {
			parent::validate();
			if($this->getFile() === null) {
				throw new Exception('A PUT file must be specified for PUT requests.');
			}
		}
	}
}