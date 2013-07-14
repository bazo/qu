<?php

namespace Qu;

use Kdyby\Redis\RedisClient;

/**
 * Description of QueueManager
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class QueueManager
{

	const QUEUE_KEY = 'queues';
	
	/** @var RedisClient */
	private $redis;


	/**
	 * @param \Kdyby\Redis\RedisClient $redis
	 */
	public function __construct(RedisClient $redis)
	{
		$this->redis = $redis;
	}


	/**
	 * Select database
	 * @param int $db
	 * @return QueueManager
	 * @throws \InvalidArgumentException
	 */
	public function selectDatabase($db)
	{
		if (!is_int($db) or ($db < 0) or ($db > 15)) {
			throw new \InvalidArgumentException('db must be an integer between 0 and 15');
		}
		$this->redis->select($db);
		return $this;
	}


	/**
	 * @param string $queue
	 * @param Message $message
	 * @return QueueManager
	 */
	public function publishMessage($queue, Message $message)
	{
		$this->redis->multi();
		$this->redis->sAdd('queues', $queue);
		$this->redis->rPush($this->formatQueueKey($queue), json_encode($message));
		$this->redis->exec();
		return $this;
	}


	/**
	 * @param string $queue
	 * @return Message $message
	 */
	public function getMessage($queue)
	{
		$payload = $this->redis->lPop($this->formatQueueKey($queue));
		if ($payload === FALSE) {
			return NULL;
		}
		return new Message((array)json_decode($payload));
	}

	
	public function listQueues()
	{
		$queues = $this->redis->sMembers(self::QUEUE_KEY);
		return $queues;
	}

	
	
	public function listQueueMessages($queue)
	{
		$messages = $this->redis->lRange($this->formatQueueKey($queue), 0, -1);
		return $messages;
	}
	
	
	
	/**
	 * @param string $queue
	 * @return QueueManager
	 */
	public function clearQueue($queue)
	{
		$this->redis->multi();
		$this->redis->sRem(self::QUEUE_KEY, $queue);
		$this->redis->lTrim($this->formatQueueKey($queue), 1, 0);
		$this->redis->exec();

		return $this;
	}


	private function formatQueueKey($queue)
	{
		return 'queue:' . $queue;
	}


}

