<?php

namespace Crackle\Exceptions {

	/**
	 * Represents an error related to a response.
	 * @author George Brighton
	 */
	class ResponseException extends CrackleException {

		/**
		 * Initialise a new response exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
