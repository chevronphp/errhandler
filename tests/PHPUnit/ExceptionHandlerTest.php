<?php

use \Chevron\ErrHandler;

class ExceptionHandlerTest extends PHPUnit_Framework_TestCase {

	function throw_new_exception(){
		throw new \Exception("Uncaught Exception!!", 999);
	}

	/**
	 * @expectedException \Exception
	 */
	function test_exception_handler(){
		set_exception_handler(new ErrHandler\ExceptionHandler(ErrHandler\ExceptionHandler::ENV_DEV));

		ob_start();
		$this->throw_new_exception();
		$result = ob_get_clean();

		$expected = PHP_EOL.PHP_EOL;
		$expected .= __FILE__ .":". 8 . PHP_EOL;
		$expected .= "====================================" . PHP_EOL;
		$expected .= "(Type) Exception ** (Code) 999 ** (Severity) 999" . PHP_EOL;
		$expected .= "Uncaught Exception!!" . PHP_EOL;
		$expected .=  PHP_EOL . PHP_EOL;

		$this->assertEquals($expected, $result);

	}

	/**
	 * @expectedException \ErrorException
	 */
	function test_exception_handler_error(){
		set_error_handler(new \Chevron\ErrHandler\ErrorHandler);
		set_exception_handler(new ErrHandler\ExceptionHandler(ErrHandler\ExceptionHandler::ENV_DEV));

		ob_start();
		trigger_error("Uncaught ErrorException!!", E_USER_NOTICE);
		$result = ob_get_clean();

		$expected =  PHP_EOL. PHP_EOL;
		$expected .= __FILE__ .":". 40 . PHP_EOL;
		$expected .= "====================================" . PHP_EOL;
		$expected .= "(Type) ErrorException ** (Code) 90053 ** (Severity) " . E_USER_NOTICE . PHP_EOL . PHP_EOL;
		$expected .= "Uncaught ErrorException!!" . PHP_EOL;
		$expected .=  PHP_EOL . PHP_EOL;

		$this->assertEquals($expected, $result);

	}

}