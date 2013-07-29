<?php

namespace Qu;

/**
 * Description of Message
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class Message implements \JsonSerializable
{

	/** @var int */
	private $timestamp;

	/** @var string */
	private $payload;

	/** @var bool */
	private $requeued = FALSE;

	/**
	 * @var Boolean Whether no further event listeners should be triggered
	 */
	private $propagationStopped = FALSE;

	/**
	 * @param string $payload
	 */
	function __construct($payload)
	{
		$this->payload = $payload;
	}

	/**
	 * @return string
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * Stops the propagation of the message to further callbacks.
	 *
	 * If multiple callbacks are connected to the same queue, no
	 * further callbacks will be triggered once any consumer calls
	 * stopPropagation().
	 *
	 * @return void
	 */
	public function stopPropagation()
	{
		$this->propagationStopped = TRUE;
	}

	/**
	 * @return bool
	 */
	public function isPropagationStopped()
	{
		return $this->propagationStopped;
	}

	/**
	 * @return mixed|string
	 */
	public function jsonSerialize()
	{
		return $this->payload;
	}

	/**
	 * Requeue message
	 * @return Message
	 */
	public function requeue()
	{
		$this->requeued = TRUE;
		return $this;
	}

	/**
	 * If message is requeued
	 * @return bool
	 */
	public function isRequeued()
	{
		return $this->requeued;
	}

}