<?php

namespace Crackle\Requests\Fields {

	use \Crackle\Requests\Files\POSTFile;

	use \InvalidArgumentException;

	/**
	 * Represents the POST files of a request.
	 * @author George Brighton
	 */
	class Files extends Fields {

		/**
		 * Add a new field to the request.
		 * @param string $name										The name of the field.
		 * @param \Crackle\Requests\Parts\Files\POSTFile $value		The value of the field.
		 * @throws InvalidArgumentException							If the name is invalid, or the value is not an instance of \Crackle\Requests\Parts\Files\POSTFile.
		 * @see \Crackle\Requests\Parts\Fields::add()
		 */
		public function add($name, $value) {
			if(!($value instanceof POSTFile)) {
				throw new InvalidArgumentException('Value must be an instance of \Crackle\Requests\Parts\Files\POSTFile.');
			}
			parent::add($name, $value);
		}
	}
}