<?php

namespace Crackle\Collections {

	/**
	 * Defines a key/value pair that can be set or retrieved.
	 * @author George Brighton
	 */
	class KeyValuePair {

		/**
		 * The key in the key/value pair.
		 * @var mixed
		 */
		private $key;

		/**
		 * The value in the key/value pair.
		 * @var mixed
		 */
		private $value;

		/**
		 * Get the key in the key/value pair.
		 * @return mixed		The key in the key/value pair.
		 */
		public final function getKey() {
			return $this->key;
		}

		/**
		 * Set the key in the key/value pair.
		 * @param mixed $key		The key in the key/value pair.
		 */
		public final function setKey($key) {
			$this->key = (string)$key;
		}

		/**
		 * Get the value in the key/value pair.
		 * @return mixed		The value in the key/value pair.
		 */
		public final function getValue() {
			return $this->value;
		}

		/**
		 * Set the value in the key/value pair.
		 * @param mixed $value		The value in the key/value pair.
		 */
		public final function setValue($value) {
			$this->value = $value;
		}
	}
}