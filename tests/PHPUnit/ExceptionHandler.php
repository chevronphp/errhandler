<?php

use \Chevron\ErrHandler;

class ExceptionHandlerTest extends PHPUnit_Framework_TestCase {


	function test_exception_handler(){
		$exceptionhandler = new ErrHandler\ExceptionHandler(ErrHandler\ExceptionHandler::ENV_DEV);

		ob_start();
		$e = new \Exception("Uncaught Exception!!", 999);
		$exceptionhandler($e);
		$result = ob_get_clean();

		$expected = PHP_EOL.PHP_EOL;
		$expected .= __FILE__ .":". 12 . PHP_EOL;
		$expected .= str_repeat("=", 54) . PHP_EOL;
		$expected .= "(Type) Exception ** (Code) 999 ** (Severity) 999" . PHP_EOL.PHP_EOL;
		$expected .= "Uncaught Exception!!" . PHP_EOL;
		$expected .=  PHP_EOL . PHP_EOL;

		$this->assertEquals($expected, $result);

	}

}