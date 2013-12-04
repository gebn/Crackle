<?php

namespace Crackle {

	require_once('Exceptions/IOException.php');
	require_once('Utilities/Path.php');

	use \Crackle\Exceptions\IOException;
	use \Crackle\Utilities\Path;

	/**
	 * Represents the data returned by a request.
	 * @author George Brighton
	 */
	class Response {

		/**
		 * The final URL reached, after any redirects - this may not match the original requested URL.
		 * @var string
		 */
		private $url;

		/**
		 * The HTTP status code returned by the server.
		 * @var int
		 */
		private $responseCode;

		/**
		 * The response body.
		 * @var string
		 */
		private $content;

		/**
		 * Get the effective URL of this resource.
		 * @return string		The final URL.
		 */
		public final function getUrl() {
			return $this->url;
		}

		/**
		 * Set the effective URL of this resource.
		 * @param string $url		The final URL.
		 */
		private final function setUrl($url) {
			$this->url = (string)$url;
		}

		/**
		 * Get the HTTP status code returned by the destination server.
		 * @return int			The status code.
		 */
		public final function getResponseCode() {
			return $this->responseCode;
		}

		/**
		 * Set the HTTP status code returned by the destination server.
		 * @param int $responseCode			The status code.
		 */
		private final function setResponseCode($responseCode) {
			$this->responseCode = (int)$responseCode;
		}

		/**
		 * Get the raw body of this response.
		 * @return string		The body.
		 */
		public final function getContent() {
			return $this->content;
		}

		/**
		 * Set the raw body of this response.
		 * @param string $content		The body.
		 */
		private final function setContent($content) {
			$this->content = (string)$content;
		}

		/**
		 * Forgets the content body of this response.
		 * This method can be called in the request callback to free up some memory when downloading lots of large files.
		 */
		public final function clearContent() {
			$this->content = null;
		}

		/**
		 * Get an instance of this class representing the response of an executed cURL handle.
		 * @param resource $handle				The executed handle to import.
		 * @return \Crackle\Response			The created Response instance.
		 */
		public static function factory($handle) {
			$response = new Response();
			$response->import($handle);
			return $response;
		}

		/**
		 * Make this response represent an executed cURL handle.
		 * @param resource $handle			An executed cURL handle.
		 */
		private function import($handle) {
			$this->setUrl(curl_getinfo($handle, CURLINFO_EFFECTIVE_URL));
			$this->setResponseCode(curl_getinfo($handle, CURLINFO_HTTP_CODE));
			$this->setContent(curl_multi_getcontent($handle));
		}

		/**
		 * Retrieve the name of this resource from its URL.
		 * @return string			The name of the resource returned by the request.
		 */
		public function getFilename() {
			return basename(parse_url($this->getUrl(), PHP_URL_PATH));
		}

		/**
		 * Write this resource to a file.
		 * @param string $directory			The directory to write to.
		 * @param string $name				The name of the file to write. If omitted, the original name will be used.
		 * @throws IOException				If the file cannot be written.
		 */
		public function writeTo($directory, $name = null) {
			if ($name === null) {
				$name = $this->getFilename();
			}

			if (!is_writable($directory)) {
				throw new IOException('Insufficient permissions to write to directory.');
			}

			if (!@file_put_contents(Path::join($directory, $name), $this->getContent())) {
				throw new IOException('Failed to write file.');
			}
		}
	}
}