<?php

require_once __DIR__.'/../vendor/autoload.php';

$console = new Symfony\Component\Console\Application;

$redis = new Kdyby\Redis\RedisClient($host = '127.0.0.1', $port = 6379, $database = 15);
$qm = new Qu\QueueManager($redis);

$payload = ['id' => uniqid(), 'message' => 'test'];

$message = new \Qu\Message($payload);
$qm->publishMessage('test', $message);

$commands = [
	new \Qu\Tools\Console\Command\QueueInfo($qm),
	new \Qu\Tools\Console\Command\ListMessages($qm),
	new Qu\Tools\Console\Command\ClearQueue($qm)
];

foreach($commands as $command) {
	$console->add($command);
}

$console->run();