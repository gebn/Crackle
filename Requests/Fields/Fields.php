<?php

namespace Crackle\Requests\Fields {

	use \Crackle\Structures\KeyValuePair;

	use \InvalidArgumentException;

	/**
	 * Holds fields of a specific type for a request, e.g.
	 * GET parameters or POST files.
	 * @author George Brighton
	 */
	abstract class Fields {

		/**
		 * The named fields to be included in the request.
		 * @var array
		 */
		private $fields;

		/**
		 * Retrieve the fields to be included in the request.
		 * @return array			The fields to be included in the request.
		 */
		private final function getFields() {
			return $this->fields;
		}

		/**
		 * Set the fields to be included in the request.
		 * @param array $fields		The new fields to be included in the request.
		 */
		private final function setFields(array $fields) {
			$this->fields = $fields;
		}

		/**
		 * Initialise a new fields container.
		 */
		public function __construct() {
			$this->setFields(array());
		}

		/**
		 * Add a new field to the request.
		 * @param string $name					The name of the field.
		 * @param mixed $value					The value of the field.
		 * @throws InvalidArgumentException		If the name is invalid.
		 */
		public function add($name, $value) {
			$matches = array();
			if(!preg_match('/^([^\[\]]+)(?:\[([a-z0-9]*)\])?$/i', $name, $matches)) {
				throw new InvalidArgumentException('Invalid field name: ' . $name);
			}

			$isIndexed = strpos($name, '[') !== false;

			$parsed = $matches[1];
			$suffixed = $parsed . '[]';
			$index = isset($matches[2]) ? $matches[2] : '';

			if($index == '') {
				if(isset($this->fields[$parsed])) {
					$this->fields[$parsed][] = $value;
				}
				else if(isset($this->fields[$suffixed])) {
					$this->fields[$suffixed] = $value;
				}
				else if(strpos($name, '[') === false) {
					// not indexed
					$this->fields[$parsed] = array($value);
				}
				else {
					// indexed
					$this->fields[$suffixed] = array($value);
				}
			}
			else {
				if(isset($this->fields[$parsed])) {
					$this->fields[$parsed][$index] = $value;
				}
				else if(isset($this->fields[$suffixed])) {
					$this->fields[$parsed][$index] = $value;
					if(count($this->fields[$suffixed]) != 1) {
						// rename key
						$this->fields[$parsed] = $this->fields[$suffixed];
						unset($this->fields[$suffixed]);
					}
				}
				else {
					$this->fields[$suffixed] = array($index => $value);
				}
			}
		}

		/**
		 * Retrieve the field pairs.
		 * @return array[\Crackle\Structures\KeyValuePair]		KVPs of fields in this container
		 */
		public final function getPairs() {
			$pairs = array();
			foreach($this->getFields() as $key => $entry) {
				$indexed = substr($key, -2) == '[]'; // force array
				if($indexed || count($entry) != 1) {
					$key = $indexed ? substr($key, 0, -2) : $key;
					foreach($entry as $index => $value) {
						$pairs[] = new KeyValuePair($key . '[' . $index . ']', $value);
					}
				}
				else {
					$pairs[] = new KeyValuePair($key, $entry[0]);
				}
			}
			return $pairs;
		}
	}
}