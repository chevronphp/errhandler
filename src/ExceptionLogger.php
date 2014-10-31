<?php

namespace Chevron\ErrHandler;
use Psr\Log;
/**
 * a simple error to exception convertor inspired by corpus/autoloader
 *
 * @package Chevron\Error
 */
class ExceptionLogger implements ExceptionLoggerInterface {

	use Log\LoggerAwareTrait;

	/**
	 * invokable proxy
	 * @param \Exception $e
	 * @param array $context
	 * @return
	 */
	function __invoke(\Exception $e, array $context = []){
		$this->logException($e, $context);
	}

	/**
	 * create a flat key => value array of an exception and it's getPrevious() values
	 * @param \Exception $e
	 * @param array $context
	 * @return
	 */
	function logException(\Exception $e, array $context = []){

		if($this->logger InstanceOf Log\LoggerInterface){
			$error = [
				"e.type"      => get_class($e),
				"e.message"   => $e->getMessage(),
				"e.code"      => $e->getCode(),
				"e.file"      => $e->getFile(),
				"e.line"      => $e->getLine(),
			];

			$error = $error + $context;

			$i = 1;
			$prev = $e->getPrevious();
			while($prev && $i){
				$error += [
					"e.prev.[{$i}].type"    => get_class($prev),
					"e.prev.[{$i}].message" => $prev->getMessage(),
					"e.prev.[{$i}].code"    => $prev->getCode(),
					"e.prev.[{$i}].file"    => $prev->getFile(),
					"e.prev.[{$i}].line"    => $prev->getLine(),
				];
				$prev = $prev->getPrevious();
				$i += 1;
			}

			$this->logger->error($e->getCode(), $error);
		}
	}
}