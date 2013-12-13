<?php

namespace Crackle\Requests {

	/**
	 * Represents an HTTP request sent using the GET method.
	 * @author George Brighton
	 */
	class GETRequest extends Request {

		/**
		 * Initialise a new HTTP GET request.
		 * @param string $url			An optional URL to initialise with.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 */
		public function finalise() {
			$queryString = $this->buildQueryString();
			if($queryString !== '') {
				$this->appendQueryString($queryString);
			}
			parent::finalise();
		}

		/**
		 * Adds a query string to the existing URL, merging if necessary.
		 * @param string $queryString			The query string to append.
		 */
		private final function appendQueryString($queryString) {
			if(strpos($this->getUrl(), '?') === false) {
				// no existing query string; add one onto the end of the URL
				$this->setUrl($this->getUrl() . '?' . $queryString);
				return;
			}

			// need to merge with the existing query string
			$this->setUrl(rtrim($this->getUrl(), '?&') . '?' . $queryString);
		}

		/**
		 * Creates a query string from the fields.
		 * @return string			The build query string ready to append to the URL.
		 */
		private final function buildQueryString() {
			// Crackle permits duplicate keys, so we cannot use PHP's http_build_query()
			$pieces = array();
			foreach($this->getFields() as $field) {
				$pieces[] = urlencode($field->getKey()) . '=' . urlencode($field->getValue());
			}
			return implode('&', $pieces);
		}
	}
}