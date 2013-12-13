<?php

namespace Crackle\Requests {

	use \InvalidArgumentException;

	/**
	 * Represents an HTTP request sent using the GET method.
	 * @author George Brighton
	 */
	class GETRequest extends Request {

		/**
		 * Set the URL this request will be sent to.
		 * @param string $url					The URL this request will be sent to.
		 * @throws \InvalidArgumentException	If the given URL is invalid.
		 */
		public function setUrl($url) {
			$parts = parse_url($url);
			if($parts === false) {
				throw new InvalidArgumentException('The supplied URL is invalid.');
			}

			// extract GET parameters and add them as fields
			$params = array();
			parse_str($parts['query'], $params);
			foreach($params as $name => $value) {
				$this->addField($name, $value);
			}

			// set the original URL without the parameters as the request URL
			parent::setUrl(sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']));
		}

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
				// append the query string; we removed and imported any existing one in the setter
				parent::setUrl($this->getUrl() . '?' . $queryString); // want to use parent as our override would re-parse the query string
			}
			parent::finalise();
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