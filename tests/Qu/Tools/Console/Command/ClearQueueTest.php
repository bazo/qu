<?php

namespace Qu\Tools\Console\Command;
use \PHPUnit_Framework_TestCase;


class ClearQueueTest extends PHPUnit_Framework_TestCase
{

	protected $qm;

	/**
	 *
	 */
	public function setup() {
		$this->qm = $this->getMockBuilder('Qu\QueueManager')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @covers Qu\Tools\Console\Command\ClearQueue::__construct
	 */
	public function testInitialize() {
		$clearQueue = $this->getMockBuilder('Qu\Tools\Console\Command\ClearQueue')
			->disableOriginalConstructor()
			->setMethods(['configure'])
			->getMock();

		$clearQueue->expects($this->once())
			->method('configure');
		$clearQueue->setName('avoiding exception for parent constructor');

		$clearQueue->__construct($this->qm);
	}

	/**
	 * @expectedException LogicException
	 * @covers Qu\Tools\Console\Command\ClearQueue::configure
	 */
	public function testConfigure() {
		$clearQueue = $this->getMockBuilder('Qu\Tools\Console\Command\ClearQueue')
			->disableOriginalConstructor()
			->setMethods(['setName', 'addArgument', 'setDescription'])
			->getMock();

		$clearQueue->expects($this->once())
			->method('setName')
			->with('qu:queue:clear')
			->will($this->returnValue($clearQueue));
		$clearQueue->expects($this->once())
			->method('addArgument')
			->with('queue', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'which queue to clear')
			->will($this->returnValue($clearQueue));
		$clearQueue->expects($this->once())
			->method('setDescription')
			->with('clear messages from queue');

		$clearQueue->__construct($this->qm);
	}

	/**
	 * @covers Qu\Tools\Console\Command\ClearQueue::execute
	 */
	public function testExecute() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

		$input->expects($this->once())
			->method('getArgument')
			->will($this->returnValue('onlyclear'));

		$this->qm->expects($this->never())
			->method('listQueues');

		$this->qm->expects($this->once())
			->method('clearQueue')
			->with('onlyclear');
		$clearQueue = new ClearQueue($this->qm);
		$clearQueue->run($input, $output);
	}

	/**
	 * @covers Qu\Tools\Console\Command\ClearQueue::execute
	 */
	public function testExecuteSelectQueue() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
		$dialogHelper = $this->getMock('Symfony\Component\Console\Helper\DialogHelper');

		$input->expects($this->once())
			->method('getArgument')
			->will($this->returnValue(null));

		$dialogHelper->expects($this->once())
			->method('select')
			->will($this->returnValue(0));

		$this->qm->expects($this->once())
			->method('listQueues')
			->will($this->returnValue(['selected']));

		$this->qm->expects($this->once())
			->method('clearQueue')
			->with('selected');

		$clearQueue = $this->getMock('Qu\Tools\Console\Command\ClearQueue', ['getDialogHelper'], [$this->qm]);
		$clearQueue->expects($this->once())
			->method('getDialogHelper')
			->will($this->returnValue($dialogHelper));

		$clearQueue->run($input, $output);
	}
}