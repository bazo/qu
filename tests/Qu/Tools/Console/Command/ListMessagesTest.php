<?php

namespace Qu\Tools\Console\Command;
use \PHPUnit_Framework_TestCase;


class ListMessagesTest extends PHPUnit_Framework_TestCase
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
	 * @covers Qu\Tools\Console\Command\ListMessages::__construct
	 */
	public function testInitialize() {
		$listMessages = $this->getMockBuilder('Qu\Tools\Console\Command\ListMessages')
			->disableOriginalConstructor()
			->setMethods(['configure'])
			->getMock();

		$listMessages->expects($this->once())
			->method('configure');
		$listMessages->setName('avoiding exception for parent constructor');

		$listMessages->__construct($this->qm);
	}

	/**
	 * @expectedException LogicException
	 * @covers Qu\Tools\Console\Command\ListMessages::configure
	 */
	public function testConfigure() {
		$listMessages = $this->getMockBuilder('Qu\Tools\Console\Command\ListMessages')
			->disableOriginalConstructor()
			->setMethods(array('setName', 'addArgument', 'addOption', 'setDescription'))
			->getMock();

		$listMessages->expects($this->once())
			->method('setName')
			->with('qu:messages:list')
			->will($this->returnValue($listMessages));
		$listMessages->expects($this->once())
			->method('addArgument')
			->with('queue', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'which queue to show messages for')
			->will($this->returnValue($listMessages));
		$listMessages->expects($this->once())
			->method('addOption')
			->with('pretty', 'p', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'pretty print?')
			->will($this->returnValue($listMessages));
		$listMessages->expects($this->once())
			->method('setDescription')
			->with('list messages in queue');

		$listMessages->__construct($this->qm);
	}

	/**
	 * @covers Qu\Tools\Console\Command\ListMessages::execute
	 */
	public function testExecute() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

		$input->expects($this->once())
			->method('getArgument')
			->with('queue')
			->will($this->returnValue('queuename'));
		$input->expects($this->once())
			->method('getOption')
			->with('pretty')
			->will($this->returnValue(false));

		$output->expects($this->at(0))
			->method('writeln')
			->with('<info>1</info> messages in queue <info>queuename</info>');
		$output->expects($this->at(1))
			->method('writeln')
			->with('"message"');

		$this->qm->expects($this->never())
			->method('listQueues');

		$this->qm->expects($this->once())
			->method('listQueueMessages')
			->with('queuename')
			->will($this->returnValue(array('"message"')));
		$listMessages = new ListMessages($this->qm);
		$listMessages->run($input, $output);
	}

	/**
	 * @covers Qu\Tools\Console\Command\ListMessages::execute
	 */
	public function testExecuteSelectQueue() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
		$dialogHelper = $this->getMock('Symfony\Component\Console\Helper\DialogHelper');

		$input->expects($this->once())
			->method('getArgument')
			->with('queue')
			->will($this->returnValue(null));
		$input->expects($this->once())
			->method('getOption')
			->with('pretty')
			->will($this->returnValue(false));

		$this->qm->expects($this->once())
			->method('listQueues')
			->will($this->returnValue(['selected']));

		$dialogHelper->expects($this->once())
			->method('select')
			->will($this->returnValue(0));

		$this->qm->expects($this->once())
			->method('listQueueMessages')
			->with('selected')
			->will($this->returnValue(['"message"']));

		$output->expects($this->at(0))
			->method('writeln')
			->with('<info>1</info> messages in queue <info>selected</info>');
		$output->expects($this->at(1))
			->method('writeln')
			->with('"message"');

		$listMessages = $this->getMock('Qu\Tools\Console\Command\ListMessages', ['getDialogHelper'], [$this->qm]);
		$listMessages->expects($this->once())
			->method('getDialogHelper')
			->will($this->returnValue($dialogHelper));

		$listMessages->run($input, $output);
	}

	/**
	 * @covers Qu\Tools\Console\Command\ListMessages::execute
	 */
	public function testExecutePretty() {
		$input = $this->GetMock('Symfony\Component\Console\Input\InputInterface');
		$output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

		$input->expects($this->once())
			->method('getArgument')
			->with('queue')
			->will($this->returnValue('queuename'));
		$input->expects($this->once())
			->method('getOption')
			->with('pretty')
			->will($this->returnValue(true));

		$this->qm->expects($this->once())
			->method('listQueueMessages')
			->with('queuename')
			->will($this->returnValue(['"message"']));

		$output->expects($this->at(1))
			->method('writeln')
			->with('');
		$this->expectOutputString("- message\n");

		$listMessages = new ListMessages($this->qm);
		$listMessages->run($input, $output);
	}

	/**
	 * @dataProvider hashMaps
	 * @covers Qu\Tools\Console\Command\ListMessages::format
	 * @covers Qu\Tools\Console\Command\ListMessages::isAssocArray
	 */
	public function testFormat($hashmap, $indent, $expected) {
		$listMessages = new ListMessages($this->qm);
		$result = $listMessages->format($hashmap, $indent);
		$this->assertSame($expected, $result);
	}

	/**
	 * Data provider for testFormat
	 *
	 * @return array
	 */
	public function hashMaps() {
		return [
			[[], 0, ''],
			[['numeric'], 0, "- numeric\n"],
			[['key' => 'value'], 0,	"key: value\n"],
			[['key' => ['deeper' => 'nested']],	0, "key:\n  deeper: nested\n"],
			[['numeric'], 2, "    - numeric\n"],
			[['key' => 'value'], 2, "    key: value\n"],
			[['key' => ['deeper' => 'nested']],	2, "    key:\n      deeper: nested\n"]
		];
	}

}