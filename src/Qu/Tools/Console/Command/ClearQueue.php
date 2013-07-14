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
class ClearQueue extends Console\Command\Command
{

	/** @var \Qu\QueueManager */
	private $qm;

	function __construct(\Qu\QueueManager $qm)
	{
		$this->qm = $qm;
		parent::__construct(NULL);
	}


	protected function configure()
	{
		$this
			->setName('qu:queue:clear')
			->addArgument('queue', InputArgument::OPTIONAL, 'which queue to show messages for')
			->setDescription('clear messages from queue')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$table = new Console\Helper\TableHelper;
		
		$dialog = new Console\Helper\DialogHelper;
		
		$queue = $input->getArgument('queue');
		if($queue === NULL) {
			$queues = $this->qm->listQueues();
			
			$selection = $dialog->select(
				$output, 
				'Please select a queue', 
				$queues, 
				$default = NULL, 
				$attempts = FALSE, 'Value "%s" is invalid', 
				$multi = FALSE
			);
			
			$queue = $queues[$selection];
		}
		
		$this->qm->clearQueue($queue);
	}
	


}

