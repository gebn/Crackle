<?php

namespace Crackle {

	require_once('Exceptions/CurlException.php');
	require_once('Requests/Request.php');

	use \Crackle\Exceptions\CurlException;
	use \Crackle\Requests\Request;

	use \SplQueue;

	/**
	 * Manages parallel execution of HTTP requests.
	 * @author George Brighton
	 */
	class Requester {

		/**
		 * The maximum number of requests allowed to be executing simultaneously.
		 * @var int
		 */
		private $parallelLimit = 40;

		/**
		 * The list of requests still to execute. These will be executed in the order they were added (FIFO).
		 * @var \SplQueue
		 */
		private $queue;

		/**
		 * A lookup array of resource ID => request object. Used to retrieve the request object when a handle is finished.
		 * @var array
		 */
		private $executing;

		/**
		 * The cURL multi handle used for executing requests simultaneously.
		 * @var resource
		 */
		private $multiHandle;

		/**
		 * Get the maximum number of requests allowed to be executing simultaneously.
		 * @return int		The maximum number of requests allowed to be executing simultaneously.
		 */
		private final function getParallelLimit() {
			return $this->parallelLimit;
		}

		/**
		 * Set the maximum number of requests allowed to be executing simultaneously.
		 * @param int $parallelLimit		The maximum number of requests allowed to be executing simultaneously.
		 */
		private final function setParallelLimit($parallelLimit) {
			$this->parallelLimit = (int)$parallelLimit;
		}

		/**
		 * Get the queue of requests still to execute.
		 * @return \SplQueue			The queue of requests still to execute.
		 */
		private final function getQueue() {
			return $this->queue;
		}

		/**
		 * Set the queue of requests still to execute.
		 * @param \SplQueue $queue		The queue of requests still to execute.
		 */
		private final function setQueue(SplQueue $queue) {
			$this->queue = $queue;
		}

		/**
		 * Retrieve a request object from the executing array.
		 * @param resource $handle					The handle of the request to retrieve.
		 * @return \Crackle\Requests\Request		The corresponding request.
		 */
		private final function getExecutingRequest($handle) {
			return $this->executing[(int)$handle];
		}

		/**
		 * Set the lookup array of currently executing requests.
		 * @param array[\Crackle\Requests\Request] $executing		The lookup array of currently executing requests.
		 */
		private final function setExecuting(array $executing) {
			$this->executing = $executing;
		}

		/**
		 * Get the cURL multi handle used to execute requests queued to this object.
		 * @return resource		The cURL multi handle.
		 */
		private final function getMultiHandle() {
			return $this->multiHandle;
		}

		/**
		 * Set the cURL multi handle used to execute requests queued to this object.
		 * @param resource $multiHandle		The cURL multi handle.
		 */
		private final function setMultiHandle($multiHandle) {
			$this->multiHandle = $multiHandle;
		}

		/**
		 * Initialise a new requester instance.
		 * @param int $parallelLimit		The maximum number of requests to allow to run simultaneously. Default: 40.
		 */
		public function __construct($parallelLimit = null) {
			if($parallelLimit !== null) {
				$this->setParallelLimit($parallelLimit);
			}
			$this->setQueue(new SplQueue());
			$this->setExecuting(array());
			$this->setMultiHandle(curl_multi_init());
		}

		/**
		 * Disposes of this requester instance.
		 */
		public function __destruct() {
			curl_multi_close($this->getMultiHandle());
		}

		/**
		 * Schedule a request for execution.
		 * @param \Crackle\Requests\Request $request		The request to queue.
		 */
		public function queue(Request $request) {
			$this->getQueue()->enqueue($request);
		}

		/**
		 * Schedule multiple requests for execution.
		 * @param array[\Crackle\Requests\Request] $requests		The requests to queue.
		 */
		public final function queueAll(array $requests) {
			foreach($requests as $request) {
				$this->queue($request);
			}
		}

		/**
		 * Execute a single request immediately.
		 * @param \Crackle\Requests\Request $request		The request to send.
		 */
		public static function fire(Request $request) {
			$request->finalise();
			curl_exec($request->getHandle());
			$request->recover(curl_errno($request->getHandle()));
		}

		/**
		 * Execute the current queue simultaneously.
		 * @throws \CurlException			If the multi handle returns an error at any stage.
		 */
		public function fireAll() {
			// reduce the parallel limit if it is greater than the size of the queue
			$length = $this->getQueue()->count();
			if($length < $this->getParallelLimit()) {
				$this->setParallelLimit($length);
			}

			$this->add($this->getParallelLimit()); // fill the handle with initial requests to run
			$multiHandle = $this->getMultiHandle(); // optimisation: avoids many calls to $this->getMultiHandle()
			$running = null;

			do {
				while (($status = curl_multi_exec($multiHandle, $running)) == CURLM_CALL_MULTI_PERFORM);

				// if the multi handle is in an error state, stop
				if ($status !== CURLM_OK) {
					throw new CurlException(curl_strerror($status));
				}

				// deal with finished requests
				while ($done = curl_multi_info_read($multiHandle)) {
					$this->finished($done);
				}

				// block until something happens
				if ($running) {
					curl_multi_select($multiHandle);
				}
			} while ($running);
		}

		/**
		 * Moves requests from the queue to the multi handle.
		 * @param int $number		Optional: the number of requests to remove. Default: 1.
		 */
		private function add($number = 1) {
			for($i = 0; $i < $number; $i++) {
				$request = $this->getQueue()->dequeue();
				$request->finalise();
				$this->executing[(int)$request->getHandle()] = $request;
				curl_multi_add_handle($this->getMultiHandle(), $request->getHandle());
			}
		}

		/**
		 * Removes a finished request from the multi handle.
		 * @param \Crackle\Requests\Request $request		The request to remove.
		 */
		private function remove(Request $request) {
			unset($this->executing[(int)$request->getHandle()]);
			curl_multi_remove_handle($this->getMultiHandle(), $request->getHandle());
		}

		/**
		 * Deals with a finished request, and adds a new one if the queue isn't empty.
		 * @param array[string] $message			The message array returned by curl_multi_info_read().
		 */
		private function finished(array $message) {
			$request = $this->getExecutingRequest($message['handle']);
			$request->recover($message['result']);
			if(!$this->getQueue()->isEmpty()) {
				$this->add();
			}
			$this->remove($request);
		}
	}
}