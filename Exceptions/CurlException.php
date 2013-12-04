<?php

namespace Crackle\Exceptions {

	use \Exception;

	/**
	 * The exception that is thrown when a cURL error occurs.
	 * @author George Brighton
	 */
	class CurlException extends Exception {

		/**
		 * Initialises a new instance with its message set to $message.
		 * @param string $message			A string that describes the error.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}