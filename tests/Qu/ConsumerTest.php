<?php

namespace Qu;

use \PHPUnit_Framework_TestCase;

class ConsumerTest extends PHPUnit_Framework_TestCase
{

	protected $fixture;
	protected $stub;


	public function setup()
	{
		$this->stub = $this->getMockBuilder('Qu\QueueManager')
				->disableOriginalConstructor()
				->getMock();
		$this->fixture = new Consumer($this->stub);
	}


	/**
	 * @covers Qu\Consumer::addCallback
	 */
	public function testAddCallback()
	{
		$func = function () {
			return true;
		};
		$result = $this->fixture->addCallback($func);
		$this->assertSame($this->fixture, $result);
		$callback = current($result->callbacks);
		$this->assertTrue($callback());
	}


	/**
	 * @expectedExceptionMessage callback executed
	 * @expectedException Exception
	 * @covers Qu\Consumer::consume
	 * @covers Qu\Consumer::fireCallbacks
	 */
	public function testConsumeFireCallbacks()
	{
		$qm = $this->getMockBuilder('Qu\QueueManager')
				->disableOriginalConstructor()
				->getMock();

		$message = $this->getMock('Qu\Message', ['isPropagationStopped'], ['message']);
		$message->expects($this->atLeastOnce())
				->method('isPropagationStopped')
				->will($this->returnValue(false));

		$qm->expects($this->atLeastOnce())
				->method('getMessage')
				->with('queue', 10)
				->will($this->returnValue($message));

		$this->fixture = new Consumer($qm);
		$func = function ($arg) {
			throw new \Exception('callback executed');
		};
		$this->fixture->addCallback($func);

		$this->fixture->consume('queue', 10);
	}


	/**
	 * @expectedExceptionMessage published
	 * @expectedException Exception
	 * @covers Qu\Consumer::consume
	 */
	public function testConsumePublish()
	{
		$qm = $this->getMockBuilder('Qu\QueueManager')
				->disableOriginalConstructor()
				->getMock();

		$message = $this->getMock('Qu\Message', ['isRequeued'], ['message']);
		$message->expects($this->any())
				->method('isRequeued')
				->will($this->returnValue(true));

		$qm->expects($this->any())
				->method('getMessage')
				->with('queue', 10)
				->will($this->returnValue($message));

		$qm->expects($this->any())
				->method('publishMessage')
				->with('queue', $message)
				->will($this->throwException(new \Exception('published')));

		$this->fixture = new Consumer($qm);

		$this->fixture->consume('queue', 10);
	}


	/**
	 * @expectedExceptionMessage no callback executed
	 * @expectedException Exception
	 * @covers Qu\Consumer::fireCallbacks
	 */
	public function testConsumeFireCallbacksPropagationStopped()
	{
		$qm = $this->getMockBuilder('Qu\QueueManager')
				->disableOriginalConstructor()
				->getMock();

		$message = $this->getMock('Qu\Message', ['isRequeued', 'isPropagationStopped'], ['message']);
		$message->expects($this->any())
				->method('isRequeued')
				->will($this->returnValue(true));

		$message->expects($this->any())
				->method('isPropagationStopped')
				->will($this->returnValue(true));

		$qm->expects($this->any())
				->method('getMessage')
				->with('queue', 10)
				->will($this->returnValue($message));

		$qm->expects($this->any())
				->method('publishMessage')
				->with('queue', $message)
				->will($this->throwException(new \Exception('no callback executed')));

		$this->fixture = new Consumer($qm);

		$func = function ($arg) {
			throw new \Exception('should not be executed');
		};
		$this->fixture->addCallback($func);
		$this->fixture->consume('queue', 10);
	}


}