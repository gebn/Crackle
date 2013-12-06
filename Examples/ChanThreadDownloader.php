<?php

namespace Crackle\Examples {

	require_once('../Utilities/Path.php');
	require_once('../Requests/GETRequest.php');
	require_once('../Requester.php');

	use \Crackle\Utilities\Path;
	use \Crackle\Requests\GETRequest;
	use \Crackle\Requester;

	use \stdClass;
	use \Exception;

	$dl = new ChanThreadDownloader();
	$dl->parseUrl(''); // enter thread URL, e.g. http://boards.4chan.org/wg/res/1234567
	$dl->setOutputDirectory('');
	echo $dl->go() ? 'Success!' : $dl->getErrors() . ' error(s) occurred.';

	/**
 	 * Demonstrates Crackle's ability to run parallel GET requests by downloading all images in a 4Chan thread.
	 * @author George Brighton
	 */
	class ChanThreadDownloader {

		/**
		 * The name of the board that the thread is a member of.
		 * @var string
		 */
		private $board;

		/**
		 * The thread identifier (i.e. the post number of the first post within the thread).
		 * @var int
		 */
		private $thread;

		/**
		 * The absolute path of the directory to save images to.
		 * PHP must have permission to write to this folder.
		 * @var string
		 */
		private $outputDirectory;

		/**
		 * The number of errors that occurred during downloading.
		 * @var int
		 */
		private $errors;

		/**
		 * Retrieve the board name.
		 * @return string		The board name.
		 */
		private final function getBoard() {
			return $this->board;
		}

		/**
		 * Set the board name.
		 * @param string $board		The board name.
		 */
		public final function setBoard($board) {
			$this->board = (string)$board;
		}

		/**
		 * Get the thread number.
		 * @return int			The thread number.
		 */
		private final function getThread() {
			return $this->thread;
		}

		/**
		 * Set the thread number.
		 * @param int $thread		The thread number.
		 */
		public final function setThread($thread) {
			$this->thread = (int)$thread;
		}

		/**
		 * Get the save directory.
		 * @return string		The save directory.
		 */
		private final function getOutputDirectory() {
			return $this->outputDirectory;
		}

		/**
		 * Set the save directory.
		 * @param string $outputDirectory		The directory to save to. If this doesn't exist, an attempt will be made to create it.
		 * @throws Exception					If the specified output directory doesn't exist and cannot be created or is otherwise invalid.
		 */
		public final function setOutputDirectory($outputDirectory) {
			if(!is_dir($outputDirectory)) {
				if(!@mkdir($outputDirectory)) {
					throw new Exception('Failed to create output directory - check permissions.');
				}
			}
			$resolved = realpath($outputDirectory);
			if($resolved === false) {
				throw new Exception('Invalid output directory path: ' . $outputDirectory);
			}
			$this->outputDirectory = $resolved;
		}

		/**
		 * Get the number of errors that occurred while downloading images.
		 * @return int		The number of errors that occurred while downloading images.
		 */
		public final function getErrors() {
			return $this->errors;
		}

		/**
		 * Set the number of errors that occurred while downloading images.
		 * @param int $errors		The number of errors that occurred while downloading images.
		 */
		private final function setErrors($errors) {
			$this->errors = $errors;
		}

		/**
		 * Add 1 to the number of errors that occurred while downloading images.
		 */
		private final function incrementErrors() {
			$this->setErrors($this->getErrors() + 1);
		}

		/**
		 * Find the thread and board from the thread URL.
		 * @param string $url		The URL of the thread to download.
		 * @throws \Exception		If $url is invalid.
		 */
		public function parseUrl($url) {
			$matches = null;
			if(!preg_match('/\/([a-z]+)\/res\/([0-9]+)/', $url, $matches)) {
				throw new Exception('Invalid board URL: ' . $url);
			}
			$this->setBoard($matches[1]);
			$this->setThread($matches[2]);
		}

		/**
		 * Download images within the configured thread.
		 * @return boolean			True if no errors occurred, false otherwise.
		 */
		public function go() {
			$this->setErrors(0); // reset the error count
			$thread = json_decode($this->downloadThread());
			$urls = $this->getImageUrls($thread);
			$requests = $this->getRequests($urls);
			$this->download($requests);
			return $this->getErrors() == 0;
		}

		/**
		 * Get the URL that the API call to retrieve the JSON representation of the thread should be sent to.
		 * @return string		The API thread URL.
		 */
		private function getJsonUrl() {
			return 'http://a.4cdn.org/' . $this->getBoard() . '/res/' . $this->getThread() . '.json';
		}

		/**
		 * Retrieves the JSON representation of the thread to download.
		 * @throws \Exception		If the request to the 4Chan API fails.
		 * @return string			The JSON.
		 */
		private function downloadThread() {
			$request = new GETRequest($this->getJsonUrl());
			Requester::fire($request);
			if($request->isError() || $request->getResponse()->getResponseCode() != 200) {
				throw new Exception('Error retrieving JSON: ' . $request->getError());
			}
			return $request->getResponse()->getContent();
		}

		/**
		 * Extract the URLs of images in the thread that have not yet been downloaded.
		 * @param array $thread			The json_decode()d thread.
		 * @return array[string]		The image URLs.
		 */
		private function getImageUrls(stdClass $thread) {
			$urls = array();
			foreach($thread->posts as $post) {
				if(isset($post->filename)) {
					$filename = $post->tim . $post->ext;
					$url = 'http://i.4cdn.org/' . $this->getBoard() . '/src/' . $filename;
					if(!file_exists(Path::join($this->getOutputDirectory(), $filename))) {
						$urls[] = $url;
					}
				}
			}
			return $urls;
		}

		/**
		 * Turns an array of URLs into an array of GETRequest objects.
		 * @param array $urls								The image URLs to download.
		 * @return array[\Crackle\Requests\GETRequest]		The corresponding request objects for each URL.
		 */
		private function getRequests(array $urls) {
			$requests = array();
			foreach($urls as $url) {
				$request = new GETRequest($url);
				$request->setCallback(function($request) {
					if($request->isError()) {
						$this->incrementErrors();
					}
					else {
						$request->getResponse()->writeTo($this->getOutputDirectory());
						$request->getResponse()->clearContent();
					}
				});
				$requests[] = $request;
			}
			return $requests;
		}

		/**
		 * Executes an array of requests.
		 * @param array[\Crackle\Requests\GETRequest] $requests		The requests to run.
		 */
		private function download(array $requests) {
			$requester = new Requester();
			$requester->queueAll($requests);
			$requester->fireAll();
		}
	}
}