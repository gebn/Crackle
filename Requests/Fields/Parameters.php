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
		 * @return string The parameters formatted as a query string.
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
		 * @param string $url The URL to parse.
		 * @return string The inputted URL, without query string (if it had one).
		 * @throws \InvalidArgumentException If the URL provided couldn't be parsed.
		 */
		public function parse($url) {
			$parts = parse_url($url);
			if($parts === false) {
				throw new InvalidArgumentException('The supplied URL is invalid.');
			}

			if(isset($parts['query'])) {
				// extract GET parameters and add them as fields
				parse_str($parts['query'], $params);
				foreach($params as $name => $value) {
					$this->set($name, $value);
				}
			}

			// default to http if no protocol is specified
			if(!isset($parts['scheme'])) {
				$parts['scheme'] = 'http';
			}

			// if we were given an IP address, there will be no host
			if(!isset($parts['host'])) {
				$parts['host'] = '';
			}

			// if the URL contains a host with no trailing slash, this key will not exist
			if(!isset($parts['path'])) {
				$parts['path'] = '';
			}

			// return the original URL without the parameters
			return sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']);
		}
	}
}
