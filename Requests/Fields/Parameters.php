<?php

namespace Crackle\Requests\Fields {

	use \InvalidArgumentException;

	/**
	 * Represents the GET parameters of a request.
	 * @author George Brighton
	 */
	class Parameters extends Fields {

		/**
		 * Retrieve the GET parameters as a query string.
		 * @return string			The parameters formatted as a query string.
		 */
		public function getQueryString() {
			// Crackle permits duplicate keys, so we cannot use PHP's http_build_query()
			$parts = array();
			foreach($this->getPairs() as $pair) {
				$parts[] = urlencode($pair->getKey()) . '=' . urlencode($pair->getValue());
			}
			return implode('&', $parts);
		}

		/**
		 * Import any GET parameters in a URL.
		 * @param string $url					The URL to parse.
		 * @throws InvalidArgumentException		If the URL provided couldn't be parsed.
		 * @return string						The inputted URL, without query string (if it had one).
		 */
		public function parse($url) {
			$parts = parse_url($url);
			if($parts === false) {
				throw new InvalidArgumentException('The supplied URL is invalid.');
			}

			if(isset($parts['query'])) {
				// extract GET parameters and add them as fields
				$this->import($parts['query']);
			}

			// if the URL contains a host with no trailing slash, this key will not exist
			if(!isset($parts['path'])) {
				$parts['path'] = '';
			}

			// return the original URL without the parameters
			return sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']);
		}

		/**
		 * Parses a query string, importing all parameters. Similar to parse_url() without its quirks.
		 * @param string $string					The query string to parse and import.
		 * @throws InvalidArgumentException			If the query string is very malformed.
		 */
		private final function import($string) {
			foreach(explode('&', $string) as $part) {
				$equals = strpos($part, '=');

				if($equals === false) { // minimal validation - we don't check there's only 1 '=' etc.
					throw new InvalidArgumentException('Malformed query string.');
				}

				$this->add(
						urldecode(substr($part, 0, $equals)), // name
						urldecode(substr($part, $equals + 1))); // value
			}
		}
	}
}