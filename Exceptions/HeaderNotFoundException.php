<?php

namespace Crackle\Exceptions {

	use \Exception;

	/**
	 * Thrown when a header that wasn't sent is requested.
	 * @author George Brighton
	 */
	class HeaderNotFoundException extends Exception {

		/**
		 * Initialises a new instance with its message set to $message.
		 * @param string $message A string that describes the error.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
