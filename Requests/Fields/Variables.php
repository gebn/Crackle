<?php

namespace Crackle\Requests\Fields {

	use \Crackle\Requests\Parts\POSTVariable;

	/**
	 * Represents the POST variables of a request.
	 * @author George Brighton
	 */
	class Variables extends Fields {

		/**
		 * Add a new field to the request.
		 * @param string $name					The name of the field.
		 * @param string $value					The value of the field.
		 * @throws InvalidArgumentException		If the name is invalid.
		 * @see \Crackle\Requests\Parts\Fields::add()
		 */
		public function add($name, $value) {
			parent::add($name, new POSTVariable($value));
		}
	}
}