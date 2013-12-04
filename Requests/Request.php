<?php

namespace Crackle\Requests {

	require_once('../Collections/KeyValuePair.php');
	require_once('../Authentication/Credentials.php');
	require_once('../Response.php');

	use \Crackle\Collections\KeyValuePair;
	use \Crackle\Authentication\Credentials;
	use \Crackle\Response;

	/**
	 * Represents an HTTP request.
	 * @author George Brighton
	 */
	abstract class Request {

		/**
		 * The cURL handle representing this request.
		 * @var resource
		 */
		private $handle;

		/**
		 * The URL this request will be sent to.
		 * @var string
		 */
		private $url;

		/**
		 * The credentials to use to authenticate this request.
		 * @var \Crackle\Authentication\Credentials
		 */
		private $credentials;

		/**
		 * Parameters to send with this request.
		 * @var array[\Crackle\Collections\KeyValuePair]
		 */
		private $fields;

		/**
		 * Container for a possible cURL error message returned when this request is sent.
		 * @var string
		 */
		private $error;

		/**
		 * The response representing data returned from this request.
		 * @var \Crackle\Response
		 */
		private $response;

		/**
		 * A function to run upon completion of this request.
		 * This fuction should accept a \Crackle\Request object as its only parameter.
		 * @var callable
		 */
		private $callback;

		/**
		 * Get the cURL handle representing this request.
		 * @return resource		The cURL handle representing this request.
		 */
		public final function getHandle() {
			return $this->handle;
		}

		/**
		 * Set the cURL handle representing this request.
		 * @param resource $handle		The cURL handle representing this request.
		 */
		private final function setHandle($handle) {
			$this->handle = $handle;
		}

		/**
		 * Get the URL this request will be sent to.
		 * @return string		The URL this request will be sent to.
		 */
		private final function getUrl() {
			return $this->url;
		}

		/**
		 * Set the URL this request will be sent to.
		 * @param string $url		The URL this request will be sent to.
		 */
		private final function setUrl($url) {
			$this->url = (string)$url;
		}

		/**
		 * Get the credentials to use to authenticate this request.
		 * @return \Crackle\Authentication\Credentials		The credentials to use to authenticate this request.
		 */
		private final function getCredentials() {
			return $this->credentials;
		}

		/**
		 * Set the credentials to use to authenticate this request.
		 * @param \Crackle\Authentication\Credentials $credentials		The credentials to use to authenticate this request.
		 */
		public final function setCredentials(Credentials $credentials) {
			$this->credentials = $credentials;
		}

		/**
		 * Get the parameters to send with this request.
		 * @return multitype:\Crackle\Collections\KeyValuePair		The parameters to send with this request.
		 */
		protected final function getFields() {
			return $this->fields;
		}

		/**
		 * Set the parameters to send with this request.
		 * @param multitype:\Crackle\Collections\KeyValuePair $fields		The parameters to send with this request.
		 */
		private final function setFields(array $fields) {
			$this->fields = $fields;
		}

		/**
		 * Get any error message returned by cURL when this request was sent.
		 * @return string		Any error message returned by cURL when this request was sent.
		 */
		public final function getError() {
			return $this->error;
		}

		/**
		 * Find whether this cURL generated an error when asked to execute this request.
		 * @return boolean			True if it failed; false if it didn't.
		 */
		public final function isError() {
			return is_string($this->getError());
		}

		/**
		 * Set the error status of this request.
		 * @param boolean|string $error			False if no error occurred, or the error message if one did.
		 */
		private final function setError($error) {
			$this->error = $error;
		}

		/**
		 * Get the response representing data returned from this request.
		 * N.B. this will be null if $this->isError() returns true - always check that no error occurred first.
		 * @return \Crackle\Response		The response representing data returned from this request.
		 */
		public final function getResponse() {
			return $this->response;
		}

		/**
		 * Set the response representing data returned from this request.
		 * @param \Crackle\Response $response		The response representing data returned from this request.
		 */
		private final function setResponse(Response $response) {
			$this->response = $response;
		}

		/**
		 * Get the function to run upon completion of this request.
		 * @return callable			The function to run upon completion of this request.
		 */
		private final function getCallback() {
			return $this->callback;
		}

		/**
		 * Set the function to run upon completion of this request.
		 * @param callable $callback		The function to run upon completion of this request.
		 */
		public final function setCallback(callable $callback) {
			$this->callback = $callback;
		}

		/**
		 * Initialise a new HTTP request.
		 * @param string $url			An optional URL to initialise with.
		 */
		public function __construct($url = null) {
			if($url !== null) {
				$this->setUrl($url);
			}

			$this->setHandle(curl_init());
			$this->setFields(array());
			$this->setDefaultOptions();
		}

		/**
		 * Dispose of the handle when this object goes out of scope.
		 */
		public function __destruct() {
			curl_close($this->getHandle());
		}

		/**
		 * Sets default options for the cURL session used by this request.
		 */
		private function setDefaultOptions() {
			curl_setopt_array($this->getHandle(), array(
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 5));
		}

		/**
		 * Add a new field set to this request.
		 * @param string $name		The name of the field. Duplicate names are permitted.
		 * @param mixed $value		The value to send under the field name.
		 */
		public final function addField($name, $value) {
			$this->fields[] = new KeyValuePair($name, $value);
		}

		/**
		 * Find whether this request has been sent.
		 * @return boolean		True if it has, false if it hasn't.
		 */
		public final function isFired() {
			return $this->getResponse() !== null;
		}

		/**
		 * Creates a query string from the fields.
		 * @return string			The build query string ready to append to the URL.
		 */
		protected final function buildQueryString() {
			// Crackle permits duplicate keys, so we cannot use PHP's http_build_query()
			$pieces = array();
			foreach($this->getFields() as $field) {
				$pieces[] = urlencode($field->getKey()) . '=' . urlencode($field->getValue());
			}
			return implode('&', $pieces);
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 */
		public function finalise() {
			curl_setopt_array($this->getHandle(), array(
					CURLOPT_URL => $this->getUrl(),
					CURLOPT_RETURNTRANSFER => true));

			if($this->getCredentials() !== null) {
				$this->getCredentials()->addTo($this->getHandle());
			}
		}

		/**
		 * Builds the response object and executes the callback for this request.
		 * Called immediately after the request has finished.
		 * @param int $result			One of the CURLE_* constants, indicating the status.
		 */
		public function recover($result) {

			// if an error occurred, set it
			$this->setError($result === CURLE_OK ? false : curl_strerror($result));

			if(!$this->isError()) {
				// build the response
				$this->setResponse(Response::factory($this->getHandle()));
			}

			// execute any callback
			if($this->getCallback() !== null) {
				call_user_func($this->getCallback(), $this);
			}
		}
	}
}