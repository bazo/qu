<?php

namespace Qu\Tools\Console\Command;
use \PHPUnit_Framework_TestCase;


class QueueInfoTest extends PHPUnit_Framework_TestCase
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
	 * @covers Qu\Tools\Console\Command\QueueInfo::__construct
	 */
	public function testInitialize() {
		$queueInfo = $this->getMockBuilder('Qu\Tools\Console\Command\QueueInfo')
			->disableOriginalConstructor()
			->setMethods(['configure'])
			->getMock();

		$queueInfo->expects($this->once())
			->method('configure');
		$queueInfo->setName('avoiding exception for parent constructor');

		$queueInfo->__construct($this->qm);
	}

	/**
	 * @expectedException LogicException
	 * @covers Qu\Tools\Console\Command\QueueInfo::configure
	 */
	public function testConfigure() {
		$queueInfo = $this->getMockBuilder('Qu\Tools\Console\Command\QueueInfo')
			->disableOriginalConstructor()
			->setMethods(array('setName', 'setDescription'))
			->getMock();

		$queueInfo->expects($this->once())
			->method('setName')
			->with('qu:queues:info')
			->will($this->returnValue($queueInfo));

		$queueInfo->expects($this->once())
			->method('setDescription')
			->with('shows info about queues');

		$queueInfo->__construct($this->qm);
	}

	/**
	 * @covers Qu\Tools\Console\Command\QueueInfo::execute
	 */
	public function testExecute() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
		$tableHelper = $this->getMock('Symfony\Component\Console\Helper\TableHelper');

		$output->expects($this->once())
			->method('writeln')
			->with('Queues:');

		$this->qm->expects($this->once())
			->method('listQueues')
			->will($this->returnValue(['one', 'two']));

		$tableHelper->expects($this->once())
			->method('setHeaders')
			->with(['queue', 'message count']);

		$this->qm->expects($this->at(1))
			->method('listQueueMessages')
			->with('one')
			->will($this->returnValue(['"one, first message"', '"one, second message"']));

		$this->qm->expects($this->at(2))
			->method('listQueueMessages')
			->with('two')
			->will($this->returnValue(['"two, just one message"']));

		$tableHelper->expects($this->at(1))
			->method('addRow')
			->with(['one', 2]);
		$tableHelper->expects($this->at(2))
			->method('addRow')
			->with(['two', 1]);

		$tableHelper->expects($this->once())
			->method('render')
			->with($output);

		$queueInfo = $this->getMock('Qu\Tools\Console\Command\QueueInfo', ['getTableHelper'], [$this->qm]);
		$queueInfo->expects($this->once())
			->method('getTableHelper')
			->will($this->returnValue($tableHelper));

		$queueInfo->run($input, $output);
	}
}