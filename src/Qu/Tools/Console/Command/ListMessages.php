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
class ListMessages extends Console\Command\Command
{

	/** @var \Qu\QueueManager */
	private $qm;

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
			->setName('qu:messages:list')
			->addArgument('queue', InputArgument::OPTIONAL, 'which queue to show messages for')
			->addOption('pretty', 'p', InputOption::VALUE_NONE, 'pretty print?')
			->setDescription('list messages in queue')
		;
	}

	/**
	 * @param Console\Input\InputInterface $input
	 * @param Console\Output\OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$queue = $input->getArgument('queue');
		$pretty = $input->getOption('pretty');
		if($queue === null) {
			$queues = $this->qm->listQueues();

			$dialog = $this->getDialogHelper();
			$selection = $dialog->select(
				$output, 
				'Please select a queue', 
				$queues, 
				$default = null,
				$attempts = false, 'Value "%s" is invalid',
				$multi = false
			);
			
			$queue = $queues[$selection];
		}
		
		$messages = $this->qm->listQueueMessages($queue);
		$output->writeln(sprintf('<info>%d</info> messages in queue <info>%s</info>', count($messages), $queue));
		
		foreach($messages as $message) {
			
			if($pretty) {
				echo $this->format((array)json_decode($message));
				$output->writeln('');
			} else {
				$output->writeln($message);
			}
		}
	}

	/**
	 * @param array $hashmap
	 * @param int $indentLevel
	 * @return string
	 */
	public function format(array $hashmap, $indentLevel = 0)
	{
		$prefix = str_repeat(' ', 2 * $indentLevel);
		$output = '';
		$isAssocArray = $this->isAssocArray($hashmap);
		foreach ($hashmap as $key => $value) {
			if (is_array($value)) {
				$output .= sprintf("$prefix%s\n", $isAssocArray ? "$key:" : '-');
				$output .= $this->format($value, $indentLevel + 1);
			} else {
				$output .= sprintf("$prefix%s\n", $isAssocArray ? "$key: $value" : "- $value");
			}
		}
		return $output;
	}

	/**
	 * @param $array
	 * @return bool
	 */
	private function isAssocArray($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * @return Console\Helper\DialogHelper
	 */
	protected function getDialogHelper() {
		return new Console\Helper\DialogHelper;
	}
}

