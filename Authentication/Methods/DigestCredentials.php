<?php

namespace Crackle\Authentication {

	require_once('Credentials.php');

	/**
	 * Represents a set of HTTP digest authentication credentials.
	 * @author George Brighton
	 */
	class DigestCredentials extends Credentials {

		/**
		 * Initialise a new set of digest credentials.
		 * @param string $username		The username to send.
		 * @param string $password		The password to send.
		 */
		public function __construct($username = null, $password = null) {
			parent::__construct($username, $password);
		}

		/**
		 * Add parameters defined by this object to a cURL handle.
		 * @param resource $handle			The cURL handle to set options on.
		 */
		public function addTo($handle) {
			parent::addTo($handle);
			curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		}
	}
}