<?php

namespace Qu;

/**
 * Message Queue Consumer
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class Consumer
{

	/** @var QueueManager */
	private $qm;

	public $queue;

	public $callbacks = array();

	/**
	 * @param QueueManager $qm
	 */
	public function __construct(QueueManager $qm)
	{
		$this->qm = $qm;
	}

	/**
	 * Bind a queue to listen on
	 * @param type $queue
	 * @return IronMQConsumer
	 */
	public function bindQueue($queue)
	{
		$this->queue = $queue;
		return $this;
	}

	/**
	 * Add a callback
	 * @param Callable $callback
	 * @return IronMQConsumer
	 */
	public function addCallback(Callable $callback)
	{
		$this->callbacks[spl_object_hash($callback)] = $callback;
		return $this;
	}

	/**
	 * @param $queue
	 * @param int $timeout
	 * @return void
	 */
	public function consume($queue, $timeout = 0)
	{
		while (TRUE) {
			$message = $this->qm->getMessage($queue, $timeout);
			if ($message !== NULL) {
				$this->fireCallbacks($message);

				if($message->isRequeued()) {
					$this->qm->publishMessage($queue, $message);
				}
			}
		}
	}

	/**
	 * @param Message $message
	 * @return void
	 */
	private function fireCallbacks(Message $message)
	{
		foreach ($this->callbacks as $callback) {
			if ($message->isPropagationStopped()) {
				return;
			}
			$callback($message);
		}
	}

}