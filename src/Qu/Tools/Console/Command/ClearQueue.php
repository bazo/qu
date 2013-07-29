<?php

namespace Qu\Tools\Console\Command;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * QueueInfo
 * @author Martin Bažík <martin.bazik@fatchilli.com>
 */
class ClearQueue extends Console\Command\Command
{

	/** @var \Qu\QueueManager */
	private $qm;


	/**
	 * @param \Qu\QueueManager $qm
	 */
	public function __construct(\Qu\QueueManager $qm)
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
			->setName('qu:queue:clear')
			->addArgument('queue', InputArgument::OPTIONAL, 'which queue to clear')
			->setDescription('clear messages from queue')
		;
	}


	/**
	 * @param Console\Input\InputInterface $input
	 * @param Console\Output\OutputInterface $output
	 * @return void
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$queue = $input->getArgument('queue');
		if ($queue === null) {
			$queues = $this->qm->listQueues();

			$dialog = $this->getDialogHelper();
			$selection = $dialog->select(
					$output,
					'Please select a queue',
					$queues, $default = NULL,
					$attempts = FALSE,
					'Value "%s" is invalid',
					$multi = FALSE
			);

			$queue = $queues[$selection];
		}

		$this->qm->clearQueue($queue);
	}


	/**
	 * @return Console\Helper\DialogHelper
	 */
	protected function getDialogHelper()
	{
		return new Console\Helper\DialogHelper;
	}


}

