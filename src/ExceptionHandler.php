<?php

namespace Chevron\ErrHandler;

use \Psr\Log;
/**
 * a simple uncaught exception catcher inspired by corpus/autoloader
 *
 * @package Chevron\Exception
 */
class ExceptionHandler implements Log\LoggerAwareInterface {

	use Log\LoggerAwareTrait;

	/**
	 * the current environment
	 */
	protected $env;

	/**
	 * an optional logger
	 */
	// protected $logger;

	/**
	 * a dev environment
	 */
	const ENV_DEV   = 1;

	/**
	 * a staging environment
	 */
	const ENV_STAGE = 2;

	/**
	 * a production environment
	 */
	const ENV_PROD  = 3;

	/**
	 * error levels as strings
	 */
	protected $e_consts = [
		E_ERROR             => "E_ERROR",
		E_WARNING           => "E_WARNING",
		E_PARSE             => "E_PARSE",
		E_NOTICE            => "E_NOTICE",
		E_CORE_ERROR        => "E_CORE_ERROR",
		E_CORE_WARNING      => "E_CORE_WARNING",
		E_COMPILE_ERROR     => "E_COMPILE_ERROR",
		E_COMPILE_WARNING   => "E_COMPILE_WARNING",
		E_USER_ERROR        => "E_USER_ERROR",
		E_USER_WARNING      => "E_USER_WARNING",
		E_USER_NOTICE       => "E_USER_NOTICE",
		E_STRICT            => "E_STRICT",
		E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
		E_DEPRECATED        => "E_DEPRECATED",
		E_USER_DEPRECATED   => "E_USER_DEPRECATED",
		E_ALL               => "E_ALL",
	];

	/**
	 * @param int $env The current environment
	 * @param LoggerInterface $logger An optional logger
	 */
	function __construct($env, Log\LoggerInterface $logger = null){
		$this->env    = (int)$env;
		$this->logger = $logger;
	}

	/**
	 * @param Exception $e The thrown/uncaught exception
	 */
	function __invoke(){
		list($e) = func_get_args();

		$output = PHP_EOL.PHP_EOL;

		if(!($e InstanceOf \Exception)){
			// if we caught something that wasn't an exception, the world is ending.
			$output .= "Something VERY wrong is happening." . PHP_EOL;
			echo $output . PHP_EOL . PHP_EOL;
			exit(1);
		}

		$type = get_class($e);
		if($this->env >= static::ENV_STAGE){
			$type = substr($type, strrpos($type, "\\") + 1);
		}

		// use the error code unless it has a severity
		$severity = $e->getCode();
		if($e InstanceOf \ErrorException){
			$severity = $e->getSeverity();
		}

		// change the severity int for a descriptive string
		if(isset($this->e_consts[$severity])){
			$severity = $this->e_consts[$severity];
		}

		if($this->logger){
			$this->logger->error($type, [
				"message"  => $e->getMessage(),
				"severity" => $severity,
				"file"     => $e->getFile(),
				"line"     => $e->getLine(),
				"code"     => $e->getCode(),
			]);
		}

		// cleaup if we're not in DEV, strip the path off the file for security
		$file = $e->getFile();
		if($this->env != static::ENV_DEV){
			$file    = pathinfo($file, PATHINFO_BASENAME);
		}

		$line    = $e->getLine();
		$message = $e->getMessage();
		$code    = $e->getCode();

		$output .= "{$file}:{$line}" . PHP_EOL;
		$output .= "====================================" . PHP_EOL;
		$output .= "{$type} -- Code: {$code} -- Severity: {$severity}" . PHP_EOL . PHP_EOL;
		$output .= "{$message}" . PHP_EOL;

		echo $output . PHP_EOL . PHP_EOL;
		exit($e->getCode());

	}

}