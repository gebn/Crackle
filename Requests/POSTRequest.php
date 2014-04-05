<?php

namespace Crackle\Requests {

	use \Crackle\Requests\Fields\Fields;
	use \Crackle\Requests\Fields\Files;
	use \Crackle\Requests\Fields\Variables;

	/**
	 * Represents an HTTP request sent using the POST method.
	 * @author George Brighton
	 */
	class POSTRequest extends GETRequest {

		/**
		 * The POST variables to send with this request.
		 * @var \Crackle\Requests\Fields\Variables
		 */
		private $variables;

		/**
		 * POST files to send with this request.
		 * @var \Crackle\Requests\Fields\Files
		 */
		private $files;

		/**
		 * Retrieve the POST variables to send with this request.
		 * @return \Crackle\Requests\Fields\Variables		The POST variables to send with this request.
		 */
		public final function getVariables() {
			return $this->variables;
		}

		/**
		 * Set the POST variables to send with this request.
		 * @param \Crackle\Requests\Fields\Variables $variables The new POST variables to send with this request.
		 */
		private final function setVariables($variables) {
			$this->variables = $variables;
		}

		/**
		 * Get the files to send with this request.
		 * @return \Crackle\Requests\Fields\Files files to send with this request.
		 */
		public final function getFiles() {
			return $this->files;
		}

		/**
		 * Set the files to send with this request.
		 * @param \Crackle\Requests\Fields\Files $files files to send with this request.
		 */
		private final function setFiles(Files $files) {
			$this->files = $files;
		}

		/**
		 * Initialise a new HTTP POST request.
		 * @param string $url		Optional: the URL to send this request to.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
			$this->setVariables(new Variables());
			$this->setFiles(new Files());
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 * @see \Crackle\Requests\GETRequest::finalise()
		 */
		public function finalise() {
			$this->buildRequest();
			parent::finalise();
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
			//$headers->set('Expect', '100-continue');
			$headers->set('Content-Length', strlen($content));
			$headers->set('Content-Type', 'multipart/form-data; boundary=' . $boundary);
		}

		/**
		 * Creates a boundary to divide parts of the request.
		 * @return string		The generated boundary.
		 */
		private static function generateBoundary() {
			$algorithms = hash_algos();
			$preferences = array(
					'sha1',
					'md5');
			foreach ($preferences as $algorithm) {
				if (in_array($algorithm, $algorithms)) {
					return '----------------------------' . substr(hash('sha1', 'crackle' . microtime()), 0, 12);
				}
			}
			return '----------------------------' . substr(hash($algorithms[0], 'crackle' . microtime()), 0, 12);
		}

		/**
		 * Builds the lines of the request content.
		 * @param string $boundary		The boundary to use to divide parts.
		 * @return string				The created body.
		 */
		private function buildContent($boundary) {
			// will contain lines that make up this request
			$lines = array();

			// add variables
			self::appendParts($lines, $boundary, $this->getVariables());

			// add files
			self::appendParts($lines, $boundary, $this->getFiles());

			$lines[] = '--' . $boundary . '--';
			$lines[] = '';

			return implode("\r\n", $lines);
		}

		/**
		 * Append all fields in a container to this request.
		 * @param array[string] $lines		The array of lines representing this request's body.
		 * @param string $boundary			The request boundary.
		 * @param Fields $fields			The fields container whose contents to append.
		 */
		private final function appendParts(array &$lines, $boundary, Fields $fields) {
			foreach($fields->getPairs() as $pair) {
				$lines[] = '--' . $boundary;
				$pair->getValue()->appendPart($lines, $pair->getKey());
			}
		}
	}
}