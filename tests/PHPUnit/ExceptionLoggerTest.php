<?php

use Chevron\ErrHandler\ExceptionLogger;

class TestLog extends \Psr\Log\AbstractLogger {
	protected $container;
	function log($level, $message, array $context = []){
		$this->container = "{$level}|{$message}|" . count($context);
	}
	function getLog(){
		return $this->container;
	}
}

class ExceptionLoggerTest extends PHPUnit_Framework_TestCase {

	function test___invoke(){
		$inst = new ExceptionLogger;
		$logger = new TestLog;
		$inst->setLogger($logger);

		$firstE  = new \Exception("First Exception", 24);
		$secondE = new \Exception("Second Exception", 25, $firstE);

		$inst($secondE, ["additional" => "info"]);

		$this->assertEquals($logger->getLog(), "error|25|11");

	}


}