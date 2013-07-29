<?php

namespace Qu\Tools\Console\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Elastica\Client;

/**
 * QueueInfo
 * @author Martin Bažík <martin.bazik@fatchilli.com>
 */
class QueueInfo extends Console\Command\Command
{

	/** @var \Qu\QueueManager */
	private $qm;

	/**
	 * @param \Qu\QueueManager $qm
	 */
	function __construct(\Qu\QueueManager $qm)
	{
		$this->qm = $qm;
		parent::__construct(NULL);
	}

	/**
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('qu:queues:info')
			->setDescription('shows info about queues')
		;
	}

	/**
	 * @param Console\Input\InputInterface $input
	 * @param Console\Output\OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$table = $this->getTableHelper();

		$output->writeln('Queues:');

		$queues = $this->qm->listQueues();

		$table->setHeaders(['queue', 'message count']);
		foreach ($queues as $queueName) {
			$messagesCount = count($this->qm->listQueueMessages($queueName));
			$table->addRow([$queueName, $messagesCount]);
		}
		
		$table->render($output);
		
	}

	protected function getTableHelper() {
		return new Console\Helper\TableHelper;
	}
}