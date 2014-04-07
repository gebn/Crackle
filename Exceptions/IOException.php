<?php

namespace Crackle\Exceptions {

	use \Exception;

	/**
	 * The exception that is thrown when an I/O error occurs.
	 * @author George Brighton
	 */
	class IOException extends Exception {

		/**
		 * Initialises a new instance with its message set to $message.
		 * @param string $message A string that describes the error.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
