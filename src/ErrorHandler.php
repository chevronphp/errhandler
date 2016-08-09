<?php

namespace Chevron\ErrHandler;
/**
 * a simple error to exception convertor inspired by corpus/autoloader
 *
 * @package Chevron\Error
 */
class ErrorHandler {

	/**
	 * @param int $errno The error level
	 * @param string $errstr The error message
	 * @param string $errfile The file of the error
	 * @param int $errline The line of the error
	 */
	function __invoke(){
		list($errno, $errstr, $errfile, $errline) = func_get_args();
		throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
	}
}