<?php

namespace Qu;

use \PHPUnit_Framework_TestCase;

class MessageTest extends PHPUnit_Framework_TestCase
{

	protected $fixture;


	public function setup()
	{
		$this->fixture = new Message('testpayload');
	}


	/**
	 * @covers Qu\Message::__construct
	 * @covers Qu\Message::getPayload
	 * @covers Qu\Message::jsonSerialize
	 */
	public function testPayload()
	{
		$this->assertSame('testpayload', $this->fixture->getPayload());
		$this->assertSame('"testpayload"', json_encode($this->fixture));
	}


	/**
	 * @covers Qu\Message::isPropagationStopped
	 * @covers Qu\Message::stopPropagation
	 */
	public function testPropagation()
	{
		$this->assertFalse($this->fixture->isPropagationStopped());
		$result = $this->fixture->stopPropagation();
		$this->assertTrue($this->fixture->isPropagationStopped());
	}


	/**
	 * @covers Qu\Message::isRequeued
	 * @covers Qu\Message::requeue
	 */
	public function testRequeue()
	{
		$this->assertFalse($this->fixture->isRequeued());
		$result = $this->fixture->requeue();
		$this->assertInstanceOf('Qu\Message', $result);
		$this->assertTrue($this->fixture->isRequeued());
	}


}