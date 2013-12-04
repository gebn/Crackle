<?php

namespace Crackle\Exceptions {

	use \Exception;

	/**
	 * Thrown when an internal error is discovered in a request's content.
	 * @author George Brighton
	 */
	class ValidationException extends Exception {

		/**
		 * Initialises a new instance with its message set to $message.
		 * @param string $message			A string that describes the error.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}