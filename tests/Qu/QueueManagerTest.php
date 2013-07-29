<?php

namespace Qu;

use \PHPUnit_Framework_TestCase;

class QueueManagerTest extends PHPUnit_Framework_TestCase
{

	protected $fixture;


	public function setup()
	{
		$this->stub = $this->getMockBuilder('Kdyby\Redis\RedisClient')
				->disableOriginalConstructor()
				->setMethods(['select', 'multi', 'sAdd', 'rPush', 'exec', 'blPop', 'sMembers', 'lRange', 'sRem', 'lTrim'])
				->getMock();
		$this->fixture = new QueueManager($this->stub);
	}


	/**
	 * @expectedExceptionMessage db must be an integer between 0 and 15
	 * @expectedException InvalidArgumentException
	 * @param $db
	 * @dataProvider argumentProvider
	 * @covers Qu\QueueManager::selectDatabase
	 */
	public function testSelectDatabaseInvalidArguments($db)
	{
		$this->fixture->selectDatabase($db);
	}


	/**
	 * @covers Qu\QueueManager::selectDatabase
	 */
	public function testSelectDatabase()
	{
		$this->stub->expects($this->once())
				->method('select')
				->with(5);
		$result = $this->fixture->selectDatabase(5);
		$this->assertSame($this->fixture, $result);
	}


	/**
	 * @covers Qu\QueueManager::publishMessage
	 */
	public function testPublishMessage()
	{
		$message = $this->getMock('Qu\Message', null, array('payload'));

		$this->stub->expects($this->once())
				->method('multi');
		$this->stub->expects($this->once())
				->method('sAdd')
				->with('queues', 'queue');
		$this->stub->expects($this->once())
				->method('rPush')
				->with('queue:queue', '"payload"');
		$this->stub->expects($this->once())
				->method('exec');

		$result = $this->fixture->publishMessage('queue', $message);
		$this->assertSame($this->fixture, $result);
	}


	/**
	 * @covers Qu\QueueManager::formatQueueKey
	 */
	public function testPublishMessageFormatQueueKey()
	{
		$message = $this->getMock('Qu\Message', null, ['payload']);

		$this->stub->expects($this->once())
				->method('rPush')
				->with('queue:queue', '"payload"');
		$this->fixture->publishMessage('queue', $message);
	}


	/**
	 * @covers Qu\QueueManager::getMessage
	 */
	public function testGetMessageEmpty()
	{
		$this->stub->expects($this->once())
				->method('blPop')
				->with('queue:queue', 5)
				->will($this->returnValue(null));

		$this->assertNull($this->fixture->getMessage('queue', 5));
	}


	/**
	 * @covers Qu\QueueManager::getMessage
	 */
	public function testGetMessage()
	{
		$payload = array(1 => '"payload"');
		$this->stub->expects($this->once())
				->method('blPop')
				->with('queue:queue', 5)
				->will($this->returnValue($payload));

		$result = $this->fixture->getMessage('queue', 5);
		$this->assertInstanceOf('Qu\Message', $result);
		$this->assertSame('["payload"]', json_encode($result));
	}


	/**
	 * @covers Qu\QueueManager::listQueues
	 */
	public function testListQueues()
	{
		$this->stub->expects($this->once())
				->method('sMembers')
				->with('queues')
				->will($this->returnValue('queues'));

		$this->assertSame('queues', $this->fixture->listQueues());
	}


	/**
	 * @covers Qu\QueueManager::listQueueMessages
	 */
	public function testListQueueMessages()
	{
		$this->stub->expects($this->once())
				->method('lRange')
				->with('queue:queue', 0, -1)
				->will($this->returnValue('messages'));

		$this->assertSame('messages', $this->fixture->listQueueMessages('queue'));
	}


	/**
	 * @covers Qu\QueueManager::clearQueue
	 */
	public function testClearQueue()
	{
		$this->stub->expects($this->once())
				->method('multi');
		$this->stub->expects($this->once())
				->method('sRem')
				->with('queues', 'queue');
		$this->stub->expects($this->once())
				->method('lTrim')
				->with('queue:queue', 1, 0);
		$this->stub->expects($this->once())
				->method('exec');

		$result = $this->fixture->clearQueue('queue');
		$this->assertSame($this->fixture, $result);
	}


	/*
	 * Data provider for testSelectDatabaseInvalidArguments
	 */

	public function argumentProvider()
	{
		return [
			['string'],
			[false],
			[true],
			[-1],
			[16]
		];
	}


}