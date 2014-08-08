<?php

class ErrorHandlerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \ErrorException
	 */
	function test_error_handler(){
		set_error_handler(new \Chevron\ErrHandler\ErrorHandler);
		trigger_error("This is a test error.");
		$this->assertEquals(1, 1);
	}

}